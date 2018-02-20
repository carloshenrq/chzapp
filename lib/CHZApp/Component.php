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
 * Classe para componenetes da aplicação. Normalmente utilizada
 * para aplicação de plugins.
 *
 * @abstract
 */
abstract class Component
{
    /**
     * Aplicação que é vinculada ao componente.
     * @var Application
     */
    private $application;

    /**
     * Construtor para o componente, deve receber a aplicação.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;

        // Após instânciar tudo e definir... chama o inicializador.
        $this->init();

        // Aplica os hooks a classe atual caso o application permita que
        // seja hookada.
        $this->runHook();
    }

    /**
     * Método para executar e aplicar os mods relacionados a classe que está sendo instânciada
     * caso exista a possibilidade da mesma ser hookada via mods...
     */
    private function runHook()
    {
        // Verifica se a aplicação permite que os componentes possam ser
        // hookados, se permitir, então, será realizado o teste de se o componente
        // pode ser hookado...
        if(!$this->getApplication()->canHook() || !$this->getApplication()->canHook())
            return;

        // Realiza os processos de hooking das classes.
    }

    /**
     * Verifica se a classe do componente pode ser hookada
     *
     * @return boolean
     */
    protected function getCanHook()
    {
        return false;
    }

    /**
     * Inicializador para os componentes.
     */
    protected function init()
    {
        return;
    }

    /**
     * Getter para a aplicação vinculada ao componente.
     *
     * @return Application
     */
    final public function getApplication()
    {
        return $this->application;
    }

    /**
     * Obtém os dados de sessão dentro do componente.
     *
     * @return Session
     */
    final public function getSession()
    {
        return $this->getApplication()->getSession();
    }
}
