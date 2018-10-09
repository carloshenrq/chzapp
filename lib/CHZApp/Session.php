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

use \CHZApp\Interfaces\ICrypto;
use \CHZApp\Interfaces\ISession;
use \CHZApp\Interfaces\IApplication;

/**
 * Classe para gerenciamento de sessões.
 */
class Session extends ConfigComponent implements ISession, ICrypto
{
    /**
     * Algoritmo de criptografia.
     * @var string
     */
    private $cryptAlgo;

    /**
     * Chave de criptografia para a sessão.
     * @var string
     */
    private $cryptKey;

    /**
     * IV de criptografia para a sessão.
     * @var string
     */
    private $cryptIv;

    /**
     * @see Component::__construct()
     */
    public function __construct(IApplication $application, $configs = array())
    {
        // Faz a chamada do construtor herdado.
        parent::__construct($application, $configs);

        // Remove o limitador de cache para sessões.
        @session_cache_limiter(false);
        @session_start();

        // Inicializa informações de sessão.
        $this->init();
    }

    /**
     * @see ConfigComponent::parseConfigs()
     */
    protected function parseConfigs($configs = array())
    {
        $this->configs = array_merge([
            'timeout' => 300,
        ], $configs);
    }

    /**
     * Inicializa a sessão com os dados iniciais.
     */
    public function init()
    {
        // Se ainda não houver dados de criação de sessão
        // Então irá inicializar informações
        if(!isset($this->CHZApp_SessionCreated))
        {
            $sessionCreated = microtime(true);
            $this->CHZApp_SessionCreated = $sessionCreated;

            // Caso necessário, define o timeout para 
            if($this->configs['timeout'] > 0)
                $this->CHZApp_SessionTimeout = $sessionCreated + $this->configs['timeout'];
        }
        else if(isset($this->CHZApp_SessionTimeout) && floatval($this->CHZApp_SessionTimeout) < microtime(true))
        {
            // Se foi definido dados de timeout e o mesmo expirou, então, irá gerar novamente os dados
            // de sessão.
            unset($this->CHZApp_SessionCreated, $this->CHZApp_SessionTimeout);
            $this->recreate();
            $this->init();
            return;
        }

        parent::init();
    }

    /**
     * @see ISession::__unset($name)
     */
    final public function __unset($name)
    {
        if($this->hasCrypt()) $name = $this->encrypt($name);

        unset($_SESSION[$name]);
    }

    /**
     * @see ISession::__isset($name)
     */
    final public function __isset($name)
    {
        if($this->hasCrypt()) $name = $this->encrypt($name);

        return isset($_SESSION[$name]);
    }

    /**
     * @see ISession::__set($name, $value)
     */
    final public function __set($name, $value)
    {
        // Se estiver definido a criptografia então
        // criptografa ambos os valores para uso.
        if($this->hasCrypt())
        {
            $name = $this->encrypt($name);
            $value = $this->encrypt($value);
        }

        // Define os dados de sessão.
        $_SESSION[$name] = $value;
    }

    /**
     * @see ISession::__get($name)
     */
    final public function __get($name)
    {
        // Se houver parametros de criptografia então
        // criptografa para encontrar o nome dentro da sessão.
        if($this->hasCrypt()) $name = $this->encrypt($name);
        
        $data = $_SESSION[$name];

        // Retorna os dados da sessão.
        return (($this->hasCrypt()) ? $this->decrypt($data) : $data);
    }

    /**
     * @see ISession::recreate($deleteOldSession = false)
     */
    public function recreate($deleteOldSession = false)
    {
        return @session_regenerate_id($deleteOldSession);
    }

    /**
     * @see ICrypto::setCryptInfo($algo, $key, $iv)
     */
    final public function setCryptInfo($algo, $key, $iv)
    {
        $this->cryptAlgo = $algo;
        $this->cryptKey = $key;
        $this->cryptIv = $iv;
    }

    /**
     * @see ICrypto::hasCrypt()
     */
    final public function hasCrypt()
    {
        return !empty($this->cryptAlgo);
    }

    /**
     * @see ICrypto::encrypt($data)
     */
    public function encrypt($data)
    {
        // Se não existe informações para criptografia,
        // então, retornará false.
        if(!$this->hasCrypt())
            return false;

        // Retorna os dados criptografados.
        return openssl_encrypt($data, $this->cryptAlgo, $this->cryptKey, false, $this->cryptIv);
    }

    /**
     * @see ICrypto::decrypt($data)
     */
    public function decrypt($data)
    {
        // Se não existe informações para criptografia,
        // então, retornará false.
        if(!$this->hasCrypt())
            return false;

        // Retorna os dados criptografados.
        return openssl_decrypt($data, $this->cryptAlgo, $this->cryptKey, false, $this->cryptIv);
    }
}
