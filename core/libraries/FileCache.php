<?php
	defined('ROOT_PATH') or exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework created using the concept of codeigniter with bootstrap twitter
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

	class FileCache implements CacheInterface{
		/**
		 * Whether to enable compression of the cache data file.
		 * @var boolean
		 */
		private $compressCacheData = true;
		/**
		 * The logger instance
		 * @var Log
		 */
		private static $logger;

		/**
		 * Get the logger singleton instance
		 * @return Log the logger instance
		 */
		private static function getLogger(){
			if(static::$logger == null){
				static::$logger[0] =& class_loader('Log');
				static::$logger[0]->setLogger('Library::FileCache');
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
			$filePath = $this->getFilePath($key);
			if(! file_exists($filePath)){
				$logger->info('No cache file found for the key ['. $key .'], return false');
				return false;
			}
			$logger->info('The cache file [' .$filePath. '] for the key ['. $key .'] exists, check if the cache data is valid');
			$handle = fopen($filePath,'r');
			if( ! $handle){
				$logger->error('Can not open the file cache [' .$filePath. '] for the key ['. $key .'], return false');
				return false;
			}
			// Getting a shared lock 
		    flock($handle, LOCK_SH);
		    $data = file_get_contents($filePath);
      		fclose($handle);
      		$data = @unserialize($this->compressCacheData ? gzinflate($data) : $data);
      		if (!$data) {
      			$logger->error('Can not unserialize the cache data for the key ['. $key .'], return false');
		         // If unserializing somehow didn't work out, we'll delete the file
		         unlink($filePath);
		         return false;
	      	}
	      	if (time() > $data[0]) {
	      		$logger->info('The cache data for the key ['. $key .'] already expired delete the cache file [' .$filePath. ']');
		        // Unlinking when the file was expired
		        unlink($filePath);
		        return false;
		     }
		     else{
		     	$logger->info('The cache not yet expire, now return the cache data for key ['. $key .'], the cache will expire at [' .date('Y-m-d H:i:s', $data[0]). ']');
		     	return $data[1];
		     }
			return false;
		}


		/**
		 * Save data to the cache
		 * @param string  $key  the key to identify this cache data
		 * @param mixed  $data the cache data
		 * @param integer $ttl  the cache life time
		 */
		public function set($key, $data, $ttl = 0){
			$expire = time() + $ttl;
			$logger = static::getLogger();
			$logger->debug('Setting cache data for key ['. $key .'], time to live [' .$ttl. '], expire at [' .$expire. ']');
			$filePath = $this->getFilePath($key);
			$handle = fopen($filePath,'w');
			if( ! $handle){
				$logger->error('Can not open the file cache [' .$filePath. '] for the key ['. $key .'], return false');
				return false;
			}
			flock($handle, LOCK_EX); // exclusive lock, will get released when the file is closed
			//Serializing along with the TTL
		    $data = serialize(array($expire, $data));		   
		    $result = fwrite($handle, $this->compressCacheData ? gzdeflate($data, 9) : $data);
		    if(! $result){
		    	$logger->error('Can not write cache data into file [' .$filePath. '] for the key ['. $key .'], return false');
		    	fclose($handle);
		    	return false;
		    }
		    else{
		    	$logger->info('Cache data saved into file [' .$filePath. '] for the key ['. $key .']');
		    	fclose($handle);
		    }
		}	


		/**
		 * Delete the cache data for given key
		 * @param  string $key the key for cache to be deleted
		 * @return boolean      true if the cache is delete, false if can't delete 
		 * the cache or the cache with the given key not exist
		 */
		public function delete($key){
			$logger = static::getLogger();
			$logger->debug('Deleting of cache data for key [' .$key. ']');
			$filePath = $this->getFilePath($key);
			$logger->info('The file path for the key [' .$key. '] is [' .$filePath. ']');
			if(! file_exists($filePath)){
				$logger->info('This cache file does not exists skipping');
				return false;
			}
			else{
				$logger->info('Found cache file [' .$filePath. '] remove it');
	      		@unlink($filePath);
			}
		}


		/**
		 * Used to delete expired cache data
		 */
		public function deleteExpiredCache(){
			$logger = static::getLogger();
			$logger->debug('Deleting of expired cache files');
			$list = glob(CACHE_PATH . '*.cache');
			if(! $list){
				$logger->info('No cache files were found skipping');
			}
			else{
				$logger->info('Found [' .count($list). '] cache files to remove if expired');
				foreach ($list as $file) {
					$logger->debug('Processing the cache file [' .$file. ']');
					$data = file_get_contents($file);
		      		$data = @unserialize($this->compressCacheData ? gzinflate($data) : $data);
		      		if(! $data){
		      			$logger->warning('Can not unserialize the cache data for file [' .$file. ']');
		      		}
		      		else if(time() > $data[0]){
		      			$logger->info('The cache data for file [' .$file. '] already expired remove it');
		      			@unlink($file);
		      		}
		      		else{
		      			$logger->info('The cache data for file [' .$file. '] not yet expired skip it');
		      		}
				}
			}
		}	

		/**
		 * Remove all file from cache folder
		 */
		public function clean(){
			$logger = static::getLogger();
			$logger->debug('Deleting of all cache files');
			$list = glob(CACHE_PATH . '*.cache');
			if(! $list){
				$logger->info('No cache files were found skipping');
			}
			else{
				$logger->info('Found [' .count($list). '] cache files to remove');
				foreach ($list as $file) {
					$logger->debug('Processing the cache file [' .$file. ']');
					@unlink($file);
				}
			}
		}

		private function getFilePath($key){
			return CACHE_PATH . md5($key) . '.cache';
		}
	
	    /**
	     * @return boolean
	     */
	    public function isCompressCacheData(){
	        return $this->compressCacheData;
	    }

	    /**
	     * @param boolean $compressCacheData
	     *
	     * @return self
	     */
	    public function setCompressCacheData($compressCacheData){
	        $this->compressCacheData = $compressCacheData;
	    }
	}