<?php
	defined('ROOT_PATH') or exit('Access denied');
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

	/**
	 * PDF library to generate PDF document using the vendor DOMPDF
	 */
	class PDF{
		/**
		 * the super controller instance
		 * @var Object
		 */
		protected $OBJ = null;

		private $logger;
		
		public function __construct(){
			if(!class_exists('Log')){
	            //here the Log class is not yet loaded
	            //load it manually
	            require_once CORE_LIBRARY_PATH . 'Log.php';
	        }
	        $this->logger = new Log();
	        $this->logger->setLogger('Library::PDF');

			require_once VENDOR_PATH.'dompdf/dompdf_config.inc.php';
			$dompdf = new Dompdf();
			$this->OBJ = & get_instance();
			$this->OBJ->dompdf = $dompdf;
		}

		public function generate($html, $filename = '', $stream = true, $paper = 'A4', $orientation = 'portrait'){
			$this->logger->info('Generate PDF document: filename [' .$filename. '], stream [' .($stream? 'TRUE':'FALSE'). '], paper [' .$paper. '], orientation [' .$orientation. ']');
			$this->OBJ->dompdf->load_html($html);
			$this->OBJ->dompdf->set_paper($paper, $orientation);
			$this->OBJ->dompdf->render();
			if($stream){
				$this->OBJ->dompdf->stream($filename);
			}
			else{
				return $this->OBJ->dompdf->output();
			}
		}
		
	}
