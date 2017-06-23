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

use \Slim\App;

/**
 * Classe principal para realizar os tratamentos de aplicação
 * e gerenciamento de sessão, usuários, e renderização.
 *
 * @abstract
 */
abstract class Application extends App
{
    private static $instance;

    /**
     * Dados para controle e gerenciamento de sessão.
     *
     * @var Session
     */
    private $session;

    /**
     * Obtém informação do viewer para a aplicação.
     *
     * @var SmartyView
     */
    private $smartyView;

    /**
     * Cliente para requisições externas.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Cliente para envio de e-mails.
     * 
     * @var Mailer
     */
    private $mailer;

    /**
     * Dados de memcache para criação de cache.
     * 
     * @var MemCache
     */
    private $memcache;

    /**
     * Eloquent para realizar a conexão com o banco de dados.
     *
     * @var \Illuminate\Database\Capsule\Manager
     */
    private $eloquent;

    /**
     * Define o objeto de parser de assets na aplicação.
     * @var AssetParser
     */
    private $assetParser;

    /**
     * Construtor para a classe de aplicação
     *
     * @param bool $developerMode Identifica se a classe usará modo desenvolvedor
     * @param array $sessionConfigs Configurações de sessão. Ao enviar NULL não será definido.
     * @param array $smartyConfigs Configurações do smarty. Ao enviar NULL não será definido.
     * @param array $mailerConfigs Configurações do mailer. Ao enviar NULL não será definido.
     * @param array $eloquentConfigs Configurações do eloquent. Ao enviar NULL não será definido.
     */
    public function __construct($developerMode = false,
        $sessionConfigs = array(),
        $smartyConfigs = array(),
        $mailerConfigs = array(),
        $eloquentConfigs = array(),
        $cacheConfigs = array())
    {
        // Inicializa a classe pai com os dados oficiais.
        parent::__construct([
            'settings' => [
                'displayErrorDetails' => $developerMode
            ]
        ]);

        // Define a instância.
        self::$instance = $this;

        // Define se está usando os assets locais.
        $this->assetParser = new AssetParser($this);

        // Inicializa informações de sessão.
        if(!is_null($sessionConfigs))
            $this->session = new Session($this, $sessionConfigs);
        
        if(!is_null($smartyConfigs))
            $this->smartyView = new SmartyView($this, $smartyConfigs);

        $this->httpClient = new HttpClient($this);

        if(!is_null($mailerConfigs))
            $this->mailer = new Mailer($this, $mailerConfigs);

        if(!is_null($eloquentConfigs))
            $this->eloquent = new EloquentManager($this, $eloquentConfigs);

        // Verifica os dados de configuração de cache.
        if(!is_null($cacheConfigs) && extension_loaded('memcache'))
            $this->memcache = new MemCache($this, $cacheConfigs);

        // Adição dos middlewares padrões.
        $this->add(new Router($this));
    }

    /**
     * Obtém o asset parser para o framework
     *
     * @return AssetParser
     */
    public function getAssetParser()
    {
        return $this->assetParser;
    }

    /**
     * Obtém o manager do eloquent.
     *
     * @return EloquentManager
     */
    public function getEloquent()
    {
        return $this->eloquent;
    }

    /**
     * Define o manager para o eloquent.
     *
     * @param EloquentManager $eloquent
     */
    public function setEloquent(EloquentManager $eloquent)
    {
        $this->eloquent = $eloquent;
    }

    /**
     * Objeto responsável pelo envio dos e-mails.
     *
     * @return Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * Define o componente de mailer para a aplicação.
     *
     * @param Mailer $mailer
     */
    public function setMailer(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Gets memcache object
     *
     * @return MemCache
     */
    public function getMemCache()
    {
        return $this->memcache;
    }

    /**
     * Defines memcache object
     *
     * @param MemCache $memcache
     */
    public function setMemCache(MemCache $memcache)
    {
        $this->memcache = $memcache;
    }

    /**
     * Obtém o objeto que gerência as chamadas de http externo.
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Define o componente de HttpClient para a aplicação.
     *
     * @param HttpClient $httpClient
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Obtém informações de visualização para o smarty.
     *
     * @return SmartyView
     */
    public function getSmartyView()
    {
        return $this->smartyView;
    }

    /**
     * Define o uso do SmartyView para a aplicação.
     *
     * @param SmartyView $smartyView
     */
    public function setSmartyView(SmartyView $smartyView)
    {
        $this->smartyView = $smartyView;
    }

    /**
     * Obtém os dados de sessão.
     * 
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Define os dados de sessão.
     *
     * @param Session $session
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Instância global da aplicção.
     *
     * @return Application
     */
    public static function getInstance()
    {
        return self::$instance;
    }
}
