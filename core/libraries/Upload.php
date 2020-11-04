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
     *    Upload
     *
     *    A complete class to upload files with php 5 or higher, but the best: very simple to use.
     *
     *    @author Olaf Erlandsen <info@webdevfreelance.com>
     *    @author http://www.webdevfreelance.com/
     *
     *    @package FileUpload
     *    @version 1.5
     */
    class Upload extends BaseClass {

        /**
         *   Version
         *
         *   @since      1.5
         *   @version    1.0
         */
        const VERSION = '1.5';

        /**
         *    Upload function name
         *    Remember:
         *        Default function: move_uploaded_file
         *        Native options:
         *            - move_uploaded_file (Default and best option)
         *            - copy
         *
         *    @since        1.0
         *    @version    1.0
         *    @var        string
         */
        private $uploadFunction = 'move_uploaded_file';

        /**
         *    Array with the information obtained from the
         *    variable $_FILES or $HTTP_POST_FILES.
         *
         *    @since        1.0
         *    @version    1.0
         *    @var        array
         */
        private $uploadedFileData = array();

        /**
         *    If the file you are trying to upload already exists it will
         *    be overwritten if you set the variable to true.
         *
         *    @since        1.0
         *    @version    1.0
         *    @var        boolean
         */
        private $overwriteFile = false;

        /**
         *    Input element
         *    Example:
         *        <input type="file" name="file" />
         *    Result:
         *        FileUpload::$input = file
         *
         *    @since        1.0
         *    @version    1.0
         *    @var        string
         */
        private $input;

        /**
         *    Path output
         *
         *    @since        1.0
         *    @version    1.0
         *    @var        string
         */
        private $destinationDirectory;

        /**
         *    Output filename
         *
         *    @since        1.0
         *    @version    1.0
         *    @var        string
         */
        private $filename;

        /**
         *    Max file size
         *
         *    @since        1.0
         *    @version    1.0
         *    @var        float
         */
        private $maxFileSize = 0.0;

        /**
         *    List of allowed mime types
         *
         *    @since        1.0
         *    @version    1.0
         *    @var        array
         */
        private $allowedMimeTypes = array();

        /**
         *    Callbacks
         *
         *    @since        1.0
         *    @version    1.0
         *    @var        array
         */
        private $callbacks = array();

        /**
         *    File object
         *
         *    @since        1.0
         *    @version    1.0
         *    @var        object
         */
        private $file;

        /**
         *    Helping mime types
         *
         *    @since        1.0
         *    @version    1.0
         *    @var        array
         */
        private $mimeHelping = array(
            'text'      =>    array('text/plain',),
            'image'     =>    array(
                'image/jpeg',
                'image/jpg',
                'image/pjpeg',
                'image/png',
                'image/gif',
            ),
            'document'  =>    array(
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-powerpoint',
                'application/vnd.ms-excel',
                'application/vnd.oasis.opendocument.spreadsheet',
                'application/vnd.oasis.opendocument.presentation',
            ),
            'video'    =>    array(
                'video/3gpp',
                'video/3gpp',
                'video/x-msvideo',
                'video/avi',
                'video/mpeg4',
                'video/mp4',
                'video/mpeg',
                'video/mpg',
                'video/quicktime',
                'video/x-sgi-movie',
                'video/x-ms-wmv',
                'video/x-flv',
            ),
        );

        /**
         * The loaded translations errors messages
         * @var array
         */
        private $errorMessages = array();

        /**
         * The upload error message
         * @var string
         */
        private $error = null;


        /**
         *    Construct
         *
         *    @since     0.1
         *    @version   1.0.1
         *    @return    object
         *    @method    object    __construct
         */
        public function __construct() {
            parent::__construct();

            //Load language messages
            $this->loadLangMessages();

            $this->file = array(
                'status'                =>    false, // True: success upload
                'mime'                  =>    '', // Empty string
                'filename'              =>    '', // Empty string
                'original'              =>    '', // Empty string
                'size'                  =>    0, // 0 Bytes
                'sizeFormated'          =>    '0B', // 0 Bytes
                'destination'           =>    './', // Default: ./
                'allowed_mime_types'    =>    array(), // Allowed mime types
                'error'                 =>    null, // File error
            );

            // Change dir to current dir
            $this->destinationDirectory = dirname(__FILE__) . DIRECTORY_SEPARATOR;

            // Set file array
            $this->uploadedFileData = get_instance()->globalvar->files();
            $this->logger->info('The upload file information are : ' . stringify_vars($this->uploadedFileData));
        }

        
        /**
         *    Set input.
         *    If you have $_FILES["file"], you must use the key "file"
         *    Example:
         *        $object->setInput("file");
         *
         *    @since     1.0
         *    @version   1.0
         *    @param     string      $input
         *    @return    object
         *    @method    boolean     setInput
         */
        public function setInput($input) {
            $this->input = $input;
            return $this;
        }

        
        /**
         *    Set new filename
         *    Example:
         *        FileUpload::setFilename("new file.txt")
         *    Remember:
         *        Use %s to retrive file extension
         *
         *    @since     1.0
         *    @version   1.0
         *    @param     string      $filename
         *    @return    object
         *    @method    boolean     setFilename
         */
        public function setFilename($filename) {
            if ($this->isFilename($filename)) {
                $this->filename = $filename;
            }
            return $this;
        }

        /**
         *    Set automatic filename
         *
         *    @since     1.0
         *    @version   1.5
         *    @param     string      $extension
         *    @return    object
         *    @method    boolean     setAutoFilename
         */
        public function setAutoFilename() {
            $this->filename = sha1(mt_rand(1, 9999) . uniqid()) . time();
            return $this;
        }

        /**
         *    Set file size limit
         *
         *    @since     1.0
         *    @version   1.0
         *    @param     double     $fileSize
         *    @return    object
         *    @method    boolean     setMaxFileSize
         */
        public function setMaxFileSize($fileSize) {
            $size = $this->sizeInBytes($fileSize);
            if (is_numeric($size) && $size > -1) {
                // Get PHP upload max file size config
                $phpMaxUploadSize = ini_get('upload_max_filesize');
                // check difference
                if ($this->sizeInBytes((int) $phpMaxUploadSize) < $size) {
                    $this->logger->warning('The upload max file size you set [' . $fileSize . '] '
                            . 'is greather than the PHP configuration for upload max file size [' . $phpMaxUploadSize . ']');
                }
                $this->maxFileSize = $size;
            }
            return $this;
        }

         /**
         *    Append a mime type to allowed mime types
         *
         *    @since     1.0
         *    @version   1.0.1
         *    @param     string      $mime
         *    @return    object
         *    @method    boolean     setAllowMimeType
         */
        public function setAllowMimeType($mime) {
            $this->allowedMimeTypes[] = strtolower($mime);
            $this->file['allowed_mime_types'][] = strtolower($mime); 
            return $this;
        }

        /**
         *    Set array mime types
         *
         *    @since     1.0
         *    @version   1.0
         *    @param     array       $mimes
         *    @return    object
         *    @method    boolean     setAllowMimeTypes
         */
        public function setAllowMimeTypes(array $mimes) {
            array_map(array($this, 'setAllowMimeType'), $mimes);
            return $this;
        }

        /**
         *    Set allowed mime types from mime helping
         *
         *    @since     1.0.1
         *    @version   1.0.1
         *    @return    object
         *    @method    boolean    setMimeHelping
         */
        public function setMimeHelping($name) {
            if (array_key_exists($name, $this->mimeHelping)) {
                return $this->setAllowMimeTypes($this->mimeHelping[$name]);
            }
            return $this;
        }

        /**
         *    Clear allowed mime types cache
         *
         *    @since     1.0
         *    @version   1.0
         *    @return    object
         *    @method    boolean    clearAllowedMimeTypes
         */
        public function clearAllowedMimeTypes() {
            $this->allowedMimeTypes = array();
            $this->file['allowed_mime_types'] = array();
            return $this;
        }

        /**
         *    Set input callback
         *
         *    @since     1.0
         *    @version   1.0
         *    @param     mixed       $callback
         *    @return    object
         *    @method    boolean     setCallbackInput
         */
        public function setCallbackInput($callback) {
            if (is_callable($callback, false)) {
                $this->callbacks['input'] = $callback;
            }
            return $this;
        }

        /**
         *    Set output callback
         *
         *    @since     1.0
         *    @version   1.0
         *    @param     mixed       $callback
         *    @return    object
         *    @method    boolean     setCallbackOutput
         */
        public function setCallbackOutput($callback) {
            if (is_callable($callback, false)) {
                $this->callbacks['output'] = $callback;
            }
            return $this;
        }

        /**
         *    Set function to upload file
         *    Examples:
         *        1.- FileUpload::setUploadFunction("move_uploaded_file");
         *        2.- FileUpload::setUploadFunction("copy");
         *
         *    @since     1.0
         *    @version   1.0
         *    @param     string      $function
         *    @return    object
         *    @method    boolean     setUploadFunction
         */
        public function setUploadFunction($function) {
            if (is_callable($function)) {
                $this->uploadFunction = $function;
            }
            return $this;
        }

        /**
         *    Allow overwriting files
         *
         *    @since      1.0
         *    @version    1.0
         *    @return     object
         *    @method     boolean    allowOverwriting
         */
        public function allowOverwriting($status = true) {
            $this->overwriteFile = $status;
            return $this;
        }

         /**
         *    Get the allow overwriting
         *    @return    boolean
         */
        public function isAllowOverwriting() {
            return $this->overwriteFile ;
        }

        /**
         *    Set destination output
         *
         *    @since     1.0
         *    @version   1.0
         *    @param     string      $directory      Destination path
         *    @return    object
         *    @method    boolean     setDestinationDirectory
         */
        public function setDestinationDirectory($directory) {
            $dir = realpath($directory);
            $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if ($this->isDirpath($dir)) {
                if ($this->dirExists($dir)) {
                    $this->destinationDirectory = $dir;
                    chdir($dir);
                } else {
                    $this->logger->warning('The upload directory [' . $directory . '] does not exist');
                }
            }
            return $this;
        }

        /**
         * Check if the file is uploaded
         * @return boolean
         */
        public function isUploaded() {
            return isset($this->uploadedFileData[$this->input])
                        && is_uploaded_file($this->uploadedFileData[$this->input]['tmp_name']);
        }

        /**
         *    Upload file
         *
         *    @since     1.0
         *    @version   1.0.1
         *    @return    boolean
         *    @method    boolean    save
         */
        public function save() {
            if (!$this->isUploaded()) {
                return false;
            }
            // set original filename if not have a new name
            $this->setFilenameUsingUploadedData();

            // set file info
            $this->file['mime']         = $this->uploadedFileData[$this->input]['type'];
            $this->file['tmp']          = $this->uploadedFileData[$this->input]['tmp_name'];
            $this->file['original']     = $this->uploadedFileData[$this->input]['name'];
            $this->file['size']         = $this->uploadedFileData[$this->input]['size'];
            $this->file['sizeFormated'] = $this->sizeFormat($this->file['size']);
            $this->file['destination']  = $this->destinationDirectory . $this->filename;
            $this->file['filename']     = $this->filename;
            $this->file['error']        = $this->uploadedFileData[$this->input]['error'];

            $this->logger->info('The upload file information to process is : ' . stringify_vars($this->file));

            if ($this->uploadHasError()) {
                return false;
            }
            // Execute input callback
            $this->runCallback('input');

            $this->file['status'] = call_user_func_array(
                $this->uploadFunction, array(
                    $this->uploadedFileData[$this->input]['tmp_name'],
                    $this->destinationDirectory . $this->filename
                )
            );

            // Execute output callback
            $this->runCallback('output');

            return $this->file['status'];
        }

        /**
         * Get the upload error message
         * @return string
         */
        public function getError() {
            return $this->error;
        }

         /**
         *    Retrive status of upload
         *
         *    @since     1.0
         *    @version   1.0
         *    @return    boolean
         *    @method    boolean    getStatus
         */
        public function getStatus() {
            return $this->file['status'];
        }

        /**
         *    File info
         *
         *    @since      1.0
         *    @version    1.0
         *    @return     object
         *    @method     object    getInfo
         */
        public function getInfo() {
            return (object) $this->file;
        }


        /**
         *    Check file exists
         *
         *    @since      1.0
         *    @version    1.0.1
         *    @param      string     $file
         *    @return     boolean
         *    @method     boolean    fileExists
         */
        protected function fileExists($file) {
            return $this->isFilename($file) 
                                && file_exists($file) 
                                && is_file($file);
        }

         /**
         * Set the filename if is empty using the uploaded data information
         *
         * @return object the current instance
         */
        protected function setFilenameUsingUploadedData() {
            // set original filename if not have a new name
            if (empty($this->filename)) {
                $this->filename = $this->uploadedFileData[$this->input]['name'];
            } else {
                // Replace %s for extension in filename
                // Before: /[\w\d]*(.[\d\w]+)$/i
                // After: /^[\s[:alnum:]\-\_\.]*\.([\d\w]+)$/iu
                // Support unicode(utf-8) characters
                // Example: "русские.jpeg" is valid; "Zhōngguó.jpeg" 
                // is valid; "Tønsberg.jpeg" is valid
                $extension = preg_replace(
                    '/^[\p{L}\d\s\-\_\.\(\)]*\.([\d\w]+)$/iu',
                    '$1',
                    $this->uploadedFileData[$this->input]['name']
                );
                $this->filename = $this->filename . '.' . $extension;
            }
            return $this;
        }

        /**
         *    Check dir exists
         *
         *    @since        1.0
         *    @version    1.0.1
         *    @param      string     $path
         *    @return     boolean
         *    @method     boolean    dirExists
         */
        protected function dirExists($path) {
            return $this->isDirpath($path) 
                                && file_exists($path) 
                                && is_dir($path);
        }

        /**
         *    Check valid filename
         *
         *    @since     1.0
         *    @version   1.0.1
         *    @param     string      $filename
         *    @return    boolean
         *    @method    boolean     isFilename
         */
        protected function isFilename($filename) {
            $filename = basename($filename);
            return !empty($filename);
        }

        /**
         *    Validate mime type with allowed mime types,
         *    but if allowed mime types is empty, this method return true
         *
         *    @since     1.0
         *    @version   1.0
         *    @param     string      $mime
         *    @return    boolean
         *    @method    boolean     checkMimeType
         */
        protected function checkMimeType($mime) {
            if (count($this->allowedMimeTypes) === 0) {
                return true;
            }
            return in_array(strtolower($mime), $this->allowedMimeTypes);
        }

        /**
         *    Validate max upload file size,
         *    but if max size is 0, this method return true
         *
         *    @since     1.0
         *    @version   1.0
         *    @param     double|integer      $size
         *    @return    boolean
         */
        protected function checkMaxSize($size) {
            if ($this->maxFileSize <= 0) {
                return true;
            }
            return $this->maxFileSize >= $size;
        }

         /**
         *    Check the file overwritting
         *    @since     1.0
         *    @version   1.0
         *    @return    boolean
         */
        protected function checkFileOverwritting() {
            if ($this->fileExists($this->destinationDirectory . $this->filename)) {
                return $this->overwriteFile;
            }
            return true;
        }
       
        /**
         *    Check valid path
         *
         *    @since        1.0
         *    @version    1.0.1
         *    @param        string    $filename
         *    @return     boolean
         *    @method     boolean    isDirpath
         */
        protected function isDirpath($path) {
            if (DIRECTORY_SEPARATOR == '/') {
                return (preg_match('/^[^*?"<>|:]*$/', $path) == 1);
            }
            return (preg_match("/^[^*?\"<>|:]*$/", substr($path, 2)) == 1);
        }

        /**
         *    File size for humans.
         *
         *    @since      1.0
         *    @version    1.0
         *    @param      integer    $bytes
         *    @param      integer    $precision
         *    @return     string
         *    @method     string     sizeFormat
         */
        protected function sizeFormat($size, $precision = 2) {
            if ($size > 0) {
                $base     = log($size) / log(1024);
                $suffixes = array('B', 'K', 'M', 'G', 'T');
                $suffixe = '';
                if (isset($suffixes[floor($base)])) {
                    $suffixe = $suffixes[floor($base)];
                }
                return round(pow(1024, $base - floor($base)), $precision) . $suffixe;
            }
            return null;
        }

        
        /**
         *    Convert human file size to bytes
         *
         *    @since      1.0
         *    @version    1.0.1
         *    @param      integer|double    $size
         *    @return     integer|double
         *    @method     string     sizeInBytes
         */
        protected function sizeInBytes($size) {
            $unit = 'B';
            $units = array('B' => 0, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4);
            $matches = array();
            preg_match('/(?<size>[\d\.]+)\s*(?<unit>b|k|m|g|t)?/i', $size, $matches);
            if (array_key_exists('unit', $matches)) {
                $unit = strtoupper($matches['unit']);
            }
            return (floatval($matches['size']) * pow(1024, $units[$unit]));
        }

        /**
         * Set the upload error message
         * @param string $message the upload error message to set
         *
         * @return object the current instance
         */
        protected function setError($message) {
            $this->logger->error('The file upload got error : ' . $message);
            $this->error = $message;
            return $this;
        }

        /**
         * Run the callbacks in the file uploaded
         * @param string $type the type of callback "input" or "output"
         * @return void 
         */
        protected function runCallback($type) {
            if (!empty($this->callbacks[$type])) {
                call_user_func($this->callbacks[$type], (object) $this->file);
            }
        }

        /**
         * Check if file upload has error
         * @return boolean
         */
        protected function uploadHasError() {
            //check if file upload is  allowed in the configuration
            if (!ini_get('file_uploads')) {
                $this->setError($this->errorMessages['file_uploads']);
                return true;
            }

            //check for php upload error
            $error = $this->getPhpUploadErrorMessageByCode($this->file['error']);
            if ($error) {
                $this->setError($error);
                return true;
            }
            
            //check for mime type
            if (!$this->checkMimeType($this->file['mime'])) {
                $this->setError($this->errorMessages['accept_file_types']);
                return true;
            }

            // Check file size
            if (!$this->checkMaxSize($this->file['size'])) {
                $this->setError(sprintf($this->errorMessages['max_file_size'], $this->sizeFormat($this->maxFileSize)));
                return true;
            }

            // Check if exists file
            if (!$this->checkFileOverwritting()) {
                $this->setError($this->errorMessages['overwritten_not_allowed']);
                return true;
            }
            return false;
        }

        /**
         * Get the PHP upload error message for the given code
         * @param  int $code the error code
         * @return string the error message
         */
        protected function getPhpUploadErrorMessageByCode($code) {
            $error = null;
            $codeMessageMaps = array(
                1 => $this->errorMessages['upload_err_ini_size'],
                2 => $this->errorMessages['upload_err_form_size'],
                3 => $this->errorMessages['upload_err_partial'],
                4 => $this->errorMessages['upload_err_no_file'],
                6 => $this->errorMessages['upload_err_no_tmp_dir'],
                7 => $this->errorMessages['upload_err_cant_write'],
                8 => $this->errorMessages['upload_err_extension'],
            );
            if (isset($codeMessageMaps[$code])) {
                $error = $codeMessageMaps[$code];
            }
            return $error;
        }

        /**
         * Load the language messages for upload
         */
        protected function loadLangMessages() {
            get_instance()->loader->lang('file_upload');
            $this->errorMessages = array(
                                    'upload_err_ini_size'     => get_instance()->lang->get('fu_upload_err_ini_size'),
                                    'upload_err_form_size'    => get_instance()->lang->get('fu_upload_err_form_size'),
                                    'upload_err_partial'      => get_instance()->lang->get('fu_upload_err_partial'),
                                    'upload_err_no_file'      => get_instance()->lang->get('fu_upload_err_no_file'),
                                    'upload_err_no_tmp_dir'   => get_instance()->lang->get('fu_upload_err_no_tmp_dir'),
                                    'upload_err_cant_write'   => get_instance()->lang->get('fu_upload_err_cant_write'),
                                    'upload_err_extension'    => get_instance()->lang->get('fu_upload_err_extension'),
                                    'accept_file_types'       => get_instance()->lang->get('fu_accept_file_types'),
                                    'file_uploads'            => get_instance()->lang->get('fu_file_uploads_disabled'),
                                    'max_file_size'           => get_instance()->lang->get('fu_max_file_size'),
                                    'overwritten_not_allowed' => get_instance()->lang->get('fu_overwritten_not_allowed'),
                                );
        }

    }
