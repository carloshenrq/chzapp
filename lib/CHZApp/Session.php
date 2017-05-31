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

/**
 * Classe para gerenciamento de sessões.
 */
class Session extends Component
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
     * Timeout para limpar a sessão e criar tudo novamente.
     * @var int
     */
    private $timeout;

    /**
     * @see Component::__construct()
     */
    public function __construct(Application $application, $timeout = 300)
    {
        parent::__construct($application);

        // Remove o limitador de cache para sessões.
        session_cache_limiter(false);

        // Obtém o status de bloqueio de informações de sessão.
        $sessionStatus = session_status();

        // Se as sessões do PHP estiverem desabilitadas, então lançara
        // uma exception para que não continue apartir daqui.
        if($sessionStatus == \PHP_SESSION_DISABLED)
            throw new \Exception('Impossível iniciar sessão. Sessões desabilitadas em configuração.');

        // Se não houver sessão ativa, então inicializa uma nova sessão
        if($sessionStatus == \PHP_SESSION_NONE)
            session_start();

        // Define o tempo de inicio de timeout para a função.
        $this->timeout = max(0, $timeout);

        // Tempo minimo de duração é de 1 minuto.
        if($this->timeout > 0) $this->timeout = min(60, $this->timeout);

        // Inicializa informações de sessão.
        $this->init();
    }

    /**
     * Inicializa a sessão com os dados iniciais.
     */
    protected function init()
    {
        // Se ainda não houver dados de criação de sessão
        // Então irá inicializar informações
        if(!isset($this->CHZApp_SessionCreated))
        {
            $sessionCreated = microtime(true);
            $this->CHZApp_SessionCreated = $sessionCreated;

            // Caso necessário, define o timeout para 
            if($this->timeout > 0)
                $this->CHZApp_SessionTimeout = $sessionCreated + $this->timeout;
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
     * Remove os dados de sessão.
     *
     * @param string $name
     */
    final public function __unset($name)
    {
        if($this->hasCrypt()) $name = $this->encrypt($name);

        unset($_SESSION[$name]);
    }

    /**
     * Verifica se existe o indice nos dados de sessão.
     *
     * @param string $name
     *
     * @return bool Verdadeiro se existir.
     */
    final public function __isset($name)
    {
        if($this->hasCrypt()) $name = $this->encrypt($name);

        return isset($_SESSION[$name]);
    }

    /**
     * Define os dados de sessão com a chave informada.
     *
     * @param string $name
     * @param mixed $value
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
     * Obtém dados de sessão com a chave informada.
     *
     * @param string $name
     *
     * @return mixed Dados retornados.
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
     * Recria a sessão informando se irá deletar os dados anteriores.
     *
     * @param bool $deleteOldSession
     *
     * @return bool Se foi deletado com sucesso, então verdadeiro.
     */
    public function recreate($deleteOldSession = false)
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Define dados e informações de criptografia para a sessão.
     *
     * @param string $algo Algoritmo de criptografia.
     * @param string $key Chave de criptografia.
     * @param string $iv IV de criptografia.
     */
    final public function setCryptInfo($algo, $key, $iv)
    {
        $this->cryptAlgo = $algo;
        $this->cryptKey = $key;
        $this->cryptIv = $iv;
    }

    /**
     * Verifica se existe criptografia para sessão.
     *
     * @return bool Verdadeiro caso exista.
     */
    private function hasCrypt()
    {
        return !empty($this->cryptAlgo);
    }

    /**
     * Criptografa os dados enviados com os dados de session.
     *
     * @param string $data
     *
     * @return string Dados criptografados.
     */
    private function encrypt($data)
    {
        // Se não existe informações para criptografia,
        // então, retornará false.
        if(!$this->hasCrypt())
            return false;

        // Retorna os dados criptografados.
        return openssl_encrypt($data, $this->cryptAlgo, $this->cryptKey, false, $this->cryptIv);
    }

    /**
     * Decriptografa os dados enviados com informações da session.
     *
     * @param string $data
     *
     * @return string Dados decriptografados.
     */
    private function decrypt($data)
    {
        // Se não existe informações para criptografia,
        // então, retornará false.
        if(!$this->hasCrypt())
            return false;

        // Retorna os dados criptografados.
        return openssl_decrypt($data, $this->cryptAlgo, $this->cryptKey, false, $this->cryptIv);
    }
}
