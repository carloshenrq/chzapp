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

class SessionTest extends TestCase
{
    private $sessObj;

    public function setUp()
    {
        $this->appObj = $this->getMockForAbstractClass('\CHZApp\Application');
        $this->sessObj = $this->getMockBuilder('\CHZApp\Session')
                            ->enableOriginalConstructor()
                            ->setConstructorArgs([$this->appObj, []])
                            ->enableProxyingToOriginalMethods()
                            ->setMethods(['getException'])
                            ->getMock();
    }

    public function testCryptoInfos()
    {
        $key = base64_encode(openssl_random_pseudo_bytes(32));
        $algo = 'aes-256-ecb';
        $ivLen = openssl_cipher_iv_length($algo);
        $iv = openssl_random_pseudo_bytes($ivLen);

        // Dados para criptografar.
        $dataToCipher = base64_encode(openssl_random_pseudo_bytes(rand(8, 32)));

        // Define informações de criptografia.
        $this->sessObj->setCryptInfo($algo, $key, $iv);

        // Criptografa os dados e grava informações.
        $cipherSessKey = $this->sessObj->encrypt('testCipher');
        $cipherData = $this->sessObj->encrypt($dataToCipher);

        // Joga os dados na sessão de forma criptografada.
        $this->sessObj->testCipher = $dataToCipher;

        // Verifica se existe a chave criptografada na sessão
        // e se o dado é igual ao enviado.
        $this->assertTrue(isset($_SESSION[$cipherSessKey]));
        $this->assertEquals($_SESSION[$cipherSessKey], $cipherData);

        // Verifica também se os dados de criptografia, voltam de acordo.
        $this->assertTrue(isset($this->sessObj->testCipher));
        $this->assertEquals($this->sessObj->testCipher, $dataToCipher);
    }

    public function testUnset()
    {
        unset($this->sessObj->CHZApp_SessionTimeout);
        $this->assertFalse(isset($this->sessObj->CHZApp_SessionTimeout));
    }

    public function testInit()
    {
        $this->sessObj->CHZApp_SessionTimeout = microtime(true) - 1;
        $this->sessObj->init();
        $this->assertNotNull($this->sessObj->CHZApp_SessionTimeout);
    }

}
