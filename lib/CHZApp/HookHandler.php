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
 * Classe para gerenciamento de hooks e também de eventos
 * que os hooks poderão e farão uso.
 */
abstract class HookHandler implements IEventHandler
{
	/**
	 * Array de eventos em memória.
	 * @var array
	 */
	private $events;

	/**
	 * Construtor para o gerenciador de hooks e eventos.
	 */
	public function __construct()
	{
		$this->events = [];

		// Faz a leitura dos métodos que são eventos da propria classe.
		$this->parseEventMethods();
	}

	/**
	 * @see IEventHandler::parseEventMethods()
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
