<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2017, Carlos Henrique
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 ** Redistributions of source code must retain the above copyright notice, this
 *  list of conditions and the following disclaimer.
 *
 ** Redistributions in binary form must reproduce the above copyright notice,
 *  this list of conditions and the following disclaimer in the documentation
 *  and/or other materials provided with the distribution.
 *
 ** Neither the name of the copyright holder nor the names of its
 *  contributors may be used to endorse or promote products derived from
 *  this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace CHZApp;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \CHZApp\Interfaces\IApplication;

use \ReflectionClass;

/**
 * Classe padrão para os controllers da aplicação.
 *
 * @abstract
 */
abstract class Controller extends Component
{
    /**
     * Array com todas as rotas customizadas.
     * @var array
     */
    private $customRoutes;

    /**
     * Array com todas as restrições de rotas. Também se aplicam as rotas customizadas.
     * @var array
     */
    private $restrictionRoutes;

    /**
     * Array com todas as expressões de rotas.
     * @var array
     */
    private $regexRoutes;

    /**
     * Array com todas as rotas tratadas.
     * @var array
     */
    private $parsedRoutes;

    /**
     * Array com o conteúdo de $_GET
     * @var array
     */
    protected $get;

    /**
     * Array com o conteúdo de $_POST
     * @var array
     */
    protected $post;

    /**
     * Array com o conteúdo de $_FILES.
     * @var array
     */
    protected $files;

    /**
     * Construtor para a classe de controller.
     * Recebe os posts, get e arquivos.
     *
     * @param IApplication $application
     */
    public function __construct(IApplication $application)
    {
        // Inicializa as variaveis de rotas customizadas e
        // restrições de rota.
        $this->customRoutes = [];
        $this->restrictionRoutes = [];
        $this->regexRoutes = [];
        $this->parsedRoutes = [
            'GET' => [],
            'POST' => [],
            'PUT' => [],
            'PATCH' => [],
            'DELETE' => [],
        ];

        // Trata as rotas com comentários para poder
        // direcionar as rotas de outras formas.
        $this->parseDocRoutes();

        // Chama o construtor de componentes.
        parent::__construct($application);
    }

    /**
     * Realiza o tratamento das rotas que foram comentadas.
     *
     * @return void
     */
    private function parseDocRoutes()
    {
        $ref = new ReflectionClass($this);
        $methods = $ref->getMethods();

        foreach ($methods as $method) {
            $name = $method->getName();

            $doc = $method->getDocComment();
            if (empty($doc))
                continue;

            if (preg_match('/\@route\s([^\s]+)\s(GET|POST|PUT|PATCH|DELETE)/i', $doc, $match) == false)
                continue;

            // Dados de rota e tipo de requisição que serão tratados.
            $route = strtolower($match[1]);
            $method = strtoupper($match[2]);

            // Rota já estava antes na lista???
            // Pula a execução e passa para a próxima
            if (isset($this->parsedRoutes[$method][$route]) === true)
                continue;

            // Adiciona na memória a referência do método a ser executado.
            $this->parsedRoutes[$method][$route] = $name;
        }
    }

    /**
     * Renderiza o template e retorna os dados para a tela.
     *
     * @param ResponseInterface $response
     * @param string $template
     * @param array $data
     *
     * @return ResponseInterface
     */
    public function response(ResponseInterface $response, $template, $data = [])
    {
        // Renderiza e retorna os dados para a tela,
        return $this->getApplication()
                    ->getViewer()
                    ->response($response, $template, $data);
    }

    /**
     * Define todos os dados recebidos pelo controller.
     *
     * @param array $get
     * @param array $post
     * @param array $files
     */
    private function setReceivedData($get, $post, $files)
    {
        $this->get = $get;
        $this->post = $post;
        $this->files = $files;
    }

    /**
     * Aplica em todas as rotas somente pula algumas rotas.
     *
     * @param callback $callBack
     * @param array $skipRoutes
     *
     * @return void
     */
    final public function applyRestrictionOnAllRoutes($callBack, $skipRoutes = [])
    {
        foreach($this->getAllRoutes() as $route)
        {
            if(in_array($route, $skipRoutes))
                continue;
            $this->addRouteRestriction($route, $callBack);
        }

        return;
    }

    /**
     * Obtém todas as rotas para o controller
     *
     * @return array
     */
    final public function getAllRoutes()
    {
        $classMethods = get_class_methods(get_class($this));
        $routesInfo = [];
        foreach($classMethods as $method)
        {
            if(preg_match('/\_(GET|POST|PUT|PATCH|DELETE)$/', $method))
                $routesInfo[] = $method;
        }
        return $routesInfo;

    }

    /**
     * Adiciona a expressão regular.
     *
     * @param string $routeRegexp Expressão regular da rota.
     * @param string $routeSlim Rota que será tratada pelo slim.
     *
     * @return void
     */
    protected function addRouteRegexp($routeRegexp, $routeSlim)
    {
        $this->regexRoutes[$routeRegexp] = $routeSlim;
    }

    /**
     * Adiciona uma nova rota ao controller.
     *
     * @param string $route
     * @param callback $callback
     */
    protected function addRoute($route, $callback)
    {
        if(!is_callable($callback))
            return;

        $this->customRoutes[$route] = $callback;
    }

    /**
     * Adiciona uma nova rota ao controller.
     *
     * @param string $route
     * @param callback $callback
     */
    protected function addRouteRestriction($route, $callback)
    {

        if(!is_callable($callback))
            return;

        $this->restrictionRoutes[$route][] = $callback;
    }

