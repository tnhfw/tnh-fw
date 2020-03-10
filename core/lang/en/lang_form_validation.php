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
    $lang['fv_required']         = 'Field {label} is required';
    $lang['fv_min_length']       = 'Field {label} must contain at least {paramValue} characters';
    $lang['fv_max_length']       = 'Field {label} must contain at most {paramValue} characters';
    $lang['fv_exact_length']     = 'Field {label} must contain exactly {paramValue} characters';
    $lang['fv_matches']          = 'Field {label} must be identical to field {label2}';
    $lang['fv_not_equal']        = 'Field {label} must not be the same as field {label2}';
    $lang['fv_min']              = 'Field {label} must be greather or equal to {paramValue}';
    $lang['fv_max']              = 'Field {label} must be less or equal to {paramValue}';
    $lang['fv_between']          = 'Field {label} must be between [{paramValue}]';
    $lang['fv_in_list']          = 'The value of field {label} must be one of the following ({paramValue})';
    $lang['fv_numeric']          = 'The value of field {label} must be a number';
    $lang['fv_integer']          = 'The value of field {label} must be an integer';
    $lang['fv_integer_natural']  = 'The value of field {label} must be a natural number (0, 1, 2, etc.)';
    $lang['fv_alpha']            = 'Field {label} must contain alphabetic characters';
    $lang['fv_alpha_dash']       = 'Field {label} must contain alphabetic characters and -, _';
    $lang['fv_alnum']            = 'Field {label} must contain alphanumeric characters';
    $lang['fv_alnum_dash']       = 'Field {label} must contain alphanumeric characters and -, _';
    $lang['fv_email']            = 'Field {label} must contain a valid e-mail address';
    $lang['fv_date']             = 'Field {label} must contain a correct date format {paramValue}';
    $lang['fv_date_before']      = 'Field {label} must be before the date {paramValue}';
    $lang['fv_date_after']       = 'Field {label} must be after the date {paramValue}';
    $lang['fv_url']              = 'Field {label} must be a valid URL';
    $lang['fv_ip']               = 'Field {label} must be a valid ip address';
    $lang['fv_ipv4']             = 'Field {label} must be a valid ipv4 address';
    $lang['fv_ipv6']             = 'Field {label} must be a valid ipv6 address';
    $lang['fv_is_unique']        = 'The value of field {label} already exists';
    $lang['fv_is_unique_update'] = 'The value of field {label} already exists for another record';
    $lang['fv_exists']           = 'The value of the field {label} does not exist';
    $lang['fv_regex']            = 'The value of the field {label} does not use the correct format';
    $lang['fv_callback']         = 'The value of field {label} is not valid';
