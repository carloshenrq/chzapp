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

use PHPUnit\Framework\TestCase;

class MemCacheTest extends TestCase
{
    private $cacheObj;

    public function setUp()
    {
        $this->appObj = $this->getMockForAbstractClass('\CHZApp\Application');
        $this->cacheObj = $this->getMockBuilder('\CHZApp\MemCache')
                            ->enableOriginalConstructor()
                            ->setConstructorArgs([$this->appObj, []])
                            ->enableProxyingToOriginalMethods()
                            ->setMethods(['getException'])
                            ->getMock();
    }

    public function testGet()
    {
        $response = $this->cacheObj->get('test_index1');
        $this->assertNull($response);

        $cacheString = base64_encode(openssl_random_pseudo_bytes(32));
        $cachedString = $this->cacheObj->create('test_index1', $cacheString, 60);

        $this->assertEquals($cacheString, $cachedString);

        $cachedString = $this->cacheObj->create('test_index1', $cacheString, 60);
        
        $cacheGetString = $this->cacheObj->get('test_index1');
        $this->assertEquals($cacheGetString, $cachedString);
        $this->assertEquals($cacheGetString, $cacheString);

        $cachedRemove = $this->cacheObj->remove('test_index1');
        $this->assertTrue($cachedRemove);

        $cachedString = $this->cacheObj->create('test_index1', function() use ($cacheString) {
            return $cacheString;
        }, 60);

        $this->assertEquals($cacheString, $cachedString);
    }

    public function testRemove()
    {
        $response = $this->cacheObj->remove('test_index0');
        $this->assertFalse($response);
    }
}
