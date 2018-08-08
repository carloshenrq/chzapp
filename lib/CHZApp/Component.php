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
 * Classe para componenetes da aplicação. Normalmente utilizada
 * para aplicação de plugins.
 *
 * @abstract
 */
abstract class Component
{
    /**
     * Aplicação que é vinculada ao componente.
     * @var Application
     */
    private $application;

    /**
     * Construtor para o componente, deve receber a aplicação.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;

        // Diretório para realizar os hooks.
        $this->hookDir = realpath(join(DIRECTORY_SEPARATOR, [
            __DIR__,
            '..',
            '..',
            '..',
            '..',
            '..',
            'hooks'
        ]));

        // Aplica os hooks a classe atual caso o application permita que
        // seja hookada.
        if($this->hookDir !== false)
            $this->runHook();

        // Adiciona todos os eventos que estão no formato correto
        // dos métodos de leitura...
        $this->parseEventMethods();

        // Após instânciar tudo e definir... chama o inicializador.
        $this->init();
    }

    /**
     * Verifica se a classe do componente pode ser hookada
     *
     * @return boolean
     */
    protected function getCanHook()
    {
        return false;
    }

    /**
     * Inicializador para os componentes.
     */
    protected function init()
    {
        return;
    }

    /**
     * Verifica todos os métodos deste componente que possuem notação para
     * os eventos. Para declarar um evento por método é necessário que esteja
     * da seguinte forma:
     *
     * -> on_init
     * -> on_ready
     * -> on_loaded
     * -> on_<xxxx>
     *
     * Sempre iniciados com o 'on_', para disparar usar o dispatchEvent('xxxx')
     * exemplo, para disparar o método 'on_init', usar dispatchEvent('init')
     */
    private function parseEventMethods()
    {
        // Obtém todos os métodos referentes para poder adicionar
        // Aos eventos de forma automatica...
        $methods = get_class_methods($this);

        foreach($methods as $method)
        {
            // Se não encontrar o evento, continua a busca dos métodos seguintes...
            if(!preg_match('/^on_([a-zA-Z0-9\_]+)$/i', $method, $match))
                continue;

            $event = $match[1];
            $this->addEventListener($event, [$this, $method]);
        }

    }

    /**
     * Getter para a aplicação vinculada ao componente.
     *
     * @return Application
     */
    final public function getApplication()
    {
        return $this->application;
    }

    /**
     * Obtém os dados de sessão dentro do componente.
     *
     * @return Session
     */
    final public function getSession()
    {
        return $this->getApplication()->getSession();
    }

    /**
     * Informações para eventos referentes ao componente.
     * @var array
     */
    private $events = [];

    /**
     * Adiciona uma rotina de evento ao componente. Os eventos
     * executados serão efeitos conforme fila, serão diversos callbacks
     * para apenas um nome de evento.
     *
     * @param string $event Nome do evento
     * @param callback $callback Rotina para realizar o callback
     *
     * @return void
     */
    final public function addEventListener($event, $callback)
    {
        // Se callback não puder ser chamado, então...
        if(!is_callable($callback))
            throw new \Exception('The content from "$callback" is not a valid callable function/method.');

        // Se não houver fila de eventos para o evento declarado
        if(!isset($this->events[$event]))
            $this->events[$event] = [];

        // Adiciona o evento a fila de eventos...
        $this->events[$event][] = $callback;
    }

    /**
     * Dispara os eventos referentes ao objeto.
     * Aqui, pode ser sobreescrito para obter informações ou até mesmo tratamento
     * dos dados enviados, se necessário...
     *
     * @param string $event Nome do evenot a ser disparado
     * @param mixed $data Dados a serem enviados ao evento
     *
     */
    public function trigger($event)
    {
		$args = func_get_args();
		array_shift($args);
		
        $this->dispatchEvent($event, $args);
    }

    /**
     * Dispara os eventos me memória para o componente.
     *
     * @param string $event Nome do evento a ser disparado
     * @param mixed Dados a serem enviados ao evento
     *
     * @return void
     */
    private function dispatchEvent($event, $data)
    {
        // Se não houver eventos a disparar, apenas ignora...
        if(!isset($this->events[$event]))
            return;

        // Dispara todos os eventos em memória
        foreach($this->events[$event] as $eventCall)
        {
            if(is_array($eventCall))
            {
                call_user_func_array($eventCall, $data);
            }
            else
            {
                $closureObj = \Closure::bind($eventCall, $this);
                call_user_func_array($closureObj, $data);
            }
        }

        return;
    }

    /**
     * Apaga todos os callbacks para os eventos informados
     *
     * @param string $event Nome do evento
     *
     * @return void
     */
    final public function removeEventListener($event)
    {
        // Se não houver eventos a disparar, apenas ignora...
        if(!isset($this->events[$event]))
            return;

        unset($this->events[$event]);
    }

    /**
     * Diretório que contém os arquivos de hook.
     * @var string
     */
    private $hookDir;

    /**
     * Vetor para os métodos hookados.
     * @var array
     */
    private $_hookedMethods = [];

    /**
     * Vetor para as propriedades hookadas.
     * @var array
     */
    private $_hookedProperties = [];

