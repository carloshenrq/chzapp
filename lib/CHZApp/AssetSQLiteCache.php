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

class AssetSQLiteCache extends Component
{
    /**
     * Obtém a a conexão com o banco de dados do cache em SQLite
     * @return \PDO
     */
    private function getConnection()
    {
        return $this->getApplication()->getSQLiteCache()->getConnection();
    }

    /**
     * Obtém informações sobre o AssetParser para tratamento dos dados SCSS e JS
     * @return AssetParser
     */
    private function getAssetParser()
    {
        return $this->getApplication()
                    ->getAssetParser();
    }

    /**
     * Obtém os dados do cache para os ASSETS
     *
     * @return string Conteudo em cache
     */
    public function parseFileFromCache($file, $fileContent, $minify = true, $vars = [], $importPath = __DIR__)
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('
            SELECT
                FileOutput,
                Filehash
            FROM
                asset_cache
            WHERE
                Filename = :Filename
        ');
        $stmt->execute([
            ':Filename' => $file,
        ]);
        $rs = $stmt->fetchObject();

        // Aplica o hash no conteudo para realizar a leitura
        $hash = hash('md5', $fileContent);

        // Verifica se não existe o conteudo, se não existir, então cria
        // O Novo cache com os dados informados
        if($rs === false || $rs->Filehash !== $hash)
        {
            // Prepara execução para apagar do banco de dados e remove o cache
            // Caso exista...
            $stmt = $pdo->prepare('DELETE FROM asset_cache WHERE Filename = :Filename');
            $stmt->execute([
                ':Filename' => $file,
            ]);

            // Caso não dê pra fazer o minify...
            $minifyData = $fileContent;

            // Se for um arquivo SCSS então irá compilar o arquivo
            // E depois devolver em tela.
            if(preg_match('/\.scss$/', $file))
                $minifyData = $this->getAssetParser()->scssContent($minifyData, $minify, $vars, $importPath);

            // Se for arquivo javascript
            if(preg_match('/\.js$/', $file))
                $minifyData = $this->getAssetParser()->jsMinify($fileContent);
            else if(preg_match('/\.css$/', $file))
                $minifyData = $this->getAssetParser()->cssMinify($fileContent);
            
            // Grava os dados na tabela de cache..
            $stmt = $pdo->prepare('
                INSERT INTO asset_cache VALUES (:Filename, :Filehash, :FileOutput)
            ');
            $stmt->execute([
                ':Filename'         => $file,
                ':Filehash'         => $hash,
                ':FileOutput'       => $minifyData,
            ]);

            // Envia as informações
            $pdo->commit();

            // Chama a função novamente para devolver o cache...
            return $this->parseFileFromCache($file, $fileContent);
        }

        // Envia as informações
        $pdo->commit();

        // Retorna o output de dados
        return $rs->FileOutput;
    }
}

