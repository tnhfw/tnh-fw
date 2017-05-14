<?php
		
	function show_error($msg, $title = 'error'){
		$data['error'] = $msg;
		$data['title'] = ucfirst($title);
		Log::error('['.$title.'] '.strip_tags($msg));
		Response::sendError($data);
		die();
	}
	
	function exception_handler($ex){
		show_error('Une exception est survenue sur le fichier <b>'.$ex->getFile().'</b> en ligne <b>'.$ex->getLine().'</b> cause : '.$ex->getMessage(), 'PHP Exception #'.$ex->getCode());
		return true;
	}
	
	function error_handler($errno , $errstr, $errfile , $errline, array $errcontext){
		if (!(error_reporting() & $errno)) {
			return;
		}
		$error_type = 'error';
		switch ($errno) {
			case E_USER_ERROR:
				$error_type = 'error';
				break;

			case E_USER_WARNING:
				$error_type = 'warning';
				break;

			case E_USER_NOTICE:
				$error_type = 'notice';
				break;

			default:
				$error_type = 'error';
				break;
		}
		show_error('Une erreur est survenue sur le fichier <b>'.$errfile.'</b> en ligne <b>'.$errline.'</b> cause : '.$errstr, 'PHP '.$error_type);
		return true;
	}
	
	function & get_instance(){
		return Controller::get_instance();
	}
	
?>