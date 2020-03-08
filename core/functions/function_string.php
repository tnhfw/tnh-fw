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

    /**
     *  @file function_string.php
     *
     *  This file contains the definition of the functions relating to the processing of strings characters.
     *
     *  @package	core
     *  @author	TNH Framework team
     *  @copyright	Copyright (c) 2017
     *  @license	http://opensource.org/licenses/MIT	MIT License
     *  @link	http://www.iacademy.cf
     *  @version 1.0.0
     *  @since 1.0.0
     *  @filesource
     */

    if (!function_exists('get_random_string')) {
        /**
         * Generate a random string
         * @param  string $type the type of generation. It can take the values: "alpha" for alphabetic characters,
         * "alnum" for alpha-numeric characters and "num" for numbers.
         * By default it is "alnum".
         * @param  integer $length the length of the string to generate. By default it is 10.
         * @param  boolean $lower if we return the generated string in lowercase (true). By default it's false.
         * @return string the generated string.
         */
        function get_random_string($type = 'alnum', $length = 10, $lower = false) {
            $chars = array(
                            'alpha' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                            'alnum' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
                            'num' => '1234567890'
                        );
            $str = null;
            if (isset($chars[$type])) {
                $str = $chars[$type];
            }
            $random = null;
            for ($i = 0; $i < $length; $i++) {
                $random .= $str[mt_rand() % strlen($str)];
            }
            if ($lower) {
                $random = strtolower($random);
            }
            return $random;
        }
    }
