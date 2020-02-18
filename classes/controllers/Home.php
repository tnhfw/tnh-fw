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
		 * The default method
		 * @return null
		 */
		public function index() {
			Loader::library('Assets');
			Loader::library('Html');
			$this->response->render('home');
		}
	}
