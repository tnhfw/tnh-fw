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
     * This file contains the pagination library configuration of your application
     */

    /**
     * Number of pagination digit
     */
    $config['nb_link'] = 10;

    /**
     * The pagination.
     *
     * Represents the number of data to display per page.
     * Note: this value must be strictly greater than zero (0)
     */
    $config['pagination_per_page'] = 10;
	

    /**
     * The query string name used to fetch the current page
     */
    $config['page_query_string_name'] = 'page';

    /**
     * The opened and closed tag for active link
     */
    $config['active_link_open'] = '<li class = "active"><a href = "#">';
    $config['active_link_close'] = '</a></li>';

    /**
     * The opened and closed tag for previous link
     */
    $config['previous_open'] = '<li>';
    $config['previous_close'] = '</li>';

    /**
     * The opened and closed tag for next link
     */
    $config['next_open'] = '<li>';
    $config['next_close'] = '</li>';


    /**
     * The displayed text for previous and next link
     */
    $config['previous_text'] = '&laquo;&laquo;';	
    $config['next_text'] = '&raquo;&raquo;';

    /**
     * The opened and closed tag for the pagination bloc
     */
    $config['pagination_open'] = '<ul class = "pagination">';
    $config['pagination_close'] = '</ul>';

    /**
     * The opened and closed tag for each link digit
     */
    $config['digit_open'] = '<li>';
    $config['digit_close'] = '</li>';

    /**
     * The HTML attributes to use in each link
     */
    $config['attributes'] = array();
