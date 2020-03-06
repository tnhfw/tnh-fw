<?php 

    /**
    * Function used in callback validation
    */
    function callback_validation($value){
         return strlen($value) >= 3;
    }
    
    
    class FormValidationTest extends TnhTestCase
    {   
        
        public function testSetGetDatabase()
        {
            $db = $this->getMockBuilder('Database')->getMock();
              
            $fv = new FormValidation();
            $this->assertNull($fv->getDatabase());
            $fv->setDatabase($db);
            $this->assertInstanceOf('Database', $fv->getDatabase());
        }
        
        public function testValidationData()
        {
            $fv = new FormValidation();
            $this->assertEmpty($fv->getData());
            $fv->setData(array('name' => 'mike'));
            $this->assertNotEmpty($fv->getData());
            $this->assertArrayHasKey('name', $fv->getData());
        }

        public function testIsSuccess()
        {
            $fv = new FormValidation();
            $this->assertFalse($fv->isSuccess());
        }
        
        public function testDoValidation()
        {
            $fv = new FormValidation();
            $this->assertFalse($fv->canDoValidation());
            
            $fv->setData(array('name' => 'mike'));
            $this->assertTrue($fv->canDoValidation());
        }

        public function testSettingErrorDelimiter()
        {
            $fv = new FormValidation();
            $fv->setErrorDelimiter('<a>', '</b>');
            $this->assertContains('<a>', $fv->getErrorDelimiter());
            $this->assertContains('</b>', $fv->getErrorDelimiter());
        }
        
        public function testSettingErrorsDelimiter()
        {
            $fv = new FormValidation();
            $fv->setErrorsDelimiter('<foo>', '</bar>');
            $this->assertContains('<foo>', $fv->getErrorsDelimiter());
            $this->assertContains('</bar>', $fv->getErrorsDelimiter());
        }
        
        public function testSettingErrorMessageOverride()
        {
            
            //field specific message for the rule
            $fv = new FormValidation();
            $fv->setData(array('foo' => ''));
            $fv->setRule('foo', 'bar', 'required');
            $fv->setMessage('required', 'foo', 'foo required message error');
            
            $this->assertFalse($fv->run());
            $this->assertContains('foo required message error', $fv->returnErrors());
            
            //global message for the rule
            $fv = new FormValidation();
            $fv->setData(array('foo' => '', 'bar' => null));
            $fv->setRule('foo', 'bar', 'required');
            $fv->setRule('bar', 'foo', 'required');
            $fv->setMessage('required', 'global required message error');

            $this->assertFalse($fv->run());
            $this->assertContains('global required message error', $fv->returnErrors());
            
            //invalid setMessage() parameters
            $fv = new FormValidation();
            $fv->setData(array('foo' => '', 'bar' => null));
            $fv->setRule('foo', 'bar', 'required');
            $fv->setRule('bar', 'foo', 'required');
            $fv->setMessage();

            $this->assertFalse($fv->run());
            $this->assertNotContains('global required message error', $fv->returnErrors());
        }
        
        public function testSettingCustomErrorMessage()
        {
            
            $fv = new FormValidation();
            $fv->setData(array('foo' => ''));
            $fv->setRule('foo', 'bar', 'required');
            $fv->setCustomError('foo', 'custom foo message error');
            
            $this->assertFalse($fv->run());
            $this->assertContains('custom foo message error', $fv->returnErrors());
            
            //with label in the message
            $fv = new FormValidation();
            $fv->setData(array('foo' => ''));
            $fv->setRule('foo', 'bar', 'required');
            $fv->setCustomError('foo', 'custom "%1" message error');
            
            $this->assertFalse($fv->run());
            $this->assertContains('custom "bar" message error', $fv->returnErrors());	
        }
        
        public function testReturnErrorsArray()
        {
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => ''));
            $this->assertFalse($fv->run());
            $this->assertNotEmpty($fv->returnErrors());
            $this->assertArrayHasKey('name', $fv->returnErrors());
        }
        
        
        public function testValidateCSRF()
        {
             //generate CSRF
            $this->config->set('csrf_enable', true);
            $this->config->set('csrf_key', 'kcsrf');
            $this->config->set('csrf_expire', 100);
            
            $csrfValue = uniqid();
            $_SESSION['kcsrf'] =  $csrfValue;
            $_SESSION['csrf_expire'] = time() + 600;
            
            $request = $this->getMockBuilder('Request')->getMock();
            $request->expects($this->any())
                    ->method('method')
                    ->will($this->returnValue('POST'));
                    
            $request->expects($this->any())
                    ->method('query')
                    ->with('kcsrf')
                    ->will($this->returnValue($csrfValue));
                    
            $obj = & get_instance();
            $obj->request = $request;
            
           
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => 'foo'));
            $this->assertFalse($fv->run());
            
             //disable CSRF
            $this->config->set('csrf_enable', false);
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => 'foo'));
            $this->assertTrue($fv->run());
        }
        
        
        public function testSetRules()
        {
            $fv = new FormValidation();
            $rules = array(
                            array(
                                'name' => 'foo',
                                'label' => 'foo label',
                                'rules' => 'required'
                              ),
                              array(
                                'name' => 'bar',
                                'label' => 'bar label',
                                'rules' => array('required', 'min_length[1]')
                              )
            );
            $fv->setRules($rules);
            $fv->setData(array('foo' => 'foo value', 'bar' => 'bar value'));
            $this->assertTrue($fv->run());
        }
        
        public function testDisplayErrors()
        {
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'required');
            $fv->setData(array('foo' => '', 'bar' => null));
            $this->assertFalse($fv->run());
            
            //Default behavor will display errors
            $fv->displayErrors();
            
            //Return the errors string
            $this->assertNotEmpty($fv->displayErrors(false));
        }
        
        //////Each rule tests //////////////////////
        
        public function testRuleRequired()
        {
            //empty string
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => ''));
            $this->assertFalse($fv->run());
            
            //null value
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => null));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => 'tony'));
            $this->assertTrue($fv->run());
        }
        
        public function testRuleHoneypot()
        {
            
            //If field contains value the validation will failed
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'honeypot');
            $fv->setData(array('name' => 'foo'));
            $this->assertFalse($fv->run());
        }
        
        public function testRuleCallback()
        {
            
            //note the function check just the strlen of the value 
            //if less than 3 return false else return true
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'callback[callback_validation]');
            $fv->setData(array('name' => 'foo'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'callback[callback_validation]');
            $fv->setData(array('name' => 'fo'));
            $this->assertFalse($fv->run());
        }
        
        public function testRuleDepends()
        {
            //depends validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'depends[foo]');
            $fv->setData(array('foo' => '', 'bar' => null));
            $this->assertFalse($fv->run());
            
            //depends validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'depends[foo]');
            $fv->setData(array('foo' => 'baz', 'bar' => null));
            $this->assertTrue($fv->run());
        }
        
        public function testRuleNotEqual()
        {
            //fields value are equal, validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'not_equal[foovalue]');
            $fv->setData(array('foo' => 'foo', 'bar' => 'foovalue'));
            $this->assertFalse($fv->run());
            
            //fields values are not equal, validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'not_equal[barvalue]');
            $fv->setData(array('foo' => 'baz', 'bar' => 'foovalue'));
            $this->assertTrue($fv->run());
            
             //fields value are equal using post:* (post:field_name), validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'not_equal[post:foo]');
            $fv->setData(array('foo' => 'foovalue', 'bar' => 'foovalue'));
            $this->assertFalse($fv->run());
            
            //fields value are equal using post:* (post:field_name), validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'not_equal[post:foo]');
            $fv->setData(array('foo' => 'foo', 'bar' => 'bar'));
            $this->assertTrue($fv->run());
        }
        
        public function testRuleMatches()
        {
            //matches validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'matches[foo]');
            $fv->setData(array('foo' => 'foo', 'bar' => 'oof'));
            $this->assertFalse($fv->run());
            
            //matches validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'matches[foo]');
            $fv->setData(array('foo' => 'baz', 'bar' => 'baz'));
            $this->assertTrue($fv->run());
        }
        
        public function testRuleValidEmail()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'required|valid_email');
            $fv->setData(array('fooemail' => ''));
            //the field is required
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'valid_email');
            $fv->setData(array('fooemail' => 'e'));
            $this->assertFalse($fv->run());
            
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'valid_email');
            $fv->setData(array('fooemail' => 'e@'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'valid_email');
            $fv->setData(array('fooemail' => 'e@.'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'valid_email');
            $fv->setData(array('fooemail' => 'e@.com'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'valid_email');
            $fv->setData(array('fooemail' => '.@e.v'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'valid_email');
            $fv->setData(array('fooemail' => 'gghhghg@gm@il.com'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'valid_email');
            $fv->setData(array('fooemail' => 'e@f.c'));
            $this->assertFalse($fv->run());
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'valid_email');
            $fv->setData(array('fooemail' => ''));
            //the field is not required
            $this->assertTrue($fv->run());
            
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'valid_email');
            $fv->setData(array('fooemail' => 'eamil@domain.com'));
            $this->assertTrue($fv->run());
         }
         
        public function testRuleExactLength()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exact_length[3]');
            $fv->setData(array('foo' => 'fo'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exact_length[3]');
            $fv->setData(array('foo' => 'f'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exact_length[3]');
            $fv->setData(array('foo' => 'fdsdksk'));
            $this->assertFalse($fv->run());
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exact_length[3]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exact_length[5]');
            $fv->setData(array('foo' => ''));
            //the field is not required
            $this->assertTrue($fv->run());
        }
        
        public function testRuleMaxLength()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[3]');
            $fv->setData(array('foo' => 'fo34'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[3]');
            $fv->setData(array('foo' => 'f345543'));
            $this->assertFalse($fv->run());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[3]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[3]');
            $fv->setData(array('foo' => 'b'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[3]');
            $fv->setData(array('foo' => 'ba'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[1]');
            $fv->setData(array('foo' => ''));
            //the field is not required
            $this->assertTrue($fv->run());
        }
        
        public function testRuleMinLength()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min_length[3]');
            $fv->setData(array('foo' => 'fo'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min_length[3]');
            $fv->setData(array('foo' => 'f'));
            $this->assertFalse($fv->run());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min_length[3]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min_length[3]');
            $fv->setData(array('foo' => 'b344'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min_length[3]');
            $fv->setData(array('foo' => 'babarz'));
            $this->assertTrue($fv->run());
        }
        
        public function testRuleLessThan()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'less_than[30]');
            $fv->setData(array('foo' => '30'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'less_than[-1]');
            $fv->setData(array('foo' => '0'));
            $this->assertFalse($fv->run());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'less_than[3]');
            $fv->setData(array('foo' => '1'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'less_than[3]');
            $fv->setData(array('foo' => '2'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'less_than[3]');
            $fv->setData(array('foo' => '-999'));
            $this->assertTrue($fv->run());
        }
        
        public function testRuleGreaterThan()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'greater_than[30]');
            $fv->setData(array('foo' => '30'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'greater_than[0]');
            $fv->setData(array('foo' => '-1'));
            $this->assertFalse($fv->run());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'greater_than[1]');
            $fv->setData(array('foo' => '3'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'greater_than[2]');
            $fv->setData(array('foo' => '3'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'greater_than[-999999999]');
            $fv->setData(array('foo' => '1'));
            $this->assertTrue($fv->run());
        }
        
        public function testRuleNumeric()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '30a'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '-f.1'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '1.00000000e'));
            $this->assertFalse($fv->run());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '3.00000001'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '2e8'));
            //2e8 = 2 x 10^8 = 200000000
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '-999999999'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => ''));
            //the field is not required
            $this->assertTrue($fv->run());
        }
        
        
        public function testRuleInList()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[a,b]');
            $fv->setData(array('foo' => 'ab'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[1,2,3,4]');
            $fv->setData(array('foo' => '1.00000001'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[ab, a, c]');
            $fv->setData(array('foo' => 'ac'));
            $this->assertFalse($fv->run());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[0,1]');
            $fv->setData(array('foo' => '1'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[alpha,beta,teta]');
            $fv->setData(array('foo' => 'beta'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[-1,5,3]');
            $fv->setData(array('foo' => '-1'));
            $this->assertTrue($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[1,3]');
            $fv->setData(array('foo' => ''));
            //the field is not required
            $this->assertTrue($fv->run());
        }
  
        
        public function testRuleRegex()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'regex[/^([a-b])$/]');
            $fv->setData(array('foo' => 'ab3'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'regex[/^([a-b])$/]');
            $fv->setData(array('foo' => 'AB'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'regex[/^([a-b0-9])$/]');
            $fv->setData(array('foo' => 'ab 3'));
            $this->assertFalse($fv->run());
            
           
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'regex[/^([a-b])$/]');
            $fv->setData(array('foo' => 'ab'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required|regex[/^([a-b])$/]');
            $fv->setData(array('foo' => 'ab'));
            $this->assertFalse($fv->run());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'regex[/^([a-b])$/]');
            $fv->setData(array('foo' => ''));
            //the field is not required
            $this->assertTrue($fv->run());
        }
        
        
        
        public function testRuleExists()
        {
            $cfg = $this->getDbConfig();
            $db = new Database($cfg, false);
            $isConnected = $db->connect();
            $this->assertTrue($isConnected);
            
            //Validation failed
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'exists[form_validation.name]');
            $fv->setData(array('foo' => 'ab3'));
            $this->assertFalse($fv->run());  

            //Validation success
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'exists[form_validation.name]');
            $fv->setData(array('foo' => 'foo'));
            $this->assertTrue($fv->run());  
            
            
            //using super object database instance
            $obj = &get_instance();
            $obj->database = $db;
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exists[form_validation.name]');
            $fv->setData(array('foo' => 'foo'));
            $this->assertTrue($fv->run()); 

            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'exists[form_validation.name]');
            //Field is not required 
            $fv->setData(array('foo' => ''));
            $this->assertTrue($fv->run());
        }
        
        public function testRuleIsUnique()
        {
            $cfg = $this->getDbConfig();
            $db = new Database($cfg, false);
            $isConnected = $db->connect();
            $this->assertTrue($isConnected);
            
            //Validation failed
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'is_unique[form_validation.name]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertFalse($fv->run());  

            //Validation success
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'is_unique[form_validation.name]');
            $fv->setData(array('foo' => 'foovalue'));
            $this->assertTrue($fv->run());  
            
             //using super object database instance
            $obj = &get_instance();
            $obj->database = $db;
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'is_unique[form_validation.name]');
            $fv->setData(array('foo' => 'foovalue'));
            $this->assertTrue($fv->run());  
            
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'is_unique[form_validation.name]');
            //Field is not required 
            $fv->setData(array('foo' => ''));
            $this->assertTrue($fv->run());
        }
        
        public function testRuleIsUniqueUpdate()
        {
            $cfg = $this->getDbConfig();
            $db = new Database($cfg, false);
            $isConnected = $db->connect();
            $this->assertTrue($isConnected);
            
            //Validation failed
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            //current id is 1, but the value 'bar' already exists for id 2 so can not use it to do update
            $fv->setRule('foo', 'foo label', 'is_unique_update[form_validation.name,id=1]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertFalse($fv->run()); 

            //invalid rule definition
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'is_unique_update[form_validation.name]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertTrue($fv->run()); 

            //Validation success
            $fv = new FormValidation();
            $fv->setDatabase($db);   
             //current id is 1, and the value 'foo' already exists and id is 1 so is the same can use it to do update
            $fv->setRule('foo', 'foo label', 'is_unique_update[form_validation.name,id=1]');
            $fv->setData(array('foo' => 'foo'));
            $this->assertTrue($fv->run());  
            
             //using super object database instance
            $obj = &get_instance();
            $obj->database = $db;
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'is_unique_update[form_validation.name,id=1]');
            $fv->setData(array('foo' => 'foo'));
            $this->assertTrue($fv->run());  
            
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'is_unique_update[form_validation.name,id=1]');
            //Field is not required 
            $fv->setData(array('foo' => ''));
            $this->assertTrue($fv->run());
        }
        
        
        
    }
