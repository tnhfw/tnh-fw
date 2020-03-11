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
     *  @file function_user_agent.php
     *    
     *  Contains most of the utility functions for agent, platform, mobile, browser, and other management.
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
	
	 
    if (!function_exists('get_ip')) {
        /**
         *  Retrieves the user's IP address
         *  
         *  This function allows to retrieve the IP address of the client
         *  even if it uses a proxy, the actual IP address is retrieved.
         *  
         *  @return string the IP address.
         */
        function get_ip() {
            $ip = '127.0.0.1';
            $ipServerVars = array(
                                'REMOTE_ADDR',
                                'HTTP_CLIENT_IP',
                                'HTTP_X_FORWARDED_FOR',
                                'HTTP_X_FORWARDED',
                                'HTTP_FORWARDED_FOR',
                                'HTTP_FORWARDED'
                            );
            $globals = & class_loader('GlobalVar', 'classes');
            foreach ($ipServerVars as $var) {
                if ($globals->server($var)) {
                    $ip = $globals->server($var);
                    break;
                }
            }
            // Strip any secondary IP etc from the IP address
            if (strpos($ip, ',') > 0) {
                $ip = substr($ip, 0, strpos($ip, ','));
            }
            return $ip;
        }
    }
