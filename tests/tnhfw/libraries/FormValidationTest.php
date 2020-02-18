<?php 
use PHPUnit\Framework\TestCase;
class FormValidationTest extends TestCase
{   
	public static function setUpBeforeClass()
    {
    
    }
	
	public static function tearDownAfterClass()
    {
        
    }
	
    protected function setUp()
    {
		
    }

    protected function tearDown()
    {
		
    }
	
	

    // tests
    public function testValidationDataIsEmpty()
    {
		$fv = new FormValidation();
		$this->assertEmpty($fv->getData());
    }
	
	public function testValidationDataIsNotEmpty()
    {
		$fv = new FormValidation();
		$fv->setData(array('name' => 'mike'));
		$this->assertNotEmpty($fv->getData());
		$this->assertArrayHasKey('name', $fv->getData());
    }
	
	public function testCannotDoValidation()
    {
		$fv = new FormValidation();
		$this->assertFalse($fv->canDoValidation());
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
    }
	
	
	public function testRequiredRule()
    {
		$fv = new FormValidation();
		$fv->setRule('name', 'name', 'required');
		$fv->setData(array('name' => ''));
		$this->assertFalse($fv->run());
		
		$fv = new FormValidation();
		$fv->setRule('name', 'name', 'required');
		$fv->setData(array('name' => null));
		$this->assertFalse($fv->run());
		
		$fv = new FormValidation();
		$fv->setRule('name', 'name', 'required');
		$fv->setData(array('name' => 'tony'));
		$this->assertTrue($fv->run());
    }
}