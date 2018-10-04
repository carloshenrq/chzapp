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

namespace CHZApp\Interfaces;

/**
 * Interface para a sessão de dados
 */
interface ISession
{
    /**
     * Recria os dados de sessão.
     * 
     * @param boolean $deleteOldSession Informa se irá deletar os dados da sessão anterior.
     * 
     * @return boolean Verdadeiro se comando foi executado com sucesso.
     */
    public function recreate($deleteOldSession = false);

    /**
     * Método mágico para poder remover a variavel de sessão.
     * 
     * @param string $name
     */
    public function __unset($name);

    /**
     * Método para verificar se há definições para a variavel de sessão
     * 
     * @param string $name
     * 
     * @return boolean Verdadeiro caso esteja definido.
     */
    public function __isset($name);

    /**
     * Método usado para definir entradas de sessão
     * 
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value);

    /**
     * Faz a leitura de dados nas entradas de sessão
     * 
     * @param string $name
     * 
     * @return mixed
     */
    public function __get($name);
}
