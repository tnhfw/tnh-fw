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
     *  @file constants.php
     *    
     *  This file contains the declaration of most of the constants used in the system, 
     *  for example: the version, the name of the framework, etc.
     *  
     *  @package	core	
     *  @author	TNH Framework team
     *  @copyright	Copyright (c) 2017
     *  @license	http://opensource.org/licenses/MIT	MIT License
     *  @link	http://www.iacademy.cf
     *  @version 1.0.0
     *  @filesource
     */

    /**
     *  The framework name
     */
    define('TNH_NAME', 'TNH Framework');

    /**
     *  The version of the framework in X.Y.Z format (Major, minor and bugs). 
     *  If there is the presence of the word "dev", it means that 
     *  it is a version under development.
     */
    define('TNH_VERSION', '1.0.0-dev');

    /**
     *  The release date of the framework
     */
    define('TNH_RELEASE_DATE', '2017/02/05');

    /**
     *  The minimum PHP version required to use the framework. 
     *  If the version of PHP installed is lower, then the application will not work.
     *  Note: we use the PHP version_compare function to compare the required version with 
     *  the version installed on your system.
     */
    define('TNH_REQUIRED_PHP_MIN_VERSION', '5.4');

    /**
     *  The maximum version of PHP required to use the framework. 
     *  If the version of PHP installed is higher than the required one, then the application will not work.
     */
    define('TNH_REQUIRED_PHP_MAX_VERSION', '7.1');
