<?php
    defined('ROOT_PATH') || exit('Access denied');
    /**
     * TNH Framework
     *
     * A simple PHP framework using HMVC architecture
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
     * A base model with a series of CRUD functions (powered by CI's query builder),
     * validation-in-model support, event callbacks and more.
     *
     * @link http://github.com/jamierumbelow/codeigniter-base-model
     * @copyright Copyright (c) 2012, Jamie Rumbelow <http://jamierumbelow.net>
     */

    class Model{

        /* --------------------------------------------------------------
         * VARIABLES
         * ------------------------------------------------------------ */

        /**
         * This model's default database table. Automatically
         * guessed by pluralising the model name.
         */
        protected $_table;

        /**
         * The database connection object. Will be set to the default
         * connection. This allows individual models to use different DBs
         * without overwriting the global database connection.
         */
        protected $_database;

        /**
         * This model's default primary key or unique identifier.
         * Used by the get(), update() and delete() functions.
         */
        protected $primary_key = 'id';

        /**
         * Support for soft deletes and this model's 'deleted' key
         */
        protected $soft_delete = false;
        protected $soft_delete_key = 'is_deleted';
        protected $_temporary_with_deleted = FALSE;
        protected $_temporary_only_deleted = FALSE;

        /**
         * The various callbacks available to the model. Each are
         * simple lists of method names (methods will be run on $this).
         */
        protected $before_create = array();
        protected $after_create = array();
        protected $before_update = array();
        protected $after_update = array();
        protected $before_get = array();
        protected $after_get = array();
        protected $before_delete = array();
        protected $after_delete = array();

        protected $callback_parameters = array();

        /**
         * Protected, non-modifiable attributes
         */
        protected $protected_attributes = array();

        /**
         * Relationship arrays. Use flat strings for defaults or string
         * => array to customise the class name and primary key
         */
        protected $belongs_to = array();
        protected $has_many = array();

        protected $_with = array();

        /**
         * An array of validation rules. This needs to be the same format
         * as validation rules passed to the FormValidation library.
         */
        protected $validate = array();

        /**
         * Optionally skip the validation. Used in conjunction with
         * skip_validation() to skip data validation for any future calls.
         */
        protected $skip_validation = FALSE;

        /**
         * By default we return our results as objects. If we need to override
         * this, we can, or, we could use the `as_array()` and `as_object()` scopes.
         */
        protected $return_type = 'object';
        protected $_temporary_return_type = NULL;
    	
    	
    	/**
    		The database cache time 
    	*/
    	protected $dbCacheTime = 0;

        /**
         * Instance of the Loader class
         * @var Loader
         */
        protected $loaderInstance = null;

        /**
         * Instance of the FormValidation library
         * @var FormValidation
         */
        protected $formValidationInstance = null;

        /* --------------------------------------------------------------
         * GENERIC METHODS
         * ------------------------------------------------------------ */

        /**
         * Initialise the model, tie into the CodeIgniter superobject and
         * try our best to guess the table name.
         */
        public function __construct(Database $db = null){
            if(is_object($db)){
                $this->setDatabaseInstance($db);
            }
            else{
                $obj = & get_instance();
        		if(isset($obj->database) && is_object($obj->database)){
                    /**
                    * NOTE: Need use "clone" because some Model need have the personal instance of the database library
                    * to prevent duplication
                    */
        			$this->setDatabaseInstance(clone $obj->database);
                }
            }

            array_unshift($this->before_create, 'protect_attributes');
            array_unshift($this->before_update, 'protect_attributes');
            $this->_temporary_return_type = $this->return_type;
        }

        /* --------------------------------------------------------------
         * CRUD INTERFACE
         * ------------------------------------------------------------ */

        /**
         * Fetch a single record based on the primary key. Returns an object.
         */
        public function get($primary_value)
        {
    		return $this->get_by($this->primary_key, $primary_value);
        }

        /**
         * Fetch a single record based on an arbitrary WHERE call. Can be
         * any valid value to $this->_database->where().
         */
        public function get_by()
        {
            $where = func_get_args();

            if ($this->soft_delete && $this->_temporary_with_deleted !== TRUE)
            {
                $this->_database->where($this->soft_delete_key, (bool)$this->_temporary_only_deleted);
            }

    		$this->_set_where($where);

            $this->trigger('before_get');
			$type = $this->_temporary_return_type == 'array' ? 'array' : false;
            $row = $this->_database->from($this->_table)->get($type);
            $this->_temporary_return_type = $this->return_type;
            $row = $this->trigger('after_get', $row);
            $this->_with = array();
            return $row;
        }

        /**
         * Fetch an array of records based on an array of primary values.
         */
        public function get_many($values)
        {
            $this->_database->in($this->primary_key, $values);
            return $this->get_all();
        }

        /**
         * Fetch an array of records based on an arbitrary WHERE call.
         */
        public function get_many_by()
        {
            $where = func_get_args();
            $this->_set_where($where);
            return $this->get_all();
        }

        /**
         * Fetch all the records in the table. Can be used as a generic call
         * to $this->_database->get() with scoped methods.
         */
        public function get_all()
        {
            $this->trigger('before_get');
            if ($this->soft_delete && $this->_temporary_with_deleted !== TRUE)
            {
                $this->_database->where($this->soft_delete_key, (bool)$this->_temporary_only_deleted);
            }
			$type = $this->_temporary_return_type == 'array' ? 'array':false;
            $result = $this->_database->from($this->_table)->getAll($type);
            $this->_temporary_return_type = $this->return_type;

            foreach ($result as $key => &$row)
            {
                $row = $this->trigger('after_get', $row, ($key == count($result) - 1));
            }
            $this->_with = array();
            return $result;
        }

        /**
         * Insert a new row into the table. $data should be an associative array
         * of data to be inserted. Returns newly created ID.
         */
        public function insert($data = array(), $skip_validation = FALSE, $escape = true)
        {
            if ($skip_validation === FALSE)
            {
                $data = $this->validate($data);
            }

            if ($data !== FALSE)
            {
                $data = $this->trigger('before_create', $data);
                $this->_database->from($this->_table)->insert($data, $escape);
                $insert_id = $this->_database->insertId();
                $this->trigger('after_create', $insert_id);
                return $insert_id;
            }
            else
            {
                return FALSE;
            }
        }

        /**
         * Insert multiple rows into the table. Returns an array of multiple IDs.
         */
        public function insert_many($data = array(), $skip_validation = FALSE, $escape = true)
        {
            $ids = array();
            foreach ($data as $key => $row)
            {
                $ids[] = $this->insert($row, $skip_validation, $escape);
            }
            return $ids;
        }

        /**
         * Updated a record based on the primary value.
         */
        public function update($primary_value, $data = array(), $skip_validation = FALSE, $escape = true)
        {
            $data = $this->trigger('before_update', $data);
            if ($skip_validation === FALSE)
            {
                $data = $this->validate($data);
            }

            if ($data !== FALSE)
            {
                $result = $this->_database->where($this->primary_key, $primary_value)
                                   ->from($this->_table)
                                   ->update($data, $escape);
                $this->trigger('after_update', array($data, $result));
                return $result;
            }
            else
            {
                return FALSE;
            }
        }

        /**
         * Update many records, based on an array of primary values.
         */
        public function update_many($primary_values, $data = array(), $skip_validation = FALSE, $escape = true)
        {
            $data = $this->trigger('before_update', $data);
            if ($skip_validation === FALSE)
            {
                $data = $this->validate($data);
            }
            if ($data !== FALSE)
            {
                $result = $this->_database->in($this->primary_key, $primary_values)
                                   ->from($this->_table)
                                   ->update($data, $escape);
                $this->trigger('after_update', array($data, $result));
                return $result;
            }
            else
            {
                return FALSE;
            }
        }

        /**
         * Updated a record based on an arbitrary WHERE clause.
         */
        public function update_by()
        {
            $args = func_get_args();
            $data = array();
            if(count($args) == 2){
                if(is_array($args[1])){
                    $data = array_pop($args);
                }
            }
            else if(count($args) == 3){
                if(is_array($args[2])){
                    $data = array_pop($args);
                }
            }
            $data = $this->trigger('before_update', $data);
            if ($this->validate($data) !== FALSE)
            {
                $this->_set_where($args);
                $result = $this->_database->from($this->_table)->update($data);
                $this->trigger('after_update', array($data, $result));
                return $result;
            }
            else
            {
                return FALSE;
            }
        }

        /**
         * Update all records
         */
        public function update_all($data = array(), $escape = true)
        {
            $data = $this->trigger('before_update', $data);
            $result = $this->_database->from($this->_table)->update($data, $escape);
            $this->trigger('after_update', array($data, $result));
            return $result;
        }

        /**
         * Delete a row from the table by the primary value
         */
        public function delete($id)
        {
            $this->trigger('before_delete', $id);
            $this->_database->where($this->primary_key, $id);
            if ($this->soft_delete)
            {
                $result = $this->_database->from($this->_table)->update(array( $this->soft_delete_key => TRUE ));
            }
            else
            {
                $result = $this->_database->from($this->_table)->delete();
            }

            $this->trigger('after_delete', $result);
            return $result;
        }

        /**
         * Delete a row from the database table by an arbitrary WHERE clause
         */
        public function delete_by()
        {
            $where = func_get_args();
    	    $where = $this->trigger('before_delete', $where);
            $this->_set_where($where);
            if ($this->soft_delete)
            {
                $result = $this->_database->from($this->_table)->update(array( $this->soft_delete_key => TRUE ));
            }
            else
            {
                $result = $this->_database->from($this->_table)->delete();
            }
            $this->trigger('after_delete', $result);
            return $result;
        }

        /**
         * Delete many rows from the database table by multiple primary values
         */
        public function delete_many($primary_values)
        {
            $primary_values = $this->trigger('before_delete', $primary_values);
            $this->_database->in($this->primary_key, $primary_values);
            if ($this->soft_delete)
            {
                $result = $this->_database->from($this->_table)->update(array( $this->soft_delete_key => TRUE ));
            }
            else
            {
                $result = $this->_database->from($this->_table)->delete();
            }
            $this->trigger('after_delete', $result);
            return $result;
        }


        /**
         * Truncates the table
         */
        public function truncate()
        {
            $result = $this->_database->from($this->_table)->delete();
            return $result;
        }

        /* --------------------------------------------------------------
         * RELATIONSHIPS
         * ------------------------------------------------------------ */

        public function with($relationship)
        {
            $this->_with[] = $relationship;
            if (!in_array('relate', $this->after_get))
            {
                $this->after_get[] = 'relate';
            }
            return $this;
        }

        public function relate($row)
        {
    		if (empty($row))
            {
    		    return $row;
            }

            foreach ($this->belongs_to as $key => $value)
            {
                if (is_string($value))
                {
                    $relationship = $value;
                    $options = array( 'primary_key' => $value . '_id', 'model' => $value . '_model' );
                }
                else
                {
                    $relationship = $key;
                    $options = $value;
                }

                if (in_array($relationship, $this->_with))
                {
                    if(is_object($this->loaderInstance)){
                        $this->loaderInstance->model($options['model'], $relationship . '_model');
                    }
                    else{
                        Loader::model($options['model'], $relationship . '_model');    
                    }
                    if (is_object($row))
                    {
                        $row->{$relationship} = $this->{$relationship . '_model'}->get($row->{$options['primary_key']});
                    }
                    else
                    {
                        $row[$relationship] = $this->{$relationship . '_model'}->get($row[$options['primary_key']]);
                    }
                }
            }

            foreach ($this->has_many as $key => $value)
            {
                if (is_string($value))
                {
                    $relationship = $value;
                    $options = array( 'primary_key' => $this->_table . '_id', 'model' => $value . '_model' );
                }
                else
                {
                    $relationship = $key;
                    $options = $value;
                }

                if (in_array($relationship, $this->_with))
                {
                    if(is_object($this->loaderInstance)){
                        $this->loaderInstance->model($options['model'], $relationship . '_model');
                    }
                    else{
                        Loader::model($options['model'], $relationship . '_model');    
                    }
                    if (is_object($row))
                    {
                        $row->{$relationship} = $this->{$relationship . '_model'}->get_many_by($options['primary_key'], $row->{$this->primary_key});
                    }
                    else
                    {
                        $row[$relationship] = $this->{$relationship . '_model'}->get_many_by($options['primary_key'], $row[$this->primary_key]);
                    }
                }
            }
            return $row;
        }

        /* --------------------------------------------------------------
         * UTILITY METHODS
         * ------------------------------------------------------------ */

        /**
         * Retrieve and generate a form_dropdown friendly array
         */
        public function dropdown()
        {
            $args = func_get_args();
            if(count($args) == 2)
            {
                list($key, $value) = $args;
            }
            else
            {
                $key = $this->primary_key;
                $value = $args[0];
            }
            $this->trigger('before_dropdown', array( $key, $value ));
            if ($this->soft_delete && $this->_temporary_with_deleted !== TRUE)
            {
                $this->_database->where($this->soft_delete_key, FALSE);
            }
            $result = $this->_database->select(array($key, $value))
                               ->from($this->_table)
                               ->getAll();
            $options = array();
            foreach ($result as $row)
            {
                $options[$row->{$key}] = $row->{$value};
            }
            $options = $this->trigger('after_dropdown', $options);
            return $options;
        }

        /**
         * Fetch a count of rows based on an arbitrary WHERE call.
         */
        public function count_by()
        {
            if ($this->soft_delete && $this->_temporary_with_deleted !== TRUE)
            {
                $this->_database->where($this->soft_delete_key, (bool)$this->_temporary_only_deleted);
            }
            $where = func_get_args();
            $this->_set_where($where);
            $this->_database->from($this->_table)->getAll();
            return $this->_database->numRows();
        }

        /**
         * Fetch a total count of rows, disregarding any previous conditions
         */
        public function count_all()
        {
            if ($this->soft_delete && $this->_temporary_with_deleted !== TRUE)
            {
                $this->_database->where($this->soft_delete_key, (bool)$this->_temporary_only_deleted);
            }
            $this->_database->from($this->_table)->getAll();
            return $this->_database->numRows();
        }
		
		/**
		* Enabled cache temporary
		*/
		public function cached($ttl = 0){
		  if($ttl > 0){
			$this->_database = $this->_database->cached($ttl);
		  }
		  return $this;
		}

        /**
         * Tell the class to skip the insert validation
         */
        public function skip_validation()
        {
            $this->skip_validation = TRUE;
            return $this;
        }

        /**
         * Get the skip validation status
         */
        public function get_skip_validation()
        {
            return $this->skip_validation;
        }

        /**
         * Return the next auto increment of the table. Only tested on MySQL.
         */
        public function get_next_id()
        {
            return (int) $this->_database->select('AUTO_INCREMENT')
                ->from('information_schema.TABLES')
                ->where('TABLE_NAME', $this->_table)
                ->where('TABLE_SCHEMA', $this->_database->getDatabaseName())->get()->AUTO_INCREMENT;
        }

        /**
         * Getter for the table name
         */
        public function table()
        {
            return $this->_table;
        }

        /* --------------------------------------------------------------
         * GLOBAL SCOPES
         * ------------------------------------------------------------ */

        /**
         * Return the next call as an array rather than an object
         */
        public function as_array()
        {
            $this->_temporary_return_type = 'array';
            return $this;
        }

        /**
         * Return the next call as an object rather than an array
         */
        public function as_object()
        {
            $this->_temporary_return_type = 'object';
            return $this;
        }

        /**
         * Don't care about soft deleted rows on the next call
         */
        public function with_deleted()
        {
            $this->_temporary_with_deleted = TRUE;
            return $this;
        }

        /**
         * Only get deleted rows on the next call
         */
        public function only_deleted()
        {
            $this->_temporary_only_deleted = TRUE;
            return $this;
        }

        /* --------------------------------------------------------------
         * OBSERVERS
         * ------------------------------------------------------------ */

        /**
         * MySQL DATETIME created_at and updated_at
         */
        public function created_at($row)
        {
            if (is_object($row))
            {
                $row->created_at = date('Y-m-d H:i:s');
            }
            else
            {
                $row['created_at'] = date('Y-m-d H:i:s');
            }

            return $row;
        }

        public function updated_at($row)
        {
            if (is_object($row))
            {
                $row->updated_at = date('Y-m-d H:i:s');
            }
            else
            {
                $row['updated_at'] = date('Y-m-d H:i:s');
            }
            return $row;
        }

        /**
         * Serialises data for you automatically, allowing you to pass
         * through objects and let it handle the serialisation in the background
         */
        public function serialize($row)
        {
            foreach ($this->callback_parameters as $column)
            {
                $row[$column] = serialize($row[$column]);
            }
            return $row;
        }

        public function unserialize($row)
        {
            foreach ($this->callback_parameters as $column)
            {
                if (is_array($row))
                {
                    $row[$column] = unserialize($row[$column]);
                }
                else
                {
                    $row->$column = unserialize($row->$column);
                }
            }
            return $row;
        }

        /**
         * Protect attributes by removing them from $row array
         */
        public function protect_attributes($row)
        {
            foreach ($this->protected_attributes as $attr)
            {
                if (is_object($row))
                {
					if(isset($row->$attr)){
						unset($row->$attr);
					}
                }
                else
                {
					if(isset($row[$attr])){
						unset($row[$attr]);
					}
                }
            }
            return $row;
        }
		
		 /**
         * Return the database instance
         * @return Database the database instance
         */
        public function getDatabaseInstance(){
            return $this->_database;
        }

        /**
         * set the Database instance for future use
         * @param Database $db the database object
         */
         public function setDatabaseInstance($db){
            $this->_database = $db;
            if($this->dbCacheTime > 0){
                $this->_database->setCache($this->dbCacheTime);
            }
            return $this;
        }

        /**
         * Return the loader instance
         * @return Loader the loader instance
         */
        public function getLoader(){
            return $this->loaderInstance;
        }

        /**
         * set the loader instance for future use
         * @param Loader $loader the loader object
         */
         public function setLoader($loader){
            $this->loaderInstance = $loader;
            return $this;
        }

        /**
         * Return the FormValidation instance
         * @return FormValidation the form validation instance
         */
        public function getFormValidation(){
            return $this->formValidationInstance;
        }

        /**
         * set the form validation instance for future use
         * @param FormValidation $fv the form validation object
         */
         public function setFormValidation($fv){
            $this->formValidationInstance = $fv;
            return $this;
        }

        /* --------------------------------------------------------------
         * QUERY BUILDER DIRECT ACCESS METHODS
         * ------------------------------------------------------------ */

        /**
         * A wrapper to $this->_database->orderBy()
         */
        public function order_by($criteria, $order = 'ASC')
        {
            if ( is_array($criteria) )
            {
                foreach ($criteria as $key => $value)
                {
                    $this->_database->orderBy($key, $value);
                }
            }
            else
            {
                $this->_database->orderBy($criteria, $order);
            }
            return $this;
        }

        /**
         * A wrapper to $this->_database->limit()
         */
        public function limit($offset = 0, $limit = 10)
        {
            $this->_database->limit($offset, $limit);
            return $this;
        }

        /* --------------------------------------------------------------
         * INTERNAL METHODS
         * ------------------------------------------------------------ */

        /**
         * Trigger an event and call its observers. Pass through the event name
         * (which looks for an instance variable $this->event_name), an array of
         * parameters to pass through and an optional 'last in interation' boolean
         */
        protected function trigger($event, $data = FALSE, $last = TRUE)
        {
            if (isset($this->$event) && is_array($this->$event))
            {
                foreach ($this->$event as $method)
                {
                    if (strpos($method, '('))
                    {
                        preg_match('/([a-zA-Z0-9\_\-]+)(\(([a-zA-Z0-9\_\-\., ]+)\))?/', $method, $matches);

                        $method = $matches[1];
                        $this->callback_parameters = explode(',', $matches[3]);
                    }
                    $data = call_user_func_array(array($this, $method), array($data, $last));
                }
            }
            return $data;
        }

        /**
         * Run validation on the passed data
         */
        protected function validate(array $data)
        {
            if($this->skip_validation)
            {
                return $data;
            }
            if(!empty($this->validate))
            {
                $fv = null;
                if(is_object($this->formValidationInstance)){
                    $fv = $this->formValidationInstance;
                }
                else{
                    Loader::library('FormValidation');
                    $fv = $this->formvalidation;
                    $this->setFormValidation($fv);
                }
               
                $fv->setData($data);
                $fv->setRules($this->validate);

                if ($fv->run())
                {
                    return $data;
                }
                else
                {
                    return FALSE;
                }
            }
            else
            {
                return $data;
            }
        }


        /**
         * Set WHERE parameters, cleverly
         */
        protected function _set_where($params)
        {
            if (count($params) == 1 && is_array($params[0]))
            {
                foreach ($params[0] as $field => $filter)
                {
                    if (is_array($filter))
                    {
                        $this->_database->in($field, $filter);
                    }
                    else
                    {
                        if (is_int($field))
                        {
                            $this->_database->where($filter);
                        }
                        else
                        {
                            $this->_database->where($field, $filter);
                        }
                    }
                }
            }
            else if (count($params) == 1)
            {
                $this->_database->where($params[0]);
            }
        	else if(count($params) == 2)
    		{
                if (is_array($params[1]))
                {
                    $this->_database->in($params[0], $params[1]);
                }
                else
                {
                    $this->_database->where($params[0], $params[1]);
                }
    		}
    		else if(count($params) == 3)
    		{
    			$this->_database->where($params[0], $params[1], $params[2]);
    		}
            else
            {
                if (is_array($params[1]))
                {
                    $this->_database->in($params[0], $params[1]);
                }
                else
                {
                    $this->_database->where($params[0], $params[1]);
                }
            }
        }

        /**
            Shortcut to controller
        */
        public function __get($key){
            return get_instance()->{$key};
        }

    }
