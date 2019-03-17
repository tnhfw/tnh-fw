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
	interface CacheInterface{

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
		 * Used to delete expired cache data
		 */
		public function deleteExpiredCache();

		/**
		 * Remove all cache data
		 */
		public function clean();
	}