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

use \GuzzleHttp\Client;

/**
 * Gerenciador de requisições HTTP para fora da aplicação.
 */
class HttpClient extends Component
{
    /**
     * Realiza a verificação de componente do reCaptcha solicitado.
     *
     * @param string $challengeResponse Retorno do desafio em tela preenchido pelo usuário.
     * @param string $secretKey Chave secreta para comunicação direta com o google.
     *
     * @return bool Verdadeiro caso os dados estejam validos.
     */
    public function verifyRecaptcha($challengeResponse, $secretKey)
    {
        // Obtém a resposta da google quanto a chave informada
        // e o desafio realizado.
        $googleResponse = $this->createClient()
                                ->post('https://www.google.com/recaptcha/api/siteverify', [
                                    'form_params'   => [
                                        'secret'    => $secretKey,
                                        'response'  => $challengeResponse
                                    ]
                                ])
                                ->getBody()
                                ->getContents();
        // Retorna os dados em json
        $googleJson = json_decode($googleResponse);

        // Caso haja sucesso na validação dos dados, 
        // Então retornará 1. (Dando verdadeiro no retorno)
        return ($googleJson->success == 1);
    }


    /**
     * Cria o cliente de conexão com o URL informado para realizar
     * chamadas.
     *
     * @param string $url Caminho que será chamado.
     * @param bool $verify Caso for HTTPS verificar o certificado.
     * @param array $options Opções do client guzzle.
     *
     * @return \GuzzleHttp\Client
     */
    private function createClient($url = '', $verify = false, $options = [])
    {
        return new Client(array_merge([
            'base_uri'      => $url,
            'verify'        => $verify
        ], $options));
    }
}
