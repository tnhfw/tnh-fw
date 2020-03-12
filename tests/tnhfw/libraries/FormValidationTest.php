<?php 

    /**
    * Function used in callback validation
    * @see above for method FormValidationTest::testRuleCallback
    */
    function callback_validation($value){
         return strlen($value) >= 3;
    }
    
    /**
     * FormValidation library class tests
     *
     * @group core
     * @group libraries
     */
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
        
        public function testValidateDataIsEmpty()
        {
            $fv = new FormValidation();
            $this->assertEmpty($fv->getData());
            $this->assertFalse($fv->validate());
        }
        
        public function testValidateInvalidRule()
        {
            $fv = new FormValidation();
            $fv->setData(array('foo' => 'bar value'));
            $fv->setRule('foo', 'bar', 'invalid_rule');
            $this->assertFalse($fv->validate());
        }

        public function testIsValid()
        {
            $fv = new FormValidation();
            $this->assertFalse($fv->isValid());
        }
        
        public function testSettingCustomErrorMessage()
        {
            
            //field specific message for the rule
            $fv = new FormValidation();
            $fv->setData(array('foo' => ''));
            $fv->setRule('foo', 'bar', 'required');
            $fv->setCustomErrorMessage('required', 'foo required message error', 'foo');
            
            $this->assertFalse($fv->validate());
            $this->assertContains('foo required message error', $fv->getErrors());
            
            //global message for the rule
            $fv = new FormValidation();
            $fv->setData(array('foo' => '', 'bar' => null));
            $fv->setRule('foo', 'bar', 'required');
            $fv->setRule('bar', 'foo', 'required');
            $fv->setCustomErrorMessage('required', 'global required message error');

            $this->assertFalse($fv->validate());
            $this->assertContains('global required message error', $fv->getErrors());
        }
        
        
        public function testGetErrors()
        {
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => ''));
            $this->assertFalse($fv->validate());
            $this->assertNotEmpty($fv->getErrors());
            $this->assertArrayHasKey('name', $fv->getErrors());
        }
        
        
        public function testValidateCsrf()
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
            $this->assertFalse($fv->validate());
            
            //invalid CSRF
            $_SESSION['kcsrf'] =  $csrfValue;
            $_SESSION['csrf_expire'] = time() + 600;
            
            $request = $this->getMockBuilder('Request')->getMock();
            $request->expects($this->any())
                    ->method('method')
                    ->will($this->returnValue('POST'));
                    
            $request->expects($this->any())
                    ->method('query')
                    ->with('kcsrf')
                    ->will($this->returnValue('invalid CSRF'));
                    
            $obj = & get_instance();
            $obj->request = $request;
            
           
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => 'foo'));
            $this->assertFalse($fv->validate());
            
             //disable CSRF
            $this->config->set('csrf_enable', false);
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => 'foo'));
            $this->assertTrue($fv->validate());
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
            $this->assertSame(2, count($fv->getRules()));
        }
        
        
        //////Each rule tests //////////////////////
        public function testRuleDefaultValue()
        {
            //empty string without default_value set
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => ''));
            $this->assertFalse($fv->validate());
            
            //empty string with default_value set 
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'default_value[123]|required');
            $fv->setData(array('name' => ''));
            $this->assertTrue($fv->validate());
            $this->assertSame('123', $fv->getFieldValue('name'));
            
        }
        
        public function testRuleRequired()
        {
            //empty string
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => ''));
            $this->assertFalse($fv->validate());
            
            //null value
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => null));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'required');
            $fv->setData(array('name' => 'tony'));
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleCallback()
        {
            
            //note the function check just the strlen of the value 
            //if less than 3 return false else return true
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'callback[callback_validation]');
            $fv->setData(array('name' => 'foo'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'callback[callback_validation]');
            $fv->setData(array('name' => 'fo'));
            $this->assertFalse($fv->validate());
            
            //callback not exist
            $fv = new FormValidation();
            $fv->setRule('name', 'name', 'callback[callback_validation_foo]');
            $fv->setData(array('name' => 'fo'));
            $this->assertFalse($fv->validate());
        }
        
        
        public function testRuleNotEqual()
        {
             //fields value are equal, validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'not_equal[foo]');
            $fv->setData(array('foo' => 'foovalue', 'bar' => 'foovalue'));
            $this->assertFalse($fv->validate());
            
            //fields value are not equal, validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'not_equal[foo]');
            $fv->setData(array('foo' => 'foo', 'bar' => 'bar'));
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleMatches()
        {
            //matches validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'matches[foo]');
            $fv->setData(array('foo' => 'foo', 'bar' => 'oof'));
            $this->assertFalse($fv->validate());
            
            //matches validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required');
            $fv->setRule('bar', 'bar label', 'matches[foo]');
            $fv->setData(array('foo' => 'baz', 'bar' => 'baz'));
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleEmail()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'required|email');
            $fv->setData(array('fooemail' => ''));
            //the field is required
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'email');
            $fv->setData(array('fooemail' => 'e'));
            $this->assertFalse($fv->validate());
            
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'email');
            $fv->setData(array('fooemail' => 'e@'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'email');
            $fv->setData(array('fooemail' => 'e@.'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'email');
            $fv->setData(array('fooemail' => 'e@.com'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'email');
            $fv->setData(array('fooemail' => '.@e.v'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'email');
            $fv->setData(array('fooemail' => 'gghhghg@gm@il.com'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'email');
            $fv->setData(array('fooemail' => 'e@f.c'));
            $this->assertTrue($fv->validate());
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'email');
            $fv->setData(array('fooemail' => ''));
            //the field is not required
            $this->assertTrue($fv->validate());
            
            
            $fv = new FormValidation();
            $fv->setRule('fooemail', 'foo label', 'email');
            $fv->setData(array('fooemail' => 'eamil@domain.com'));
            $this->assertTrue($fv->validate());
         }
         
         public function testRuleUrl()
        {
            //Validation failed
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'url');
            $fv->setData(array('foo' => 'foo.com'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'url');
            $fv->setData(array('foo' => 'www.foo.bar'));
            $this->assertFalse($fv->validate());
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'url');
            $fv->setData(array('foo' => 'http://localhost'));
            $this->assertTrue($fv->validate());
            
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'url');
            $fv->setData(array('foo' => 'http://foo.bar'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'url');
            $fv->setData(array('foo' => 'ftp://myhost.com'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'url');
            $fv->setData(array('foo' => 'ftp://user@pass/host.com'));
            $this->assertTrue($fv->validate());  

            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'url');
            $fv->setData(array('foo' => 'ftp://user@pass:231/host.com'));
            $this->assertTrue($fv->validate());              
        }
        
        public function testRuleIp()
        {
            //Validation failed
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ip');
            $fv->setData(array('foo' => '1.1.1'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ip');
            $fv->setData(array('foo' => 'q::2'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ip');
            $fv->setData(array('foo' => '2006::2:m'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ip');
            $fv->setData(array('foo' => '192.168.0.256'));
            $this->assertFalse($fv->validate());
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ip');
            $fv->setData(array('foo' => '1.1.1.1'));
            $this->assertTrue($fv->validate());
            
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ip');
            $fv->setData(array('foo' => '0.0.0.0'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ip');
            $fv->setData(array('foo' => '::1'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ip');
            $fv->setData(array('foo' => '2006::1'));
            $this->assertTrue($fv->validate());  

            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ip');
            $fv->setData(array('foo' => '127.0.0.1'));
            $this->assertTrue($fv->validate());    

            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ip');
            $fv->setData(array('foo' => '255.255.255.255'));
            $this->assertTrue($fv->validate());
        }
         
         public function testRuleIpv4()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv4');
            $fv->setData(array('foo' => '::1'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv4');
            $fv->setData(array('foo' => '2006::1'));
            $this->assertFalse($fv->validate());  
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv4');
            $fv->setData(array('foo' => '1.1.1'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv4');
            $fv->setData(array('foo' => 'q::2'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv4');
            $fv->setData(array('foo' => '2006::2:m'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv4');
            $fv->setData(array('foo' => '192.168.0.256'));
            $this->assertFalse($fv->validate());
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv4');
            $fv->setData(array('foo' => '1.1.1.1'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv4');
            $fv->setData(array('foo' => '0.0.0.0'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv4');
            $fv->setData(array('foo' => '255.255.255.255'));
            $this->assertTrue($fv->validate());

            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv4');
            $fv->setData(array('foo' => '127.0.0.1'));
            $this->assertTrue($fv->validate());              
        }
        
        public function testRuleIpv6()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv6');
            $fv->setData(array('foo' => '1.1.1'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv6');
            $fv->setData(array('foo' => 'q::2'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv6');
            $fv->setData(array('foo' => '2006::2:m'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv6');
            $fv->setData(array('foo' => '192.168.0.256'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv6');
            $fv->setData(array('foo' => '1.1.1.1'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv6');
            $fv->setData(array('foo' => '0.0.0.0'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv6');
            $fv->setData(array('foo' => '255.255.255.255'));
            $this->assertFalse($fv->validate());

            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv6');
            $fv->setData(array('foo' => '127.0.0.1'));
            $this->assertFalse($fv->validate());          
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv6');
            $fv->setData(array('foo' => '::1'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'ipv6');
            $fv->setData(array('foo' => '2006::1'));
            $this->assertTrue($fv->validate());  
            
        }
         
        public function testRuleExactLength()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exact_length[3]');
            $fv->setData(array('foo' => 'fo'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exact_length[3]');
            $fv->setData(array('foo' => 'f'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exact_length[3]');
            $fv->setData(array('foo' => 'fdsdksk'));
            $this->assertFalse($fv->validate());
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exact_length[3]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exact_length[5]');
            $fv->setData(array('foo' => ''));
            //the field is not required
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleMaxLength()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[3]');
            $fv->setData(array('foo' => 'fo34'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[3]');
            $fv->setData(array('foo' => 'f345543'));
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[3]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[3]');
            $fv->setData(array('foo' => 'b'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[3]');
            $fv->setData(array('foo' => 'ba'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max_length[1]');
            $fv->setData(array('foo' => ''));
            //the field is not required
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleMinLength()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min_length[3]');
            $fv->setData(array('foo' => 'fo'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min_length[3]');
            $fv->setData(array('foo' => 'f'));
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min_length[3]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min_length[3]');
            $fv->setData(array('foo' => 'b344'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min_length[3]');
            $fv->setData(array('foo' => 'babarz'));
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleMin()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min[30]');
            $fv->setData(array('foo' => '29'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min[0]');
            $fv->setData(array('foo' => '-1'));
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min[30]');
            $fv->setData(array('foo' => '30'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min[1]');
            $fv->setData(array('foo' => '3'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min[2]');
            $fv->setData(array('foo' => '3'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'min[-999999999]');
            $fv->setData(array('foo' => '1'));
            $this->assertTrue($fv->validate());
        }
        
        
        public function testRuleMax()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max[30]');
            $fv->setData(array('foo' => '31'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max[35]');
            $fv->setData(array('foo' => '67'));
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max[3]');
            $fv->setData(array('foo' => '3'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max[3]');
            $fv->setData(array('foo' => '1'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max[3]');
            $fv->setData(array('foo' => '2'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'max[3]');
            $fv->setData(array('foo' => '-999'));
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleAlpha()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha');
            $fv->setData(array('foo' => '29'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha');
            $fv->setData(array('foo' => 'qbc1'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha');
            $fv->setData(array('foo' => 'a-b')); //dash
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha');
            $fv->setData(array('foo' => 'abc'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha');
            //UTF8 char
            $fv->setData(array('foo' => 'éîû'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha');
            //with space
            $fv->setData(array('foo' => 'a b'));
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleAlphaDash()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha_dash');
            $fv->setData(array('foo' => '29'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha_dash');
            $fv->setData(array('foo' => 'qbc1'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha_dash');
            $fv->setData(array('foo' => 'a b')); //space
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha_dash');
            $fv->setData(array('foo' => 'abc'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha_dash');
            //UTF8 char
            $fv->setData(array('foo' => 'éîû'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alpha_dash');
            //with space
            $fv->setData(array('foo' => 'a-b'));
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleAlnum()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum');
            $fv->setData(array('foo' => '29$'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum');
            $fv->setData(array('foo' => '#$'));
            $this->assertFalse($fv->validate());
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum');
            $fv->setData(array('foo' => 'abc'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum');
            $fv->setData(array('foo' => 'ab34c'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum');
            //UTF8 char
            $fv->setData(array('foo' => 'éîû'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum');
            //with space
            $fv->setData(array('foo' => 'a 45b'));
            $this->assertTrue($fv->validate());
        }
        
        
        public function testRuleAlnumDash()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum_dash');
            $fv->setData(array('foo' => '29$'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum_dash');
            $fv->setData(array('foo' => '#$'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum_dash');
            $fv->setData(array('foo' => 'abc 123'));
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum_dash');
            $fv->setData(array('foo' => 'ab34c'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum_dash');
            //UTF8 char
            $fv->setData(array('foo' => 'éîû'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'alnum_dash');
            //with space
            $fv->setData(array('foo' => 'a-45b'));
            $this->assertTrue($fv->validate());
        }
        
        
        public function testRuleDate()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date[Y-m-d]');
            $fv->setData(array('foo' => '29$'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date[Y-m-d]');
            $fv->setData(array('foo' => '01-01-2019'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date[Y]');
            $fv->setData(array('foo' => '20'));
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date[Y-m-d]');
            $fv->setData(array('foo' => '2019-10-19'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date[Y]');
            
            $fv->setData(array('foo' => '2010'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date[dmY]');
            $fv->setData(array('foo' => '21021991'));
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleDateBefore()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_before[1990-2-1]');
            $fv->setData(array('foo' => '1990-2-1'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_before[1990-2-1]');
            $fv->setData(array('foo' => '1990-2-2'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_before[1990-2-1]');
            $fv->setData(array('foo' => '1-2-1990'));
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_before[2020-2-1]');
            $fv->setData(array('foo' => '2020-1-31'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_before[2020-2-1]');
            $fv->setData(array('foo' => '2020-1-31 23:59:59'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_before[2011-2-1]');
            $fv->setData(array('foo' => '2010-2'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_before[2010-2-1]');
            $fv->setData(array('foo' => '21021991'));
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleDateAfter()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_after[1990-2-1]');
            $fv->setData(array('foo' => '1990-2-1'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_after[1990-2-1]');
            $fv->setData(array('foo' => '1990-1-2'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_after[1990-2-1]');
            $fv->setData(array('foo' => '1-1-1990'));
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_after[2020-2-1]');
            $fv->setData(array('foo' => '2020-2-31'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_after[2020-2-1]');
            $fv->setData(array('foo' => '2020-2-1 00:00:01'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_after[2011-2-1]');
            $fv->setData(array('foo' => '2011-3'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'date_after[2010-2-1]');
            $fv->setData(array('foo' => '2022010'));
            $this->assertTrue($fv->validate());
        }
        
        
        public function testRuleInList()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[a,b]');
            $fv->setData(array('foo' => 'ab'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[1,2,3,4]');
            $fv->setData(array('foo' => '1.00000001'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[ab, a, c]');
            $fv->setData(array('foo' => 'ac'));
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[0,1]');
            $fv->setData(array('foo' => '1'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[alpha,beta,teta]');
            $fv->setData(array('foo' => 'beta'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[-1,5,3]');
            $fv->setData(array('foo' => '-1'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'in_list[1,3]');
            $fv->setData(array('foo' => ''));
            //the field is not required
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleBetween()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'between[10,100]');
            $fv->setData(array('foo' => '1'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'between[10,100]');
            $fv->setData(array('foo' => '101'));
            $this->assertFalse($fv->validate());
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'between[10,100]');
            $fv->setData(array('foo' => '10'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'between[10,100]');
            $fv->setData(array('foo' => '100'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'between[-1,100]');
            $fv->setData(array('foo' => '0'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'between[-100,-1]');
            $fv->setData(array('foo' => '-2'));
            $this->assertTrue($fv->validate());
            
        }
        
        public function testRuleNumeric()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '30a'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '-f.1'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '1.00000000e'));
            $this->assertFalse($fv->validate());
            
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '3.00000001'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '2e8'));
            //2e8 = 2 x 10^8 = 200000000
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => '-999999999'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'numeric');
            $fv->setData(array('foo' => ''));
            //the field is not required
            $this->assertTrue($fv->validate());
        }
        
        
        public function testRuleInteger()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer');
            $fv->setData(array('foo' => '1a'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer');
            $fv->setData(array('foo' => '1e2'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer');
            $fv->setData(array('foo' => '1.4'));
            $this->assertFalse($fv->validate());
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer');
            $fv->setData(array('foo' => '10'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer');
            $fv->setData(array('foo' => '100'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer');
            $fv->setData(array('foo' => '0'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer');
            $fv->setData(array('foo' => '-2'));
            $this->assertTrue($fv->validate());
            
        }
  
        public function testRuleIntegerNatural()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer_natural');
            $fv->setData(array('foo' => '1a'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer_natural');
            $fv->setData(array('foo' => '1e2'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer_natural');
            $fv->setData(array('foo' => '1.4'));
            $this->assertFalse($fv->validate());
            
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer_natural');
            $fv->setData(array('foo' => '-1'));
            $this->assertFalse($fv->validate());
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer_natural');
            $fv->setData(array('foo' => '10'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer_natural');
            $fv->setData(array('foo' => '100'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer_natural');
            $fv->setData(array('foo' => '0'));
            $this->assertTrue($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'integer_natural');
            $fv->setData(array('foo' => '2'));
            $this->assertTrue($fv->validate());
            
        }
        
        public function testRuleRegex()
        {
            //Validation failed
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'regex[/^([a-b])$/]');
            $fv->setData(array('foo' => 'ab3'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'regex[/^([a-b])$/]');
            $fv->setData(array('foo' => 'AB'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'regex[/^([a-b0-9])$/]');
            $fv->setData(array('foo' => 'ab 3'));
            $this->assertFalse($fv->validate());
            
           
            
            //Validation success
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'regex[/^([a-b])$/]');
            $fv->setData(array('foo' => 'ab'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'required|regex[/^([a-b])$/]');
            $fv->setData(array('foo' => 'ab'));
            $this->assertFalse($fv->validate());
            
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'regex[/^([a-b])$/]');
            $fv->setData(array('foo' => ''));
            //the field is not required
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleExists()
        {
            $db = $this->getDbInstanceForTest();
            //Validation failed
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'exists[form_validation.name]');
            $fv->setData(array('foo' => 'ab3'));
            $this->assertFalse($fv->validate());  

            //Validation success
            $fv = new FormValidation();
            $fv->setDatabase($db);    
            $fv->setRule('foo', 'foo label', 'exists[form_validation.name]');
            $fv->setData(array('foo' => 'foo'));
            $this->assertTrue($fv->validate());  
            
            $db = $this->getDbInstanceForTest();
            
            //using super object database instance
            $obj = &get_instance();
            $obj->database = $db;
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'exists[form_validation.name]');
            $fv->setData(array('foo' => 'foo'));
            $this->assertTrue($fv->validate()); 

            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'exists[form_validation.name]');
            //Field is not required 
            $fv->setData(array('foo' => ''));
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleIsUnique()
        {
            $db = $this->getDbInstanceForTest();
           
            //Validation failed
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'is_unique[form_validation.name]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertFalse($fv->validate());  

            //Validation success
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'is_unique[form_validation.name]');
            $fv->setData(array('foo' => 'foovalue'));
            $this->assertTrue($fv->validate());  
            
             //using super object database instance
            $obj = &get_instance();
            $obj->database = $db;
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'is_unique[form_validation.name]');
            $fv->setData(array('foo' => 'foovalue'));
            $this->assertTrue($fv->validate());  
            
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'is_unique[form_validation.name]');
            //Field is not required 
            $fv->setData(array('foo' => ''));
            $this->assertTrue($fv->validate());
        }
        
        public function testRuleIsUniqueUpdate()
        {
            $db = $this->getDbInstanceForTest();
            
            //Validation failed
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            //current id is 1, but the value 'bar' already exists for id 2 so can not use it to do update
            $fv->setRule('foo', 'foo label', 'is_unique_update[form_validation.name,id=1]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertFalse($fv->validate()); 

            //invalid rule definition
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'is_unique_update[form_validation.name]');
            $fv->setData(array('foo' => 'bar'));
            $this->assertTrue($fv->validate()); 

            //Validation success
            $fv = new FormValidation();
            $fv->setDatabase($db);   
             //current id is 1, and the value 'foo' already exists and id is 1 so is the same can use it to do update
            $fv->setRule('foo', 'foo label', 'is_unique_update[form_validation.name,id=1]');
            $fv->setData(array('foo' => 'foo'));
            $this->assertTrue($fv->validate());  
            
             //using super object database instance
            $obj = &get_instance();
            $obj->database = $db;
            $fv = new FormValidation();
            $fv->setRule('foo', 'foo label', 'is_unique_update[form_validation.name,id=1]');
            $fv->setData(array('foo' => 'foo'));
            $this->assertTrue($fv->validate());  
            
            $fv = new FormValidation();
            $fv->setDatabase($db);            
            $fv->setRule('foo', 'foo label', 'is_unique_update[form_validation.name,id=1]');
            //Field is not required 
            $fv->setData(array('foo' => ''));
            $this->assertTrue($fv->validate());
        }
        
        /**
        * Return the database instance for test of rules related to database
        */
        private function getDbInstanceForTest() {
            $cfg = $this->getDbConfig();
            $connection = new DatabaseConnection($cfg, false);
            $isConnected = $connection->connect();
            $this->assertTrue($isConnected);
            $db = new Database($connection);
            
            
            $qr = new DatabaseQueryRunner($connection);
            $qr->setBenchmark(new Benchmark());
            
            $qresult = new DatabaseQueryResult();
            $qr->setQueryResult($qresult);
            
            $db->setQueryRunner($qr);
            
            $qb = new DatabaseQueryBuilder($connection);
            $db->setQueryBuilder($qb);
            
            return $db;
        }
        
        
    }
