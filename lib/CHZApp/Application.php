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
    /**
     * Dados para controle e gerenciamento de sessão.
     *
     * @var Session
     */
    private $session;

    /**
     * Construtor para a classe de aplicação
     *
     * @param bool $developerMode Identifica se a classe usará modo desenvolvedor
     */
    public function __construct($developerMode = false,
        $sessionTimeout = 300)
    {
        // Inicializa a classe pai com os dados oficiais.
        parent::__construct([
            'settings' => [
                'displayErrorDetails' => $developerMode
            ]
        ]);

        // Inicializa informações de sessão.
        $this->session = new Session($this, $sessionTimeout);
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
}
