<?php
	defined('ROOT_PATH') or exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework using HMVC architecture
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
	 * PDF library to generate PDF document using the library DOMPDF
	 */
	class PDF extends BaseClass {
		
		/**
		 * The dompdf instance
		 * @var Dompdf
		 */
		private $dompdf = null;
		
		/**
		 * Create PDF library instance
		 */
		public function __construct() {
			parent::__construct();

			require_once VENDOR_PATH . 'dompdf/dompdf_config.inc.php';
			$this->dompdf = new Dompdf();
		}

		/**
		 * Generate PDF document
		 * @param  string  $html        the HTML content to use for generation
		 * @param  string  $filename    the generated PDF document filename
		 * @param  boolean $stream      if need send the generated PDF to browser for download
		 * @param  string  $paper       the PDF document paper type like 'A4', 'A5', 'letter', etc.
		 * @param  string  $orientation the PDF document orientation like 'portrait', 'landscape'
		 * @return string|void               if $stream is true send PDF document to browser for download, else return the generated PDF
		 * content like to join in Email attachment of for other purpose use.
		 */
		public function generate($html, $filename = 'output.pdf', $stream = true, $paper = 'A4', $orientation = 'portrait'){
			$this->logger->info('Generating of PDF document: filename [' .$filename. '], stream [' .($stream ? 'TRUE':'FALSE'). '], paper [' .$paper. '], orientation [' .$orientation. ']');
			$this->dompdf->load_html($html);
			$this->dompdf->set_paper($paper, $orientation);
			$this->dompdf->render();
			if($stream){
				$this->dompdf->stream($filename);
			} else{
				return $this->dompdf->output();
			}
		}
		
		/**
		* Return the instance of Dompdf
		*
		* @return object the dompdf instance
		*/
		public function getDompdf() {
			return $this->dompdf;
		}
		
	}
