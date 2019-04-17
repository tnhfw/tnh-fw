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
		function index() {
			Loader::library('Assets');
			Loader::library('Html');
			Loader::library('Url');
			$this->response->render('home');
		}
	}
