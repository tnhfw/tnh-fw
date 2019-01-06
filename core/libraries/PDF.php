<?php
	defined('ROOT_PATH') or exit('Access denied');
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
