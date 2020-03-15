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

    class Model {

       /**
         * This model's default database table. 
         * @var string the name of table
         */
        protected $table = '';

        /**
         * The database connection object. Will be set to the default
         * connection. This allows individual models to use different DBs
         * without overwriting the global database connection.
         */
        protected $db = null;

        /**
         * This model's default primary key or unique identifier.
         * Used by the getSingleRecord(), update() and delete() functions.
         */
        protected $primaryKey = 'id';

        /**
         * Support for soft deletes and this model's 'deleted' key
         * Whether soft delete is enabled or not
         * @var boolean
         */
        protected $softDeleteStatus = false;

        /**
         * Soft delete table column name to use
         * @var string
         */
        protected $softDeleteTableColumn = 'is_deleted';

        /**
         * Whether to return the records with the deleted
         * @var boolean
         */
        protected $returnRecordWithDeleted = false;

        /**
         * Whether to return only the deleted records
         * @var boolean
         */
        protected $returnOnlyRecordDeleted = false;

        /**
         * The various callbacks available to the model. Each are
         * simple lists of method names (methods will be call on $this->xxx).
         */
        protected $beforeCreateCallbacks = array();
        protected $afterCreateCallbacks = array();
        protected $beforeUpdateCallbacks = array();
        protected $afterUpdateCallbacks = array();
        protected $beforeGetCallbacks = array();
        protected $afterGetCallbacks = array();
        protected $beforeDeleteCallbacks = array();
        protected $afterDeleteCallbacks = array();

        /**
         * List of methods parameters to use in model callbacks
         * @var array
         */
        protected $callbackParameters = array();

        /**
         * Protected, non-modifiable tables columns
         * @var array the list of table attributes that can not be inserted 
         * or updated
         */
        protected $protectedTableColumns = array();

        /**
         * Relationship arrays. Use flat strings for defaults or 
         * string => array to customise the class name and primary key
         *
         * @example 
         *  For flat string:
         *  $manyToOne = array('relation_name')
         *
         *  For array:
         * $manyToOne = array('relation_name' => array(
         *  'primary_key' => 'pk_value',
         *  'modek' => 'model_name'
         * ))
         */
        protected $manyToOne = array();
        protected $oneToMany = array();

        /**
         * List of relation to return with the fetched record
         * @var array
         */
        protected $withs = array();

        /**
         * An array of validation rules. This needs to be the same format
         * as validation rules passed to the FormValidation::setRules library.
         */
        protected $validationRules = array();

        /**
         * Optionally skip the rules validation. Used in conjunction with
         * setSkipRulesValidation() to skip data validation for any future calls.
         */
        protected $skipRulesValidation = false;

        /**
         * By default we return our results as objects. If we need to override
         * this, we can, or, we could use the `asArray()` and `asObject()` scopes.
         */
        protected $returnRecordType = 'object';

        /**
         * Set current return type array or object
         * @var string
         */
        protected $temporaryReturnRecordType = null;
    	
    	
        /**
         * The database cache time to live
         * The value is in second 
         * @example $dbCacheTimeToLive = 300; //so means 5 minutes
         *  for the cache to live
         *
         * @var integer 
         */
        protected $dbCacheTimeToLive = 0;

        /**
         * Initialization of the model.
         *
         * @param object $db the Database instance to use
         * NOTE: each model need use different database instance 
         * for cache feature to work, so need use "clone" instead of use the global database 
         * instance directly.
         */
        public function __construct(Database $db = null) {
            //Note: don't use the property direct access here as 
            //some update is done in the method
            //$this->setDb()
            if ($db !== null) {
                $this->setDb($db);
            } else {
                 /**
                 * NOTE: Need use "clone" because some Model need have the personal instance of the database library
                 * to prevent duplication
                 */
                 $obj = & get_instance();
                 $this->setDb(clone $obj->database);
            }
            array_unshift($this->beforeCreateCallbacks, 'removeProtectedTableColumns');
            array_unshift($this->beforeUpdateCallbacks, 'removeProtectedTableColumns');
            $this->temporaryReturnRecordType = $this->returnRecordType;
        }

        /**
         * Fetch a single record based on the primary key. Returns an object.
         *
         * @param mixed $pk the primary keys to get record for
         *
         * @return array|object
         */
        public function getSingleRecord($pk) {
            return $this->getSingleRecordCond($this->primaryKey, $pk);
        }


        /**
         * Fetch a single record based on an arbitrary WHERE call. Can be
         * any valid value to DatabaseQueryBuilder->where().
         *
         * @return array|object
         */
        public function getSingleRecordCond() {
            $where = func_get_args();
            $this->checkForSoftDelete();
            $this->setWhereValues($where);
            $this->trigger('beforeGetCallbacks');
            $type = $this->getReturnType();
            $this->getQueryBuilder()->from($this->table);
            $row = $this->db->get($type);
            $row = $this->trigger('afterGetCallbacks', $row);
            $this->temporaryReturnRecordType = $this->returnRecordType;
            $this->withs = array();
            return $row;
        }

        /**
         * Fetch all the records in the table. Can be used as a generic call
         * to $this->db->get() with scoped methods.
         *
         * @param array $pks the list of primary keys if not empty to get record
         *
         * @return array|object
         */
        public function getListRecord(array $pks = array()) {
            $this->trigger('beforeGetCallbacks');
            $this->checkForSoftDelete();
            if (!empty($pks)) {
                $this->getQueryBuilder()->in($this->primaryKey, $pks);
            }
            $type = $this->getReturnType();
            $this->getQueryBuilder()->from($this->table);
            $result = $this->db->getAll($type);
            foreach ($result as $key => &$row) {
                $row = $this->trigger('afterGetCallbacks', $row, ($key == count($result) - 1));
            }
            $this->temporaryReturnRecordType = $this->returnRecordType;
            $this->withs = array();
            return $result;
        }

        /**
         * Fetch an array of records based on an arbitrary WHERE call.
         *
         * @return array|object
         */
        public function getListRecordCond() {
            $where = func_get_args();
            $this->setWhereValues($where);
            return $this->getListRecord();
        }

        /**
         * Insert a new row into the table. $data should be an associative array
         * of data to be inserted. Returns newly created ID.
         *
         * @param array $data the data to be inserted
         * @param boolean $skipRulesValidation whether to skip rules validation or not
         * @param boolean $escape whether to escape all data values
         *
         * @return mixed the insert id if the table have auto increment or sequence id field
         */
        public function insert(array $data = array(), $skipRulesValidation = false, $escape = true) {
            if ($this->validateData($data, $skipRulesValidation) !== false) {
                $data = $this->trigger('beforeCreateCallbacks', $data);
                $this->getQueryBuilder()->from($this->table);
                $this->db->insert($data, $escape);
                $insertId = $this->db->insertId();
                $this->trigger('afterCreateCallbacks', $insertId);
                //if the table doesn't have the auto increment field 
                //or sequence, the value of 0 will be returned 
                if (!$insertId) {
                    $insertId = true;
                }
                return $insertId;
            } 
            return false;
        }

        /**
         * Insert multiple rows into the table.
         *
         * @param array $data the data to be inserted
         * @param boolean $skipRulesValidation whether to skip rules validation or not
         * @param boolean $escape whether to escape all data values
         *
         * @return mixed the array of insert id if the table have auto increment or sequence id field
         */
        public function insertMultiple(array $data = array(), $skipRulesValidation = false, $escape = true) {
            $ids = array();
            foreach ($data as $key => $row) {
                $ids[] = $this->insert($row, $skipRulesValidation, $escape);
            }
            return $ids;
        }

        /**
         * Updated a record based on the primary key value.
         *
         * @param mixed $pk the value of primary key to use to do update
         * @param array $data the data to be inserted
         * @param boolean $skipRulesValidation whether to skip rules validation or not
         * @param boolean $escape whether to escape all data values
         *
         * @return boolean the status of the operation
         */
        public function update($pk, array $data = array(), $skipRulesValidation = false, $escape = true) {
            $data = $this->trigger('beforeUpdateCallbacks', $data);
            if ($this->validateData($data, $skipRulesValidation) !== false) {
                $this->getQueryBuilder()->where($this->primaryKey, $pk)
                                        ->from($this->table);
                $result = $this->db->update($data, $escape);
                $this->trigger('afterUpdateCallbacks', array($data, $result));
                return $result;
            } 
            return false;
        }

        /**
         * Update many records, based on an array of primary keys values.
         * 
         * @param array $pks the array value of primary keys to do update
         * @param array $data the data to be inserted
         * @param boolean $skipRulesValidation whether to skip rules validation or not
         * @param boolean $escape whether to escape all data values
         *
         * @return boolean the status of the operation
         */
        public function updateMultiple($pks, array $data = array(), $skipRulesValidation = false, $escape = true) {
            $data = $this->trigger('beforeUpdateCallbacks', $data);
            if ($this->validateData($data, $skipRulesValidation) !== false) {
                $this->getQueryBuilder()->in($this->primaryKey, $pks)
                                        ->from($this->table);
                $result = $this->db->update($data, $escape);
                $this->trigger('afterUpdateCallbacks', array($data, $result));
                return $result;
            }
            return false;
        }

        /**
         * Updated a record based on an arbitrary WHERE clause.
         *
         * @return boolean the status of the operation
         */
        public function updateCond() {
            $args = func_get_args();
            $data = array();
            if (count($args) == 2 && is_array($args[1])) {
                $data = array_pop($args);
            } else if (count($args) == 3 && is_array($args[2])) {
                $data = array_pop($args);
            }
            $data = $this->trigger('beforeUpdateCallbacks', $data);
            if ($this->validateRules($data) !== false) {
                $this->setWhereValues($args);
                $this->getQueryBuilder()->from($this->table);
                $result = $this->db->update($data);
                $this->trigger('afterUpdateCallbacks', array($data, $result));
                return $result;
            }
            return false;
        }

        /**
         * Update all records in the database without conditions
         * 
         * @param array $data the data to be inserted
         * @param boolean $escape whether to escape all data values
         *
         * @return boolean the status of the operation
         */
        public function updateAllRecord(array $data = array(), $escape = true) {
            $data = $this->trigger('beforeUpdateCallbacks', $data);
            $this->getQueryBuilder()->from($this->table);
            $result = $this->db->update($data, $escape);
            $this->trigger('afterUpdateCallbacks', array($data, $result));
            return $result;
        }

        /**
         * Delete a row from the table by the primary value
         * @param array $id the value of primary key to do delete
         * 
         * @return boolean the status of the operation
         */
        public function delete($id) {
            $this->trigger('beforeDeleteCallbacks', $id);
            $this->getQueryBuilder()->where($this->primaryKey, $id);
            $this->getQueryBuilder()->from($this->table);  
            $result = $this->deleteRecords();
            $this->trigger('afterDeleteCallbacks', $result);
            return $result;
        }

        /**
         * Delete a row from the database table by an arbitrary WHERE clause
         * 
         * @return boolean the status of the operation
         */
        public function deleteCond() {
            $where = func_get_args();
            $where = $this->trigger('beforeDeleteCallbacks', $where);
            $this->setWhereValues($where);
            $this->getQueryBuilder()->from($this->table);  
            $result = $this->deleteRecords();
            $this->trigger('afterDeleteCallbacks', $result);
            return $result;
        }

        /**
         * Delete many rows from the database table by multiple primary values
         * 
         * @param array $pks the array value of primary keys to do delete
         *
         * @return boolean the status of the operation
         */
        public function deleteListRecord(array $pks) {
            $pks = $this->trigger('beforeDeleteCallbacks', $pks);
            $this->getQueryBuilder()->in($this->primaryKey, $pks);
            $this->getQueryBuilder()->from($this->table);  
            $result = $this->deleteRecords();
            $this->trigger('afterDeleteCallbacks', $result);
            return $result;
        }

        /**
         * Truncates the table
         *
         * @return boolean the truncate status
         */
        public function truncate() {
            $this->getQueryBuilder()->from($this->table); 
            $result = $this->db->delete();
            return $result;
        }

        /**
         * Return the record with the relation
         * @param  string $relationship the name of relation to fetch record
         * @return object               the current instance
         */
        public function with($relationship) {
            $this->withs[] = $relationship;
            if (!in_array('relate', $this->afterGetCallbacks)) {
                $this->afterGetCallbacks[] = 'relate';
            }
            return $this;
        }
		
        /**
         * Relationship
         * @param array|object $row the row to add relation data into
         *
         * @return array|object the final row after add relation data
         */
        protected function relate($row) {
            if (empty($row)) {
                return $row;
            }
            $row = $this->relateManyToOne($row);
            $row = $this->relateOneToMany($row);
            return $row;
        }

        /**
         * Retrieve and generate a data to use directly in Form::select()
         *
         * @return array
         */
        public function dropdown() {
            $args = func_get_args();
            if (count($args) == 2) {
                list($key, $value) = $args;
            } else {
                $key = $this->primaryKey;
                $value = $args[0];
            }
            $this->trigger('before_dropdown', array($key, $value));
            $this->checkForSoftDelete();
            $this->getQueryBuilder()->select(array($key, $value))
                                    ->from($this->table);
            $result = $this->db->getAll();
            $options = array();
            foreach ($result as $row) {
                $options[$row->{$key}] = $row->{$value};
            }
            $options = $this->trigger('after_dropdown', $options);
            return $options;
        }

        /**
         * Fetch a total count of rows, disregarding any previous conditions
         * 
         * @return integer the number of rows
         */
        public function countAllRecord() {
            $this->checkForSoftDelete();
            $this->getQueryBuilder()->from($this->table);
            $this->db->getAll();
            return $this->db->numRows();
        }
        
        /**
         * Fetch a count of rows based on an arbitrary WHERE call.
         *
         * @return integer the number of rows
         */
        public function countCond() {
            $where = func_get_args();
            $this->checkForSoftDelete();
            $this->setWhereValues($where);
            $this->getQueryBuilder()->from($this->table);
            $this->db->getAll();
            return $this->db->numRows();
        }
        
        /**
         * Enabled cache temporary. This method is the shortcut to Database::cached
         *
         * @param integer $ttl the cache default time to live
         *
         * @return object the current instance
         */
        public function cached($ttl = 0) {
            $this->db = $this->db->cached($ttl);
            return $this;
        }

        /**
         * Tell the class to skip the data validation
         * @param boolean $status the status of rules validation
         *
         * @return object the current instance
         */
        public function setSkipRulesValidation($status = true) {
            $this->skipRulesValidation = $status;
            return $this;
        }

        /**
         * Get the skip validation status
         *
         * @return boolean
         */
        public function isSkipRulesValidation() {
            return $this->skipRulesValidation;
        }

        /**
         * Return the next auto increment of the table. 
         * Only tested on MySQL and SQLite
         *
         * @return mixed
         */
        public function getNextAutoIncrementId() {
            $driver = $this->db->getConnection()->getDriver();
            if ($driver == 'mysql') {
                $this->getQueryBuilder()->select('AUTO_INCREMENT')
                                        ->from('information_schema.TABLES')
                                        ->where('TABLE_NAME', $this->getTable())
                                        ->where('TABLE_SCHEMA', $this->db->getConnection()->getDatabase());
                return (int) $this->db->get()->AUTO_INCREMENT;
            }

            if ($driver == 'sqlite') {
                $this->getQueryBuilder()->select('SEQ')
                                        ->from('SQLITE_SEQUENCE')
                                        ->where('NAME', $this->getTable());
                return ((int) $this->db->get()->seq) + 1;
            }
            return null;
        }

        /**
         * Getter for the table name
         *
         * @return string the name of table
         */
        public function getTable() {
            return $this->table;
        }

        /**
         * Getter for the primary key name
         *
         * @return string the name of primary key
         */
        public function getPrimaryKey() {
            return $this->primaryKey;
        }

        /**
         * Return the next call as an array rather than an object
         */
        public function asArray() {
            $this->temporaryReturnRecordType = 'array';
            return $this;
        }

        /**
         * Return the next call as an object rather than an array
         */
        public function asObject() {
            $this->temporaryReturnRecordType = 'object';
            return $this;
        }

        /**
         * Don't care about soft deleted rows on the next call
         *
         * @return object the current instance
         */
        public function recordWithDeleted() {
            $this->returnRecordWithDeleted = true;
            return $this;
        }

        /**
         * Only get deleted rows on the next call
         * 
         * @return object the current instance
        */
        public function onlyRecordDeleted() {
            $this->returnOnlyRecordDeleted = true;
            return $this;
        }

        /**
         * Table DATETIME field created_at
         *
         * @param array $row the data to be inserted
         *
         * @return array the data after add field for created time
         */
        public function createdAt($row) {
            $row['created_at'] = date('Y-m-d H:i:s');
            return $row;
        }

        /**
         * Table DATETIME field  updated_at
         *
         * @param array $row the data to be updated
         *
         * @return array the data after add field for updated time
         */
        public function updatedAt($row) {
           $row['updated_at'] = date('Y-m-d H:i:s');
           return $row;
        }

        /**
         * Serialises data for you automatically, allowing you to pass
         * through objects and let it handle the serialisation in the background
         *
         * @param array|object $row the data to be serialized
         * 
         * @return array|object the data after processing
         */
        public function serialize($row) {
            foreach ($this->callbackParameters as $column) {
                $row[$column] = serialize($row[$column]);
            }
            return $row;
        }

        /**
         * Unserialises data for you automatically, allowing you to pass
         * through objects and let it handle the serialisation in the background
         *
         * @param array|object $row the data to be unserialized
         * 
         * @return array|object the data after processing
         */
        public function unserialize($row) {
            foreach ($this->callbackParameters as $column) {
                if (is_array($row)) {
                    $row[$column] = unserialize($row[$column]);
                } else {
                    $row->$column = unserialize($row->$column);
                }
            }
            return $row;
        }

        /**
         * Protect attributes by removing them from data to insert or update
         *
         * @return mixed the final row after remove the protected
         * table columns if they exist
         */
        public function removeProtectedTableColumns($row) {
            foreach ($this->protectedTableColumns as $attr) {
               if (isset($row[$attr])) {
                    unset($row[$attr]);
                }
            }
            return $row;
        }
		
        /**
         * Return the database instance
         * @return Database the database instance
         */
        public function getDb() {
            return $this->db;
        }

        /**
         * Set the Database instance for future use
         * @param Database $db the database object
         */
        public function setDb(Database $db) {
            $this->db = $db;
            if ($this->dbCacheTimeToLive > 0) {
                $this->db->setCacheTimeToLive($this->dbCacheTimeToLive);
            }
            return $this;
        }

        /**
         * Return the queryBuilder instance this is the shortcut to database queryBuilder
         * @return object the DatabaseQueryBuilder instance
         */
        public function getQueryBuilder() {
            return $this->db->getQueryBuilder();
        }

        /**
         * Set the DatabaseQueryBuilder instance for future use
         * @param object $queryBuilder the DatabaseQueryBuilder object
         * @return object
         */
        public function setQueryBuilder($queryBuilder) {
            $this->db->setQueryBuilder($queryBuilder);
            return $this;
        }

        /* --------------------------------------------------------------
         * QUERY BUILDER DIRECT ACCESS METHODS
         * ------------------------------------------------------------ */

        /**
         * A wrapper to $this->getQueryBuilder()->orderBy()
         *
         * @see  DatabaseQueryBuilder::orderBy
         */
        public function orderBy($criteria, $order = 'ASC') {
            if (is_array($criteria)) {
                foreach ($criteria as $key => $value) {
                    $this->getQueryBuilder()->orderBy($key, $value);
                }
            } else {
                $this->getQueryBuilder()->orderBy($criteria, $order);
            }
            return $this;
        }

        /**
         * A wrapper to $this->getQueryBuilder()->limit()
         * 
         * @see  DatabaseQueryBuilder::limit
         */
        public function limit($offset = 0, $limit = 10) {
            $this->getQueryBuilder()->limit($offset, $limit);
            return $this;
        }

        /**
         * Delete record in tha database
         * 
         * @return boolean the delete status
         */
        protected function deleteRecords() {
            $result = false;
            if ($this->softDeleteStatus) {
                $result = $this->db->update(array($this->softDeleteTableColumn => 1));
            } else {
                $result = $this->db->delete();
            }
            return $result;
        }

        /**
         * Validate the data using the validation rules
         * 
         * @param  array $data the data to validate before insert, update, etc.
         * @param boolean $skipValidation whether to skip validation or not
         * 
         * @return array|boolean
         */
        protected function validateData($data, $skipValidation) {
            if ($skipValidation === false) {
                $data = $this->validateRules($data);
            }
            return $data;
        }

        /**
         * Run validation on the passed data
         * @param  array $data the data to validate before insert, update, etc.
         * 
         * @return array|boolean 
         */
        protected function validateRules(array $data) {
            if ($this->isSkipRulesValidation() || empty($this->validationRules)) {
                return $data;
            }
            get_instance()->formvalidation->setData($data);
            get_instance()->formvalidation->setRules($this->validationRules);
            if (get_instance()->formvalidation->validate() === true) {
                return $data;
            }
            return false;
        }

         /**
         * Get the record return type array or object
         * 
         * @return string|boolean
         */
        protected function getReturnType(){
            $type = false;
            if ($this->temporaryReturnRecordType == 'array') {
               $type = 'array';
            }
            return $type;
        }

         /**
         * Check if soft delete is enable setting the condition
         * @return object the current instance 
         */
        protected function checkForSoftDelete(){
            if ($this->softDeleteStatus && $this->returnRecordWithDeleted !== true) {
                $this->getQueryBuilder()->where(
                                                $this->softDeleteTableColumn, 
                                                (int) $this->returnOnlyRecordDeleted
                                            );
            }
            return $this;
        }

         /**
         * Relate for "manyToOne" and "oneToMany"
         * 
         * @param  string $relationship the name of relation
         * @param  string|array $options      the model and primary key values
         * @param  object|array $row          the row to update
         * @param  string $type the type can be "manyToOne", "oneToMany"
         * 
         * @return array|object the final row values
         */
        protected function relateOneToManyAndManyToOne($relationship, $options, $row, $type){
            if (in_array($relationship, $this->withs)) {
                get_instance()->loader->model($options['model'], $relationship . '_model');
                $model = get_instance()->{$relationship . '_model'};
                if($type == 'manyToOne'){
                    if (is_object($row)) {
                        $row->{$relationship} = $model->getSingleRecord($row->{$options['primary_key']});
                    } else {
                        $row[$relationship] = $model->getSingleRecord($row[$options['primary_key']]);
                    }
                } else {
                    if (is_object($row)) {
                        $row->{$relationship} = $model->getListRecordCond($options['primary_key'], $row->{$this->primaryKey});
                    } else {
                        $row[$relationship] = $model->getListRecordCond($options['primary_key'], $row[$this->primaryKey]);
                    }
                }
            }
            return $row;
        }

        /**
         * Relate for the relation "manyToOne"
         * @see Model::relateOneToManyAndManyToOne
         */
        protected function relateManyToOne($row) {
            foreach ($this->manyToOne as $key => $value) {
                $options = $this->getRelationshipOptions($key, $value);
                $row = $this->relateOneToManyAndManyToOne(
                                                            $options['relationship'], 
                                                            $options, 
                                                            $row, 
                                                            'manyToOne'
                                                        );
            }
            return $row;
        }

        /**
         * Relate for the relation "oneToMany"
         * @see Model::relateOneToManyAndManyToOne
         */
        protected function relateOneToMany($row) {
            foreach ($this->oneToMany as $key => $value) {
                $options = $this->getRelationshipOptions($key, $value);
                $row = $this->relateOneToManyAndManyToOne(
                                                            $options['relationship'], 
                                                            $options, 
                                                            $row, 
                                                            'oneToMany'
                                                        );
            }
            return $row;
        }

        /**
         * Get the relationship options to use 
         * @param  mixed $key   the relationship key
         * @param  mixed $value the raltionship value for custom option
         * @return array the options
         */
        protected function getRelationshipOptions($key, $value) {
            $relationship = null;
            $options = null;
            if (is_string($value)) {
                $relationship = $value;
                $options = array('primary_key' => $this->table . '_id', 'model' => $value . '_model');
            } else {
                $relationship = $key;
                $options = $value;
            }
            $options['relationship'] = $relationship;
            return $options;
        }
		
        /**
         * Trigger an event and call its observers. Pass through the event name
         * (which looks for an instance variable $this->event_name), an array of
         * parameters to pass through and an optional 'last in interation' boolean
         *
         * @param string $event the name of event like afterGetCallbacks
         * @param mixed $data the data to pass to the callback
         * @param boolean $last if is the last row of data to process
         *
         * @return mixed the data after each callbacks processed
         */
        protected function trigger($event, $data = false, $last = true) {
            if (isset($this->$event) && is_array($this->$event)) {
                foreach ($this->$event as $method) {
                    if (strpos($method, '(')) {
                        preg_match('/([a-zA-Z0-9\_\-]+)(\(([a-zA-Z0-9\_\-\., ]+)\))?/', $method, $matches);
                        $method = $matches[1];
                        $this->callbackParameters = explode(',', $matches[3]);
                    }
                    $data = call_user_func_array(array($this, $method), array($data, $last));
                }
            }
            return $data;
        }
		
        /**
         * Set WHERE parameters, when is array
         * @param array $params
         *
         * @return object the current instance
         */
        protected function setWhereValuesArray(array $params) {
            foreach ($params as $field => $filter) {
                if (is_array($filter)) {
                    $this->getQueryBuilder()->in($field, $filter);
                } else {
                    if (is_int($field)) {
                        $this->getQueryBuilder()->where($filter);
                    } else {
                        $this->getQueryBuilder()->where($field, $filter);
                    }
                }
            }
            return $this;
        }

        /**
         * Set WHERE parameters, cleverly
         * @param mixed $params the parameters of where
         * 
         * @return object the current instance
         */
        protected function setWhereValues($params) {
            if (count($params) == 1) {
                if (is_array($params[0])) {
                    $this->setWhereValuesArray($params[0]);
                } else {
                    $this->getQueryBuilder()->where($params[0]);
                }
            } else if (count($params) == 2) {
                if (is_array($params[1])) {
                    $this->getQueryBuilder()->in($params[0], $params[1]);
                } else {
                    $this->getQueryBuilder()->where($params[0], $params[1]);
                }
            } else if (count($params) == 3) {
                $this->getQueryBuilder()->where($params[0], $params[1], $params[2]);
            } 
        }
    }
