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

    /**
     *  @file function_string.php
     *
     *  This file contains the definition of the functions relating to the processing of strings characters.
     *
     *  @package	core
     *  @author	Tony NGUEREZA
     *  @copyright	Copyright (c) 2017
     *  @license	https://opensource.org/licenses/gpl-3.0.html GNU GPL License (GPL)
     *  @link	http://www.iacademy.cf
     *  @version 1.0.0
     *  @since 1.0.0
     *  @filesource
     */

    if(! function_exists('get_random_string')){
        /**
         * Generate a random string
         * @param  string $type the type of generation. It can take the values: "alpha" for alphabetic characters,
         * "alnum" for alpha-numeric characters and "num" for numbers.
         * By default it is "alnum".
         * @param  integer $length the length of the string to generate. By default it is 10.
         * @param  boolean $lower if we return the generated string in lowercase (true). By default it's false.
         * @return string the generated string.
         */
        function get_random_string($type = 'alnum', $length = 10, $lower = false){
            $chars = array(
                            'alpha' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                            'alnum' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
                            'num' => '1234567890'
                        );
            $str = null;
            if(isset($chars[$type])){
                $str = $chars[$type];
            }
            $random = null;
            for($i = 0; $i < $length; $i++){
                $random .= $str[mt_rand() % strlen($str)];
            }
            if($lower){
                $random = strtolower($random);
            }
            return $random;
        }
    }
