<?php
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework created using the concept of codeigniter with bootstrap twitter
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