<?php
	defined('ROOT_PATH') or exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework using HMVC architecture
	 *
	 * This content is released under the GNU GPL License (GPL)
	 *
	 * Copyright (C) 2017 Tony NGUEREZA
	 *
	 * This program is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU General Public License
	 * as published by the Free Software Foundation; either version 3
	 * of the License, or (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program; if not, write to the Free Software
	 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
	*/
	
	class ApcCache implements CacheInterface{

		/**
		 * The logger instance
		 * @var Log
		 */
		private static $logger;
		
		
		public function __construct(){
			if(! $this->isSupported()){
				show_error('The cache for APC[u] driver is not available. Check if APC[u] extension is loaded and enabled.');
			}
		}

		/**
		 * Get the logger singleton instance
		 * @return Log the logger instance
		 */
		private static function getLogger(){
			if(static::$logger == null){
				static::$logger[0] =& class_loader('Log');
				static::$logger[0]->setLogger('Library::ApcCache');
			}
			return static::$logger[0];
		}

		/**
		 * This is used to get the cache data using the key
		 * @param  string $key the key to identify the cache data
		 * @return mixed      the cache data if exists else return false
		 */
		public function get($key){
			$logger = static::getLogger();
			$logger->debug('Getting cache data for key ['. $key .']');
			$success = false;
			$data = apc_fetch($key, $success);
			if($success === false){
				$logger->info('No cache found for the key ['. $key .'], return false');
				return false;
			}
			else{
				$cacheInfo = $this->_getCacheInfo($key);
				$expire = time();
				if($cacheInfo){
					$expire = $cacheInfo['creation_time'] + $cacheInfo['ttl'];
				}
				$logger->info('The cache not yet expire, now return the cache data for key ['. $key .'], the cache will expire at [' . date('Y-m-d H:i:s', $expire) . ']');
				return $data;
			}
		}


		/**
		 * Save data to the cache
		 * @param string  $key  the key to identify this cache data
		 * @param mixed  $data the cache data to be saved
		 * @param integer $ttl  the cache life time
		 */
		public function set($key, $data, $ttl = 0){
			$logger = static::getLogger();
			$expire = time() + $ttl;
			$logger->debug('Setting cache data for key ['. $key .'], time to live [' .$ttl. '], expire at [' . date('Y-m-d H:i:s', $expire) . ']');
			$result = apc_store($key, $data, $ttl);
			if($result === false){
		    	$logger->error('Can not write cache data for the key ['. $key .'], return false');
		    	return false;
		    }
		    else{
		    	$logger->info('Cache data saved for the key ['. $key .']');
		    	return true;
		    }
		}


		/**
		 * Delete the cache data for given key
		 * @param  string $key the key for cache to be deleted
		 * @return boolean      true if the cache is deleted, false if can't delete 
		 * the cache or the cache with the given key not exist
		 */
		public function delete($key){
			$logger = static::getLogger();
			$logger->debug('Deleting of cache data for key [' .$key. ']');
			$cacheInfo = $this->_getCacheInfo($key);
			if($cacheInfo === false){
				$logger->info('This cache data does not exists skipping');
				return false;
			}
			else{
				$logger->info('Found cache data for the key [' .$key. '] remove it');
	      		return apc_delete($key);
			}
			return false;
		}


		/**
		 * Used to delete expired cache data
		 */
		public function deleteExpiredCache(){
			//for APC[u] is done automatically
			return true;
		}

		/**
		 * Remove all cache data
		 */
		public function clean(){
			$logger = static::getLogger();
			$logger->debug('Deleting of all cache data');
			$cacheInfos = apc_cache_info('user');
			if(empty($cacheInfos['cache_list'])){
				$logger->info('No cache data were found skipping');
				return false;
			}
			else{
				$logger->info('Found [' . count($cacheInfos) . '] cache data to remove');
				return apc_clear_cache('user');
			}
		}
		
		
		/**
		 * Check whether the cache feature for the handle is supported
		 *
		 * @return bool
		 */
		public function isSupported(){
			return (extension_loaded('apc') || extension_loaded('apcu')) && ini_get('apc.enabled');
		}
		
		/**
		* Return the array of cache information
		*
		* @param string $key the cache key to get the cache information 
		* @return array
		*/
		private function _getCacheInfo($key){
			$caches = apc_cache_info('user');
			if(! empty($caches['cache_list'])){
				$cacheLists = $caches['cache_list'];
				foreach ($cacheLists as $c){
					if(isset($c['info']) && $c['info'] === $key){
						return $c;
					}
				}
				
			}
			return false;
		}
	}