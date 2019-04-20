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
    class Upload{

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
        *    @var        mixex
        */
        private $upload_function = 'move_uploaded_file';

        /**
        *    Array with the information obtained from the
        *    variable $_FILES or $HTTP_POST_FILES.
        *
        *    @since        1.0
        *    @version    1.0
        *    @var        array
        */
        private $file_array    = array();

        /**
        *    If the file you are trying to upload already exists it will
        *    be overwritten if you set the variable to true.
        *
        *    @since        1.0
        *    @version    1.0
        *    @var        boolean
        */
        private $overwrite_file = false;

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
        private $destination_directory;

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
        private $max_file_size= 0.0;

        /**
        *    List of allowed mime types
        *
        *    @since        1.0
        *    @version    1.0
        *    @var        array
        */
        private $allowed_mime_types = array();

        /**
        *    Callbacks
        *
        *    @since        1.0
        *    @version    1.0
        *    @var        array
        */
        private $callbacks = array('before' => null, 'after' => null);

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
        private $mime_helping = array(
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
         * The upload error message
         * @var array
         */
        public $error_messages = array();

        /**
         * The upload error message
         * @var string
         */
        protected $error = null;

        /**
         * The logger instance
         * @var Log
         */
        private $logger;


        /**
        *    Construct
        *
        *    @since     0.1
        *    @version   1.0.1
        *    @return    object
        *    @method    object    __construct
        */
        public function __construct(){
            $this->logger =& class_loader('Log');
            $this->logger->setLogger('Library::Upload');

            Loader::lang('file_upload');
            $obj =& get_instance();

            $this->error_messages = array(
                'upload_err_ini_size' => $obj->lang->get('fu_upload_err_ini_size'),
                'upload_err_form_size' => $obj->lang->get('fu_upload_err_form_size'),
                'upload_err_partial' => $obj->lang->get('fu_upload_err_partial'),
                'upload_err_no_file' => $obj->lang->get('fu_upload_err_no_file'),
                'upload_err_no_tmp_dir' => $obj->lang->get('fu_upload_err_no_tmp_dir'),
                'upload_err_cant_write' => $obj->lang->get('fu_upload_err_cant_write'),
                'upload_err_extension' => $obj->lang->get('fu_upload_err_extension'),
                'accept_file_types' => $obj->lang->get('fu_accept_file_types'),
                'file_uploads' => $obj->lang->get('fu_file_uploads_disabled'),
                'max_file_size' => $obj->lang->get('fu_max_file_size'),
                'overwritten_not_allowed' => $obj->lang->get('fu_overwritten_not_allowed'),
            );

            $this->file = array(
                'status'                =>    false,    // True: success upload
                'mime'                  =>    '',       // Empty string
                'filename'              =>    '',       // Empty string
                'original'              =>    '',       // Empty string
                'size'                  =>    0,        // 0 Bytes
                'sizeFormated'          =>    '0B',     // 0 Bytes
                'destination'           =>    './',     // Default: ./
                'allowed_mime_types'    =>    array(),  // Allowed mime types
                'error'                 =>    null,        // File error
            );

            // Change dir to current dir
            $this->destination_directory = dirname(__FILE__) . DIRECTORY_SEPARATOR;

            // Set file array
            if (isset($_FILES) && is_array($_FILES)) {
                $this->file_array = $_FILES;
            } elseif (isset($HTTP_POST_FILES) && is_array($HTTP_POST_FILES)) {
                $this->file_array = $HTTP_POST_FILES;
            }
            $this->logger->info('The upload file information are : ' .stringfy_vars($this->file_array));
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
        *    @return    boolean
        *    @method    boolean     setInput
        */
        public function setInput($input)
        {
            if (!empty($input) && (is_string($input) || is_numeric($input) )) {
                $this->input = $input;
            }
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
        *    @return    boolean
        *    @method    boolean     setFilename
        */
        public function setFilename($filename)
        {
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
        *    @return    boolean
        *    @method    boolean     setAutoFilename
        */
        public function setAutoFilename()
        {
            $this->filename = sha1(mt_rand(1, 9999).uniqid());
            $this->filename .= time();
            return $this;
        }
        /**
        *    Set file size limit
        *
        *    @since     1.0
        *    @version   1.0
        *    @param     integer     $file_size
        *    @return    boolean
        *    @method    boolean     setMaxFileSize
        */
        public function setMaxFileSize($file_size)
        {
            $file_size = $this->sizeInBytes($file_size);
            if (is_numeric($file_size) && $file_size > -1) {
                // Get php config
                $php_size = $this->sizeInBytes(ini_get('upload_max_filesize'));
                // Calculate difference
                if ($php_size < $file_size) {
                    $this->logger->warning('The upload max file size you set [' .$file_size. '] is greather than the PHP configuration for upload max file size [' .$php_size. ']');
                }
                $this->max_file_size = $file_size;
            }
            return $this;
        }
        /**
        *    Set array mime types
        *
        *    @since     1.0
        *    @version   1.0
        *    @param     array       $mimes
        *    @return    boolean
        *    @method    boolean     setAllowedMimeTypes
        */
        public function setAllowedMimeTypes(array $mimes)
        {
            if (count($mimes) > 0) {
                array_map(array($this , 'setAllowMimeType'), $mimes);
            }
            return $this;
        }
        /**
        *    Set input callback
        *
        *    @since     1.0
        *    @version   1.0
        *    @param     mixed       $callback
        *    @return    boolean
        *    @method    boolean     setCallbackInput
        */
        public function setCallbackInput($callback)
        {
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
        *    @return    boolean
        *    @method    boolean     setCallbackOutput
        */
        public function setCallbackOutput($callback)
        {
            if (is_callable($callback, false)) {
                $this->callbacks['output'] = $callback;
            }
            return $this;
        }
        /**
        *    Append a mime type to allowed mime types
        *
        *    @since     1.0
        *    @version   1.0.1
        *    @param     string      $mime
        *    @return    boolean
        *    @method    boolean     setAllowMimeType
        */
        public function setAllowMimeType($mime)
        {
            if (!empty($mime) && is_string($mime)) {
                if (preg_match('#^[-\w\+]+/[-\w\+]+$#', $mime)) {
                    $this->allowed_mime_types[] = strtolower($mime);
                    $this->file['allowed_mime_types'][] = strtolower($mime);
                } else {
                    return $this->setMimeHelping($mime);
                }
            }
            return $this;
        }
        /**
        *    Set allowed mime types from mime helping
        *
        *    @since     1.0.1
        *    @version   1.0.1
        *    @return    boolean
        *    @method    boolean    setMimeHelping
        */
        public function setMimeHelping($name)
        {
            if (!empty($name) && is_string($name)) {
                if (array_key_exists($name, $this->mime_helping)) {
                    return $this->setAllowedMimeTypes($this->mime_helping[ $name ]);
                }
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
        *    @param     string      $mime
        *    @return    boolean
        *    @method    boolean     setUploadFunction
        */
        public function setUploadFunction($function)
        {
            if (!empty($function) && (is_array($function) || is_string($function) )) {
                if (is_callable( $function)) {
                    $this->upload_function = $function;
                }
            }
            return $this;
        }
        /**
        *    Clear allowed mime types cache
        *
        *    @since     1.0
        *    @version   1.0
        *    @return    boolean
        *    @method    boolean    clearAllowedMimeTypes
        */
        public function clearAllowedMimeTypes()
        {
            $this->allowed_mime_types = array();
            $this->file['allowed_mime_types'] = array();
            return $this;
        }
        /**
        *    Set destination output
        *
        *    @since     1.0
        *    @version   1.0
        *    @param     string      $destination_directory      Destination path
        *    @param     boolean     $create_if_not_exist
        *    @return    boolean
        *    @method    boolean     setDestinationDirectory
        */
        public function setDestinationDirectory($destination_directory, $create_if_not_exist = false) {
            $destination_directory = realpath($destination_directory);
            if (substr($destination_directory, -1) != DIRECTORY_SEPARATOR) {
                $destination_directory .= DIRECTORY_SEPARATOR;
            }

            if ($this->isDirpath($destination_directory)) {
                if ($this->dirExists($destination_directory)) {
                    $this->destination_directory = $destination_directory;
                    if (substr($this->destination_directory, -1) != DIRECTORY_SEPARATOR) {
                        $this->destination_directory .= DIRECTORY_SEPARATOR;
                    }
                    chdir($destination_directory);
                } elseif ($create_if_not_exist === true) {
                    if (mkdir($destination_directory, $this->destination_permissions, true)) {
                        if ($this->dirExists($destination_directory)) {
                            $this->destination_directory = $destination_directory;
                            if (substr($this->destination_directory, -1) != DIRECTORY_SEPARATOR) {
                                $this->destination_directory .= DIRECTORY_SEPARATOR;
                            }
                            chdir($destination_directory);
                        }
                    }
                    else{
                        $this->logger->warning('Can not create the upload directory [' .$destination_directory. ']');
                    }
                }
            }
            return $this;
        }
        /**
        *    Check file exists
        *
        *    @since      1.0
        *    @version    1.0.1
        *    @param      string     $file_destination
        *    @return     boolean
        *    @method     boolean    fileExists
        */
        public function fileExists($file_destination)
        {
            if ($this->isFilename($file_destination)) {
                return (file_exists($file_destination) && is_file($file_destination));
            }
            return false;
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
        public function dirExists($path)
        {
            if ($this->isDirpath($path)) {
                return (file_exists($path) && is_dir($path));
            }
            return false;
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
        public function isFilename($filename)
        {
            $filename = basename($filename);
            return (!empty($filename) && (is_string( $filename) || is_numeric($filename)));
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
        public function checkMimeType($mime)
        {
            if (count($this->allowed_mime_types) == 0) {
                return true;
            }
            return in_array(strtolower($mime), $this->allowed_mime_types);
        }
        /**
        *    Retrive status of upload
        *
        *    @since     1.0
        *    @version   1.0
        *    @return    boolean
        *    @method    boolean    getStatus
        */
        public function getStatus()
        {
            return $this->file['status'];
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
        public function isDirpath($path)
        {
            if (!empty( $path) && (is_string( $path) || is_numeric($path) )) {
                if (DIRECTORY_SEPARATOR == '/') {
                    return (preg_match( '/^[^*?"<>|:]*$/' , $path) == 1 );
                } else {
                    return (preg_match( "/^[^*?\"<>|:]*$/" , substr($path,2) ) == 1);
                }
            }
            return false;
        }
        /**
        *    Allow overwriting files
        *
        *    @since      1.0
        *    @version    1.0
        *    @return     boolean
        *    @method     boolean    allowOverwriting
        */
        public function allowOverwriting()
        {
            $this->overwrite_file = true;
            return $this;
        }
        /**
        *    File info
        *
        *    @since      1.0
        *    @version    1.0
        *    @return     object
        *    @method     object    getInfo
        */
        public function getInfo()
        {
            return (object)$this->file;
        }

        public function isUploaded(){
            return isset($this->file_array[$this->input])
            &&
            is_uploaded_file($this->file_array[$this->input]['tmp_name']);
        }
        /**
        *    Upload file
        *
        *    @since     1.0
        *    @version   1.0.1
        *    @return    boolean
        *    @method    boolean    save
        */
        public function save(){
            //check if file upload is  allowed in the configuration
            if(! ini_get('file_uploads')){
                $this->setError($this->error_messages['file_uploads']);
                return false;
            }
            if (count($this->file_array) > 0) {
                if (array_key_exists($this->input, $this->file_array)) {
                    // set original filename if not have a new name
                    if (empty($this->filename)) {
                        $this->filename = $this->file_array[$this->input]['name'];
                    }
                    else{
                        // Replace %s for extension in filename
                        // Before: /[\w\d]*(.[\d\w]+)$/i
                        // After: /^[\s[:alnum:]\-\_\.]*\.([\d\w]+)$/iu
                        // Support unicode(utf-8) characters
                        // Example: "русские.jpeg" is valid; "Zhōngguó.jpeg" is valid; "Tønsberg.jpeg" is valid
                        $extension = preg_replace(
                            '/^[\p{L}\d\s\-\_\.\(\)]*\.([\d\w]+)$/iu',
                            '$1',
                            $this->file_array[$this->input]['name']
                        );
                        $this->filename = $this->filename.'.'.$extension;
                    }

                    // set file info
                    $this->file['mime']         = $this->file_array[$this->input]['type'];
                    $this->file['tmp']          = $this->file_array[$this->input]['tmp_name'];
                    $this->file['original']     = $this->file_array[$this->input]['name'];
                    $this->file['size']         = $this->file_array[$this->input]['size'];
                    $this->file['sizeFormated'] = $this->sizeFormat($this->file['size']);
                    $this->file['destination']  = $this->destination_directory . $this->filename;
                    $this->file['filename']     = $this->filename;
                    $this->file['error']        = $this->file_array[$this->input]['error'];

                    $this->logger->info('The upload file information to process is : ' .stringfy_vars($this->file));

                    //check for php upload error
                    if(is_numeric($this->file['error']) && $this->file['error'] > 0){
                        $this->setError($this->getPhpUploadErrorMessageByCode($this->file['error']));
                        return false;
                    }
                    
                    //check for mime type
                    if (!$this->checkMimeType($this->file['mime'])) {
                        $this->setError($this->error_messages['accept_file_types']);
                        return false;
                    }

                     // Check file size
                    if ($this->max_file_size > 0) {
                        if ($this->max_file_size < $this->file['size']) {
                            $this->setError(sprintf($this->error_messages['max_file_size'], $this->sizeFormat($this->max_file_size)));
                            return false;
                        }
                    }

                    // Check if exists file
                    if ($this->fileExists($this->destination_directory . $this->filename)) {
                        // Check if overwrite file
                        if ($this->overwrite_file === false) {
                            $this->setError($this->error_messages['overwritten_not_allowed']);
                            return false;
                        }
                    }

                    // Execute input callback
                    if (!empty( $this->callbacks['input'])) {
                        call_user_func($this->callbacks['input'], (object)$this->file);
                    }
                   

                    $this->file['status'] = call_user_func_array(
                        $this->upload_function, array(
                            $this->file_array[$this->input]['tmp_name'],
                            $this->destination_directory . $this->filename
                        )
                    );

                    // Execute output callback
                    if (!empty( $this->callbacks['output'])) {
                        call_user_func($this->callbacks['output'], (object)$this->file);
                    }
                    return $this->file['status'];
                }
            }
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
        public function sizeFormat($size, $precision = 2)
        {
            if($size > 0){
                $base       = log($size) / log(1024);
                $suffixes   = array('B', 'K', 'M', 'G', 'T');
                return round(pow(1024, $base - floor($base)), $precision) . ( isset($suffixes[floor($base)]) ? $suffixes[floor($base)] : '');
            }
            return null;
        }

        
        /**
        *    Convert human file size to bytes
        *
        *    @since      1.0
        *    @version    1.0.1
        *    @param      integer    $size
        *    @return     string
        *    @method     string     sizeInBytes
        */
        public function sizeInBytes($size)
        {
            $unit = 'B';
            $units = array('B' => 0, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4);
            $matches = array();
            preg_match('/(?<size>[\d\.]+)\s*(?<unit>b|k|m|g|t)?/i', $size, $matches);
            if (array_key_exists('unit', $matches)) {
                $unit = strtoupper($matches['unit']);
            }
            return (floatval($matches['size']) * pow(1024, $units[$unit]) ) ;
        }

        /**
         * Get the upload error message
         * @return string
         */
        public function getError(){
            return $this->error;
        }

        /**
         * Set the upload error message
         * @param string $message the upload error message to set
         */
        public function setError($message){
            $this->error = $message;
        }

        /**
         * Get the PHP upload error message for the given code
         * @param  int $code the error code
         * @return string the error message
         */
        private function getPhpUploadErrorMessageByCode($code){
            if(! is_int($code) || $code <= 0){
                return null;
            }
            switch ($code) {
                case 1:
                    return $this->error_messages['upload_err_ini_size'];
                break;
                case 2:
                    return $this->error_messages['upload_err_form_size'];
                break;
                case 3:
                    return $this->error_messages['upload_err_partial'];
                break;
                case 4:
                    return $this->error_messages['upload_err_no_file'];
                break;
                case 6:
                    return $this->error_messages['upload_err_no_tmp_dir'];
                break;
                case 7:
                    return $this->error_messages['upload_err_cant_write'];
                break;
                case 8:
                    return $this->error_messages['upload_err_extension'];
                break;
                default:
                    return null;
                break;
            }
        }
    }
