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
     *  @file function_user_agent.php
     *    
     *  Contains most of the utility functions for agent, platform, mobile, browser, and other management.
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
            foreach ($ipServerVars as $var) {
                if (isset($_SERVER[$var])) {
                    $ip = $_SERVER[$var];
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
