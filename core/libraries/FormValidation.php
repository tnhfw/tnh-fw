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
    
    class FormValidation extends BaseClass {

        /**
         * The list of validation rules
         *
         * Format:
         * [field1] => array(rule1, rule2)
         * [field2] => array(rule3, rule3)
         *
         * @var array
         */
        private $rules = array();

        /**
         * The list of field labels
         *
         * Format:
         * [field1] => 'label 1'
         * [field2] => 'label 2'
         *
         * @var array
         */
        private $labels = array();

        /**
         * The list of errors messages
         * Format:
         * [field1] => 'error message 1';
         * [field2] => 'error message 2';
         * 
         * @var array
         */
        private $errors = array();

        /**
         * The validation status
         * @var boolean
         */
        private $valid = false;

        /**
         * Whether we force validation to be an error
         * @var boolean
         */
        private $forceError = false;
        
        /**
         * The list of rules errors messages
         *
         * Format:
         * [rule1] => 'message 1';
         * [rule2] => 'message 2';
         * Message can contain placeholder: {field}, {label}, {value}, {paramValue}, {field2}'
         *
         * @var array
         */
        private $messages = array();
        
        /**
         * The list of the custom errors messages overrides by the original
         *
         * Format:
         * [field1][rule1] = message 1
         * [field2][rule2] = message 2
         *
         * @var array
         */
        private $customErrors = array();
        
        /**
         * The data to be validated, the default is to use $_POST
         * @var array
         */
        private $data = array();

        /**
         * The database instance to use to validate rules:
         * - exists
         * - is_unique
         * - is_unique_update
         * @var object
         */
        private $database = null;
        
        /**
         * Construct new Form Validation instance
         * Reset the field to the default value
         * Set the rules validations messages
         * And if data of $_POST exists set it as the default
         */
        public function __construct() {
            parent::__construct();

            //Reset the validation instance
            $this->reset();

            //Set default messages for each validation rule
            $this->setValidationMessages();

            //Set default data to validation from $_POST
            if (is_array(get_instance()->request->post(null))) {
                $this->setData(get_instance()->request->post(null));
            }
        }

         /**
         * Reset the form validation instance
         *
         * @return object the current instance
         */
        public function reset() {
            $this->rules        = array();
            $this->labels       = array();
            $this->errors       = array();
            $this->customErrors = array();
            $this->valid        = false;
            $this->forceError   = false;
            $this->data         = array();
            return $this;
        }
        
        /**
         * Set the validation data
         * @param array $data the data to be validated
         *
         * @return object the current instance.
         */
        public function setData(array $data) {
            $this->logger->debug('Setting the form validation data, the values are: ' . stringfy_vars($data));
            $this->data = $data;
            return $this;
        }

         /**
         * Return the validation data
         * 
         * @return array the validation data
         */
        public function getData() {
            return $this->data;
        }

        /**
         * Add new validation rule.
         *
         * @param string $field name of the field or the data key to add a rule to
         * @param string $label the value of the field label
         * @param string|array $rule pipe seperated string or array of validation rules
         *
         * @return object the current instance.
         */
        public function setRule($field, $label, $rule) {
            $rules = array();
            //if the rule is array
            if (is_array($rule)) {
                $rules = $rule;
            } else {
                //the rule is not an array explode pipe values
                $rules = explode('|', $rule);
            }
            $this->rules[$field] = $rules;
            $this->labels[$field] = $label;
            return $this;
        }

        /**
         * Takes an array of rules and uses $this->setRule() to set them.
         * @param array $rules the array of rule
         *
         * @return object the current instance.
         */
        public function setRules(array $rules) {
            foreach ($rules as $rule) {
                call_user_func_array(array($this, 'setRule'), array($rule['name'], $rule['label'], $rule['rules']));
            }
            return $this;
        }

        /**
         * Return the list of the validations rules
         * @return array
         */
        public function getRules() {
            return $this->rules;
        }

         /**
         * Return the list of the validations rules for the given field
         * 
         * @return array
         */
        public function getFieldRules($field) {
            $rules = array();
            if (array_key_exists($field, $this->rules)) {
                $rules = $this->rules[$field];
            }
            return $rules;
        }

        /**
         * Return the value for the given field if exists otherwise null
         * will be returned
         * 
         * @return string|null
         */
        public function getFieldValue($field) {
             $value = null;
            if (array_key_exists($field, $this->data)) {
                $value = $this->data[$field];
            }
            return $value;
        }

        /**
         * Return the label for the given field if exists otherwise null
         * will be returned
         * 
         * @return string|null
         */
        public function getFieldLabel($field) {
            $label = null;
            if (array_key_exists($field, $this->labels)) {
                $label = $this->labels[$field];
            }
            return $label;
        }
            
        
       /**
        * Return the list of validation errors
        * 
        * @return array the errors list. 
        * Format:
        *  [field1] => 'error message 1', 
        *  [field2] => 'error message 2' 
        */
       public function getErrors() {
            return $this->errors;
        }

        /**
         * Process the data validation of each field, if it has any rules, run
         * each rule against the data value. 
         * and finally set $this->valid to true if there are no errors otherwise set to false
         * in case of error
         */
        public function validate() {
            //if the data is empty just return false
            if (empty($this->data)) {
                return false;
            }
            //Apply some filter/clean in the data to validate
            $this->filterValidationData();

            //Check the CSRF status
            $this->checkCsrf();

            //Now loop in each field rule and validate it
            foreach ($this->rules as $field => $rules) {
                $this->validateField($field, $rules);
            }
            $this->valid = empty($this->errors) && $this->forceError === false;
            return $this->valid;
        }

        /**
         * Return the validation status
         * @return boolean
         */
        public function isValid() {
            return $this->valid;
        }

        /**
         * Set a custom error message that can override the default error phrase provided.
         *  
         * If the parameter $field is null so means will set the error message for
         * all fields for the given rule, otherwise only error message for this field and rule will be set
         * the others fields use the default one for this rule.
         *
         * @param string $rule the name of the rule to set error message for.
         * @param string $message the error message it can contains the placeholder
         * {field}, {label}, {value}, {paramValue}, {field2}.
         * @param string|null $field if not null will set the error message only for this field and rule
         *
         * @return object the current instance.
         */
        public function setCustomErrorMessage($rule, $message, $field = null) {
            //1st if field is null all fields for this rule will be set to the same message
            //2nd if field is not null set only for this field
            if ($field !== null) {
                $this->customErrors[$field][$rule] = $message;
            } else {
                foreach (array_keys($this->rules) as $field) {
                    $this->customErrors[$field][$rule] = $message;
                }
            }
            return $this;
        }

        /**
         * Get the database instance
         * @return object the database instance
         */
        public function getDatabase() {
            return $this->database;
        }

        /**
         * Set the database instance
         * @param object $database the database instance
         *
         * @return object the current instance
         */
        public function setDatabase(Database $database) {
            $this->database = $database;
            return $this;
        }

         /**
         * Set the database instance using get_instance() if is null
         *
         * @return object the current instance
         */
        protected function setDatabaseFromSuperInstanceIfNotSet() {
            if (!is_object($this->database)) {
                $this->database = get_instance()->database;
            }
            return $this;
        }

        /**
         * Set the validation error for the given field
         * @param string $field      the error field
         * @param string $rule       the name of the rule raise this error
         * @param mixed $paramValue the value of rule parameter
         * Example: min_length[17], the $paramValue will be "17"
         * @param string|null $field2     the second field used in some validation rule like "matches", "not_equal"
         *
         * @return object the current instance
         */
        protected function setFieldError($field, $rule, $paramValue = null, $field2 = null) {
            //do we have error before?
            if (!array_key_exists($field, $this->errors)) {
                $this->errors[$field] = $this->getFieldErrorMessage($field, $rule, $paramValue, $field2);
            }
            return $this;
        }

        /**
         * Set the validation error for the given field by checking if the field is not required
         * or contains the value
         * @param string $field      the error field
         * @param mixed $value      the value of field
         * @param string $rule       the name of the rule raise this error
         * @param mixed $paramValue the value of rule parameter
         * Example: min_length[17], the $paramValue will be "17"
         * @param string|null $field2     the second field used in some validation rule like "matches", "not_equal"
         *
         */
         protected function setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue = null, $field2 = null) {
            //if the field is not required and his value is not set don't set error
            //but in case the field have value validate it
            if (!$this->fieldIsRequired($field) && strlen($value) <= 0) {
                return;
            }
            $this->setFieldError($field, $rule, $paramValue, $field2);
        }

        /**
         * Check whether the given field is required or not
         * @param  string $field the name of the field
         * @return boolean
         */
        protected function fieldIsRequired($field) {
            $rules = $this->getFieldRules($field);
            return in_array('required', $rules);
        }
        
        /**
         * Return the name of the method to use to validate the given rule
         * each method has a prefix of "checkRule"
         * @param  string $rule the name of the rule
         * @return string       the name of the validation method
         */
        protected function getRuleValidationMethod($rule) {
             $parts = explode('_', $rule);
             $parts = array_map('ucfirst', $parts);
             return 'checkRule' . implode('', $parts);
        }
        
        /**
         * This method is used to apply some filter rule on the validation data
         * like remove space, do some clean, etc.
         * @return object the current instance
         */
        protected function filterValidationData() {
            foreach ($this->data as $key => $value ) {
                if (is_string($value)) {
                   $this->data[$key] = trim($value);
                } else if(is_array($value)) {
                    $this->data[$key] = array_map('trim', $value);
                }
            }
            return $this;
        }

        /**
         * Validate the CSRF if the current request method is POST
         * and the check of CSRF is enabled in the configuration
         */
        protected function checkCsrf() {
            if (get_instance()->request->method() == 'POST') {
                $this->logger->debug('Check if CSRF is enabled in configuration');
                //first check for CSRF
                if (get_config('csrf_enable', false) && !get_instance()->security->validateCSRF()) {
                    $this->forceError = true;
                    show_error('Invalid data, Cross Site Request Forgery do his job, the data to validate is corrupted.');
                } else {
                    $this->logger->info('CSRF is not enabled in configuration or not set manully, no need to check it');
                }
            }
        }
              
        /**
         * Return the validation error message content for the given field
         * after replace all placeholders with their values
         * @param string $field      the error field
         * @param string $rule       the name of the rule raise this error
         * @param mixed $paramValue the value of rule parameter
         * Example: min_length[17], the $paramValue will be "17"
         * @param string|null $field2     the second field used in some validation rule like "matches", "not_equal"
         *
         * @return string the error message
         */
        protected function getFieldErrorMessage($field, $rule, $paramValue = null, $field2 = null) {
            $template = array(
                            '{field}'      => $field,
                            '{value}'      => $this->getFieldValue($field),
                            '{label}'      => $this->getFieldLabel($field),
                            '{paramValue}' => $paramValue
                            );
            if ($field2 !== null) {
                $template['field2}']  = $field2;
                $template['{value2}'] = $this->getFieldValue($field2);
                $template['{label2}'] = $this->getFieldLabel($field2);
             }     
            $message = $this->messages[$rule];
            //Check for custom message
            if (isset($this->customErrors[$field][$rule])) {
                $message = $this->customErrors[$field][$rule];
            }
            return strtr($message, $template);
        }

        /**
         * Perform validation for the given field
         * @param  string $field the field name
         * @param  array $rules the list of rule to validate
         */
        protected function validateField($field, array $rules) {
            foreach ($rules as $rule) {
                $match = array();
                $paramValue = null;
                //Is the rule with parameter ??
                preg_match('/\[(.*)\]/', $rule, $match);
                if (isset($match[1])) {
                    $paramValue = $match[1];
                }
                //Now get the real rule name for example 
                //min_length[34]
                //the name will be "min_length" and paramValue will be "14"
                $realRuleName = preg_replace('/\[(.*)\]/', '', $rule);

                //Get the name of the method to validate this rule
                $method = $this->getRuleValidationMethod($realRuleName);
                if (method_exists($this, $method)) {
                       call_user_func_array(array($this, $method), array($field, $realRuleName, $paramValue));
                } else {
                    $this->forceError = true;
                    show_error('Invalid validaton rule "' . $realRuleName . '"');
                }
            }
        }
        
        /**
         * Set the rules validation messages using translation messages
         */
        protected function setValidationMessages() {
            //Load form validation language message
            get_instance()->loader->lang('form_validation');
            
            $this->messages['required']         = get_instance()->lang->get('fv_required');
            $this->messages['min_length']       = get_instance()->lang->get('fv_min_length');
            $this->messages['max_length']       = get_instance()->lang->get('fv_max_length');
            $this->messages['exact_length']     = get_instance()->lang->get('fv_exact_length');
            $this->messages['matches']          = get_instance()->lang->get('fv_matches');
            $this->messages['not_equal']        = get_instance()->lang->get('fv_not_equal');
            $this->messages['min']              = get_instance()->lang->get('fv_min');
            $this->messages['max']              = get_instance()->lang->get('fv_max');
            $this->messages['between']          = get_instance()->lang->get('fv_between');
            $this->messages['in_list']          = get_instance()->lang->get('fv_in_list');
            $this->messages['numeric']          = get_instance()->lang->get('fv_numeric');
            $this->messages['integer']          = get_instance()->lang->get('fv_integer');
            $this->messages['integer_natural']  = get_instance()->lang->get('fv_integer_natural');
            $this->messages['alpha']            = get_instance()->lang->get('fv_alpha');
            $this->messages['alpha_dash']       = get_instance()->lang->get('fv_alpha_dash');
            $this->messages['alnum']            = get_instance()->lang->get('fv_alnum');
            $this->messages['alnum_dash']       = get_instance()->lang->get('fv_alnum_dash');
            $this->messages['email']            = get_instance()->lang->get('fv_email');
            $this->messages['date']             = get_instance()->lang->get('fv_date');
            $this->messages['date_before']      = get_instance()->lang->get('fv_date_before');
            $this->messages['date_after']       = get_instance()->lang->get('fv_date_after');
            $this->messages['url']              = get_instance()->lang->get('fv_url');
            $this->messages['ip']               = get_instance()->lang->get('fv_ip');
            $this->messages['ipv4']             = get_instance()->lang->get('fv_ipv4');
            $this->messages['ipv6']             = get_instance()->lang->get('fv_ipv6');
            $this->messages['is_unique']        = get_instance()->lang->get('fv_is_unique');
            $this->messages['is_unique_update'] = get_instance()->lang->get('fv_is_unique_update');
            $this->messages['exists']           = get_instance()->lang->get('fv_exists');
            $this->messages['regex']            = get_instance()->lang->get('fv_regex');
            $this->messages['callback']         = get_instance()->lang->get('fv_callback');
        }
        

        /* ---------------------------------------------------------------------------------- */
        ///////////////////////////////////////////////////////////////////////////////////////
        /**************************** RULES VALIDATION METHODS ******************************/
        /////////////////////////////////////////////////////////////////////////////////////
        
        /**
         * Validation of rule "default_value[param]"
         *
         * NOTE: This one it's not a validation rule it just check if the value for the
         * given field is null, empty and set it by the parameter value.
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("default_value")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleDefaultValue($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);
            if (strlen($value) <= 0) {
                $this->data[$field] = $paramValue;
            }
        }
            
        /**
         * Validation of rule "required"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("required")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleRequired($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);
            if ($value == '') {
                $this->setFieldError($field, $rule, $paramValue);
            }
        }
        
        /**
         * Validation of rule "min_length[param]"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("min_length")
         * @param  string|null  $paramValue  the rule parameter
         */ 
        protected function checkRuleMinLength($field, $rule, $paramValue) {
           $value = $this->getFieldValue($field);    
            if (strlen($value) < $paramValue) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "max_length[param]"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("max_length")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleMaxLength($field, $rule, $paramValue) {
           $value = $this->getFieldValue($field);    
            if (strlen($value) > $paramValue) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "exact_length[param]"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("exact_length")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleExactLength($field, $rule, $paramValue) {
           $value = $this->getFieldValue($field);    
            if (strlen($value) != $paramValue) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "matches[param]"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("matches")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleMatches($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if ($value != $this->getFieldValue($paramValue)) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue, $field2 = $paramValue);
            }
        }

        /**
         * Validation of rule "not_equal[param]"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("not_equal")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleNotEqual($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if ($value == $this->getFieldValue($paramValue)) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue, $field2 = $paramValue);
            }
        }

        /**
         * Validation of rule "min[param]"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("min")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleMin($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if ($value < $paramValue) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "max[param]"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("max")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleMax($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if ($value > $paramValue) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "between[param]" param format is x,y
         * Example: between[1,100]
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("between")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleBetween($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            $betweens = explode(',', $paramValue, 2);
            $betweens = array_map('trim', $betweens);
            if (($value < $betweens[0]) || ($value > $betweens[1])) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "in_list[param]" param format is a,b,c,d
         * Example: in_list[1,3,56,100]
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("in_list")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleInList($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            $list = explode(',', $paramValue);
            $list = array_map('trim', $list);
            $paramValue = implode(',', $list);
            if (!in_array($value, $list)) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "numeric"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("numeric")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleNumeric($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (!is_numeric($value)) {
               $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "integer"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("integer")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleInteger($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (filter_var($value, FILTER_VALIDATE_INT) === false) {
               $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "integer_natural"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("integer_natural")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleIntegerNatural($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (filter_var($value, FILTER_VALIDATE_INT) === false || $value < 0) {
               $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "alpha"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("alpha")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleAlpha($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (!preg_match('/^[\pL\pM\s]+$/u', $value)) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "alpha_dash"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("alpha_dash")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleAlphaDash($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);
            if (!preg_match('/^[\pL\pM_-]+$/u', $value)) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "alnum"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("alnum")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleAlnum($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);
            if (!preg_match('/^[\pL\pM\pN\s]+$/u', $value)) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "alnum_dash"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("alnum_dash")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleAlnumDash($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);
            if (!preg_match('/^[\pL\pM\pN_-]+$/u', $value)) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "email"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("email")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleEmail($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "date[param]" param can be a valid 
         * value supported by function date()
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("date")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleDate($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);
            $format = $paramValue;
            $dateValue = date_create_from_format($format, $value);    
            if ($dateValue === false || $dateValue->format($format) !== $value) {
               $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "date_before[param]" param can be a valid 
         * value supported by function strtotime()
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("date_before")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleDateBefore($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (strtotime($value) >= strtotime($paramValue)) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "date_after[param]" param can be a valid 
         * value supported by function strtotime()
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("date_after")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleDateAfter($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (strtotime($value) <= strtotime($paramValue)) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "url"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("url")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleUrl($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (filter_var($value, FILTER_VALIDATE_URL) === false) {
               $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "ip", the correct value can be ipv4, ipv6, for specific rule
         * use the rule below like ipv4 or ipv6.
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("ip")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleIp($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (filter_var($value, FILTER_VALIDATE_IP) === false) {
               $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "ipv4"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("ipv4")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleIpv4($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "ipv6"
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("ipv6")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleIpv6($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "is_unique[param]" param value format is
         * [tablename.fieldname]
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("is_unique")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleIsUnique($field, $rule, $paramValue) {
            $this->setDatabaseFromSuperInstanceIfNotSet();
            $value = $this->getFieldValue($field);    
            list($table, $column) = explode('.', $paramValue);
            $this->database->getQueryBuilder()->from($table)
                                              ->where($column, $value);
            $this->database->get();
            if ($this->database->numRows() > 0) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "is_unique_update[param]" param value format is
         * [tablename.fieldname,keyfield=value]
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("is_unique_update")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleIsUniqueUpdate($field, $rule, $paramValue) {
            $this->setDatabaseFromSuperInstanceIfNotSet();
            $value = $this->getFieldValue($field);  
            $data = explode(',', $paramValue, 2);
            list($table, $column) = explode('.', $data[0]);
            list($columnKey, $valueKey) = explode('=', $data[1]);
            $this->database->getQueryBuilder()->from($table)
                                              ->where($column, $value)
                                              ->where($columnKey, '!=', trim($valueKey));
            $this->database->get();
            if ($this->database->numRows() > 0) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "exists[param]" param value format is
         * [tablename.fieldname]
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("exists")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleExists($field, $rule, $paramValue) {
            $this->setDatabaseFromSuperInstanceIfNotSet();
            $value = $this->getFieldValue($field);    
            list($table, $column) = explode('.', $paramValue);
            $this->database->getQueryBuilder()->from($table)
                                              ->where($column, $value);
            $this->database->get();
            if ($this->database->numRows() <= 0) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "regex[param]" param can be any value supported by
         * function preg_match()
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("regex")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleRegex($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (!preg_match($paramValue, $value)) {
                $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
            }
        }

        /**
         * Validation of rule "callback[param]" param can be any value supported by
         * function is_callable() and this callable must accept one argument for
         * the field value and must return false or true for the validation status.
         * Example:
         *
         * function check_username_exists($value) {
         *   //some check
         *   return {true|false}
         * }
         * 
         * @param  string $field the name of the field or data key name used
         * @param  string $rule  the rule name for this validation ("required")
         * @param  string|null  $paramValue  the rule parameter
         */
        protected function checkRuleCallback($field, $rule, $paramValue) {
            $value = $this->getFieldValue($field);    
            if (is_callable($paramValue)) {
                if (call_user_func_array($paramValue, array($value)) === false) {
                    $this->setFieldErrorWithRequiredCheck($field, $value, $rule, $paramValue);
                }
            } else{
                $this->forceError = true;
                show_error('The callback validation function/method "' . $paramValue . '" does not exist');
            }
        }

    }
