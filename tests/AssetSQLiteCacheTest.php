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

class AssetSQLiteCacheTest extends TestCase
{
    private $appObj;
    private $sqlCache;
	private $assetDir;

    public function setUp()
    {
        $this->appObj = $this->getMockForAbstractClass('\CHZApp\Application');
        $this->assetObj = $this->appObj->getAssetParser();
        $this->sqlCache = $this->assetObj->getSqlCache();
		$this->assetDir = join(DIRECTORY_SEPARATOR, [
			__DIR__,
			'asset'
		]);
    }

    public function testParseFileFromCache()
    {
        $sTestCompiledCss = join(DIRECTORY_SEPARATOR, [
            $this->assetDir,
            'test-compiled.scss'
        ]);
        $sTestCacheCss = join(DIRECTORY_SEPARATOR, [
            $this->assetDir,
            'test-compiled.scss.cache.css'
        ]);

        $sTestCss = file_get_contents($sTestCompiledCss);
        $sTestCacheContentCss = file_get_contents($sTestCacheCss);

        $cacheCss = $this->sqlCache->parseFileFromCache($sTestCompiledCss, $sTestCss);

        $this->assertEquals($cacheCss, $sTestCacheContentCss);

        $sTestCompiledJs = join(DIRECTORY_SEPARATOR, [
            $this->assetDir,
            'test-min.js'
        ]);
        $sTestCacheJs = join(DIRECTORY_SEPARATOR, [
            $this->assetDir,
            'test-min.cache.js'
        ]);

        $sTestJs = file_get_contents($sTestCompiledJs);
        $sTestCacheContentJs = file_get_contents($sTestCacheJs);

        $cacheJs = $this->sqlCache->parseFileFromCache($sTestCompiledJs, $sTestJs);

        $this->assertEquals($cacheJs, $sTestCacheContentJs);
    }
}
