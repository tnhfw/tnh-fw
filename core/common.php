<?php
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