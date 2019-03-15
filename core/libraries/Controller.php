<?php
	defined('ROOT_PATH') || exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework created using the concept of codeigniter with bootstrap twitter
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

	class Controller{
		/**
		 * The name of the module if this controller belong to an module
		 * @var string
		 */
		public $module_name = null;

		/**
		 * The singleton of the super object
		 * @var Controller
		 */
		private static $instance;

		/**
		 * The logger instance
		 * @var Log
		 */
		protected $logger;

		/**
		 * Class constructor
		 */
		public function __construct(){
			$this->logger =& class_loader('Log');
			$this->logger->setLogger('MainController');
			self::$instance = & $this;

			$this->logger->debug('Adding the loaded classes to the super instance');
			foreach (class_loaded() as $var => $class){
				$this->$var =& class_loader($class);
			}

			$this->logger->debug('Loading the required classes into super instance');
			$this->loader =& class_loader('Loader');
			$this->request =& class_loader('Request');
			$this->response =& class_loader('Response');
			$this->lang =& class_loader('Lang');
			
			$this->logger->debug('Setting the supported languages');
			//add the supported languages ('key', 'display name')
			$languages = get_config('languages', null);
			if(!empty($languages)){
				foreach($languages as $k => $v){
					$this->lang->addLang($k, $v);
				}
			}
			unset($languages);
			//set session config
			$this->logger->debug('Setting PHP application session handler');
			set_session_config();
		}


		/**
		 * This is a very useful method it's used to get the super object instance
		 * @return Controller the super object instance
		 */
		public static function &get_instance(){
			return self::$instance;
		}
	}
