<?php

class ThereCanBeOnlyOneModel extends AgaviSingletonModel
{
	public $foo = null;
	
	public function setFoo($value)
	{
		$this->foo = $value;
	}
	
	public function getFoo()
	{
		return $this->foo;
	}
}

class SingletonModelTestsTest extends AgaviTestCase
{

	private $_controller = null,
					$_context = null;

	public function setUp()
	{
		$this->_context = AgaviContext::getInstance();
		$this->_controller = $this->_context->getController();
	}

	public function tearDown()
	{
		$this->_controller = null;
		$this->_context = null;
	}

	public function testThereCanBeOnlyOne()
	{
		// Hmm.. this is one of the things that bugs me about this..
		// We really dont want to advocate using the classes's getInstance method directly
		// since we dont know if the instance has been initialized yet if we do.
		// Which causes me to wonder, should we initialize the instance within getInstance?
		// If we did, we would have to have access to Context when we call getInstance.. 
		// hmm.. hmm.. 
		$one = AgaviSingletonModel::getInstance('ThereCanBeOnlyOneModel');
		$one->setFoo('bar');
		$two = AgaviSingletonModel::getInstance('ThereCanBeOnlyOneModel');
		$this->assertEquals($one->getFoo(), $two->getFoo());
		$this->assertEquals($one, $two);
		$three = $this->_controller->getModel('Test', 'SingletonTest');
		$four = AgaviSingletonModel::getInstance('Test_SingletonTestModel');
		$this->assertEquals($three, $four);
	}
	
	public function testgetContext()
	{
		$model = $this->_controller->getModel('Test', 'SingletonTest');
		$mc = $model->getContext();
		$this->assertReference($this->_context, $mc);
	}
}

?>