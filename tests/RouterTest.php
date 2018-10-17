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
require_once 'lib/autoload.php';
require_once 'tests/www/Controller/Home.php';

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private $appObj;

    public function setUp()
    {
        $this->appObj = $this->getMockForAbstractClass('\CHZApp\Application');
        $container = $this->appObj->getContainer();
        $container['settings']['displayErrorDetails'] = false;
        $container['settings']['outputBuffering'] = false;
        $container['notFoundHandler'] = function($c) {
            return function($request, $response) {
                return $response->write('Page not found');
            };
        };
        $environment = $container['environment'];
        $environment['REQUEST_METHOD'] = 'GET';
    }

    public function testInvokeCustom()
    {
        $container = $this->appObj->getContainer();
        $environment = $container['environment'];

        // Ambiente padrão para execução do URI.
        $environment['REQUEST_URI'] = '/home/route';
        $response = $this->appObj->run();

        $body = $this->appObj->getBodyContent();
        $this->assertEquals('it works!', $body);
    }

    public function testInvokeError0()
    {
        $container = $this->appObj->getContainer();
        $environment = $container['environment'];

        // Ambiente padrão para execução do URI.
        $environment['REQUEST_URI'] = '/home/notfound';
        $response = $this->appObj->run();

        $body = $this->appObj->getBodyContent();
        $this->assertEquals('Page not found', $body);
    }

    public function testInvokeStatus()
    {
        $container = $this->appObj->getContainer();
        $environment = $container['environment'];

        // Ambiente padrão para execução do URI.
        $environment['REQUEST_URI'] = '/home/test';
        $response = $this->appObj->run();

        $body = $this->appObj->getBodyContent();
        $this->assertEquals('error messagePage not found', $body);
    }

    public function testInvokeHome()
    {
        $container = $this->appObj->getContainer();
        $environment = $container['environment'];

        // Ambiente padrão para execução do URI.
        $environment['REQUEST_URI'] = '/home/index';
        $response = $this->appObj->run();

        $body = $this->appObj->getBodyContent();
        $this->assertEquals('hello world', $body);
    }
}
