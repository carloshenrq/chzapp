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
     * @param Application $application
     * @param array $get
     * @param array $post
     * @param array $files
     */
    public function __construct(Application $application)
    {
        // Inicializa as variaveis de rotas customizadas e 
        // restrições de rota.
        $this->customRoutes = [];
        $this->restrictionRoutes = [];
        $this->regexRoutes = [];

        // Chama o construtor de componentes.
        parent::__construct($application);
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

        $this->restrictionRoutes[$route] = $callback;
    }

    /**
     * Realiza a chamada da rota.
     *
     * @param string $route
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    private function callRoute($route, ResponseInterface $response, $args)
    {
        if(!$this->canCallRoute($route, $args))
            return $response->withStatus(404);

        // Verifica se é uma rota customizada, se for, faz a chamada
        // E retorna.
        if(isset($this->customRoutes[$route]))
        {
            $closure = \Closure::bind($this->customRoutes[$route], $this);
            return $closure($response, $args);
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
    private function canCallRoute($route, $args)
    {
        // Verifica se uma rota customizada já existe, se não existir
        // Procura por um método fixo da classe.
        // -> Caso exista, faz teste de restrição de rotas.
        if(isset($this->customRoutes[$route]) || method_exists($this, $route))
        {
            // Se não houver restrições de rota, retorna verdadeiro...
            if(!isset($this->restrictionRoutes[$route]))
                return true;

            $closure = \Closure::bind($this->restrictionRoutes[$route], $this);
            return $closure($args);
        }

        // Se não existir nada, não tem o porque de acessar.
        return false;
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
     * Método para realizar o tratamento de chamada de rota.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface
     */
    final public function __router(ServerRequestInterface $request, ResponseInterface $response, $args)
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

        // Remove as definições de $_POST, $_GET, $_REQUEST e $_FILES
        unset($_POST, $_GET, $_REQUEST, $_FILES);

        // Define os dados recebidos.
        $this->setReceivedData($get, $post, $files);

        // Faz a chamada da rota.
        $response = $this->callRoute($action, $response, $args);

        // Verifica o retorno e devolve as informações
        // Para o gerenciador de erro, caso a página não seja encontrada.
        if($response->getStatusCode() !== 200)
        {
            $response->withStatus(200);
            return $this->getApplication()
                        ->notFoundHandler($request->withAttribute('message', 'Caminho solicitado não foi encontrado.'),
                            $response);
        }

        // Retorna a resposta devolvida.
        return $response;
    }
}
