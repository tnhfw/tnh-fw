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
	
    class ApcCache extends BaseClass implements CacheInterface {

		
		
        public function __construct() {
            parent::__construct();
            if (!$this->isSupported()) {
                show_error('The cache for APC[u] driver is not available. Check if APC[u] extension is loaded and enabled.');
            }
        }

        /**
         * This is used to get the cache data using the key
         * @param  string $key the key to identify the cache data
         * @return mixed      the cache data if exists else return false
         */
        public function get($key) {
            $this->logger->debug('Getting cache data for key [' . $key . ']');
            $success = false;
            $data = apc_fetch($key, $success);
            if ($success === false) {
                $this->logger->info('No cache found for the key [' . $key . '], return false');
                return false;
            } else {
                $cacheInfo = $this->_getCacheInfo($key);
                $expire = time();
                if ($cacheInfo) {
                    $expire = $cacheInfo['creation_time'] + $cacheInfo['ttl'];
                }
                $this->logger->info('The cache not yet expire, now return the cache data for key [' . $key . '], the cache will expire at [' . date('Y-m-d H:i:s', $expire) . ']');
                return $data;
            }
        }


        /**
         * Save data to the cache
         * @param string  $key  the key to identify this cache data
         * @param mixed  $data the cache data to be saved
         * @param integer $ttl  the cache life time
         * @return boolean true if success otherwise will return false
         */
        public function set($key, $data, $ttl = 0) {
            $expire = time() + $ttl;
            $this->logger->debug('Setting cache data for key [' . $key . '], time to live [' . $ttl . '], expire at [' . date('Y-m-d H:i:s', $expire) . ']');
            $result = apc_store($key, $data, $ttl);
            if ($result === false) {
                $this->logger->error('Can not write cache data for the key [' . $key . '], return false');
                return false;
            } else {
                $this->logger->info('Cache data saved for the key [' . $key . ']');
                return true;
            }
        }


        /**
         * Delete the cache data for given key
         * @param  string $key the key for cache to be deleted
         * @return boolean      true if the cache is deleted, false if can't delete 
         * the cache or the cache with the given key not exist
         */
        public function delete($key) {
            $this->logger->debug('Deleting of cache data for key [' . $key . ']');
            $cacheInfo = $this->_getCacheInfo($key);
            if ($cacheInfo === false) {
                $this->logger->info('This cache data does not exists skipping');
                return false;
            } else {
                $this->logger->info('Found cache data for the key [' . $key . '] remove it');
                    return apc_delete($key) === true;
            }
        }
		
        /**
         * Get the cache information for given key
         * @param  string $key the key for cache to get the information for
         * @return boolean|array    the cache information. The associative array and must contains the following information:
         * 'mtime' => creation time of the cache (Unix timestamp),
         * 'expire' => expiration time of the cache (Unix timestamp),
         * 'ttl' => the time to live of the cache in second
         */
        public function getInfo($key) {
            $this->logger->debug('Getting of cache info for key [' . $key . ']');
            $cacheInfos = $this->_getCacheInfo($key);
            if ($cacheInfos) {
                $data = array(
                            'mtime' => $cacheInfos['creation_time'],
                            'expire' => $cacheInfos['creation_time'] + $cacheInfos['ttl'],
                            'ttl' => $cacheInfos['ttl']
                            );
                return $data;
            } else {
                $this->logger->info('This cache does not exists skipping');
                return false;
            }
        }


        /**
         * Used to delete expired cache data
         */
        public function deleteExpiredCache() {
            //for APC[u] is done automatically
            return true;
        }

        /**
         * Remove all cache data
         */
        public function clean() {
            $this->logger->debug('Deleting of all cache data');
            $cacheInfos = apc_cache_info('user');
            if (empty($cacheInfos['cache_list'])) {
                $this->logger->info('No cache data were found skipping');
                return false;
            } else {
                $this->logger->info('Found [' . count($cacheInfos) . '] cache data to remove');
                return apc_clear_cache('user');
            }
        }
		
		
        /**
         * Check whether the cache feature for the handle is supported
         *
         * @return bool
         */
        public function isSupported() {
            return (extension_loaded('apc') || extension_loaded('apcu')) && ini_get('apc.enabled');
        }
		
        /**
         * Return the array of cache information
         *
         * @param string $key the cache key to get the cache information 
         * @return boolean|array
         */
        private function _getCacheInfo($key) {
            $caches = apc_cache_info('user');
            if (!empty($caches['cache_list'])) {
                $cacheLists = $caches['cache_list'];
                foreach ($cacheLists as $c) {
                    if (isset($c['info']) && $c['info'] === $key) {
                        return $c;
                    }
                }
				
            }
            return false;
        }
    }
