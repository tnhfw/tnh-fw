<?php 
	defined('ROOT_PATH') || exit('Access denied');
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

	class PasswordHash{
		 //blowfish
		private static $algo = '$2a';
		// cost parameter
		private static $cost = '$10';

		/**
		 * Get the unique salt for password hash
		 * @return string the unique generated salt
		 */
		private static function uniqueSalt() {
			return substr(sha1(mt_rand()), 0, 22);
		}

		/**
		 * Hash the given password
		 * @param  string $password the plain password text to be hashed
		 * @return string           the hashed password
		 */
		public static function hash($password) {
			return crypt($password, self::$algo .
					self::$cost .
					'$' . self::uniqueSalt());
		}

		/**
		 * Check if the hash and plain password is valid
		 * @param  string $hash     the hashed password
		 * @param  string $password the plain text password
		 * @return boolean  true if is valid or false if not
		 */
		public static function check($hash, $password) {
			$full_salt = substr($hash, 0, 29);
			$new_hash = crypt($password, $full_salt);
			return ($hash == $new_hash);
		}	
	}