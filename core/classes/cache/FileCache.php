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
		private $logger;
		
		
		public function __construct(Log $logger = null){
			if(! $this->isSupported()){
				show_error('The cache for file system is not available. Check the cache directory if is exists or is writable.');
			}
			/**
	         * instance of the Log class
	         */
	        if(is_object($logger)){
	          $this->logger = $logger;
	        }
	        else{
	            $this->logger =& class_loader('Log', 'classes');
	            $this->logger->setLogger('Library::FileCache');
	        }
			
			//if Zlib extension is not loaded set compressCacheData to false
			if(! extension_loaded('zlib')){
				$this->logger->warning('The zlib extension is not loaded set cache compress data to FALSE');
				$this->compressCacheData = false;
			}
		}

		/**
		 * This is used to get the cache data using the key
		 * @param  string $key the key to identify the cache data
		 * @return mixed      the cache data if exists else return false
		 */
		public function get($key){
			$this->logger->debug('Getting cache data for key ['. $key .']');
			$filePath = $this->getFilePath($key);
			if(! file_exists($filePath)){
				$this->logger->info('No cache file found for the key ['. $key .'], return false');
				return false;
			}
			$this->logger->info('The cache file [' .$filePath. '] for the key ['. $key .'] exists, check if the cache data is valid');
			$handle = fopen($filePath,'r');
			if(! is_resource($handle)){
				$this->logger->error('Can not open the file cache [' .$filePath. '] for the key ['. $key .'], return false');
				return false;
			}
			// Getting a shared lock 
		    flock($handle, LOCK_SH);
		    $data = file_get_contents($filePath);
      		fclose($handle);
      		$data = @unserialize($this->compressCacheData ? gzinflate($data) : $data);
      		if (! $data) {
      			$this->logger->error('Can not unserialize the cache data for the key ['. $key .'], return false');
		         // If unserializing somehow didn't work out, we'll delete the file
		         unlink($filePath);
		         return false;
	      	}
	      	if (time() > $data['expire']) {
	      		$this->logger->info('The cache data for the key ['. $key .'] already expired delete the cache file [' .$filePath. ']');
		        // Unlinking when the file was expired
		        unlink($filePath);
		        return false;
		     }
		     else{
		     	$this->logger->info('The cache not yet expire, now return the cache data for key ['. $key .'], the cache will expire at [' . date('Y-m-d H:i:s', $data['expire']) . ']');
		     	return $data['data'];
		     }
		}


		/**
		 * Save data to the cache
		 * @param string  $key  the key to identify this cache data
		 * @param mixed  $data the cache data
		 * @param integer $ttl  the cache life time
		 * @return boolean true if success otherwise will return false
		 */
		public function set($key, $data, $ttl = 0){
			$expire = time() + $ttl;
			$this->logger->debug('Setting cache data for key ['. $key .'], time to live [' .$ttl. '], expire at [' . date('Y-m-d H:i:s', $expire) . ']');
			$filePath = $this->getFilePath($key);
			$handle = fopen($filePath,'w');
			if(! is_resource($handle)){
				$this->logger->error('Can not open the file cache [' .$filePath. '] for the key ['. $key .'], return false');
				return false;
			}
			flock($handle, LOCK_EX); // exclusive lock, will get released when the file is closed
			//Serializing along with the TTL
		    $cacheData = serialize(array(
									'mtime' => time(),
									'expire' => $expire,
									'data' => $data,
									'ttl' => $ttl
									)
								);		   
		    $result = fwrite($handle, $this->compressCacheData ? gzdeflate($cacheData, 9) : $cacheData);
		    if(! $result){
		    	$this->logger->error('Can not write cache data into file [' .$filePath. '] for the key ['. $key .'], return false');
		    	fclose($handle);
		    	return false;
		    }
		    else{
		    	$this->logger->info('Cache data saved into file [' .$filePath. '] for the key ['. $key .']');
		    	fclose($handle);
				chmod($filePath, 0640);
				return true;
		    }
		}	


		/**
		 * Delete the cache data for given key
		 * @param  string $key the key for cache to be deleted
		 * @return boolean      true if the cache is delete, false if can't delete 
		 * the cache or the cache with the given key not exist
		 */
		public function delete($key){
			$this->logger->debug('Deleting of cache data for key [' .$key. ']');
			$filePath = $this->getFilePath($key);
			$this->logger->info('The file path for the key [' .$key. '] is [' .$filePath. ']');
			if(! file_exists($filePath)){
				$this->logger->info('This cache file does not exists skipping');
				return false;
			}
			else{
				$this->logger->info('Found cache file [' .$filePath. '] remove it');
	      		unlink($filePath);
				return true;
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
		public function getInfo($key){
			$this->logger->debug('Getting of cache info for key [' .$key. ']');
			$filePath = $this->getFilePath($key);
			$this->logger->info('The file path for the key [' .$key. '] is [' .$filePath. ']');
			if(! file_exists($filePath)){
				$this->logger->info('This cache file does not exists skipping');
				return false;
			}
			else{
				$this->logger->info('Found cache file [' .$filePath. '] check the validity');
	      		$data = file_get_contents($filePath);
				$data = @unserialize($this->compressCacheData ? gzinflate($data) : $data);
				if(! $data){
					$this->logger->warning('Can not unserialize the cache data for file [' . $filePath . ']');
					return false;
				}
				else{
					$this->logger->info('This cache data is OK check for expire');
					if(isset($data['expire']) && $data['expire'] > time()){
						$this->logger->info('This cache not yet expired return cache informations');
						$info = array(
							'mtime' => $data['mtime'],
							'expire' => $data['expire'],
							'ttl' => $data['ttl']
							);
						return $info;
					}
					else{
						$this->logger->info('This cache already expired return false');
						return false;
					}
				}
			}
		}


		/**
		 * Used to delete expired cache data
		 */
		public function deleteExpiredCache(){
			$this->logger->debug('Deleting of expired cache files');
			$list = glob(CACHE_PATH . '*.cache');
			if(! $list){
				$this->logger->info('No cache files were found skipping');
			}
			else{
				$this->logger->info('Found [' . count($list) . '] cache files to remove if expired');
				foreach ($list as $file) {
					$this->logger->debug('Processing the cache file [' . $file . ']');
					$data = file_get_contents($file);
		      		$data = @unserialize($this->compressCacheData ? gzinflate($data) : $data);
		      		if(! $data){
		      			$this->logger->warning('Can not unserialize the cache data for file [' . $file . ']');
		      		}
		      		else if(time() > $data['expire']){
		      			$this->logger->info('The cache data for file [' . $file . '] already expired remove it');
		      			unlink($file);
		      		}
		      		else{
		      			$this->logger->info('The cache data for file [' . $file . '] not yet expired skip it');
		      		}
				}
			}
		}	

		/**
		 * Remove all file from cache folder
		 */
		public function clean(){
			$this->logger->debug('Deleting of all cache files');
			$list = glob(CACHE_PATH . '*.cache');
			if(! $list){
				$this->logger->info('No cache files were found skipping');
			}
			else{
				$this->logger->info('Found [' . count($list) . '] cache files to remove');
				foreach ($list as $file) {
					$this->logger->debug('Processing the cache file [' . $file . ']');
					unlink($file);
				}
			}
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
	     * @return object
	     */
	    public function setCompressCacheData($status = true){
			//if Zlib extension is not loaded set compressCacheData to false
			if($status === true && ! extension_loaded('zlib')){
				
				$this->logger->warning('The zlib extension is not loaded set cache compress data to FALSE');
				$this->compressCacheData = false;
			}
			else{
				$this->compressCacheData = $status;
			}
			return $this;
	    }
		
		/**
		 * Check whether the cache feature for the handle is supported
		 *
		 * @return bool
		 */
		public function isSupported(){
			return CACHE_PATH && is_dir(CACHE_PATH) && is_writable(CACHE_PATH);
		}

		/**
	     * Return the Log instance
	     * @return object
	     */
	    public function getLogger(){
	      return $this->logger;
	    }

	    /**
	     * Set the log instance
	     * @param Log $logger the log object
	     */
	    public function setLogger(Log $logger){
	      $this->logger = $logger;
	      return $this;
	    }
		
		/**
		* Get the cache file full path for the given key
		*
		* @param string $key the cache item key
		* @return string the full cache file path for this key
		*/
		private function getFilePath($key){
			return CACHE_PATH . md5($key) . '.cache';
		}
	}
