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