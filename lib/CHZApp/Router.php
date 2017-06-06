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
 * Classe para tudo para a classe controller.
 */
class Router extends Middleware
{
    /**
     * @see Middleware::__invoke()
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        // Dados para rota que será chamada.
        $path = $request->getUri()->getPath();
        if($path != '/') $path = '/' . $path;

        // Obtém os dados de tratamento para as rotas.
        $route = explode('/', $path);
        array_shift($route);

        // Cria a instância para a variavel de controller.
        $controller = array_shift($route);
        if(empty($controller)) $controller = 'home';

        // Classe para o controller.
        $controllerClass = '\\Controller\\' . ucfirst($controller);

        // Cria a instância do controller.
        $obj = new $controllerClass($this->getApplication());

        // Realiza o tratamento da rota removendo o inicio para saber os dados seguintes.
        $subRoute = substr($path, strlen($controller) + 1);

        // Obtém a rota adequada para uso conforme indicação.
        $correctRouteCall = '/' . $controller . $obj->parseRoute($subRoute);

        // Define a rota de execução.
        $this->getApplication()->any($correctRouteCall, [$obj, '__router']);

        // Retorna para a execução seguinte.
        return $next($request, $response);
    }

}
