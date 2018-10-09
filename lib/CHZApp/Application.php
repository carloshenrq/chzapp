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
use \CHZApp\Interfaces\IApplication;
use \CHZApp\Interfaces\IHttpClient;
use \CHZApp\Interfaces\IMailer;
use \CHZApp\Interfaces\ISession;
use \CHZApp\Interfaces\IViewer;

/**
 * Classe principal para realizar os tratamentos de aplicação
 * e gerenciamento de sessão, usuários, e renderização.
 *
 * @abstract
 */
abstract class Application extends App implements IApplication
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
     * @var IViewer
     */
    private $viewer;

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
     * Obtém o cache para o SQLite
     * @var SQLiteCache
     */
    private $sqliteCache;

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
     * Obtém o endereço IP para o usuário.
     * @var string
     */
    private $ipAddress;

    /**
     * Schema validator to xml files.
     * @var SchemaValidator
     */
    private $schemaValidator;

    /**
     * Xml to Json converter.
     * @var XmlJsonConverter
     */
    private $xmlJsonConverter;

    /**
     * Identifica se os componentes da aplicação podem ser hookados.
     * @var boolean
     */
    private $canHook;

    /**
     * Define o autoload para as classes de hook.
     * @var string
     */
    private $hookAutoload;

    /**
     * Construtor para a classe de aplicação
     *
     * @param bool $developerMode Identifica se a classe usará modo desenvolvedor
     */
    public function __construct($developerMode = false)
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

        // Define o conector httpClient para a aplicação.
        $this->setHttpClient(new HttpClient($this));

        // Defines the schemaValidator
        $this->schemaValidator = new SchemaValidator($this);

        // Defines the xmlJsonConverter
        $this->xmlJsonConverter = new XmlJsonConverter($this);

        // Define os dados de cache para o banco de dados SQLite
        $this->sqliteCache = new SQLiteCache($this, []);

        // Adição dos middlewares padrões.
        $this->add(new Router($this));

        // Chama o inicializador padrão para a aplicação.
        $this->init();
    }

    /**
     * Define se as classes desta aplicação podem ser hookadas
     *
     * @abstract
     * @return boolean
     */
    public function canHook()
    {
        return false;
    }

    /**
     * Define o caminho para o autoload 
     *
     * @param string $autoloadPath
     */
    final public function setHookAutoload($autoloadPath)
    {
        if(file_exists($autoloadPath))
            require_once $autoloadPath;
    }

    /**
     * Define opções de inicialização para a aplicação.
     *
     * @abstract
     */
    abstract protected function init();

    /**
     * Define opções de instalação do banco de dados para a aplicação.
     * 
     * @abstract
     */
    public function installSchema($schema, $name = 'default')
    {
        return;   
    }

    /**
     * Rotina de teste para o banco de dados onde será testado
     * pelo travis
     * 
     * @param object $schema
     */
    public function installSchemaDefault($schema)
    {
        // Usado somente para os testes do travis...
        if (getenv('TRAVIS_CI_DEBUG') !== false && getenv('TRAVIS_CI_DEBUG') == 1) {
            if (!$schema->hasTable('travis_ci')) {
                $schema->create('travis_ci', function($table) {
                    $table->engine = 'MyISAM';
                    $table->increments('id');
                    $table->string('test', 20);
                    $table->timestamps();
                });
            }
        }
    }

    /**
     * Define opções de remoção do banco de dados para a aplicação.
     *
     * @abstract
     */
    public function unInstallSchema($schema, $name = 'default')
    {
        if (!is_null($schema))
            $tables = $schema->dropAllTables();
    }

    /**
     * Adicionado rotina para desinstalar informações de banco de dados.
     */
    public function unInstallSchemaDefault($schema)
    {
        $this->unInstallSchema($schema);
    }

    /**
     * Cria a instância da sessão com as configurações
     *
     * @param array $sessionConfigs
     *
     * @return Session
     */
    public function createSessionInstance($sessionConfigs = [])
    {
        return new Session($this, $sessionConfigs);
    }

    /**
     * Define as configurações de sessão.
     *
     * @param array $sessionConfigs
     */
    final public function setSessionConfigs($sessionConfigs = [])
    {
        // Inicializa informações de sessão.
        if(!is_null($sessionConfigs))
            $this->setSession($this->createSessionInstance($sessionConfigs));
    }

    /**
     * Cria a instância do smarty para ser utilizada.
     *
     * @param array $smartyConfigs
     *
     * @return IViewer
     */
    public function createViewerInstance($smartyConfigs = [])
    {
        return new SmartyView($this, $smartyConfigs);
    }

    /**
     * Define as configurações de smarty.
     *
     * @param array $smartyConfigs
     */
    final public function setSmartyConfigs($smartyConfigs = [])
    {
        // Inicializa informações de smarty.
        if(!is_null($smartyConfigs))
            $this->setViewer($this->createViewerInstance($smartyConfigs));
    }

    /**
     * Cria a instância do mailer.
     *
     * @param array $mailerConfigs
     */
    public function createMailerInstance($mailerConfigs = [])
    {
        return new Mailer($this, $mailerConfigs);
    }

    /**
     * Define as configurações de mailer.
     *
     * @param array $mailerConfigs
     */
    final public function setMailerConfigs($mailerConfigs = [])
    {
        // Inicializa informações de mailer.
        if(!is_null($mailerConfigs))
            $this->setMailer($this->createMailerInstance($mailerConfigs));
    }

    /**
     * Cria uma instância do gerenciador do eloquent.
     *
     * @param array $eloquentConfigs
     *
     * @return EloquentManager
     */
    public function createEloquentInstance($eloquentConfigs = [])
    {
        return new EloquentManager($this, $eloquentConfigs);
    }

    /**
     * Define as configurações do eloquent
     *
     * @param array $eloquentConfigs
     */
    final public function setEloquentConfigs($eloquentConfigs = [])
    {
        // Inicializa informações de eloquent.
        if(!is_null($eloquentConfigs))
            $this->eloquent = $this->createEloquentInstance($eloquentConfigs);
    }

    /**
     * Cria a instância de cache.
     *
     * @param array $cacheConfigs
     *
     * @return Cache
     */
    public function createCacheInstance($cacheConfigs = [])
    {
        return new MemCache($this, $cacheConfigs);
    }

    /**
     * Define as configurações do cache
     *
     * @param array $cacheConfigs
     */
    final public function setCacheConfigs($cacheConfigs = [])
    {
        // Verifica os dados de configuração de cache.
        if(!is_null($cacheConfigs))
            $this->memcache = $this->createCacheInstance($cacheConfigs);
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
     * @see IApplication::setMailer(IMailer $mailer)
     */
    final public function setMailer(IMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @see IApplication::getMailer()
     */
    final public function getMailer()
    {
        return $this->mailer;
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
     * Obtém o cache em SQLite
     * @return SQLiteCache
     */
    public function getSQLiteCache()
    {
        return $this->sqliteCache;
    }

    /**
     * Define o componente de HttpClient para a aplicação.
     *
     * @param HttpClient $httpClient
     */
    final public function setHttpClient(IHttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
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
     * @see IApplication::setViewer(IViewer $viewer)
     */
    final public function setViewer(IViewer $viewer)
    {
        $this->viewer = $viewer;
    }

    /**
     * @see IApplication::getViewer()
     */
    final public function getViewer()
    {
        return $this->viewer;
    }

    /**
     * @see IApplication::setSession(ISession $session)
     */
    final public function setSession(ISession $session)
    {
        $this->session = $session;
    }

    /**
     * @see IApplication::getSession()
     */
    final public function getSession()
    {
        return $this->session;
    }

    /**
     * Obtém o endereço ip do usuário.
     *
     * @return string
     */
    public function getIpAddress()
    {
        // Se o endereço ip já tiver sido obtido alguma vez
        // Retorna o endereço ip.
        if(!empty($this->ipAddress))
            return $this->ipAddress;
        // Define o endereço ip como padrão de '?.?.?.?'
        $this->ipAddress = '?.?.?.?';
        // Possiveis variaveis para se obter o endereço ip do cliente.
        // issue #10: HTTP_CF_CONNECTING_IP-> Usuário usando proteção do cloudfire.
        $_vars = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED',
                  'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        // Varre as opções para retornar os dados ao painel de controle.
        foreach($_vars as $ip)
        {
            if(getenv($ip) !== false)
            {
                $this->ipAddress = getenv($ip);
                break;
            }
        }
        // Retorna o endereço 
        return $this->getIpAddress();
    }

    /**
     * Gets the schema validator from this application
     *
     * @return SchemaValidator
     */
    public function getSchemaValidator()
    {
        return $this->schemaValidator;
    }

    /**
     * Defines the schema validator to this application
     *
     * @param SchemaValidator $schemaValidator
     */
    public function setSchemaValidator(SchemaValidator $schemaValidator)
    {
        $this->schemaValidator = $schemaValidator;
    }

    /**
     * Gets the xml to json converter
     *
     * @return XmlJsonConverter
     */
    public function getXmlJsonConverter()
    {
        return $this->xmlJsonConverter;
    }

    /**
     * Sets the xml to json converter
     *
     * @param XmlJsonConverter $xmlJsonConverter
     */
    public function setXmlJsonConverter(XmlJsonConverter $xmlJsonConverter)
    {
        $this->xmlJsonConverter = $xmlJsonConverter;
    }

    /**
     * Instância global da aplicção.
     *
     * @return IApplication
     */
    public static function getInstance()
    {
        return self::$instance;
    }
}
