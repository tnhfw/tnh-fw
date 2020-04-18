<?php
    defined('ROOT_PATH') or exit('Access denied');
    /**
     * TNH Framework
     *
     * A simple PHP framework using HMVC architecture
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2017 TNH Framework
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in all
     * copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
     * SOFTWARE.
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
         * The PDF generated filename
         * @var string
         */
        private $filename = 'output.pdf';

        /**
         * The HTML content
         * @var string
         */
        private $html = null;

        /**
         * The PDF document paper type like 'A4', 'A5', 'letter', etc.
         * @var string
         */
        private $paper = 'A4';

        /**
         * The PDF document orientation like 'portrait', 'landscape'
         * @var string
         */
        private $orientation = 'portrait';
		
        /**
         * Create PDF library instance
         */
        public function __construct() {
            parent::__construct();
            require_once VENDOR_PATH . 'dompdf/dompdf_config.inc.php';
            $this->dompdf = new Dompdf();
        }

        /**
         * Return the instance of Dompdf
         *
         * @return object the dompdf instance
         */
        public function getDompdf() {
            return $this->dompdf;
        }

        /**
         * Return the instance of Canvas
         *
         * @return object the canvas instance
         */
        public function getCanvas() {
            return $this->dompdf->get_canvas();
        }

        /**
         * This method is the shortcut to Dompdf::render
         * @return object the current instance
         */
        public function render() {
           $this->dompdf->load_html($this->html);
           $this->dompdf->set_paper($this->paper, $this->orientation);
           $this->dompdf->render(); 
           return $this;
        }

        /**
         * Set the filename of generated PDF document
         * @param string $filename the filename
         *
         * @return object the current instance
         */
        public function setFilename($filename) {
            if(stripos($filename, '.pdf') === false) {
                $filename .= '.pdf';     
            }
            $this->filename = $filename;
            return $this;
        }

        /**
         * Set the HTML content to use to generate the PDF
         * @param string $html the content of HTML
         *
         * @return object the current instance
         */
        public function setHtml($html) {
            $this->html = $html; 
            return $this;
        }

        /**
         * Set the page paper of the generated PDF
         * @param string $paper the page paper like "A4", "letter", etc.
         *
         * @return object the current instance
         */
        public function setPaper($paper) {
            $this->paper = $paper; 
            return $this;
        }

        /**
         * Set the page orientation of the generated PDF to "portrait"
         *
         * @return object the current instance
         */
        public function portrait() {
            $this->orientation = 'portrait'; 
            return $this;
        }

        /**
         * Set the page orientation of the generated PDF to "portrait"
         *
         * @return object the current instance
         */
        public function landscape() {
            $this->orientation = 'landscape'; 
            return $this;
        }

        /**
         * Download the generated PDF document
         * @codeCoverageIgnore
         * 
         * @return void
         */
        public function download() {
            $this->logger->info('Download of PDF document: filename [' . $this->filename . '], '
                                . 'paper [' . $this->paper . '], orientation [' . $this->orientation . ']');
            $this->prepare();
            $this->dompdf->stream($this->filename);
        }

        /**
         * Return the content of the generated PDF document as string
         * @return string
         */
        public function content() {
            $this->logger->info('Return of PDF document as string: paper '
                                . '[' . $this->paper . '], orientation [' . $this->orientation . ']');
            $this->prepare();
            return $this->dompdf->output();
        }

        /**
         * Save the content of the generated PDF document on the server filesystem
         * @return void
         */
        public function save() {
            $this->logger->info('Saving PDF document : filename path [' . $this->filename . '], paper '
                                . '[' . $this->paper . '], orientation [' . $this->orientation . ']');
            file_put_contents($this->filename, $this->content());
        }

        /**
         * Prepare the PDF to generate 
         * @return void
         */
        protected function prepare() {
            //If the canvas instance is null so means the method "render"
            // not yet called
            if ($this->dompdf->get_canvas() === null) {
                $this->render();  
            }
        }
		
    }
