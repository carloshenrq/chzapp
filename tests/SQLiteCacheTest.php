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

class SQLiteCacheTest extends TestCase
{
    private $appObj;
    private $sqlObj;

    public function setUp()
    {
        $this->appObj = $this->getMockForAbstractClass('\CHZApp\Application');
        $this->sqlObj = $this->getMockBuilder('\CHZApp\SQLiteCache')
                            ->enableOriginalConstructor()
                            ->setConstructorArgs([$this->appObj, []])
                            ->enableProxyingToOriginalMethods()
                            ->setMethods(['getException'])
                            ->getMock();
    }

    public function testHooks()
    {
        $this->assertNull($this->sqlObj->__callHooked('init', [], false));
        $this->assertTrue($this->sqlObj->testHook());

        $this->assertEquals($this->sqlObj->key1, 'value1');
        
        $this->sqlObj->key1 = 'value2';
        $this->assertNotEquals($this->sqlObj->key1, 'value1');
    }

    /**
     * @expectedException \Exception
     */
    public function testHooksException2()
    {
        if ($this->sqlObj->key2)
            return;
    }

    /**
     * @expectedException \Exception
     */
    public function testHooksException1()
    {
        $this->sqlObj->key2 = true;
    }

    /**
     * @expectedException \Exception
     */
    public function testHooksException0()
    {
        $this->sqlObj->__callHooked('performClean', [], false);
    }

    public function testRemove()
    {
        $affected = $this->sqlObj->remove('test_0');
        $this->assertEquals(true, $affected);
    }

    public function testCreate()
    {
        $cache = hash('md5', microtime(true));
        $cached = $this->sqlObj->create('test_0', $cache, 60);

        $this->assertEquals($cache, $cached);
    }

    public function testGet()
    {
        $cache = $this->sqlObj->get('test_1');
        $this->assertNull($cache);
    }

    public function testParse()
    {
        $cache = hash('md5', microtime(true));
        $cached = $this->sqlObj->parse('test_2', $cache, 60, true);

        $this->assertEquals($cache, $cached);
        unset($cached);

        $cached = $this->sqlObj->parse('test_2', $cache, 60);
        $this->assertEquals($cache, $cached);
    }
}
