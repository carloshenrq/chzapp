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

use \Illuminate\Database\Capsule\Manager;
use \Illuminate\Container\Container;

/**
 * Adicionado classe para gerenciador do eloquent.
 */
class EloquentManager extends ConfigComponent
{
    /**
     * Manager de conexão com o eloquent.
     * @var \Illuminate\Database\Capsule\Manager
     */
    private $manager;

    /**
     * @see Component::__construct()
     */
    public function __construct(Application $application, $configs = array())
    {
        parent::__construct($application, $configs);

        $manager = new Manager();
        $manager->addConnection($this->configs);
        $manager->setAsGlobal();
        $manager->bootEloquent();

        $this->manager = $manager;

        // Automaticamente instala o banco de dados da aplicação.
        $this->getApplication()->installSchema($this->manager->schema());
    }

    /**
     * Obtém o manager de conexão com o eloquent.
     *
     * @return \Illuminate\Database\Capsule\Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Define o manager para conexão com o eloquent.
     *
     * @param \Illuminate\Database\Capsule\Manager $manager
     */
    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @see ConfigComponent::parseConfigs()
     */
    protected function parseConfigs($configs = array())
    {
        $this->configs = array_merge([
            'driver'    => 'mysql',
            'host'      => '127.0.0.1',
            'database'  => 'test',
            'username'  => 'root',
            'password'  => 'root',
            'charset'   => 'utf8',
            'collation' => 'utf8_swedish_ci',
            'prefix'    => '',
        ], $configs);
    }
}

