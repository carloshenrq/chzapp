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
 * Interface para gerar os métodos de hooking.
 */
interface IHookHandler
{
    /**
     * Verifica se o objeto que faz gerança pode ser hookado.
     * 
     * @return boolean Verdadeiro se puder receber o hook.
     */
    public function canHook();

    /**
     * Define o diretório de onde serão lidos os hooks.
     * 
     * @param string $hookDir Diretório aonde estão os hooks.
     */
    public function setHookDir($hookDir);

    /**
     * Obtém o diretório que irá conter os arquivos de hook.
     * 
     * @return string Diretório aonde estão os arquivos de hook.
     */
    public function getHookDir();

    /**
     * Faz uma leitura do arquivo de hooks para procurar
     * o arquivo de hook referente a classe que está
     * tentando ser hookada
     */
    public function readHookDir();

    /**
     * Obtém todos os arquivos que participam do hook
     * para o objeto.
     * 
     * @return array
     */
    public function getHookedFiles();

    /**
     * Verifica se o método informado é um método que possui hooking.
     * 
     * @param string $method
     * 
     * @return boolean Verdadeiro caso o método esteja hookado.
     */
    public function isHookedMethod($method);

    /**
     * Verifica informações de propriedades não definidas.
     * 
     * @param string $name Propriedade
     * @param mixed $value Valor para a propriedade
     */
    public function __set($name, $value);

    /**
     * Obtém informações de propriedades não definidas.
     * 
     * @param string $name Propriedade
     * 
     * @return mixed Valor para a propriedade
     */
    public function __get($name);

    /**
     * Executa quando não há metodos definidos para
     * a invocação e faz o teste de hook.
     * 
     * @param string $name Nome do método
     * @param array $args Argumentos de chamada
     * 
     * @return mixed Retorno dos dados
     */
    public function __call($name, $args);

    /**
     * Executa a chamada de rotinas hookadas caso existam
     * 
     * @param string $name Nome do método
     * @param array $args Argumentos de chamada
     * @param boolean $force Força a chamada
     * 
     * @return mixed Dados de execução hookados
     */
    public function __callHooked($name, $args, $force = false)
}
