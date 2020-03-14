<?php 
    defined('ROOT_PATH') || exit('Access denied');
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
        public function hash($value) {
            return crypt($value, self::$algo .
                    self::$cost .
                    '$' . $this->getUniqueSalt());
        }

        /**
         * Check if the hash and plain string is valid
         * @param  string $hash     the hashed string
         * @param  string $plain the plain text
         * 
         * @return boolean  true if is valid or false if not
         */
        public function check($hash, $plain) {
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
        private function getUniqueSalt() {
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
