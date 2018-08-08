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

use \Swift_SmtpTransport;
use \Swift_Mailer;
use \Swift_Message;

/**
 * Componente responsavel pelo envio de informações
 * por e-mail.
 */
class Mailer extends ConfigComponent
{
    /**
     * Realiza o envio do e-mail com os dados informados.
     *
     * @param string $subject Assunto do e-mail
     * @param string $template Arquivo que será renderizado para o envio
     * @param string $data Dados para trocar durante o render.
     * @param array $to Destinatário da mensagem.
     * @param string $type Tipo de mensagem. HTML ou TEXT
     */
    public function sendFromTemplate($subject, $to, $template, $data = array(), $type = 'text/html')
    {
        // Renderiza os dados da mensagem para envio.
        $body = $this->getApplication()
                    ->getSmartyView()
                    ->render($template, $data);
        // Envia os dados.
        return $this->send($subject, $to, $body, $type);
    }

    /**
     * Realiza o envio do e-mail com os dados informados.
     *
     * @param string $subject Assunto do e-mail
     * @param string $template Arquivo que será renderizado para o envio
     * @param string $data Dados para trocar durante o render.
     * @param array $to Destinatário da mensagem.
     * @param string $type Tipo de mensagem. HTML ou TEXT
     */
    public function send($subject, $to, $body, $type = 'text/html')
    {
        $message = $this->createMessage($subject, $to, $body, $type);
        return $this->createMailer()->send($message);
    }

    /**
     * Cria o componente de mensagem.
     *
     * @param string $subject
     * @param array $to
     * @param string $body
     * @param string $type
     *
     * @return Swift_Message
     */
    private function createMessage($subject, $to, $body, $type = 'text/html')
    {
        // Cria o objeto da mensagem para envio.
        $message = new Swift_Message($subject);
		
		$message->setFrom([$this->configs['from'] => $this->configs['name']])
				->setTo($to);
        // Define os dados da mensagem com o conteúdo.
        $message->setBody($body, $type);

        // Retorna a mensagem pronta para o envio.
        return $message;
    }

    /**
     * Cria o mailer para enviar o e-mail.
     *
     * @return \Swift_Mailer
     */
    private function createMailer()
    {
        return new Swift_Mailer($this->createTransport());
    }

    /**
     * Cria o transporte para enviar o e-mail usando o Swift_Mailer
     *
     * @return \Swift_SmtpTransport
     */
    private function createTransport()
    {
		$transport = new Swift_SmtpTransport(
            $this->configs['host'],
            $this->configs['port'],
            $this->configs['encrypt']
        );
        return $transport->setUsername($this->configs['user'])
        ->setPassword($this->configs['pass'])
        ->setTimeout(600);
    }

    /**
     * @see ConfigComponent::parseConfigs()
     */
    protected function parseConfigs($configs = array())
    {
        $this->configs = array_merge([
            'host'      => null,
            'port'      => null,
            'encrypt'   => null,
            'user'      => null,
            'pass'      => null,
            'from'      => null,
            'name'      => null
        ], $configs);
    }
}

