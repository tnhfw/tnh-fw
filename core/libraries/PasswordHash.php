<?php 

	class PasswordHash{
		
		 //blowfish
		private static $algo = '$2a';
		// cost parameter
		private static $cost = '$10';

		public static function uniqueSalt() {
			return substr(sha1(mt_rand()), 0, 22);
		}

		public static function hash($password) {

			return crypt($password, self::$algo .
					self::$cost .
					'$' . self::uniqueSalt());
		}

		public static function check($hash, $password) {
			$full_salt = substr($hash, 0, 29);
			$new_hash = crypt($password, $full_salt);
			return ($hash == $new_hash);
		}
		
		
	}