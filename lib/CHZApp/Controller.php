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
    public function __construct(Application $application, $get, $post, $files)
    {
        // Inicializa as variaveis de rotas customizadas e 
        // restrições de rota.
        $this->customRoutes = [];
        $this->restrictionRoutes = [];

        // Inicializa os atributos da classe para informações de POST, GET e FILES
        // Pode ser acessado pelas rotas.
        $this->get = $get;
        $this->post = $post;
        $this->files = $files;

        // Chama o construtor de componentes.
        parent::__construct($application);
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
    final public function callRoute($route, ResponseInterface $response)
    {
        if(!$this->canCallRoute($route))
            return $response->withStatus(404);

        // Verifica se é uma rota customizada, se for, faz a chamada
        // E retorna.
        if(isset($this->customRoutes[$route]))
        {
            $closure = \Closure::bind($this->customRoutes[$route], $this);
            return $closure($response);
        }

        // Se não houver travas ou restrições, então, retorna a resposta
        // corretamente da rota.
        return $this->{$route}($response);
    }

    /**
     * Verifica se a rota pode ser chamada.
     *
     * @param string $route Rota a ser invocada
     *
     * @return bool Verdadeiro se puder.
     */
    private function canCallRoute($route)
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
            return $closure();
        }

        // Se não existir nada, não tem o porque de acessar.
        return false;
    }

    /**
     * Método estatico para realizar toda e qualquer
     * chamada atraves do router aqui definido.
     *
     * @static
     *
     * @param object $request
     * @param object $response
     * @param array $args
     */
    public static function router(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        // Obtém informações da rota acessada pelo cliente.
        $route = $request->getAttribute('route')->getPattern();
        $routeParams = explode('/', substr($route, 1));
        $method = strtoupper($request->getMethod());

        // obtém os dados enviados, post, get etc...
        $get        = $request->getQueryParams();   // dados do $_GET 
        $post       = $request->getParsedBody();    // dados de $_POST
        $files      = $request->getUploadedFiles(); // dados de $_FILES

        // Remove as definições de $_POST, $_GET, $_REQUEST e $_FILES
        unset($_POST, $_GET, $_REQUEST, $_FILES);

        // Obtém o controller a ser chamado e também o action.
        $controller = array_shift($routeParams);
        $action = implode('_', $routeParams);

        // Faz o teste para saber se os dados estão vazios.
        if(empty($controller)) $controller = 'home';
        if(empty($action)) $action = 'index';

        // Tratado controller para chamada correta.
        $controller = '\\Controller\\' . ucfirst($controller);

        // Adicionado método de ação ao action.
        $action .= '_' . $method;

        $app = Application::getInstance();

        // Cria a instância do controller para realizar a chamada.
        $obj = new $controller($app,
            $get, $post, $files);

        // Faz a chmada de rota
        $response = $obj->callRoute( $action, $response );

        // Verifica o retorno e devolve as informações
        // Para o gerenciador de erro, caso a página não seja encontrada.
        if($response->getStatusCode() !== 200)
        {
            $response->withStatus(200);
            return $app->notFoundHandler($request->withAttribute('message', 'Caminho solicitado não foi encontrado.'),
                $response);
        }

        // Devolve a interface de respostas.
        return $response;
    }
}
