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
        public function generate($html, $filename = 'output.pdf', $stream = true, $paper = 'A4', $orientation = 'portrait') {
            $this->logger->info('Generating of PDF document: filename [' . $filename . '], stream [' . ($stream ? 'TRUE' : 'FALSE') . '], paper [' . $paper . '], orientation [' . $orientation . ']');
            $this->dompdf->load_html($html);
            $this->dompdf->set_paper($paper, $orientation);
            $this->dompdf->render();
            if (!$stream) {
                return $this->dompdf->output();
            }
            //@codeCoverageIgnoreStart
            $this->dompdf->stream($filename);
        }
        //@codeCoverageIgnoreEnd
		
        /**
         * Return the instance of Dompdf
         *
         * @return object the dompdf instance
         */
        public function getDompdf() {
            return $this->dompdf;
        }
		
    }
