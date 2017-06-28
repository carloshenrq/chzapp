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

use \Smarty;

/**
 * Classe para tratamento dos templates para o Smarty.
 */
class SmartyView extends ConfigComponent
{
    /**
     * Objeto instânciado para smarty.
     * @var \Smarty
     */
    private $smarty;

    /**
     * Define dados iniciais e configuração para o leitor dos templates.
     *
     * @param Application $application
     * @param array $configs
     */
    public function __construct(Application $application, $configs = array())
    {
        parent::__construct($application, $configs);

        $this->smarty = new Smarty;
        $this->smarty->setTemplateDir($this->configs['templateDir']);
        $this->smarty->setCaching($this->configs['cache']);
    }

    /**
     * @see ConfigComponent::parseConfigs()
     */
    protected function parseConfigs($configs = array())
    {
        $this->configs = array_merge([
            'templateDir'   => './',
            'cache'         => \Smarty::CACHING_OFF
        ], $configs);
    }

    /**
     * Responde a requisição com os dados de templates já escritos
     *
     * @param object $response Objeto de resposta
     * @param string $template Arquivo de template
     * @param array $data Dados para popular o template
     *
     * @return object
     */
    public function response($response, $template, $data = [])
    {
        return $response->write($this->render($template, $data));
    }

    /**
     * Renderiza o template smarty em os dados fornecidos.
     *
     * @param string $template Arquivo de template que será renderizado
     * @param string $data Dados que irão para o arquivo template.
     *
     * @return string Dados renderizados.
     */
    public function render($template, $data = [])
    {
        $this->getSmarty()->assign($data);
        return $this->getSmarty()->fetch($template);
    }

    /**
     * Obtém o objeto para smarty.
     *
     * @return \Smarty
     */
    public function getSmarty()
    {
        return $this->smarty;
    }
}

