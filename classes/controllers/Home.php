<?php
	defined('ROOT_PATH') || exit('Access denied');

	/**
	 * 
	 */
	class Home extends Controller {

		/**
		 * the class constructor
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * the default method
		 * @return null
		 */
		function index() {
			$this->response->render('home');
		}
	}
