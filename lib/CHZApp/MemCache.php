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

use \Clickalicious\Memcached\Client;

/**
 * Class to management memcache server
 */
class MemCache extends Cache
{
    /**
     * Client to connect in memcache
     * @var \Memcache
     */
    private $client;

    /**
     * @see Cache::create()
     */
    public function create($index, $data, $timeout)
    {
        // If this exists on the cache, then... remove it from there.
        if(!is_null($cached = $this->get($index)))
            $this->remove($index);

        // If it's a callable data, then, execute it before
        // Put it on the cache, can't store functions...
        if(is_callable($data))
            return $this->create($index, $data(), $timeout);

        // Add the data to the cache.
        $this->client->add($index, $data, 0, $timeout);

        // Return data saved in the cache
        return $this->get($index);
    }

    /**
     * @see Cache::remove()
     */
    public function remove($index)
    {
        return $this->client->delete($index);
    }

    /**
     * @see Cache::get()
     */
    public function get($index)
    {
        // Try to get index data
        $data = $this->client->get($index);

        // If it's false, then return NULL
        if($data === false)
            return null;

        // If it's still in the cache return the data.
        return $data;
    }

    /**
     * @see Component::init()
     */
    public function init()
    {
        // Defines the memcache client to connect on the server.
        $this->client = new Client($this->configs['host'], $this->configs['port'], $this->configs['timeout']);
    }

    /**
     * @see ConfigComponent::parseConfigs()
     */
    protected function parseConfigs($configs = [])
    {
        // Configuração para o servidor de memcache
        $this->configs = array_merge([
            'host'      => '127.0.0.1',
            'port'      => 11211,
            'timeout'   => 1
        ], $configs);
    }
}
