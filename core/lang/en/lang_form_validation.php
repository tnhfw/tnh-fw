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
     * Form validation language message (English) 
     */
    $lang['fv_required'] = 'Field %1 is required.';
    $lang['fv_min_length']   		= 'Field %1 must contain at least %2 characters.';
    $lang['fv_max_length']   		= 'Field %1 must contain at most %2 characters.';
    $lang['fv_exact_length'] 		= 'Field %1 must contain exactly %2 characters.';
    $lang['fv_less_than'] = 'Field %1 must less than %2.';
    $lang['fv_greater_than'] 		= 'Field %1 must greater than %2.';
    $lang['fv_matches']      		= 'Field %1 must be identical to field %2.';
    $lang['fv_valid_email']  		= 'Field %1 must contain a valid E-mail address.';
    $lang['fv_not_equal_post_key']  = 'Field %1 must not be the same as field %2.';
    $lang['fv_not_equal_string']    = 'Field %1 must not contain the value %2.';
    $lang['fv_depends']      		= 'Field %1 depends on field %2 which is not valid.';
    $lang['fv_is_unique']	   		= 'The value of field %1 already exists.';
    $lang['fv_is_unique_update'] = 'The value of field %1 already exists for another record.';
    $lang['fv_exists'] = 'The value of the field %1 does not exist.';
    $lang['fv_regex'] = 'The value of the field %1 does not use the correct format.';
    $lang['fv_in_list']	   			= 'The value of field %1 must be one of the list (%2).';
    $lang['fv_numeric']	   			= 'The value of field %1 must be a number.';
    $lang['fv_callback']	   		= 'The value of field %1 is not valid.';
 	
