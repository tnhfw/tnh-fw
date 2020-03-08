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
     * This file contains the connection information to your database, this file is read when you load the "Database" library.
     * You will find all its configuration parameters at your web hosting.
     */
	
    /**
     * The type or driver of your database.
     *
     * Note: the "Database" library uses the PHP PDO extension to interact with your database, 
     * so most of the PDO supported drivers namely: mysql, pgsql, sqlite, oracle are accepted.
     * For example :
     *
     * 	$db['driver'] = 'mysql'; // for MySQL Database
     * or
     * 	$db['driver'] = 'pgsql'; // for PostgreSQL Database
     * or
     * 	$db['driver'] = 'sqlite'; // for SQLite Database
     * or
     * 	$db['driver'] = 'oracle'; // for Oracle Database
     *
     */
    $db['driver'] = 'mysql';
	
    /**
     *  The address of your database server
     *
     * It can be a domain name or IP address, for example:
     *	$db['hostname'] = 'localhost';
     * or
     *	$db['hostname'] = 'mysql.host.com';
     * or
     *	$db['hostname'] = '187.15.14.17';
     *	* or if port is not a standart port
     *	$db['hostname'] = 'mydb.server.com:6356';
     */
    $db['hostname'] = 'localhost';
	
    /**
     * The username of your database server
     * for example :
     *
     *	$db['username'] = 'root';
     * or
     *	$db['username'] = 'myusername';
     */
    $db['username'] = 'root';
	
	
    /**
     * The password of your database server.
     *
     * Note: Some configuration settings on some database management systems do not allow 
     * connection with a user without password, so it is recommended to set a password for this user for a security measure.
     * for example :
     *
     *	$db['password'] = ''; //for an empty password, this is the case most often, if you are in local, ie for the user "root".
     * or
     * 	$db['password'] = 'M@5CUR3P@$$W0rd';
     */
    $db['password'] = '';
	
    /**
     * The name of your database
     *
     * for example :
     *
     *	$db['database'] = 'database_name';
     */
    $db['database'] = '';
	
	
    /**
     * The character set that will be used by the database server.
     * for example :
     *
     * $db['charset'] = 'utf8';
     */
    $db['charset'] = 'utf8';
	
    /**
     * In addition to the character set, a certain database management system allows you to 
     * choose how the data will be classified (ORDER BY) by what is called a "COLLATE". 
     * This makes it possible, for example, to answer the classic problem of case sensitivity.
     * for example :
     *	$db['collation'] = 'utf8_general_ci';
     */
    $db['collation'] = 'utf8_general_ci';
	
	
    /**
     * If your tables in your database contain prefixes, this is the case most on some application, 
     * to avoid the conflict of name of the tables between different database, 
     * it is possible to define prefixes for the tables.
     * for example :
     *
     * $db['prefix'] = 'pf_';
     * or
     *
     * $db['prefix'] = 'my_db_';
     */
    $db['prefix'] = '';