    /**
     * Realiza a chamada da rota.
     *
     * @param string $route
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    private function callRoute($route, ResponseInterface $response, $args, $original = null)
    {
        if(!$this->canCallRoute($route, $args, $original))
            return $response->withStatus(404);

        // Verifica se é uma rota hookada, caso seja, irá preferir a rota de hook a rota
        // convencional.
        if($this->isHookedMethod($route))
            return $this->__callHooked($route, [$response, $args], true);

        // Verifica se é uma rota customizada, se for, faz a chamada
        // E retorna.
        if(isset($this->customRoutes[$route]))
        {
            $closure = $this->customRoutes[$route];
            if (!is_array($closure))
                $closure = \Closure::bind($closure, $this);

            return $closure($response, $args);
        }

        // Verifia a rota se está definida em uma das rotas por comentário
        // Se estiver, irá invocar ela ao invez da rota padrão...
        if ($original !== null
            && isset($this->parsedRoutes[$original->method][$original->route])) {
            $route = $this->parsedRoutes[$original->method][$original->route];
        }

        // Se não houver travas ou restrições, então, retorna a resposta
        // corretamente da rota.
        return $this->{$route}($response, $args);
    }

    /**
     * Verifica se a rota pode ser chamada.
     *
     * @param string $route Rota a ser invocada
     * @param array $args Argumentos a serem verificados.
     *
     * @return bool Verdadeiro se puder.
     */
    private function canCallRoute($route, $args, $original)
    {
        // Verifica se uma rota customizada já existe, se não existir
        // Procura por um método fixo da classe.
        // -> Caso exista, faz teste de restrição de rotas.
        if(isset($this->customRoutes[$route]) || method_exists($this, $route) || $this->isHookedMethod($route))
        {
            // Se não houver restrições de rota, retorna verdadeiro...
            if(!isset($this->restrictionRoutes[$route]))
                return true;

            $result = true;
            foreach ($this->restrictionRoutes[$route] as $closure) {
                if (is_array($closure) && is_callable($closure)) {
                    $obj = $closure;
                } else {
                    $obj = \Closure::bind($closure, $this);
                }

                $response = $obj($args);
                if ($response === false) {
                    $result = false;
                    break;
                }
            }

            return $result;
        }

        // Se não existir nada, não tem o porque de acessar.
        return isset($this->parsedRoutes[$original->method][$original->route]);
    }

    /**
     * Faz o tratamento da rota e retorna o caminho correto
     *  para retorno dos dados.
     *
     * @param string $route Dados de rota.
     *
     * @return string Rota a ser utilizada.
     */
    final public function parseRoute($route)
    {
        foreach($this->regexRoutes as $routePattern => $routeSlim)
        {
            if(preg_match($routePattern, $route))
                return $routeSlim;
        }

        return $route;
    }

    /**
     * Verifica as chaves no vetor POST
     *
     * @param array $keys Chaves a serem verificadas
     *
     * @return boolean Chaves a serem verificadas
     */
    protected function verifyKeysPost($keys)
    {
        return $this->verifyKeys($keys, $this->post);
    }

    /**
     * Verifica as chaves no vetor GET
     *
     * @param array $keys Chaves a serem verificadas
     *
     * @return boolean Chaves a serem verificadas
     */
    protected function verifyKeysGet($keys)
    {
        return $this->verifyKeys($keys, $this->get);
    }

    /**
     * Verifica chaves de um vetor, caso todas as chaves estejam presentes.
     *
     * @param array $keys Chaves a verificar
     * @param array $vector Vetor que será testado
     *
     * @return boolean Se todas as $keys estiverem em $vector, será retornado, verdadeiro
     */
    protected function verifyKeys($keys, $vector)
    {
        if (is_null($vector))
            $vector = [];

        // Verre todas as chaves e testa o vetor,
        // caso não exista, será retornado false.
        foreach($keys as $key)
        {
            if(!array_key_exists($key, $vector))
                return false;
        }

        return true;
    }

    /**
     * Método para realizar o tratamento de chamada de rota.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function __router(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        // Obtém os parametros de rota para realizar a chamada.
        $route = $request->getAttribute('route')->getPattern();
        $routeParams = array_values(array_filter(explode('/', $route), function($value) {
            return !empty($value) && preg_match('/^([a-z0-9_]+)$/i', $value);
        }));
        array_shift($routeParams);

        // Tratamento para chamar o action correto.
        $action = implode('_', $routeParams);
        if(empty($action)) $action = 'index';
        $action .= '_' . strtoupper($request->getMethod());

        // Após obter os parametros de rotas, obtém também todos os dados
        // Enviados pela requisição para poder enviar ao método caso
        // Necessário.
        $get        = $request->getQueryParams();   // dados do $_GET
        $post       = $request->getParsedBody();    // dados de $_POST
        $files      = $request->getUploadedFiles(); // dados de $_FILES

        // Define os dados recebidos.
        $this->setReceivedData($get, $post, $files);

        try
        {
            // Faz a chamada da rota.
            $response = $this->callRoute($action, $response, $args, (object)[
                'route' => implode('/', $routeParams),
                'method' => $request->getMethod()
            ]);

            // Verifica o retorno e devolve as informações
            // Para o gerenciador de erro, caso a página não seja encontrada.
            if($response->getStatusCode() !== 200)
                throw new ControllerException('Caminho solicitado não foi encontrado.');
        }
        catch(ControllerException $ex)
        {
            $response->withStatus(200);
            return $this->getApplication()
                        ->notFoundHandler($request->withAttribute('message', $ex->getMessage()),
                            $response);
        }

        // Retorna a resposta devolvida.
        return $response;
    }
}
