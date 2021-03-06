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

/**
 * Classe de teste para aplicação
 */
class ApplicationTest extends TestCase
{
	private $appObj;
	private $xmlJsonObj;
	private $schemaObj;
	private $sessionObj;
	private $viewerObj;
	private $httpObj;
	private $mailerObj;

	public function setUp()
	{
		$this->appObj = $this->getMockForAbstractClass('\CHZApp\Application');
		$this->xmlJsonObj = $this->createMock('\CHZApp\XmlJsonConverter');
		$this->schemaObj = $this->createMock('\CHZApp\SchemaValidator');
		$this->sessionObj = $this->createMock('\CHZApp\Session');
		$this->viewerObj = $this->createMock('\CHZApp\SmartyView');
		$this->httpObj = $this->createMock('\CHZApp\HttpClient');
		$this->mailerObj = $this->createMock('\CHZApp\Mailer');

		$this->xmlJsonObj->setApplication($this->appObj);
		$this->appObj->setXmlJsonConverter($this->xmlJsonObj);
		$this->appObj->setSchemaValidator($this->schemaObj);
		$this->appObj->setSession($this->sessionObj);
		$this->appObj->setViewer($this->viewerObj);
		$this->appObj->setHttpClient($this->httpObj);
		$this->appObj->setMailer($this->mailerObj);
		$this->appObj->setEloquentConfigs();

		if (!getenv('REMOTE_ADDR'))
			putenv('REMOTE_ADDR=127.0.0.1');
	}

	public function testSelf()
	{
		$this->assertInstanceOf('\Slim\App', $this->appObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IApplication', $this->appObj);
	}

	public function testGetInstance()
	{
		$obj = forward_static_call([$this->appObj, 'getInstance']);
		$this->assertEquals($obj, $this->appObj);
	}

	public function testSetCacheConfigs()
	{
		$this->assertNull($this->appObj->setCacheConfigs());
	}

	public function testCreateCacheInstance()
	{
		$memcache = $this->appObj->createCacheInstance();
		$this->assertNotNull($memcache);
		$this->assertInstanceOf('\CHZApp\MemCache', $memcache);
		$this->assertInstanceOf('\CHZApp\Cache', $memcache);
		$this->assertInstanceOf('\CHZApp\ConfigComponent', $memcache);
		$this->assertInstanceOf('\CHZApp\Component', $memcache);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $memcache);
	}

	public function testGetAssetParser()
	{
		$assetObj = $this->appObj->getAssetParser();

		$this->assertNotNull($assetObj);
		$this->assertInstanceOf('\CHZApp\AssetParser', $assetObj);
		$this->assertInstanceOf('\CHZApp\Component', $assetObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $assetObj);
	}

	public function testSetEloquentConfigs()
	{
		$this->assertNull($this->appObj->setEloquentConfigs());
		$this->testGetEloquent();
	}

