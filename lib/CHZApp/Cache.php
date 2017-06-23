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
abstract class Cache extends ConfigComponent
{
    /**
     * Creates an index with the proper data in cache.
     *
     * @param string $index Cache index.
     * @param mixed $data Data to save in index.
     * @param int $timeout Max data to save it.
     *
     * @return mixed The data saved in cache.
     */
    abstract public function create($index, $data, $timeout);

    /**
     * Removes an index from the cache.
     *
     * @param string $index The index saved from cache
     *
     * @return bool True if was removed.
     */
    abstract public function remove($index);

    /**
     * Gets the index from the cache.
     *
     * @param string $index The index saved in the cache
     *
     * @return mixed The data in cache. Null if has not in the index
     */
    abstract public function get($index);

    /**
     * Parse the data from cache or put it on there.
     *
     * @param string $index The index to put or get from the cache.
     * @param mixed $data Data to put in the cache if needs too
     * @param int $timeout Timeout to put in the cache.
     * @param bool $force If it exists in the cache, the data'll be overwritten
     *
     * @return mixed The data in cache
     */
    public function parse($index, $data, $timeout, $force = false)
    {
        // Remove the index from cache;
        if($force) $this->remove($index);

        // Gets the data from cache.
        $cachedData = $this->get($index);

        // If this is not null, then, has in the cache, so, return it.
        if(!is_null($cachedData))
            return $cachedData;

        // If it's null, we need to create it on index
        return $this->create($index, $data, $timeout);
    }
}

