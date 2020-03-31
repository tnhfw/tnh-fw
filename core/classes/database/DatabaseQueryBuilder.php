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
    
    class DatabaseQueryBuilder {

         /**
         * The DatabaseConnection instance
         * @var object
         */
        private $connection = null;

        /**
         * The SQL SELECT statment
         * @var string
         */
        private $select = '*';
  	
        /**
         * The SQL FROM statment
         * @var string
         */
        private $from = null;
  	
        /**
         * The SQL WHERE statment
         * @var string
         */
        private $where = null;
  	
        /**
         * The SQL LIMIT statment
         * @var string
         */
        private $limit = null;
  	
        /**
         * The SQL JOIN statment
         * @var string
         */
        private $join = null;
  	
        /**
         * The SQL ORDER BY statment
         * @var string
         */
        private $orderBy = null;
  	
        /**
         * The SQL GROUP BY statment
         * @var string
         */
        private $groupBy = null;
  	
        /**
         * The SQL HAVING statment
         * @var string
         */
        private $having = null;
  	
        /**
         * The full SQL query statment after build for each command
         * @var string
         */
        private $query = null;
  	
        /**
         * The list of SQL valid operators
         * @var array
         */
        private $operatorList = array('=', '!=', '<', '>', '<=', '>=', '<>');
  	
        /**
         * Construct new DatabaseQueryBuilder
         * @param object $connection the DatabaseConnection object
         */
        public function __construct(DatabaseConnection $connection = null) {
            if ($connection !== null) {
                $this->connection = $connection;
            }
        }

        /**
         * Set the SQL FROM statment
         * @param  string|array $table the table name or array of table list
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function from($table) {
            $prefix = $this->connection->getPrefix();
            if (is_array($table)) {
                $froms = '';
                foreach ($table as $key) {
                    $froms .= $prefix . $key . ', ';
                }
                $this->from = rtrim($froms, ', ');
            } else {
                $this->from = $prefix . $table;
            }
            return $this;
        }

        /**
         * Set the SQL SELECT statment
         * @param  string|array $fields the field name or array of field list
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function select($fields) {
            $select = $fields;
            if (is_array($fields)) {
                $select = implode(', ', $fields);
            }
            return $this->setSelectStr($select);
        }

        /**
         * Set the SQL SELECT DISTINCT statment
         * @param  string $field the field name to distinct
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function distinct($field) {
            return $this->setSelectStr('DISTINCT ' . $field);
        }

        /**
         * Set the SQL function COUNT in SELECT statment
         * @param  string $field the field name
         * @param  string $name  if is not null represent the alias used for this field in the result
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function count($field = '*', $name = null) {
            return $this->selectMinMaxSumCountAvg('COUNT', $field, $name);
        }
    
        /**
         * Set the SQL function MIN in SELECT statment
         * @param  string $field the field name
         * @param  string $name  if is not null represent the alias used for this field in the result
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function min($field, $name = null) {
            return $this->selectMinMaxSumCountAvg('MIN', $field, $name);
        }

        /**
         * Set the SQL function MAX in SELECT statment
         * @param  string $field the field name
         * @param  string $name  if is not null represent the alias used for this field in the result
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function max($field, $name = null) {
            return $this->selectMinMaxSumCountAvg('MAX', $field, $name);
        }

        /**
         * Set the SQL function SUM in SELECT statment
         * @param  string $field the field name
         * @param  string $name  if is not null represent the alias used for this field in the result
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function sum($field, $name = null) {
            return $this->selectMinMaxSumCountAvg('SUM', $field, $name);
        }

        /**
         * Set the SQL function AVG in SELECT statment
         * @param  string $field the field name
         * @param  string $name  if is not null represent the alias used for this field in the result
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function avg($field, $name = null) {
            return $this->selectMinMaxSumCountAvg('AVG', $field, $name);
        }

        /**
         * Set the SQL JOIN statment
         * @param  string $table  the join table name
         * @param  string $field1 the first field for join conditions	
         * @param  string $op     the join condition operator. If is null the default will be "="
         * @param  string $field2 the second field for join conditions
         * @param  string $type   the type of join (INNER, LEFT, RIGHT)
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function join($table, $field1 = null, $op = null, $field2 = null, $type = '') {
            $on = $field1;
            $prefix = $this->connection->getPrefix();
            $table = $prefix . $table;
            if (!is_null($op)) {
                $on = $prefix . $field1 . ' ' . $op . ' ' . $prefix . $field2;
                if (!in_array($op, $this->operatorList)) {
                    $on = $prefix . $field1 . ' = ' . $prefix . $op;
                }
            }
            if (empty($this->join)) {
                $this->join = $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
            } else {
                $this->join = $this->join . ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
            }
            return $this;
        }

        /**
         * Set the SQL INNER JOIN statment
         * @see  DatabaseQueryBuilder::join()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function innerJoin($table, $field1, $op = null, $field2 = '') {
            return $this->join($table, $field1, $op, $field2, 'INNER ');
        }

        /**
         * Set the SQL LEFT JOIN statment
         * @see  DatabaseQueryBuilder::join()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function leftJoin($table, $field1, $op = null, $field2 = '') {
            return $this->join($table, $field1, $op, $field2, 'LEFT ');
        }

        /**
         * Set the SQL RIGHT JOIN statment
         * @see  DatabaseQueryBuilder::join()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function rightJoin($table, $field1, $op = null, $field2 = '') {
            return $this->join($table, $field1, $op, $field2, 'RIGHT ');
        }

        /**
         * Set the SQL FULL OUTER JOIN statment
         * @see  DatabaseQueryBuilder::join()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function fullOuterJoin($table, $field1, $op = null, $field2 = '') {
            return $this->join($table, $field1, $op, $field2, 'FULL OUTER ');
        }

        /**
         * Set the SQL LEFT OUTER JOIN statment
         * @see  DatabaseQueryBuilder::join()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function leftOuterJoin($table, $field1, $op = null, $field2 = '') {
            return $this->join($table, $field1, $op, $field2, 'LEFT OUTER ');
        }

        /**
         * Set the SQL RIGHT OUTER JOIN statment
         * @see  DatabaseQueryBuilder::join()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function rightOuterJoin($table, $field1, $op = null, $field2 = '') {
            return $this->join($table, $field1, $op, $field2, 'RIGHT OUTER ');
        }

        /**
         * Set the SQL WHERE CLAUSE for IS NULL
         * @see  DatabaseQueryBuilder::whereIsNullAndNotNull
         */
        public function whereIsNull($field, $andOr = 'AND') {
            return $this->whereIsNullAndNotNull($field, $andOr, 'IS NULL');
        }

        /**
         * Set the SQL WHERE CLAUSE for IS NOT NULL
         * @see  DatabaseQueryBuilder::whereIsNullAndNotNull
         */
        public function whereIsNotNull($field, $andOr = 'AND') {
            return $this->whereIsNullAndNotNull($field, $andOr, 'IS NOT NULL');
        }
    
        /**
         * Set the SQL WHERE CLAUSE statment
         * @param  string|array  $where the where field or array of field list
         * @param  null|string  $op the condition operator. If is null the default will be "="
         * @param  mixed  $val the where value
         * @param  string  $type the type used for this where clause (NOT, etc.)
         * @param  string  $andOr the separator type used 'AND', 'OR', etc.
         * @param  boolean $escape whether to escape or not the $val
         * @return object the current instance
         */
        public function where($where, $op = null, $val = null, $type = '', $andOr = 'AND', $escape = true) {
            if (is_array($where)) {
                $whereStr = $this->getWhereStrArray($where, $type, $andOr, $escape);
                $this->setWhereStr($whereStr, $andOr);
                return $this;
            } 
            $whereStr = $this->getWhereStrForOperator($where, $op, $val, $type, $escape);
            $this->setWhereStr($whereStr, $andOr);
            return $this;
        }

        /**
         * Set the SQL WHERE CLAUSE statment using OR
         * @see  DatabaseQueryBuilder::where()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function orWhere($where, $op = null, $val = null, $escape = true) {
            return $this->where($where, $op, $val, '', 'OR', $escape);
        }

        /**
         * Set the SQL WHERE CLAUSE statment using AND and NOT
         * @see  DatabaseQueryBuilder::where()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function notWhere($where, $op = null, $val = null, $escape = true) {
            return $this->where($where, $op, $val, 'NOT ', 'AND', $escape);
        }

        /**
         * Set the SQL WHERE CLAUSE statment using OR and NOT
         * @see  DatabaseQueryBuilder::where()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function orNotWhere($where, $op = null, $val = null, $escape = true) {
            return $this->where($where, $op, $val, 'NOT ', 'OR', $escape);
        }

        /**
         * Set the opened parenthesis for the complex SQL query
         * @param  string $type   the type of this grouped (NOT, etc.)
         * @param  string $andOr the multiple conditions separator (AND, OR, etc.)
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function groupStart($type = '', $andOr = ' AND') {
            if (empty($this->where)) {
                $this->where = $type . '(';
            } else {
                if (substr(trim($this->where), -1) == '(') {
                    $this->where .= $type . '(';
                } else {
                    $this->where .= $andOr . $type . ' (';
                }
            }
            return $this;
        }

        /**
         * Set the opened parenthesis for the complex SQL query using NOT type
         * @see  DatabaseQueryBuilder::groupStart()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function notGroupStart() {
            return $this->groupStart(' NOT');
        }

        /**
         * Set the opened parenthesis for the complex SQL query using OR for separator
         * @see  DatabaseQueryBuilder::groupStart()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function orGroupStart() {
            return $this->groupStart('', ' OR');
        }

        /**
         * Set the opened parenthesis for the complex SQL query using OR for separator and NOT for type
         * @see  DatabaseQueryBuilder::groupStart()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function orNotGroupStart() {
            return $this->groupStart('NOT', ' OR');
        }

        /**
         * Close the parenthesis for the grouped SQL
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function groupEnd() {
            $this->where .= ')';
            return $this;
        }

        /**
         * Set the SQL WHERE CLAUSE statment for IN
         * @param  string  $field  the field name for IN statment
         * @param  array   $keys   the list of values used
         * @param  string  $type   the condition separator type (NOT)
         * @param  string  $andOr the multiple conditions separator (OR, AND)
         * @param  boolean $escape whether to escape or not the values
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function in($field, array $keys, $type = '', $andOr = 'AND', $escape = true) {
            $keysList = array();
            foreach ($keys as $k => $v) {
                $v = $this->checkForNullValue($v);
                if (! is_numeric($v)) {
                    $v = $this->connection->escape($v, $escape);
                }
                $keysList[] = $v;
            }
            $keys = implode(', ', $keysList);
            $whereStr = $field . $type . ' IN (' . $keys . ')';
            $this->setWhereStr($whereStr, $andOr);
            return $this;
        }

        /**
         * Set the SQL WHERE CLAUSE statment for NOT IN with AND separator
         * @see  DatabaseQueryBuilder::in()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function notIn($field, array $keys, $escape = true) {
            return $this->in($field, $keys, ' NOT', 'AND', $escape);
        }

        /**
         * Set the SQL WHERE CLAUSE statment for IN with OR separator
         * @see  DatabaseQueryBuilder::in()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function orIn($field, array $keys, $escape = true) {
            return $this->in($field, $keys, '', 'OR', $escape);
        }

        /**
         * Set the SQL WHERE CLAUSE statment for NOT IN with OR separator
         * @see  DatabaseQueryBuilder::in()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function orNotIn($field, array $keys, $escape = true) {
            return $this->in($field, $keys, ' NOT', 'OR', $escape);
        }

        /**
         * Set the SQL WHERE CLAUSE statment for BETWEEN
         * @param  string  $field  the field used for the BETWEEN statment
         * @param  mixed  $value1 the BETWEEN begin value
         * @param  mixed  $value2 the BETWEEN end value
         * @param  string  $type   the condition separator type (NOT)
         * @param  string  $andOr the multiple conditions separator (OR, AND)
         * @param  boolean $escape whether to escape or not the values
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function between($field, $value1, $value2, $type = '', $andOr = 'AND', $escape = true) {
            $value1 = $this->checkForNullValue($value1);
            $value2 = $this->checkForNullValue($value2);
            $whereStr = $field . $type . ' BETWEEN ' . $this->connection->escape($value1, $escape) . ' AND ' . $this->connection->escape($value2, $escape);
            $this->setWhereStr($whereStr, $andOr);
            return $this;
        }

        /**
         * Set the SQL WHERE CLAUSE statment for BETWEEN with NOT type and AND separator
         * @see  DatabaseQueryBuilder::between()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function notBetween($field, $value1, $value2, $escape = true) {
            return $this->between($field, $value1, $value2, ' NOT', 'AND', $escape);
        }

        /**
         * Set the SQL WHERE CLAUSE statment for BETWEEN with OR separator
         * @see  DatabaseQueryBuilder::between()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function orBetween($field, $value1, $value2, $escape = true) {
            return $this->between($field, $value1, $value2, '', 'OR', $escape);
        }

        /**
         * Set the SQL WHERE CLAUSE statment for BETWEEN with NOT type and OR separator
         * @see  DatabaseQueryBuilder::between()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function orNotBetween($field, $value1, $value2, $escape = true) {
            return $this->between($field, $value1, $value2, ' NOT', 'OR', $escape);
        }

        /**
         * Set the SQL WHERE CLAUSE statment for LIKE
         * @param  string  $field  the field name used in LIKE statment
         * @param  string  $data   the LIKE value for this field including the '%', and '_' part
         * @param  string  $type   the condition separator type (NOT)
         * @param  string  $andOr the multiple conditions separator (OR, AND)
         * @param  boolean $escape whether to escape or not the values
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function like($field, $data, $type = '', $andOr = 'AND', $escape = true) {
            $data = $this->checkForNullValue($data);
            $this->setWhereStr($field . $type . ' LIKE ' . ($this->connection->escape($data, $escape)), $andOr);
            return $this;
        }

        /**
         * Set the SQL WHERE CLAUSE statment for LIKE with OR separator
         * @see  DatabaseQueryBuilder::like()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function orLike($field, $data, $escape = true) {
            return $this->like($field, $data, '', 'OR', $escape);
        }

        /**
         * Set the SQL WHERE CLAUSE statment for LIKE with NOT type and AND separator
         * @see  DatabaseQueryBuilder::like()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function notLike($field, $data, $escape = true) {
            return $this->like($field, $data, ' NOT', 'AND', $escape);
        }

        /**
         * Set the SQL WHERE CLAUSE statment for LIKE with NOT type and OR separator
         * @see  DatabaseQueryBuilder::like()
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function orNotLike($field, $data, $escape = true) {
            return $this->like($field, $data, ' NOT', 'OR', $escape);
        }

        /**
         * Set the SQL LIMIT statment
         * @param  int $limit    the limit offset. If $limitEnd is null this will be the limit count
         * like LIMIT n;
         * @param  int $limitEnd the limit count
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function limit($limit, $limitEnd = null) {
            if (empty($limit)) {
                $limit = 0;
            }
            if (!is_null($limitEnd)) {
                $this->limit = $limit . ', ' . $limitEnd;
            } else {
                $this->limit = $limit;
            }
            return $this;
        }

        /**
         * Set the SQL ORDER BY CLAUSE statment
         * @param  string $orderBy   the field name used for order
         * @param  string $orderDir the order direction (ASC or DESC)
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function orderBy($orderBy, $orderDir = 'ASC') {
            if (stristr($orderBy, ' ') || $orderBy == 'rand()') {
                $this->orderBy = empty($this->orderBy) ? $orderBy : $this->orderBy . ', ' . $orderBy;
            } else {
                $this->orderBy = empty($this->orderBy) 
                            ? ($orderBy . ' ' . strtoupper($orderDir)) 
                            : $this->orderBy . ', ' . $orderBy . ' ' . strtoupper($orderDir);
            }
            return $this;
        }

        /**
         * Set the SQL GROUP BY CLAUSE statment
         * @param  string|array $field the field name used or array of field list
         * @return object        the current DatabaseQueryBuilder instance
         */
        public function groupBy($field) {
            $groupBy = $field;
            if (is_array($field)) {
                $groupBy = implode(', ', $field);
            } 
            $this->groupBy = $groupBy;
            return $this;
        }

        /**
         * Set the SQL HAVING CLAUSE statment
         * @param  string  $field  the field name used for HAVING statment
         * @param  string|null $op the operator used or array
         * @param  mixed  $val the value for HAVING comparaison
         * @param  boolean $escape whether to escape or not the values
         * @return object the current instance
         */
        public function having($field, $op = null, $val = null, $escape = true) {
            if (!in_array($op, $this->operatorList)) {
                $op = $this->checkForNullValue($op);
                $this->having = $field . ' > ' . ($this->connection->escape($op, $escape));
                return $this;
            } 
            $val = $this->checkForNullValue($val);
            $this->having = $field . ' ' . $op . ' ' . ($this->connection->escape($val, $escape));
            return $this;
        }

        /**
         * Insert new record in the database
         * @param  array   $data   the record data
         * @param  boolean $escape  whether to escape or not the values
         * @return object  the current DatabaseQueryBuilder instance        
         */
        public function insert($data = array(), $escape = true) {
            $columns = array_keys($data);
            $column = implode(', ', $columns);
            $values = implode(', ', ($escape ? array_map(array($this->connection, 'escape'), $data) : $data));

            $this->query = 'INSERT INTO ' . $this->from . '(' . $column . ') VALUES (' . $values . ')';
            return $this;
        }

        /**
         * Update record in the database
         * @param  array   $data   the record data if is empty will use the $this->data array.
         * @param  boolean $escape  whether to escape or not the values
         * @return object  the current DatabaseQueryBuilder instance 
         */
        public function update($data = array(), $escape = true) {
            $query = 'UPDATE ' . $this->from . ' SET ';
            $values = array();
            foreach ($data as $column => $val) {
                $values[] = $column . ' = ' . ($this->connection->escape($val, $escape));
            }
            $query .= implode(', ', $values);
            $query .= $this->buildQueryPart('where', ' WHERE ');
            $query .= $this->buildQueryPart('orderBy', ' ORDER BY ');
            $query .= $this->buildQueryPart('limit', ' LIMIT ');

            $this->query = $query;
            return $this;
        }

        /**
         * Delete the record in database
         * @return object  the current DatabaseQueryBuilder instance 
         */
        public function delete() {
            $query = 'DELETE FROM ' . $this->from;
            $isTruncate = $query;
            $query .= $this->buildQueryPart('where', ' WHERE ');
            $query .= $this->buildQueryPart('orderBy', ' ORDER BY ');
            $query .= $this->buildQueryPart('limit', ' LIMIT ');

            if ($isTruncate == $query && $this->connection->getDriver() != 'sqlite') {  
                $query = 'TRUNCATE TABLE ' . $this->from;
            }
            $this->query = $query;
            return $this;
        }

        /**
         * Return the current SQL query string
         * @return string
         */
        public function getQuery() {
            //INSERT, UPDATE, DELETE already set it, if is SELECT we need set it now
            if (empty($this->query)) {
                $query = 'SELECT ' . $this->select . ' FROM ' . $this->from;
                $query .= $this->buildQueryPart('join', ' ');
                $query .= $this->buildQueryPart('where', ' WHERE ');
                $query .= $this->buildQueryPart('groupBy', ' GROUP BY ');
                $query .= $this->buildQueryPart('having', ' HAVING ');
                $query .= $this->buildQueryPart('orderBy', ' ORDER BY ');
                $query .= $this->buildQueryPart('limit', ' LIMIT ');
                $this->query = trim($query);
            }
            return $this->query;
        }
	
        /**
         * Reset the DatabaseQueryBuilder class attributs to the initial values before each query.
         * @return object  the current DatabaseQueryBuilder instance 
         */
        public function reset() {
            $this->select   = '*';
            $this->from     = null;
            $this->where    = null;
            $this->limit    = null;
            $this->orderBy  = null;
            $this->groupBy  = null;
            $this->having   = null;
            $this->join     = null;
            $this->query    = null;
            return $this;
        }

        /**
         * Return the DatabaseConnection instance
         * @return object DatabaseConnection
         */
        public function getConnection() {
            return $this->connection;
        }

        /**
         * Set the DatabaseConnection instance
         * @param object DatabaseConnection $connection the DatabaseConnection object
         *
         * @return object the current instance
         */
        public function setConnection(DatabaseConnection $connection) {
            $this->connection = $connection;
            return $this;
        }


        /**
         * Build the part of SQL query
         * @param  string $property the name of this class attribute, use after $this->
         * @param  string $command  the SQL command like WHERE, HAVING, etc.
         * 
         * @return string|null
         */
         protected function buildQueryPart($property, $command = ''){
            if (!empty($this->{$property})) {
                return $command . $this->{$property};
            }
            return null;
         }


        /**
         * Set the SQL WHERE CLAUSE for IS NULL ad IS NOT NULL
         * @param  string|array $field  the field name or array of field list
         * @param  string $andOr the separator type used 'AND', 'OR', etc.
         * @param string $clause the clause type "IS NULL", "IS NOT NULLs"
         * @return object        the current DatabaseQueryBuilder instance
         */
        protected function whereIsNullAndNotNull($field, $andOr = 'AND', $clause = 'IS NULL'){
            if (is_array($field)) {
                foreach ($field as $f) {
                    $this->whereIsNullAndNotNull($f, $andOr, $clause);
                }
            } else {
                $this->setWhereStr($field . ' ' . $clause, $andOr);
            }
            return $this;
        }


        /**
         * Set the value for SELECT command and update it if already exists
         * @param string $newSelect the new value to set
         *
         * @return object the current instance
         */
        protected function setSelectStr($newSelect){
            $this->select = (($this->select == '*' || empty($this->select)) 
                                    ? $newSelect 
                                    : $this->select . ', ' . $newSelect);
            return $this;
        }

        /**
         * Check if the value is null will return an empty string
         * to prevent have error like field1 =  ANd field2 = 'foo'
         * @param  string|null $value the value to check
         * @return string        the empty string if the value is null
         * otherwise the same value will be returned
         */
        protected function checkForNullValue($value){
            if(is_null($value)){
                return '';
            }
            return $value;
        }

        /**
         * Get the SQL WHERE clause using array column => value
         * @see DatabaseQueryBuilder::where
         *
         * @return string
         */
        protected function getWhereStrArray(array $where, $type = '', $andOr = 'AND', $escape = true) {
            $wheres = array();
            foreach ($where as $column => $data) {
                $data = $this->checkForNullValue($data);
                $wheres[] = $type . $column . ' = ' . ($this->connection->escape($data, $escape));
            }
            return implode(' ' . $andOr . ' ', $wheres);
        }

        /**
         * Get the default SQL WHERE clause using operator = or the operator argument
         * @see DatabaseQueryBuilder::where
         *
         * @return string
         */
        protected function getWhereStrForOperator($where, $op = null, $val = null, $type = '', $escape = true) {
            $w = '';
            if (!in_array((string) $op, $this->operatorList)) {
                $op = $this->checkForNullValue($op);
                $w = $type . $where . ' = ' . ($this->connection->escape($op, $escape));
            } else {
                $val = $this->checkForNullValue($val);
                $w = $type . $where . ' ' . $op . ' ' . ($this->connection->escape($val, $escape));
            }
            return $w;
        }

        /**
         * Set the $this->where property 
         * @param string $whereStr the WHERE clause string
         * @param  string  $andOr the separator type used 'AND', 'OR', etc.
         */
        protected function setWhereStr($whereStr, $andOr = 'AND') {
            if (empty($this->where)) {
                $this->where = $whereStr;
            } else {
                if (substr(trim($this->where), -1) == '(') {
                    $this->where = $this->where . $whereStr;
                } else {
                    $this->where = $this->where . ' ' . $andOr . ' ' . $whereStr;
                }
            }
        }

        /**
         * Set the SQL SELECT for function MIN, MAX, SUM, AVG, COUNT, AVG
         * @param  string $clause the clause type like MIN, MAX, etc.
         * @see  DatabaseQueryBuilder::min
         * @see  DatabaseQueryBuilder::max
         * @see  DatabaseQueryBuilder::sum
         * @see  DatabaseQueryBuilder::count
         * @see  DatabaseQueryBuilder::avg
         * @return object
         */
        protected function selectMinMaxSumCountAvg($clause, $field, $name = null) {
            $clause = strtoupper($clause);
            $func = $clause . '(' . $field . ')' . (!is_null($name) ? ' AS ' . $name : '');
            return $this->setSelectStr($func);
        }
}