	public function testCreateEloquentInstance()
	{
		$eloquent = $this->appObj->createEloquentInstance();
		
		$this->assertInstanceOf('\CHZApp\EloquentManager', $eloquent);
		$this->assertInstanceOf('\CHZApp\ConfigComponent', $eloquent);
		$this->assertInstanceOf('\CHZApp\Component', $eloquent);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $eloquent);
	}

	public function testGetEloquent()
	{
		$eloquent = $this->appObj->getEloquent();

		$this->assertNotNull($eloquent);
		$this->assertInstanceOf('\CHZApp\EloquentManager', $eloquent);
		$this->assertInstanceOf('\CHZApp\ConfigComponent', $eloquent);
		$this->assertInstanceOf('\CHZApp\Component', $eloquent);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $eloquent);

		$manager = $eloquent->getManager();
		$conn = $manager->getConnection();
		$pdo = $conn->getPdo();

		$this->assertNotNull($pdo);
		$this->assertInstanceOf('\PDO', $pdo);
	}

	public function testSetMailerConfigs()
	{
		$this->assertNull($this->appObj->setMailerConfigs());
		$this->testGetMailer();
	}

	public function testCreateMailerInstance()
	{
		$mailerObj = $this->appObj->createMailerInstance();

		$this->assertNotNull($mailerObj);
		$this->assertInstanceOf('\CHZApp\Mailer', $mailerObj);
		$this->assertInstanceOf('\CHZApp\ConfigComponent', $mailerObj);
		$this->assertInstanceOf('\CHZApp\Component', $mailerObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IMailer', $mailerObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $mailerObj);
	}

	public function testSetSmartyConfigs()
	{
		$this->assertNull($this->appObj->setSmartyConfigs());
		$this->testGetViewer();
	}

	public function testCreateViewerInstance()
	{
		$viewerObj = $this->appObj->createViewerInstance();

		$this->assertNotNull($viewerObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IViewer', $viewerObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $viewerObj);
		$this->assertInstanceOf('\CHZApp\ConfigComponent', $viewerObj);
		$this->assertInstanceOf('\CHZApp\Component', $viewerObj);
	}

	public function testSetSessionConfigs()
	{
		$this->assertNull($this->appObj->setSessionConfigs());
		$this->testGetSession();
	}

	public function testCreateSessionInstance()
	{
		$sessObj = $this->appObj->createSessionInstance();

		$this->assertNotNull($sessObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\ICrypto', $sessObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\ISession', $sessObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $sessObj);
		$this->assertInstanceOf('\CHZApp\ConfigComponent', $sessObj);
		$this->assertInstanceOf('\CHZApp\Component', $sessObj);
		$this->assertInstanceOf('\CHZApp\Session', $sessObj);
	}

	public function testUnInstallSchema()
	{
		$this->assertNull($this->appObj->unInstallSchema(null));
	}

	public function testInstallSchema()
	{
		$this->assertNull($this->appObj->installSchema(null));
	}

	public function testSetHookAutoload()
	{
		$hookAutoload = realpath(join(DIRECTORY_SEPARATOR, [
			__DIR__,
			'hooks',
			'autoload.php'
		]));

		$this->assertNull($this->appObj->setHookAutoload($hookAutoload));
	}

	public function testCanHook()
	{
		$this->assertEquals(false, $this->appObj->canHook());
	}

	public function testSetMailer()
	{
		$mailerObj = $this->createMock('\CHZApp\Mailer');
		$this->appObj->setMailer($mailerObj);
		$this->testGetMailer();
	}

	public function testGetMailer()
	{
		$mailerObj = $this->appObj->getMailer();

		$this->assertNotNull($mailerObj);
		$this->assertInstanceOf('\CHZApp\Mailer', $mailerObj);
		$this->assertInstanceOf('\CHZApp\ConfigComponent', $mailerObj);
		$this->assertInstanceOf('\CHZApp\Component', $mailerObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IMailer', $mailerObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $mailerObj);
	}

	public function testGetMemCache()
	{
		$this->assertNull($this->appObj->getMemCache());
	}

	public function testGetSQLiteCache()
	{
		$sqlLite = $this->appObj->getSQLiteCache();
		
		$this->assertNotNull($sqlLite);
		$this->assertInstanceOf('\CHZApp\Cache', $sqlLite);
		$this->assertInstanceOf('\CHZApp\ConfigComponent', $sqlLite);
		$this->assertInstanceOf('\CHZApp\Component', $sqlLite);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $sqlLite);
	}

	public function testHttpClient()
	{
		$this->appObj->setHttpClient($this->httpObj);
		$this->testGetHttpClient();
	}

	public function testGetHttpClient()
	{
		$httpObj = $this->appObj->getHttpClient();

		$this->assertNotNull($httpObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IHttpClient', $httpObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $httpObj);
		$this->assertInstanceOf('\CHZApp\Component', $httpObj);
		$this->assertEquals($httpObj, $this->httpObj);
	}

	public function testSetViewer()
	{
		$this->appObj->setViewer($this->viewerObj);
		$this->testGetViewer();
	}

	public function testGetViewer()
	{
		$viewerObj = $this->appObj->getViewer();

		$this->assertNotNull($viewerObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IViewer', $viewerObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $viewerObj);
		$this->assertInstanceOf('\CHZApp\ConfigComponent', $viewerObj);
		$this->assertInstanceOf('\CHZApp\Component', $viewerObj);
	}

	public function testSetSession()
	{
		$this->appObj->setSession($this->sessionObj);
		$this->testGetSession();
	}

	public function testGetSession()
	{
		$sessObj = $this->appObj->getSession();

		$this->assertNotNull($sessObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\ICrypto', $sessObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\ISession', $sessObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $sessObj);
		$this->assertInstanceOf('\CHZApp\ConfigComponent', $sessObj);
		$this->assertInstanceOf('\CHZApp\Component', $sessObj);
		$this->assertInstanceOf('\CHZApp\Session', $sessObj);
	}

	public function testGetIpAddress()
	{
		$ipAddress = $this->appObj->getIpAddress();
		$this->assertEquals('127.0.0.1', $ipAddress);
	}

	public function testSetSchemaValidator()
	{
		$this->appObj->setSchemaValidator($this->schemaObj);
		$this->testGetSchemaValidator();
	}

	public function testGetSchemaValidator()
	{
		$schemaObj = $this->appObj->getSchemaValidator();

		$this->assertNotNull($schemaObj);
		$this->assertInstanceOf('\CHZApp\SchemaValidator', $schemaObj);
		$this->assertInstanceOf('\CHZApp\Component', $schemaObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $schemaObj);
		$this->assertEquals($schemaObj, $this->schemaObj);
	}

	public function testGetXmlJsonConverter()
	{
		$xmlObj = $this->appObj->getXmlJsonConverter();
		
		$this->assertNotNull($xmlObj);
		$this->assertInstanceOf('\CHZApp\XmlJsonConverter', $xmlObj);
		$this->assertInstanceOf('\CHZApp\Component', $xmlObj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $xmlObj);
		$this->assertEquals($xmlObj, $this->xmlJsonObj);
	}

	public function testSetXmlJsonConverter()
	{
		$this->appObj->setXmlJsonConverter($this->xmlJsonObj);
		$this->testGetXmlJsonConverter();
	}
}

