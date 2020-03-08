<?php 
    defined('ROOT_PATH') || exit('Access denied');
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
     * File upload language messages (English) 
     */
    $lang['fu_upload_err_ini_size'] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
    $lang['fu_upload_err_form_size']   	= 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
    $lang['fu_upload_err_partial']   	= 'The uploaded file was only partially uploaded.';
    $lang['fu_upload_err_no_file'] = 'No file was choosed. Please select one.';
    $lang['fu_upload_err_no_tmp_dir']   = 'Missing a temporary folder.';
    $lang['fu_upload_err_cant_write'] = 'Failed to write file to disk.';
    $lang['fu_upload_err_extension']    = 'A PHP extension stopped the file upload.';
    $lang['fu_accept_file_types'] = 'Filetype not allowed';
    $lang['fu_file_uploads_disabled']   = 'File uploading option is disabled in php.ini';
    $lang['fu_max_file_size']           = 'The uploaded file size is too big max size is %s';
    $lang['fu_overwritten_not_allowed'] = 'You don\'t allow overwriting existing file';
	
