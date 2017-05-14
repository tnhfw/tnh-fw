<?php
defined('ROOT_PATH') or exit('Access denied');

class Home extends Controller{


	public function __construct(){
		parent::__construct();
	}

	function index(){
		$this->response->render('home');
	}
}
