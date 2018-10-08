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

use \Illuminate\Events\Dispatcher;
use \Illuminate\Container\Container;
use \Illuminate\Database\Capsule\Manager;

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
     * Conexões que irão ser executadas.
     * @var array
     */
    private $schemas = [];

    /**
     * @see Component::__construct()
     */
    public function __construct(Application $application, $configs = array())
    {
        parent::__construct($application, $configs);
        $this->schemas = [];

        $manager = new Manager();
        foreach($this->configs as $name => $config)
        {
            $this->schemas[] = $name;
            $manager->addConnection((array)$config->data, $name);
        }

        // Define o disparador de eventos para os models
        $manager->setEventDispatcher(new Dispatcher(new Container));

        $manager->setAsGlobal();
        $manager->bootEloquent();

        $this->setManager($manager);
		
        // Automaticamente instala o banco de dados da aplicação.
        foreach($this->schemas as $name)
        {
            $schema = $this->manager->schema($name);
            // Constroi o nome do método para ser chamado e realizar
            // a instalação daquele schema...
            $installMethod = 'installSchema' . ucfirst($name);

            try
            {
                if(method_exists($application, $installMethod))
                    call_user_func_array([$application, $installMethod], [
                        $schema
                    ]);
                else
                    $this->getApplication()->installSchema($schema, $name);
            }
            catch(\Exception $ex)
            {
                $uninstallMethod = 'un' . ucfirst($installMethod);
                if(method_exists($application, $uninstallMethod))
                    call_user_func_array([$application, $uninstallMethod], [
                        $schema
                    ]);
                else
                    $this->getApplication()->unInstallSchema($schema, $name);
            }
        }
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
            'default' => (object)[
                'data' => (object)[
                    "driver"    => "mysql",
                    "host"      => "127.0.0.1",
                    "database"  => "chzapp",
                    "username"  => "chzapp",
                    "password"  => "chzapp",
                    "charset"   => "utf8",
                    "collation" => "utf8_swedish_ci",
                    "prefix"    => ""
                ]
            ]
        ], (array)$configs);
    }
}