    /**
     * Método para executar e aplicar os mods relacionados a classe que está sendo instânciada
     * caso exista a possibilidade da mesma ser hookada via mods...
     */
    private function runHook()
    {
        // Verifica se a aplicação permite que os componentes possam ser
        // hookados, se permitir, então, será realizado o teste de se o componente
        // pode ser hookado...
        if(!$this->getApplication()->canHook() || !$this->getCanHook())
            return;

        // Obtém o nome da classe para poder encontrar os hooks necessários para a mesma.
        $classHookFile = str_replace(['/', '\\'], '_', get_class($this));

        // Executa o iterator para obter os arquivos na pasta para saber se a classe
        // Possui hooks a serem executados
        $diFiles = new \DirectoryIterator($this->hookDir);

        // Obtém informações dos arquivos que estão, inicialmente, aptos a serem aplicados
        // ao componente atual...
        $filesToHook = [];

        // Aplica o iterador nos arquivos para tentar localizar arquivos que serão usados para
        // Hookar o componente atual...
        foreach($diFiles as $diFile)
        {
            // Verifica se é um direitório, pontos ou se não é um arquivo
            // Caso seja diretório, ponto ou um não arquivo (atalhos), ignora a leitura
            if($diFile->isDir() || $diFile->isDot() || !$diFile->isFile())
                continue;

            // Se estiver dentro da pattern, então o arquivo será hookado
            // E Aplicado no proprio componente...
            if(preg_match('/^' . preg_quote($classHookFile) . '\_(?:[^\.]+)\.php$/i', $diFile->getFilename()))
            {
                $filesToHook[] = join(DIRECTORY_SEPARATOR, [
                    $this->hookDir,
                    $diFile->getFilename()
                ]);
            }

        }

        // Se não houver arquivos encontrados... então retorna
        if(!count($filesToHook))
            return;

        // Varre todos os arquivos que possuem informações de hook
        foreach($filesToHook as $fileToHook)
        {
            // Obtém o arquivo em memória para aplicar os hooks para serem utilizados...
            $_tmpFile = @require_once($fileToHook);

            // Se houver métodos a serem hookados, então...
            if(isset($_tmpFile['methods']))
            {
                // Adiciona os métodos ao vetor de métodos hookados...
                foreach($_tmpFile['methods'] as $methodName => $methodCallBack)
                    $this->_hookedMethods[$methodName] = $methodCallBack;
            }

            // Verifica se o plugin possui propriedades definidas...
            if(isset($_tmpFile['properties']))
            {
                // Adiciona os métodos ao vetor de métodos hookados...
                foreach($_tmpFile['properties'] as $propertyName => $propertyValue)
                    $this->_hookedProperties[$propertyName] = $propertyValue;
            }

            // Verifica se existe os dados para execução inicial do plugin
            if(isset($_tmpFile['init']))
            {
                $closureObj = \Closure::bind($_tmpFile['init'], $this);
                call_user_func($closureObj);
            }

            // Adicionado a leitura de tags de evento
            if(isset($_tmpFile['events']))
            {
                foreach($_tmpFile['events'] as $event => $eventCallback)
                    $this->addEventListener($event, $eventCallback);
            }
        }
    }

    /**
     * Verifica se o método possui algum hook no componente.
     *
     * @param string $method
     *
     * @return boolean
     */
    final public function isHookedMethod($method)
    {
        return isset($this->_hookedMethods[$method]);
    }

    /**
     * Método para definir propriedades custom
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if(!array_key_exists($name, $this->_hookedProperties))
            throw new \Exception('Undefined property: '.get_class($this).'::$'.$name);

        $this->_hookedProperties[$name] = $value;
    }

    /**
     * Método para obter propriedades
     *
     * @param string $name
     *
     * @return mixed dados para o nome
     */
    public function __get($name)
    {
        if(!array_key_exists($name, $this->_hookedProperties))
            throw new \Exception('Undefined property: '.get_class($this).'::$'.$name);

        return $this->_hookedProperties[$name];
    }

    /**
     * Para caso o método não exista por padrão
     *
     * @param string $name nome do método invocado
     * @param array $args argumentos para passar ao método
     *
     * @return mixed Varia conforme método
     */
    public function __call($name, $args)
    {
        return $this->__callHooked($name, $args);
    }

    /**
     * Faz a chamada dos métodos de hook forçando ou não.
     *
     * @param string $name nome do método invocado
     * @param array $args argumentos para passar ao método
     * @param bool $force Se for verdadeiro, pula o teste de hook.
     *
     * @return mixed Varia conforme método
     */
    public function __callHooked($name, $args, $force = false)
    {
        $refl = new \ReflectionObject($this);

        // Se o método existir na classe então, entrar nos testes de if...
        if(!$force && $refl->hasMethod($name))
        {
            $method = $refl->getMethod($name);
            if($method->isPublic())
                return call_user_func_array([$this, $name], $args);
        }
        else if(array_key_exists($name, $this->_hookedMethods))
        {
            $closureObj = \Closure::bind($this->_hookedMethods[$name], $this);
            return call_user_func_array($closureObj, $args);
        }

        throw new \Exception('<strong>Fatal error:</strong> Call to undefined method ' . get_class($this) . '::' . $name . '() in <strong>' .
            __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
    }
}
