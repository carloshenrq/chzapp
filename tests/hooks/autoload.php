<?php
/**
 * BSD 3-Clause License
 * 
 * Copyright (c) 2018, Carlos Henrique
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
/**
 * Classe para realizar o autoloading dos dados.
 *
 */
final class HAutoload
{
    /**
     * Método para registrar o autoload.
     */
    public static function register()
    {
        // Registra as funções de autoload para que se tenha
        // o funcionamento das classes do framework.
        spl_autoload_register([
            'HAutoload',
            'loader'
        ], true, false);
    }
    /**
     * Método para carregar as classes do framework.
     *
     * @param string $className Nome da classe que irá ser carregada
     */
    public static function loader($className)
    {
        // Monta o nome do arquivo da classe e logo após
        // Tenta fazer a inclusão do arquivo
        $classFile = join(DIRECTORY_SEPARATOR, [
            __DIR__,
            $className . '.php'
        ]);
        $classFile = str_replace('\\', DIRECTORY_SEPARATOR, $classFile);
        // Verifica se o arquivo existe se existir inclui o arquivo no código
        if(file_exists($classFile))
            require_once $classFile;
    }
}
// Registra o autoload para o site.
HAutoload::register();
