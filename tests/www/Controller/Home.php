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

namespace Controller;

class Home extends \CHZApp\Controller
{
    public function init()
    {
        $this->applyRestrictionOnAllRoutes(function() {
            return true;
        }, ['test_GET']);

        $this->addRoute('route_GET', function($response, $args) {
            return $response->write('it works!');
        });

        $this->addRoute('brbr_GET', true);

        $this->addRouteRegexp('/^\/home\/template\/(.*)$/i', '/home/template/{file}');
    }

    public function index_GET($response, $args)
    {
        return $response->write('hello world');
    }

    public function test_GET($response, $args)
    {
        return $response->write('error message')->withStatus(404);
    }

    public function template_GET($response, $args)
    {
        $file = $args['file'];

        if ($this->verifyKeysPost(['test', 'br']))
            return $response->withStatus(404);

        if (!$this->verifyKeysGet(['test', 'br']))
            return $response->withStatus(404);

        return $this->response($response, $file, []);
    }
}
