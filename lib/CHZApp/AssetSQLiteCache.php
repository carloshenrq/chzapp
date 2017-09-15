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
     * Conexão SQLite com o PDO do banco de dados.
     * @var \PDO
     */
    private $pdo;

    /**
     * @see Component::init()
     */
    protected function init()
    {
        // Abre uma conexão persistente com o banco de dados do SQLite
        $this->pdo = new \PDO('sqlite:sqlcache.db', null, null, [
            \PDO::ATTR_ERRMODE          => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_PERSISTENT       => true
        ]);

        // Realiza a instalação das tabelas do banco de dados.
        $this->performInstall();
    }

    private function getAssetParser()
    {
        return $this->getApplication()
                    ->getAssetParser();
    }

    public function parseFileFromCache($file, $fileContent)
    {
        $stmt = $this->pdo->prepare('
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
            $stmt = $this->pdo->prepare('DELETE FROM asset_cache WHERE Filename = :Filename');
            $stmt->execute([
                ':Filename' => $file,
            ]);

            // Caso não dê pra fazer o minify...
            $minifyData = $fileContent;

            // Se for arquivo javascript
            if(preg_match('/\.js$/', $file))
                $minifyData = $this->getAssetParser()->jsMinify($fileContent);
            else if(preg_match('/\.(s)?css$/', $file))
                $minifyData = $this->getAssetParser()->cssMinify($fileContent);
            
            // Grava os dados na tabela de cache..
            $stmt = $this->pdo->prepare('
                INSERT INTO asset_cache VALUES (:Filename, :Filehash, :FileOutput)
            ');
            $stmt->execute([
                ':Filename'         => $file,
                ':Filehash'         => $hash,
                ':FileOutput'       => $minifyData,
            ]);

            // Chama a função novamente para devolver o cache...
            return $this->parseFileFromCache($file, $fileContent);
        }

        // Retorna o output de dados
        return $rs->FileOutput;
    }

    /**
     * Carrega todas as tabelas instaladas no banco de dados do SQLite
     *
     * @return array
     */
    private function loadInstalledTables()
    {
        $stmt = $this->pdo->query('
            SELECT
                tbl_name
            FROM
                sqlite_master
            WHERE
                type="table"
        ');
        $ds = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $tables = [];

        foreach($ds as $rs)
            $tables[] = $rs->tbl_name;
        
        return $tables;
    }

    /**
     * Realiza a instalação das tabelas no banco de dados.
     * @return void
     */
    private function performInstall()
    {
        $tables = $this->loadInstalledTables();

        $this->pdo->beginTransaction();

        // Cria a tabela de cache
        if(!in_array('asset_cache', $tables))
        {
            $qry = '
                CREATE TABLE asset_cache (
                    Filename STRING NOT NULL,
                    Filehash STRING NOT NULL,
                    FileOutput STRING NOT NULL
                );
                
                CREATE UNIQUE INDEX asset_cache_u01 on asset_cache (
                    Filename
                );
            ';

            foreach(explode(';', $qry) as $query)
                $this->pdo->query($query);
        }

        $this->pdo->commit();
    }
}

