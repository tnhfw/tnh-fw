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

    class StringHash {
		 
        /**
         * Using blowfish method
         * CRYPT_BLOWFISH = 1
         * Recommended algo since PHP 5.3.7 is "$2y$"
         * Before PHP 5.3.7 can use "$2a$" but this have some security issue
         * @see  http://www.php.net/security/crypt_blowfish.php 
         * @var string
         */
        private static $algo = '$2y$';
		
        /**
         * Cost parameter value
         * For CRYPT_BLOWFISH possible value are: "04", "05", "06", "07", "08", "09", "10", etc. until 
         * "30", "31"
         * 
         * @var string
         */
        private static $cost = '10';

        /**
         * Hash the given string
         * @param  string $value the plain string text to be hashed
         * 
         * @return string           the hashed string
         */
        public static function hash($value) {
            return crypt($value, self::$algo .
                    self::$cost .
                    '$' . self::uniqueSalt());
        }

        /**
         * Check if the hash and plain string is valid
         * @param  string $hash     the hashed string
         * @param  string $plain the plain text
         * 
         * @return boolean  true if is valid or false if not
         */
        public static function check($hash, $plain) {
            $fullSalt = substr($hash, 0, 29);
            $newHash = crypt($plain, $fullSalt);
            return $hash === $newHash;
        }	

        /**
         * Get the unique salt for the string hash
         * Note: extension openssl need to be available for this to work
         * 
         * @return string the unique generated salt
         */
        private static function uniqueSalt() {
            /* To generate the salt, first generate enough random bytes. Because
             * base64 returns one character for each 6 bits, so we should generate
             * at least 22*6/8 = 16.5 bytes, so we generate 17 bytes. Then we get the first
             * 22 base64 characters
             */

            /* As blowfish takes a salt with the alphabet ./A-Za-z0-9 we have to
             * replace any '+', '=' in the base64 string with '..'.
             */
            $random = base64_encode(openssl_random_pseudo_bytes(17));
            //take only the first 22 caracters
            $random = substr($random, 0, 22);

            //replace +,= by .
            return strtr($random, '+=', '..');
        }
    }
