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
     *  @file bootstrap.php
     *  
     *  Contains the loading process: loading of constants, common functions, libraries 
     *  configurations, etc
     *  verification of the environment and the routing of the request.
     *  
     *  @package	core	
     *  @author	TNH Framework team
     *  @copyright	Copyright (c) 2017
     *  @license	http://opensource.org/licenses/MIT	MIT License
     *  @link	http://www.iacademy.cf
     *  @version 1.0.0
     *  @filesource
     */
	
    //if the application is running in CLI mode $_SESSION global variable is not available
    if (IS_CLI) {
        $_SESSION = array();
    }
		
    /**
     *  inclusion of global constants of the environment that contain : name of the framework,
     *  version, release date, version of PHP required, etc.
     */
    require_once CORE_PATH . 'constants.php';	
	
    /**
     *  include file containing commons functions used in the framework such: show_error, 
     *  fw_exception_handler, fw_error_handler, get_instance, etc.
     */
    require_once CORE_PATH . 'common.php';

    /**
     * Include of the file containing the BaseClass 
     */
    require_once CORE_CLASSES_PATH . 'BaseClass.php';

    /**
     * Include of the file containing the BaseStaticClass 
     */
    require_once CORE_CLASSES_PATH . 'BaseStaticClass.php';

    /**
     * The Benchmark class
     */
    $BENCHMARK = & class_loader('Benchmark');
	
    $BENCHMARK->mark('APP_EXECUTION_START');
	
    /**
     * instance of the Log class
     */
    $LOGGER = & class_loader('Log', 'classes');

    $LOGGER->setLogger('ApplicationBootstrap');

    $LOGGER->debug('Checking PHP version ...');	
	
    /**
     * Verification of the PHP environment: minimum and maximum version
     */
    if (version_compare(phpversion(), TNH_REQUIRED_PHP_MIN_VERSION, '<')) {
        show_error('Your PHP Version [' . phpversion() . '] is less than [' . TNH_REQUIRED_PHP_MIN_VERSION . '], please install a new version or update your PHP to the latest.', 'PHP Error environment');	
    } else if (version_compare(phpversion(), TNH_REQUIRED_PHP_MAX_VERSION, '>')) {
        show_error('Your PHP Version [' . phpversion() . '] is greather than [' . TNH_REQUIRED_PHP_MAX_VERSION . '] please install a PHP version that is compatible.', 'PHP Error environment');	
    }
    $LOGGER->info('PHP version [' . phpversion() . '] is OK [REQUIRED MINIMUM: ' . TNH_REQUIRED_PHP_MIN_VERSION . ', REQUIRED MAXIMUM: ' . TNH_REQUIRED_PHP_MAX_VERSION . '], application can work without any issue');

    /**
     * Setting of the PHP error message handling function
     */
    set_error_handler('fw_error_handler');

    /**
     * Setting of the PHP error exception handling function
     */
    set_exception_handler('fw_exception_handler');

    /**
     * Setting of the PHP shutdown handling function
     */
    register_shutdown_function('fw_shudown_handler');
	
    //if user have some composer packages
    $LOGGER->debug('Check for composer autoload');
    if (file_exists(VENDOR_PATH . 'autoload.php')) {
        $LOGGER->info('The composer autoload file exists include it');
        require_once VENDOR_PATH . 'autoload.php';
    } else {
        $LOGGER->info('The composer autoload file does not exist skipping');
    }
	
    $LOGGER->debug('Begin to load the required resources');

    /**
     * Load the EventInfo class file
     */
    require_once CORE_CLASSES_PATH . 'EventInfo.php';


    $BENCHMARK->mark('CONFIG_INIT_START');
    /**
     * Load configurations and using the 
     * static method "init()" to initialize the Config class .
     */
    $CONFIG = & class_loader('Config', 'classes');	
    $CONFIG->init();
    $BENCHMARK->mark('CONFIG_INIT_END');

    $BENCHMARK->mark('MODULE_INIT_START');
    /**
     * Load modules and using the 
     * static method "init()" to initialize the Module class.
     */
    $MODULE = & class_loader('Module', 'classes');
    $MODULE->init();
    $BENCHMARK->mark('MODULE_INIT_END');

    $LOGGER->debug('Loading Base Controller ...');
    /**
     * Include of the file containing the Base Controller class 
     */
    require_once CORE_CLASSES_PATH . 'Controller.php';
    $LOGGER->info('Base Controller loaded successfully');

    /**
     * Register controllers autoload function
     */
        spl_autoload_register('autoload_controller');

    /**
     * Loading Security class
     */
    $SECURITY = & class_loader('Security', 'classes');
    if (!$SECURITY->checkWhiteListIpAccess()) {
        show_error('You are not allowed to access this application');
        return;
    }
	
    /**
     * Loading Url class
     */
    $URL = & class_loader('Url', 'classes');
	
    if (get_config('cache_enable', false)) {
        /**
         * Load Cache interface file
         */
        require_once CORE_CLASSES_CACHE_PATH . 'CacheInterface.php';
        $cacheHandler = get_config('cache_handler');
        if (!$cacheHandler) {
            show_error('The cache feature is enabled in the configuration but the cache handler class is not set.');
        }
        $CACHE = null;
        //first check if the cache handler is the system driver
        if (file_exists(CORE_CLASSES_CACHE_PATH . $cacheHandler . '.php')) {
            $CACHE = & class_loader($cacheHandler, 'classes/cache');
        } else {
            //it's not a system driver use user library
            $CACHE = & class_loader($cacheHandler);
        }
        //check if the page already cached
        if (!empty($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            $RESPONSE = & class_loader('Response', 'classes');
            if ($RESPONSE->renderFinalPageFromCache($CACHE)) {
                return;
            }
        }
    }
	
    //load model class
    require_once CORE_CLASSES_MODEL_PATH . 'Model.php';
	
    $LOGGER->info('Everything is OK load Router library and dispatch the request to the corresponding controller');
    /**
     * Routing
     * instantiation of the "Router" class and request processing.
     */
    $ROUTER = & class_loader('Router', 'classes');
    $ROUTER->processRequest();
