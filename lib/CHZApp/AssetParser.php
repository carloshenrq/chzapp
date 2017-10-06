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

use \CoffeeScript\Compiler as CSCompiler;
use \MatthiasMullie\Minify;
use \Leafo\ScssPhp\Compiler as SCSSCompiler;

/**
 * Classe para tratar o conteúdo de assets da aplicação.
 * Compilação de coffeescript e scss
 */
class AssetParser extends Component
{
    /** 
     * Componente de cache para o asset.
     *
     * @return AssetSQLiteCache
     */
    private $sqlCache;

    /**
     * @see Component::init()
     */
    protected function init()
    {
        // Define o objeto de cache para o assetparser
        $this->sqlCache = new AssetSQLiteCache($this->getApplication());
    }

    /**
     * Obtém o cache SQLite para uso dos assets.
     *
     * @return AssetSQLiteCache
     */
    public function getSqlCache()
    {
        return $this->sqlCache;
    }

    /**
     * Minifica o conteúdo de css enviado.
     *
     * @param string $css Conteúdo de CSS enviado.
     *
     * @return string Devolve o CSS minificado.
     */
    public function cssMinify($css)
    {
        $minify = new Minify\CSS;
        $minify->add($css);
        return $minify->minify();
    }

    /**
     * Obtém o arquivo scss para ser compilado e retornado os dados
     *
     * @param string $file Arquivo a ser compilado.
     * @param bool $minify Identifica se o arquivo será minificado.
     * @param array $vars Variaveis definidas para troca nos arquivos.
     * @param string $importPath Caminho para os arquivos de include
     *
     * @return string Arquivo scss compilado.
     */
    public function scss($file, $minify = true, $vars = [], $importPath = __DIR__)
    {
        // Obtém o conteúdo do arquivo a ser compilado.
        $fileContents = file_get_contents($file);

        // Retorna o CSS compilado.
        return $this->scssContent($fileContents, $minify, $vars, $importPath);
    }

    /**
     * Compila o conteudo SCSS e devolve.
     *
     * @param string $fileContent Conteudo a ser compilado.
     * @param bool $minify Identifica se o arquivo será minificado.
     * @param array $vars Variaveis definidas para troca nos arquivos.
     * @param string $importPath Caminho para os arquivos de include
     *
     * @return string SCSS compilado.
     */
    public function scssContent($fileContent, $minify = true, $vars = [], $importPath = __DIR__)
    {
        // Instância o compilador e define as variaveis e caminho
        // para os mixins
        $compiler = new SCSSCompiler;
        $compiler->setVariables($vars);
        $compiler->addImportPath($importPath);

        // Compila o arquivo informado.
        $cssCompiled = $compiler->compile($fileContent);

        // Minifica os dados a serem retornados
        // se necessário
        if($minify) $cssCompiled = $this->cssMinify($cssCompiled);

        // Retorna o CSS compilado.
        return $cssCompiled;
    }

    /**
     * Minifica o conteúdo do arquivo javascript enviado.
     *
     * @param string $javascript Conteúdo javascript a ser minificado.
     *
     * @return string Javascript minificado.
     */
    public function jsMinify($javascript)
    {
        $minify = new Minify\JS;
        $minify->add($javascript);
        return $minify->minify();
    }

    /**
     * Realiza a compilação de um arquivo coffeeScript e retorna
     * as informações compiladas.
     *
     * @param string $file Arquivo que será compilado.
     * @param bool $minify Identifica se os dados serão minificados.
     * @param array $options Opções para compilação do coffeescript
     *
     * @return string Dados compilados
     */
    public function coffeeScript($file, $minify = true, $options = [])
    {
        // Obtém o conteúdo do arquivo para realizar a compilação
        // do arquivo .coffee
        $fileContents = file_get_contents($file);

        // Opções para o compilador
        $options = array_merge([
            'filename' => $file,
        ], $options);

        // Obtém o coffeescript compilado para javascript
        $jsCompiled = CSCompiler::compile($fileContents, $options);

        // Se for identificado para minificar o arquivo
        // Então irá aplicar a minificação ao mesmo.
        if($minify) $jsCompiled = $this->jsMinify($jsCompiled);

        // Retorna os dados de coffeescript compilados.
        return $jsCompiled;
    }
}
