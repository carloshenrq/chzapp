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

interface IMailer
{
    /**
     * Envia um e-mail diretamente utilizando um template em html.
     * 
     * @param string $subject Assunto do email
     * @param array $to Para quem será enviado os dados
     * @param string $template Caminho para o arquivo de template
     * @param array $data Dados que serão usados no template
     * @param string $type Tipo de dados para o corpo do e-mail.
     * @param array $attach Arquivos deverão ser anexados ao email
     */
    public function sendFromTemplate($subject, $to, $template, $data = array(), $type = 'text/html', $attach = array());

    /**
     * Envia o email (criando a mensagem)
     * 
     * @param string $subject Assunto do email
     * @param array $to Para quem será enviado os dados
     * @param string $body Corpo do email
     * @param string $type Tipo de dados para o corpo do e-mail.
     * @param array $attach Arquivos deverão ser anexados ao email
     * 
     * @return object
     */
    public function send($subject, $to, $body, $type = 'text/html', $attach = array());

    /**
     * Cria o objeto de mensagem, que será enviado pelo 'IMailer::createMailer()'
     * 
     * @param string $subject Assunto do email
     * @param array $to Para quem será enviado os dados
     * @param string $body Corpo do email
     * @param string $type Tipo de dados para o corpo do e-mail.
     * @param array $attach Arquivos deverão ser anexados ao email
     * 
     * @return object
     */
    public function createMessage($subject, $to, $body, $type = 'text/html', $attach = array());

    /**
     * Cria o objeto que irá ser o responsavel pelo envio dos dados.
     * 
     * @return object
     */
    public function createMailer();

    /**
     * Cria o objeto interno para realizar o transporte de dados.
     * 
     * @return object
     */
    public function createTransport();
}
