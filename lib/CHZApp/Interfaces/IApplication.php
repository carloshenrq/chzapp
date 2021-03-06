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
 * Interface para a aplicação
 */
interface IApplication
{
    /**
     * Define o objeto de conexão com outros serviços Httpd
     * 
     * @param IHttpClient $httpClient
     */
    public function setHttpClient(IHttpClient $httpClient);

    /**
     * Obtém o objeto de conexão com outros serviços Http
     * 
     * @return IHttpClient
     */
    public function getHttpClient();

    /**
     * Define objeto de viewer.
     * 
     * @param IViewer $viewer
     */
    public function setViewer(IViewer $viewer);

    /**
     * Obtém objeto de Viewer.
     * 
     * @return IViewer
     */
    public function getViewer();

    /**
     * Define informações de mailer.
     * 
     * @param IMailer $mailer
     */
    public function setMailer(IMailer $mailer);

    /**
     * Obtém o objeto de mailer.
     * 
     * @return IMailer
     */
    public function getMailer();

    /**
     * Define a sessão para a aplicação.
     * 
     * @param ISession $sess
     */
    public function setSession(ISession $sess);

    /**
     * Obtém a sessão que está vinculada a aplicação.
     * @return ISession
     */
    public function getSession();
}
