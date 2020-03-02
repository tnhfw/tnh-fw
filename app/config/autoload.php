<?php
    defined('ROOT_PATH') || exit('Access denied');
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
     * This file contains the configuration of resources that you want to load automatically: 
     * personal or system libraries, configuration files, models, 
     * personal functions or systems that are used most often in your application 
     * instead of loading them every time you want to use it.
     * Note: loading a lot of resources can decrease the performance of your application.
     */


    /**
     * If you have personal libraries or systems to load automatically, then list them in the following array.
     * For example :
     *
     *	$autoload['libraries'] = array('library1', 'library1');
     *
     * Note: Personal libraries have priority over system libraries, 
     * ie the loading order is as follows: it looks in the folder of the personal libraries, 
     * if it is found, it is loaded, if not , we search in the system libraries folder, 
     * before returning an error in case it does not find it.
     */
    $autoload['libraries'] = array();

    /**
     * If you have configuration files to load automatically, then list them in the following array.
     * For example :
     *
     *	$autoload['config'] = array('config1', 'config2');
     *
     * Note 1: the file name must have as prefix "config_" 
     * for example "config_name_of_the_file_config.php" and contains as configuration variable the array $config,
     * otherwise the system can not find this configuration file.
     * For example :
     *
     *	$config['key1'] = value1;
     * 	$config['key2'] = value2;
     *
     * Note 2: the files to be loaded must be in the folder defined by the constant "CONFIG_PATH" in "index.php".
     */
    $autoload['config'] = array();

    /**
     * If you have models to load automatically, then list them in the following table.
     * For example :
     *
     *	$autoload['models'] = array('model1', 'model2');
     */
    $autoload['models'] = array();

    /**
     * If you have system or personal functions to load automatically, specify them in the following array.
     * For example :
     *
     * 	$autoload['functions'] = array('function1', 'function2');
     *
     * Note: Personal functions have priority over system functions,
     * that is to say that the order of loading is the following : it looks in the directory of the personal functions,
     * if it is found, it is loaded, otherwise, it looks in the directory of the system functions,
     * before returning an error in case he does not find it.
     */
    $autoload['functions'] = array();
	
    /**
     * If you have system or personal language to load automatically, specify them in the following array.
     * For example :
     *
     * 	$autoload['languages'] = array('lang1', 'lang2');
     */
    $autoload['languages'] = array();
