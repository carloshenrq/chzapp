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
	public function __construct(Application $application, $get, $post, $files)
	{
		parent::__construct($application);
	}

	/**
	 * Realiza a chamada da rota.
	 *
	 * @param string $route
	 * @param ResponseInterface $response
	 *
	 * @return ResponseInterface
	 */
	public function callRoute($route, ResponseInterface $response)
	{
		// @Todo:: Adicionar chamada e resolução de rota.
		return $response;
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

        // Cria a instância do controller para realizar a chamada.
        $obj = new $controller(Application::getInstance(),
        	$get, $post, $files);

        $response = $obj->callRoute( $action, $response );

		return $response;
	}
}
