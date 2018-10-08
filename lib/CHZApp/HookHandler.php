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

use \CHZApp\Interfaces\IHookHandler;
use \CHZApp\Interfaces\IEventHandler;

/**
 * Classe para gerenciamento de hooks e também de eventos
 * que os hooks poderão e farão uso.
 */
abstract class HookHandler implements IEventHandler, IHookHandler
{
	/**
	 * Construtor para o gerenciador de hooks e eventos.
	 */
	public function __construct()
	{
        // Inicializa vetor de eventos para os hooks.
		$this->events = [];

		// Faz a leitura dos métodos que são eventos da propria classe.
        $this->parseEventMethods();
        
        // Se os hooks podem ser executados então inicializa a leitura dos
        // hooks presentes na pasta.
        if($this->canHook())
        {
            // Define o local aonde serão lidos os hooks
            $this->setHookDir(realpath(join(DIRECTORY_SEPARATOR, [
                __DIR__,
                '..',
                '..',
                '..',
                '..',
                '..',
                'hooks'
            ])));

            // Inicia a leitura dos dados de hook
            $this->readHookDir();

            if (getenv('TRAVIS_CI_DEBUG') !== false && getenv('TRAVIS_CI_DEBUG') == 1) {
                $this->setHookDir(realpath(join(DIRECTORY_SEPARATOR, [
                    __DIR__,
                    '..',
                    '..',
                    'tests',
                    'hooks'
                ])));

                $this->readHookDir();
                $this->readHookDir();
            }
        }
    }
    
    // ==========  IHookHandler =========== //

    /**
     * Diretório aonde se encontram os hooks.
     * @var string
     */
    private $hookDir;

    /**
     * Métodos que foram hookados.
     * @var array
     */
    private $hookMethods;

    /**
     * Propriedades que foram hookados.
     * @var array
     */
    private $hookProperties;

    /**
     * Informa os arquivos de hooking que já foram carregados
     * @var array
     */
    private $hookReadFiles;

    /**
     * @see IHookHandler::canHook()
     */
    public function canHook()
    {
        return true;
    }

    /**
     * @see IHookHandler::setHookDir($hookDir)
	 * @final
     */
    final public function setHookDir($hookDir)
    {
        $this->hookDir = $hookDir;
    }

    /**
     * @see IHookHandler::getHookDir()
	 * @final
     */
    final public function getHookDir()
    {
        return $this->hookDir;
    }

    /**
     * IHookHandler::readHookDir()
 	 * @final
     */
    final public function readHookDir()
    {
        // Inicializa as variaveis de hooking
        $_tmpHookFiles = $this->hookMethods = $this->hookProperties = $this->hookReadFiles = [];

        // Se o diretório estiver embranco... então não será executado.
        if(empty($this->getHookDir()))
            return;

        // Inicializa o leitor de diretórios para os hookings...
        $diHook = new \DirectoryIterator($this->getHookDir());
        $class2hook = str_replace(['/', '\\'], '_', get_class($this)); 

        // Varre o diretório procurando os arquivos para a classe...
        foreach($diHook as $fHook)
        {
            if($fHook->isDir() || $fHook->isDot() || !$fHook->isFile())
                continue;
            
            if(preg_match('/^' . preg_quote($class2hook) . '\_(?:[^\.]+)\.php$/i', $fHook->getFilename()))
            {
                $_tmpHookFiles[] = join(DIRECTORY_SEPARATOR, [
                    $this->getHookDir(),
                    $fHook->getFilename()
                ]);
            }   
        }

        // Se não houver hooks, ignora a leitura e passa para o próximo.
        if(count($_tmpHookFiles) == 0)
            return;

        // Varre todos os arquivos de hooking para adicionar
        // os métodos ao componente...
        foreach($_tmpHookFiles as $hookFile)
        {
            // Se o arquivo já foi adicionado então, não há sentido
            // adicionar ele novamente.
            if(in_array($hookFile, $this->hookReadFiles))
                continue;

            // Abre os dados de arquivo de hooking
            $hookContent = @require_once($hookFile);

            // Coloca o arquivo de hook em memória.
            $this->hookReadFiles[] = $hookFile;

            // Possui os métodos para adicionar os hooks?
            if(isset($hookContent['methods']))
            {
                foreach($hookContent['methods'] as $method => $callback)
                    $this->hookMethods[$method] = $callback;
            }

            // Possui os propriedades para adicionar os hooks?
            if(isset($hookContent['properties']))
            {
                foreach($hookContent['properties'] as $propertyName => $propertyValue)
                    $this->hookProperties[$propertyName] = $propertyValue;
            }

            // Adicionado a leitura de tags de evento
            if(isset($hookContent['events']))
            {
                foreach($hookContent['events'] as $event => $eventCallback)
                    $this->addEventListener($event, $eventCallback);
            }

            // Verifica se existe os dados para execução inicial do plugin
            if(isset($hookContent['init']))
            {
                $closureObj = \Closure::bind($hookContent['init'], $this);
                call_user_func($closureObj);
            }
        }
    }

    /**
     * @see IHookHandler::getHookedFiles()
     */
    final public function getHookedFiles()
    {
        return $this->hookReadFiles;
    }

    /**
     * @see IHookHandler::isHookedMethod($method)
     */
    final public function isHookedMethod($method)
    {
        return array_key_exists($method, $this->hookMethods);
    }

    /**
     * @see IHookHandler::__set($name, $value)
     */
    public function __set($name, $value)
    {
        if(!array_key_exists($name, $this->hookProperties))
            throw new \Exception('Undefined property: '.get_class($this).'::$'.$name);

        $this->hookProperties[$name] = $value;
    }

    /**
     * @see IHookHandler::__get($name)
     */
    public function __get($name)
    {
        if(!array_key_exists($name, $this->hookProperties))
            throw new \Exception('Undefined property: '.get_class($this).'::$'.$name);

        return $this->hookProperties[$name];
    }

    /**
     * @see IHookHandler::__call($name, $args)
     */
    public function __call($name, $args)
    {
        return $this->__callHooked($name, $args);
    }

    /**
     * @see IHookHandler::__callHooked($name, $args, $force = false)
	 * @final
     */
    final public function __callHooked($name, $args, $force = false)
    {
        $refl = new \ReflectionObject($this);

        // Se o método existir na classe então, entrar nos testes de if...
        if(!$force && $refl->hasMethod($name))
        {
            $method = $refl->getMethod($name);
            if(!$method->isPublic())
                return $this->__callHooked($name, $args, true);
            
            return call_user_func_array([$this, $name], $args);
        }
        else if(array_key_exists($name, $this->hookMethods))
        {
            $closureObj = \Closure::bind($this->hookMethods[$name], $this);
            return call_user_func_array($closureObj, $args);
        }

        throw new \Exception('<strong>Fatal error:</strong> Call to undefined method ' . get_class($this) . '::' . $name . '() in <strong>' .
            __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
    }

    // ========== IEventHandler =========== //

	/**
	 * Array de eventos em memória.
	 * @var array
	 */
	private $events;
    
	/**
	 * @see IEventHandler::parseEventMethods()
	 * @final
	 */
	final public function parseEventMethods()
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
	 * @see IEventHandler::addEventListener($event, $callback)
	 * @final
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
     * @see IEventHandler::removeEventListener($event)
	 * @final
     */
    final public function removeEventListener($event)
    {
        // Se não houver eventos a disparar, apenas ignora...
        if(!isset($this->events[$event]))
            return;

        unset($this->events[$event]);
    }

    /**
     * @see IEventHandler::trigger($event)
	 * @final
     */
    final public function trigger($event)
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
}
