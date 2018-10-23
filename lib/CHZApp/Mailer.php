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

use \CHZApp\Interfaces\IMailer;

use \Swift_SmtpTransport;
use \Swift_Mailer;
use \Swift_Message;
use \Swift_Attachment;

/**
 * Componente responsavel pelo envio de informações
 * por e-mail.
 */
class Mailer extends ConfigComponent implements IMailer
{
    /**
     * @see IMailer::sendFromTemplate($subject, $to, $template, $data, $type)
     */
    public function sendFromTemplate($subject, $to, $template, $data = array(), $type = 'text/html', $attach = array())
    {
        // Renderiza os dados da mensagem para envio.
        $body = $this->getApplication()
                    ->getViewer()
                    ->render($template, $data);
        // Envia os dados.
        return $this->send($subject, $to, $body, $type, $attach);
    }

    /**
     * @see IMailer::send($subject, $to, $body, $type)
     */
    final public function send($subject, $to, $body, $type = 'text/html', $attach = array())
    {
        try
        {
            return $this->__callHooked('send', [$subject, $to, $body, $type, $attach], true);
        }
        catch(\Exception $ex)
        {
            $message = $this->createMessage($subject, $to, $body, $type, $attach);
            return $this->createMailer()->send($message);
        }
    }

    /**
     * @see IMailer::createMessage($subject, $to, $body, $type)
     */
    final public function createMessage($subject, $to, $body, $type = 'text/html', $attach = array())
    {
        try
        {
            return $this->__callHooked('createMessage', [$subject, $to, $body, $type, $attach], true);
        }
        catch(\Exception $ex)
        {
            // Cria o objeto da mensagem para envio.
            $message = new Swift_Message($subject);
    		
            foreach($attach as $name => $file) {
            	$message->attach(Swift_Attachment::fromPath($file)->setFilename($name));
            }

    		$message->setFrom([$this->configs['from'] => $this->configs['name']])
    				->setTo($to);
            // Define os dados da mensagem com o conteúdo.
            $message->setBody($body, $type);

            // Retorna a mensagem pronta para o envio.
            return $message;
        }
    }

    /**
     * @see IMailer::createMailer()
     */
    final public function createMailer()
    {
        return new Swift_Mailer($this->createTransport());
    }

    /**
     * @see IMailer::createTransport()
     */
    final public function createTransport()
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

        if (getenv('TRAVIS_CI_DEBUG') !== false && getenv('TRAVIS_CI_DEBUG') == 1) {
            $this->configs = array_merge($this->configs, [
                'host'      => getenv('MAILTRAP_HOST'),
                'port'      => getenv('MAILTRAP_PORT'),
                'encrypt'   => null,
                'user'      => getenv('MAILTRAP_USER'),
                'pass'      => getenv('MAILTRAP_PASS'),
                'from'      => 'chzapp@localhost.loc',
                'name'      => 'chzapp'
            ]);
        }
    }
}

