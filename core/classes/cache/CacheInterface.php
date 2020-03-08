<?php
    defined('ROOT_PATH') or exit('Access denied');
    /**
     * TNH Framework
     *
     * A simple PHP framework using HMVC architecture
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2017 TNH Framework
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in all
     * copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
     * SOFTWARE.
     */
	
    interface CacheInterface {

        /**
         * This is used to get the cache data using the key
         * @param  string $key the key to identify the cache data
         * @return mixed      the cache data if exists else return false
         */
        public function get($key);


        /**
         * Save data to the cache
         * @param string  $key  the key to identify this cache data
         * @param mixed  $data the cache data to be saved
         * @param integer $ttl  the cache life time
         * @return boolean true if success otherwise will return false
         */
        public function set($key, $data, $ttl = 0);


        /**
         * Delete the cache data for given key
         * @param  string $key the key for cache to be deleted
         * @return boolean      true if the cache is deleted, false if can't delete 
         * the cache or the cache with the given key not exist
         */
        public function delete($key);
		
		
        /**
         * Get the cache information for given key
         * @param  string $key the key for cache to get the information for
         * @return boolean|array    the cache information. The associative array and must contains the following information:
         * 'mtime' => creation time of the cache (Unix timestamp),
         * 'expire' => expiration time of the cache (Unix timestamp),
         * 'ttl' => the time to live of the cache in second
         */
        public function getInfo($key);


        /**
         * Used to delete expired cache data
         */
        public function deleteExpiredCache();

        /**
         * Remove all cache data
         */
        public function clean();
		
		
        /**
         * Check whether the cache feature for the handle is supported
         *
         * @return bool
         */
        public function isSupported();
    }
