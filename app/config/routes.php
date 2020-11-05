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
     * This file contains the configuration of your routes that is to say that links to your URLs.
     * The index of the $route array contains the url address to capture and the value contains 
     * the name of the module, controller and the method to call.
     * For example :
     *
     * $route['/users/login'] = 'moduleUsers#UsersController@login_action';
     *
     * it means, if the user type in the address bar an URL like http://yoursite.com/users/login, 
     * that will call the module "moduleUsers", the controller "UsersController" and the method "login_action".
     * Note 1: it is recommended that each index in the $route array start with a slash "/" 
     * otherwise on some system this will not work.
     * Note 2: to define the default controller route (homepage) of your application you use only the index "/" 
     * for example the following route represents the homepage:
     * 
     * $route['/'] = 'Home'; //only the controller or module name
     *
     * $route['/'] = 'Home@index'; //only the controller or module name with the method
     *
     * $route['/'] = 'module#Home@index'; //with module name, controller and method
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
     * - $route['/profile/(:num)'] = 'UsersController@profile'; => http://yoursite.com/profile/4 will be captured but 
     * http://yoursite.com/profile/a will not captured.
     *
     * - $route['/profile/(:alpha)'] = 'UsersController@profile'; => http://yoursite.com/profile/a will be captured 
     * but http://yoursite.com/profile/1457 will not captured.
     *
     * - $route['/profile/(:alnum)'] = 'UsersController@profile'; => http://yoursite.com/profile/4a, 
     * http://yoursite.com/profile/7 will be captured but http://yoursite.com/profile/7-dangerous will not captured.
     *
     * - $route['/(:any)'] = 'ArticleController@read'; => http://yoursite.com/1-my-super-article, 
     * http://yoursite.com/7, will be captured.
     * 
     * Note 5: The order of definition of each route is important. 
     * For example, the following definitions work:
     *
     * $route['/article/add'] = 'Article@add';
     * $route['article/(:any)'] = 'Article@read';
     *
     * however, the following definitions does not work as intended
     *
     * $route['article/(:any)'] = 'Article@read';
     * $route['/article/add'] = 'Article@add';
     *
     * Note 6: you can set route for specific HTTP method(useful for REST API)
     * 
     * $route['/users']['GET'] = 'Users@list';
     * $route['/users/(:num)']['GET'] = 'Users@detail';
     * $route['/users']['POST'] = 'Users@add';
     * $route['/users/(:num)']['PUT'] = 'Users@update';
     * $route['/users']['DELETE'] = 'Users@delete';
     */
	
    /**
     *  The default route like your home page
     */
    $route['/'] = 'Home';
