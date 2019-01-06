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
	* This file contains the configuration of your routes that is to say that links to your URLs.
	* The index of the $route array contains the url address to capture and the value contains 
	* the name of the controller and the method to call separated by a slash.
	* For example :
	*
	* $route['/users/login'] = 'UsersController/login_action';
	*
	* it means, if the user type in the address bar an address like http://yoursite.com/users/login, 
	* that will call the controller "UsersController" and the method "login_action".
	* Note 1: it is recommended that each index in the $route array start with a slash "/" 
	* otherwise on some system will not work.
	* Note 2: to define the homepage of your application you use the index "/" 
	* for example the following route represents the homepage:
	* 
	* $route['/'] = 'Home/index';
	*
	* Note 3: If the method is not specified then the "index" method will be used as 
	* default method. For example in the case of our homepage, this will be able to make:
	* 
	* $route['/'] = 'Home';
	*
	* Note 4: All methods called must have "public" visibility if you know the principle of 
	* Object Oriented Programming, you know what visibility means (public, protected, private).
	*
	* In the index of your route, you can use the markers (:num), (:alpha), (:alnum) and (:any) which correspond 
	* respectively to the regular expression [0-9], [a-zA-Z], [a-zA-Z0-9] and. *.
	* For example :
	*
	* - $route['/profile/(:num)'] = 'UsersController/profile'; => http://yoursite.com/profile/4 will be captured but 
	* http://yoursite.com/profile/a will not work.
	*
	* - $route['/profile/(:alpha)'] = 'UsersController/profile'; => http://yoursite.com/profile/a will be captured 
	* but http://yoursite.com/profile/1457 will not work.
	*
	* - $route['/profile/(:alnum)'] = 'UsersController/profile'; => http://yoursite.com/profile/4a, 
	* http://yoursite.com/profile/7 will be caught but http://yoursite.com/profile/7-dangerous will not work.
	*
	* - $route['/(:any)'] = 'ArticleController/read'; => http://yoursite.com/1-my-super-article, 
	* http://yoursite.com/7, will be captured.
	* 
	* Note 5: The order of definition of each route is important. 
	* For example, the following definitions work:
	*
	* $route['/article/add'] = 'Article/add';
	* $route['article/(:any)'] = 'Article/read';
	*
	* however, the following definitions do not work as intended
	*
	* $route['article/(:any)'] = 'Article/read';
	* $route['/article/add'] = 'Article/add';
	*
	*/
	
	/**
	 *  The default route like your home page
	 */
	$route['/'] = 'Home';
	