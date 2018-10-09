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
 * Abstract class to cache management.
 */
class SQLiteCache extends Cache
{
    /**
     * Conexão SQLite com o PDO do banco de dados.
     * @var \PDO
     */
    private $pdo;

    /**
     * @see Cache::remove()
     */
    public function remove($index)
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('DELETE FROM cache_data WHERE index_name = :index_name');
        $execute = $stmt->execute([
            ':index_name' => $index,
        ]);

        $pdo->commit();
        return $execute;
    }

    /**
     * @see Cache::create()
     */
    public function create($index, $data, $timeout)
    {
        // Obtém a conexão com o banco de dados
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        // Prepara os dados para gravar em cache
        $stmt = $pdo->prepare('
            INSERT INTO
                cache_data
            VALUES
                (:index_name, :type, :content, :timeout)
        ');

        // Executa a gravação dos dados em cache
        $stmt->execute([
            ':index_name'   => $index,
            ':type'         => gettype($data),
            ':content'      => base64_encode(serialize($data)),
            ':timeout'      => (($timeout == -1) ? 0 : (time() + $timeout))
        ]);

        $pdo->commit();
        // Após gravar, obtém as informações do cache
        return $this->get($index);
    }

    /**
     * @see Cache::get()
     */
    public function get($index)
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        // Tenta obter os dados do cache no banco
        $stmt = $pdo->prepare('
            SELECT
                *
            FROM
                cache_data
            WHERE
                index_name = :index_name
                    AND
                (timeout = 0
                    OR
                timeout > :timeout)
        ');
        $stmt->execute([
            ':index_name' => $index,
            ':timeout' => time()
        ]);

        $objCache = $stmt->fetchObject();
        $pdo->commit();

        // Se não houver registros no banco de dados
        // então, irá retornar NULL
        if($objCache === false)
            return null;

        // Obtém os dados serializados
        return unserialize(base64_decode($objCache->content));
    }

    /**
     * @see Component::init()
     */
    public function init()
    {
        // Abre uma conexão persistente com o banco de dados do SQLite
        $this->pdo = new \PDO('sqlite:sqlcache.db', null, null, [
            \PDO::ATTR_ERRMODE          => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_PERSISTENT       => true
        ]);

        // Dispara eventos init
        $this->trigger('init');
    }

    /**
     * Evento a ser disparado quando se inicia o objeto.
     */
    public function on_init()
    {
        $this->getConnection()->beginTransaction();

        // Realiza a instalação do banco de dados SQLite
        $this->performInstall();

        // Realiza a limpeza dos dados de cache no banco de dados
        $this->performClean();

        // Comita as alterações no cache
        $this->getConnection()->commit();
    }

    /**
     * Carrega todas as tabelas instaladas no banco de dados do SQLite
     *
     * @return array
     */
    private function loadInstalledTables()
    {
        $stmt = $this->getConnection()->query('
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
     * Realiza a instalação do banco de dados de cache local
     * Caso a rotina esteja hookada, então, irá fazer a chamada da mesma logo após a conclusão desta...
     */
    protected function performInstall()
    {
        $tables = $this->loadInstalledTables();

        if(!in_array('cache_data', $tables))
        {
            $qry = '
                CREATE TABLE cache_data (
                    index_name STRING NOT NULL,
                    type STRING NOT NULL,
                    content STRING NOT NULL,
                    timeout INTEGER NOT NULL
                );

                CREATE UNIQUE INDEX cache_data_u01 ON cache_data ( index_name );
            ';

            foreach(explode(';', $qry) as $query)
                $this->getConnection()->query($query);
        }

        // Cria a tabela de cache para os assets
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
                $this->getConnection()->query($query);
        }

        // Quando fizer a chamada, o hook irá aplicar as rotinas de inserção
        // no banco de dados...
        if($this->isHookedMethod('performInstall'))
            $this->__callHooked('performInstall', [], true);
    }

    /**
     * Realiza a limpeza dos dados na tabela de cache...
     */
    private function performClean()
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('DELETE FROM cache_data WHERE timeout > 0 AND timeout < :timeout');
        $stmt->execute([
            ':timeout' => time()
        ]);
    }

    /**
     * @see ConfigComponent::parseConfigs
     */
    protected function parseConfigs($configs = [])
    {
        // Carrega as configurações padrões
        $this->configs = $configs;

        return;
    }

    /**
     * Obtém a conexão de PDO
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }
}
