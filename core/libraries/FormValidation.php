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


 class FormValidation {

    protected $_success  = false;
    protected $_errorsMessages = array();
    // Array of rule sets, fieldName => PIPE seperated ruleString
    protected $_rules             = array();
    // Array of errors, niceName => Error Message
    protected $_errors             = array();
    // Array of post Key => Nice name labels
    protected $_labels          = array();
    protected $_allErrorsDelimiter   = array('<div class="error">', '</div>');
    protected $_eachErrorDelimiter   = array('<p class="error">', '</p>');
    protected $_forceFail            = false;
    protected $_errorPhraseOverrides = array();
    //super global objet
    protected $OBJ = null;

    /**
     * Sets all errors and rule sets empty, and sets success to false.
     *
     * @return void
     */
    public function __construct() {
        $this->OBJ = & get_instance();
        $this->_errorsMessages  = array(
                    'required'     => $this->OBJ->lang->get('fv_required'),
                    'min_length'   => $this->OBJ->lang->get('fv_min_length'),
                    'max_length'   => $this->OBJ->lang->get('fv_max_length'),
                    'exact_length' => $this->OBJ->lang->get('fv_exact_length'),
                    'matches'      => $this->OBJ->lang->get('fv_matches'),
                    'valid_email'  => $this->OBJ->lang->get('fv_valid_email'),
                    'not_equal'    => array(
                                            'post:key' => $this->OBJ->lang->get('fv_not_equal_post_key'),
                                            'string'   => $this->OBJ->lang->get('fv_not_equal_string')
                                        ),
                    'depends'      => $this->OBJ->lang->get('fv_depends'),
                    'is_unique'    => $this->OBJ->lang->get('fv_is_unique'),
                    'exists'       => $this->OBJ->lang->get('fv_exists'),
                    'regex'        => $this->OBJ->lang->get('fv_regex'),
                    'in_list'      => $this->OBJ->lang->get('fv_in_list'),
                    'numeric'      => $this->OBJ->lang->get('fv_numeric')
                );
        $this->_resetValidation();
        return;
    }

    protected function _resetValidation() {
        $this->_rules             = array();
        $this->_labels          = array();
        $this->_errorPhraseOverrides = array();
        $this->_errors             = array();

        $this->_success = false;
        $this->_forceFail   = false;
        return;
    }

    protected function _toCallCase($funcName, $prefix='_validate') {
        $funcName = strtolower($funcName);
        $finalFuncName = $prefix;
        foreach (explode('_', $funcName) as $funcNamePart) {
            $finalFuncName .= strtoupper($funcNamePart[0]) . substr($funcNamePart, 1);
        }

        return $finalFuncName;
    }

    /**
     * Returns the boolean of the forms success. It goes by the simple
     * "form has failed until proven otherwise".
     *
     * @return boolean Whether or not form has succeeded
     */
    public function formSuccess() {
        return $this->_success;
    }

    /**
     * Checks if the request method is POST
     *
     * @return boolean Whether or not the form has been submitted.
     */
    public function formSubmitted() {
        return $_SERVER["REQUEST_METHOD"] == 'POST';
    }

    /**
     * Runs _run once POST data has been submitted.
     *
     * @return void
     */
    public function run() {
        if ($this->formSubmitted()) {
            $this->_run();
        }
        return $this->formSuccess();
    }

    /**
     * Takes and trims each $_POST field, if it has any rules, we parse the rule string and run
     * each rule against the $_POST value. Sets formSuccess to true if there are no errors
     * afterwards.
     */
    protected function _run() {
        //first check for CSRF
        if( Config::get('csrf_enable', false)){
            if(!Security::validateCSRF()){
                show_error('Invalide Form data Cross Site Request Forgery do his job, form data corrupted.');
            }
        }
        /////////////////////////////////////////////
        $this->_forceFail = false;

        foreach ($_POST as $inputName => $inputVal) {
			if(is_array($_POST[$inputName])){
				$_POST[$inputName] = array_map('trim', $_POST[$inputName]);
			}
			else{
				$_POST[$inputName] = trim($_POST[$inputName]);
			}

            if (array_key_exists($inputName, $this->_rules)) {
                foreach ($this->_parseRuleString($this->_rules[$inputName]) as $eachRule) {
                    $this->_validateRule($inputName, $_POST[$inputName], $eachRule);
                }
            }
        }

        if (empty($this->_errors) && $this->_forceFail === false) {
            $this->_success = true;
        }
    }

    /**
     * Adds a rule to a $_POST field.
     *
     * @param string $inputField Name of the field to add a rule to
     * @param string $ruleSets PIPE seperated string of rules
     * @return formValidation Current instance of object.
     */
    public function setRule($inputField, $inputLabel, $ruleSets) {
        $this->_rules[$inputField] = $ruleSets;

        $this->_labels[$inputField] = $inputLabel;
        return $this;
    }

    /**
     * Takes an array of rules and uses setRule() to set them, accepts an array
     * of rule names rather than a pipe-delimited string as well.
     * @param array $ruleSets
     */
    public function setRules(array $ruleSets) {
        foreach ($ruleSets as $ruleSet) {
            $pipeDelimitedRules = null;

            if (is_array($ruleSet['rules'])) {
                $pipeDelimitedRules = implode('|', $ruleSet['rules']);
            } else {
                $pipeDelimitedRules = $ruleSet['rules'];
            }

            $this->setRule($ruleSet['name'], $ruleSet['label'], $pipeDelimitedRules);
        }

        return $this;
    }

    /**
     * This method creates the global errors delimiter, each argument occurs once, at the beginning, and
     * end of the errors block respectively.
     *
     * @param string $start Before block of errors gets displayed, HTML allowed.
     * @param string $end After the block of errors gets displayed, HTML allowed.
     * @return void
     */
    public function setErrorsDelimiter($start, $end) {
        $this->_allErrorsDelimiter[0] = $start;
        $this->_allErrorsDelimiter[1] = $end;
        return $this;
    }

    /**
     * This is the individual error delimiter, each argument occurs once before and after
     * each individual error listed.
     *
     * @param string $start Displayed before each error.
     * @param string $end Displayed after each error.
     * @return void
     */
    public function setErrorDelimiter($start, $end) {
        $this->_eachErrorDelimiter[0] = $start;
        $this->_eachErrorDelimiter[1] = $end;
        return $this;
    }

	public function getErrorDelimiter() {
        return $this->_eachErrorDelimiter;
    }


	public function getErrorsDelimiter() {
        return $this->_allErrorsDelimiter;
    }

    /**
     * This sets a custom error message that can override the default error phrase provided
     * by Form-Validation, it can be used in the format of setMessage('rule', 'error phrase')
     * which will globally change the error phrase of that rule, or in the format of:
     * setMessage('rule', 'fieldname', 'error phrase') - which will only change the error phrase for
     * that rule, applied on that field.
     *
     * @return boolean True on success, false on failure.
     */
    public function setMessage() {
        $numArgs = func_num_args();

        switch ($numArgs) {
            default:
                return false;
                break;

            // A global rule error message
            case 2:
                foreach ($this->post(null) as $key => $val) {
                    $this->_errorPhraseOverrides[$key][func_get_arg(0)] = func_get_arg(1);
                }
                break;

            // Field specific rule error message
            case 3:
                $this->_errorPhraseOverrides[func_get_arg(1)][func_get_arg(0)] = func_get_arg(2);
                break;
        }

        return true;
    }

    /**
     * Adds a custom error message in the errorSet array, that will
     * forcibly display it.
     *
     * @param string $errorMessage Error to display
     * @return formValidation Current instance of the object
     */
    public function setCustomError($inputName, $errorMessage) {
        $errorMessage = str_replace('%1', $this->_labels[$inputName], $errorMessage);
        $this->_errors[$inputName] = $errorMessage;
        return $this;
    }

    /**
     * Allows for an accesor to any/all post values, if a value of null is passed as the key, it
     * will recursively find all keys/values of the $_POST array. It also automatically trims
     * all values.
     *
     * @param string $key Key of $_POST to be found, pass null for all Key => Val pairs.
     * @param boolean $trim Defaults to true, trims all $_POST values.
     * @return string/array Array of post values if null is passed as key, string if only one key is desired.
     */
    public function post($key=null, $trim=true) {

        $returnValue = null;

        if (is_null($key)) {

            $returnValue = array();

            foreach ($_POST as $key => $val) {
                $returnValue[$key] = $this->post($key, $trim);
            }
        } else {
            $returnValue = (array_key_exists($key, $_POST)) ? (($trim) ? trim($_POST[$key]) : $_POST[$key]) : false;
        }

        return $returnValue;
    }

    /**
     * Gets all errors from errorSet and displays them, can be echoed out from the
     * function or just returned.
     *
     * @param boolean $echo Whether or not the values are to be returned or echoed
     * @return string Errors formatted for output
     */
    public function displayErrors($limit=null, $echo=true) {
        list($errorsStart, $errorsEnd) = $this->_allErrorsDelimiter;
        list($errorStart, $errorEnd) = $this->_eachErrorDelimiter;

        $errorOutput = $errorsStart;

		$i = 0;

        if (!empty($this->_errors)) {
            foreach ($this->_errors as $fieldName => $error) {
	    	if ($i === $limit) { break; }

                $errorOutput .= $errorStart;
                $errorOutput .= $error;
                $errorOutput .= $errorEnd;

                $i++;
            }
        }

        $errorOutput .= $errorsEnd;

        echo ($echo) ? $errorOutput : '';
        return (!$echo) ? $errorOutput : null;
    }

    /**
     * Returns raw array of errors in no format instead of displaying them
     * formatted.
     *
     * @return array
     */
    public function returnErrors() {
        return $this->_errors;
    }

    /**
     * Breaks up a PIPE seperated string of rules, and puts them into an array.
     *
     * @param string $ruleString String to be parsed.
     * @return array Array of each value in original string.
     */
    protected function _parseRuleString($ruleString) {
        $ruleSets = array();
        /*
        //////////////// hack for regex rule that can contain "|"
        */
        if(strpos($ruleString, 'regex') !== false){
            $regexRule = array();
            $rule = '#regex\[\/(.*)\/([a-zA-Z0-9]?)\]#';
            preg_match($rule, $ruleString, $regexRule);
            $ruleStringTemp = preg_replace($rule, '', $ruleString);
             if(isset($regexRule[0]) && !empty($regexRule[0])){
                 $ruleSets[] = $regexRule[0];
             }
             $ruleStringRegex = explode('|', $ruleStringTemp);
             if(is_array($ruleStringRegex)){
                foreach ($ruleStringRegex as $rule) {
                    $rule = trim($rule);
                    if($rule){
                        $ruleSets[] = $rule;
                    }
                }
             }
        }
        /***********************************/
        else{
            if (strpos($ruleString, '|') !== FALSE) {
                $ruleSets = explode('|', $ruleString);
            } else {
                $ruleSets[] = $ruleString;
            }
         }

        return $ruleSets;
    }

    /**
     * Returns whether or not a field obtains the rule "required".
     *
     * @param string $fieldName Field to check if required.
     * @return boolean Whether or not the field is required.
     */
    protected function _fieldIsRequired($fieldName) {
        $rules = $this->_parseRuleString($this->_rules[$fieldName]);

        if (in_array('required', $rules)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Takes a $_POST input name, it's value, and the rule it's being validated against (ex: max_length[16])
     * and adds an error to the errorSet if it fails validation of the rule.
     *
     * @param string $inputName Name of $_POST field
     * @param string $inputVal Value of the $_POST field
     * @param string $ruleName Rule to be validated against, including args (exact_length[5])
     * @return void
     */
    protected function _validateRule($inputName, $inputVal, $ruleName) {
        // Array to store [] args
        $ruleArgs = array();

        // Get the rule arguments, realRule is just the base rule name
        // Like min_length instead of min_length[3]
        $realRule = preg_match('/\[(.*)\]/', $ruleName, $ruleArgs);

        $ruleName = preg_replace('/\[(.*)\]/', '', $ruleName);
        
        if (method_exists($this, $this->_toCallCase($ruleName))) {
            $methodToCall = $this->_toCallCase($ruleName);
            @call_user_func(array($this, $methodToCall), $inputName, $ruleName, $ruleArgs);
        }

        return;
    }

    protected function _setError($inputName, $ruleName, $replacements=array()) {
        $rulePhraseKeyParts = explode(',', $ruleName);

        foreach ($rulePhraseKeyParts as $rulePhraseKeyPart) {
            if (array_key_exists($rulePhraseKeyPart, $this->_errorsMessages)) {
                $rulePhrase = $this->_errorsMessages[$rulePhraseKeyPart];
            } else {
                $rulePhrase = $rulePhrase[$rulePhraseKeyPart];
            }
        }

        // Any overrides?
        if (array_key_exists($inputName, $this->_errorPhraseOverrides) && array_key_exists($ruleName, $this->_errorPhraseOverrides[$inputName])) {
            $rulePhrase = $this->_errorPhraseOverrides[$inputName][$ruleName];
        }

        // Typecast to array in case it's a string
        $replacements = (array) $replacements;

        for ($i = 1, $replacementCount = count($replacements); $i <= $replacementCount; $i++) {
            $key = $i - 1;
            $rulePhrase = str_replace('%' . $i, $replacements[$key], $rulePhrase);
        }

        if (!array_key_exists($inputName, $this->_errors)) {
            $this->_errors[$inputName] = $rulePhrase;
        }
    }

    /**
     * Used to run a callback for the callback rule, as well as pass in a default
     * argument of the post value. For example the username field having a rule:
     * callback[userExists] will eval userExists($_POST[username]) - Note the use
     * of eval over call_user_func is in case the function is not user defined.
     *
     * @param type $inputArg
     * @param string $callbackFunc
     * @return anything
     */
    protected function _runCallback($inputArg, $callbackFunc) {
        return eval($callbackFunc . '("' . $inputArg . '");');
    }

    /**
     * Used for applying a rule only if the empty callback evaluates to true,
     * for example required[funcName] - This runs funcName without passing any
     * arguments.
     *
     * @param string $callbackFunc
     * @return anything
     */
    protected function _runEmptyCallback($callbackFunc) {
        return eval('return ' . $callbackFunc . '();');
    }

    /**
     * Gets a specific label of a specific field input name.
     *
     * @param string $inputName
     * @return string
     */
    protected function _getLabel($inputName) {
        return (array_key_exists($inputName, $this->_labels)) ? $this->_labels[$inputName] : $inputName;
    }

    protected function _validateHoneypot($inputName, $ruleName, array $ruleArgs) {
        if ($_POST[$inputName] != '') {
            $this->_forceFail = true;
        }
    }

    protected function _validateCallback($inputName, $ruleName, array $ruleArgs) {
        if (function_exists($ruleArgs[1]) && !empty($_POST[$inputName])) {
            $this->_runCallback($_POST[$inputName], $ruleArgs[1]);
        }
    }

    protected function _validateDepends($inputName, $ruleName, array $ruleArgs) {
        if (array_key_exists($ruleArgs[1], $this->_errors)) {
            $this->_setError($inputName, $ruleName, array($this->_getLabel($inputName), $this->_getLabel($ruleArgs[1])));
        }
    }

    protected function _validateNotEqual($inputName, $ruleName, array $ruleArgs) {
        $canNotEqual = explode(',', $ruleArgs[1]);

        foreach ($canNotEqual as $doNotEqual) {
            $inputVal = $this->post($inputName);

            if (preg_match('/post:(.*)/', $doNotEqual)) {
                if ($inputVal == $_POST[str_replace('post:', '', $doNotEqual)]) {
                    $this->_setError($inputName, $ruleName . ',post:key', array($this->_getLabel($inputName), $this->_getLabel(str_replace('post:', '', $doNotEqual))));
                    continue;
                }
            } else {
                if ($inputVal == $doNotEqual) {
                    $this->_setError($inputName, $ruleName . ',string', array($this->_getLabel($inputName), $doNotEqual));
                    continue;
                }
            }
        }
    }

    protected function _validateMatches($inputName, $ruleName, array $ruleArgs) {
        $inputVal = $this->post($inputName);

        if ($inputVal != $_POST[$ruleArgs[1]]) {
            $this->_setError($inputName, $ruleName, array($this->_getLabel($inputName), $this->_getLabel($ruleArgs[1])));
        }
    }

    protected function _validateValidEmail($inputName, $ruleName, array $ruleArgs) {
        $inputVal = $this->post($inputName);

        if (!preg_match("/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i", $inputVal)) {
            if (!$this->_fieldIsRequired($inputName) && empty($_POST[$inputName])) {
                return;
            }

            $this->_setError($inputName, $ruleName, $this->_getLabel($inputName));
        }
    }

    protected function _validateExactLength($inputName, $ruleName, array $ruleArgs) {
        $inputVal = $this->post($inputName);
        if (strlen($inputVal) != $ruleArgs[1]) { // $ruleArgs[0] is [length] $rulesArgs[1] is just length
            if (!$this->_fieldIsRequired($inputName) && empty($_POST[$inputName])) {
                return;
            }

            $this->_setError($inputName, $ruleName, array($this->_getLabel($inputName), $this->_getLabel($ruleArgs[1])));
        }
    }

    protected function _validateMaxLength($inputName, $ruleName, array $ruleArgs) {
        $inputVal = $this->post($inputName);
        if (strlen($inputVal) > $ruleArgs[1]) { // $ruleArgs[0] is [length] $rulesArgs[1] is just length
            if (!$this->_fieldIsRequired($inputName) && empty($_POST[$inputName])) {
                return;
            }

            $this->_setError($inputName, $ruleName, array($this->_getLabel($inputName), $this->_getLabel($ruleArgs[1])));
        }
    }

    protected function _validateMinLength($inputName, $ruleName, array $ruleArgs) {
        $inputVal = $this->post($inputName);

        if (strlen($inputVal) < $ruleArgs[1]) { // $ruleArgs[0] is [length] $rulesArgs[1] is just length
            if (!$this->_fieldIsRequired($inputName) && empty($_POST[$inputName])) {
                return;
            }

            $this->_setError($inputName, $ruleName, array($this->_getLabel($inputName), $this->_getLabel($ruleArgs[1])));
        }
    }
	
	 protected function _validateNumeric($inputName, $ruleName, array $ruleArgs) {
        $inputVal = $this->post($inputName);

        if (!is_numeric($inputVal)) { // $ruleArgs[0] is [length] $rulesArgs[1] is just length
            if (!$this->_fieldIsRequired($inputName) && empty($_POST[$inputName])) {
                return;
            }

            $this->_setError($inputName, $ruleName, array($this->_getLabel($inputName)));
        }
    }

	protected function _validateIsUnique($inputName, $ruleName, array $ruleArgs) {
        $inputVal = $this->post($inputName);
		$db = null;
		$obj = & get_instance();
		if(!isset($obj->database)){
			return;
		}
		$db = $obj->database;
		
		list($table, $column) = explode('.', $ruleArgs[1]);
		$db->from($table)
			->where($column, $inputVal)
			->get();
		$nb = $db->numRows();
        if ($nb != 0) {
            if (!$this->_fieldIsRequired($inputName) && empty($_POST[$inputName])) {
                return;
            }

            $this->_setError($inputName, $ruleName, array($this->_getLabel($inputName)));
        }
    }
	

    protected function _validateInList($inputName, $ruleName, array $ruleArgs) {
        $inputVal = $this->post($inputName);
		$list = explode(',', $ruleArgs[1]);
        $list = array_map('trim', $list);
        if (!in_array($inputVal, $list)) {
            if (!$this->_fieldIsRequired($inputName) && empty($_POST[$inputName])) {
                return;
            }
            $this->_setError($inputName, $ruleName, array($this->_getLabel($inputName), $this->_getLabel($ruleArgs[1])));
        }
    }

    protected function _validateRegex($inputName, $ruleName, array $ruleArgs) {
        $inputVal = $this->post($inputName);
		$regex = $ruleArgs[1];
        if (!preg_match($regex, $inputVal)) {
            if (!$this->_fieldIsRequired($inputName) && empty($_POST[$inputName])) {
                return;
            }

            $this->_setError($inputName, $ruleName, array($this->_getLabel($inputName)));
        }
    }

    protected function _validateExists($inputName, $ruleName, array $ruleArgs) {
        $inputVal = $this->post($inputName);
		$db = null;
		$obj = & get_instance();
		if(!isset($obj->database)){
			return;
		}
		$db = $obj->database;
		list($table, $column) = explode('.', $ruleArgs[1]);
		$db->from($table)
			->where($column, $inputVal)
			->get();
		$nb = $db->numRows();
        if ($nb == 0) {
            if (!$this->_fieldIsRequired($inputName) && empty($_POST[$inputName])) {
                return;
            }

            $this->_setError($inputName, $ruleName, array($this->_getLabel($inputName)));
        }
    }

    protected function _validateRequired($inputName, $ruleName, array $ruleArgs) {
        $inputVal = $this->post($inputName);

        if (array_key_exists(1, $ruleArgs) && function_exists($ruleArgs[1])) {
            $callbackReturn = $this->_runEmptyCallback($ruleArgs[1]);

            if ($inputVal == '' && $callbackReturn == true) {
                $this->_setError($inputName, $ruleName, $this->_getLabel($inputName));
            }
        } elseif ($inputVal == '') {
                $this->_setError($inputName, $ruleName, $this->_getLabel($inputName));
        }
    }
}
