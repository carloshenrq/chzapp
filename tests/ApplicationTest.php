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
	private $object;

	public function setUp()
	{
		$this->object = $this->getMockForAbstractClass('\CHZApp\Application');
	}

	public function testSelf()
	{
		$obj = $this->object;

		$this->assertInstanceOf('\Slim\App', $obj);
		$this->assertInstanceOf('\CHZApp\Interfaces\IApplication', $obj);
	}

	public function testInstallSchema()
	{
		$this->assertNull($this->object->installSchema(null));
	}

	public function testGetEloquent()
	{
		$this->assertNull($this->object->getEloquent());
	}

	public function testGetMailer()
	{
		$this->assertNull($this->object->getMailer());
	}

	public function testGetMemCache()
	{
		$this->assertNull($this->object->getMemCache());
	}

	public function testGetViewer()
	{
		$this->assertNull($this->object->getViewer());
	}

	public function testGetSession()
	{
		$this->assertNull($this->object->getSession());
	}

	public function testGetAssetParser()
	{
		$parser = $this->object->getAssetParser();

		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $parser);
		$this->assertInstanceOf('\CHZApp\Component', $parser);
		$this->assertInstanceOf('\CHZApp\AssetParser', $parser);
	}

	public function testGetSQLiteCache()
	{
		$sqlite = $this->object->getSQLiteCache();

		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $sqlite);
		$this->assertInstanceOf('\CHZApp\Component', $sqlite);
		$this->assertInstanceOf('\CHZApp\ConfigComponent', $sqlite);
		$this->assertInstanceOf('\CHZApp\Cache', $sqlite);
		$this->assertInstanceOf('\CHZApp\SQLiteCache', $sqlite);
	}

	public function testGetHttpClient()
	{
		$client = $this->object->getHttpClient();

		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $client);
		$this->assertInstanceOf('\CHZApp\Interfaces\IHttpClient', $client);
		$this->assertInstanceOf('\CHZApp\Component', $client);
		$this->assertInstanceOf('\CHZApp\HttpClient', $client);
	}

	public function testGetXmlJsonConverter()
	{
		$obj = $this->object->getXmlJsonConverter();

		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $obj);
		$this->assertInstanceOf('\CHZApp\Component', $obj);
	}

	public function testGetSchemaValidator()
	{
		$obj = $this->object->getSchemaValidator();

		$this->assertInstanceOf('\CHZApp\Interfaces\IComponent', $obj);
		$this->assertInstanceOf('\CHZApp\Component', $obj);
	}

	public function testCanHook()
	{
		$this->assertEquals(false, $this->object->canHook());
	}
}

