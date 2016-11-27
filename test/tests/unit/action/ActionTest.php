<?php
namespace Agavi\Tests\Unit\Action;
use Agavi\Action\Action;
use Agavi\Request\RequestDataHolder;
use Agavi\Util\ParameterHolder;

class SampleAction extends Action {
	public function execute(ParameterHolder $parameters)
	{
	}
}


class ActionTest extends \Agavi\Testing\UnitTestCase
{
	private $_action = null;

	public function setUp()
	{
		$this->_action = new SampleAction();
		$this->_action->initialize($this->getContext()->getController()->createExecutionContainer('Foo', 'Bar'));
	}

	public function tearDown()
	{
		$this->_action = null;
	}

	public function testgetContext()
	{
		$context = $this->getContext();
		$actionContext = $this->_action->getContext();
		$this->assertSame($context, $actionContext);
	}

	public function testCredentials()
	{
		$this->assertNull($this->_action->getCredentials());
	}

	public function testgetDefaultViewName()
	{
		$this->assertEquals('Input', $this->_action->getDefaultViewName());
	}

	public function testhandleError()
	{
		$this->assertEquals('Error', $this->_action->handleError(new RequestDataHolder()));
	}

	public function testisSecure()
	{
		$this->assertFalse($this->_action->isSecure());
	}

	public function testisSimple()
	{
		$this->assertFalse($this->_action->isSimple());
	}

	public function testvalidate()
	{
		$this->assertTrue($this->_action->validate(new RequestDataHolder()));
	}

	public function testsetAttribute() {
        $this->_action->setAttribute('foo', 'bar');
        $this->assertEquals('bar', $this->_action->getAttribute('foo'));
    }

    public function testhasAttribute() {
        $this->_action->setAttribute('foo', 'bar');
        $this->assertTrue($this->_action->hasAttribute('foo'));
    }

    public function testremoveAttribute() {
        $this->_action->setAttribute('foo', 'bar');
        $this->assertTrue($this->_action->hasAttribute('foo'));
        $this->_action->removeAttribute('foo');
        $this->assertFalse($this->_action->hasAttribute('foo'));
    }

    public function testgetAttributes() {
        $this->_action->setAttribute('foo', 'bar');
        $attributes = $this->_action->getAttributes();
        $this->assertTrue(is_array($attributes));
        $this->assertEquals('bar', $attributes['foo']);
    }

    public function testsetAttributeByRef() {
        $foo = 'bar';
        $this->_action->setAttributeByRef('foo', $foo);
        $foo = 'baz';
        $this->assertEquals('baz', $this->_action->getAttribute('foo'));
    }

    public function testsetAttributes() {
        $attrs = ['foo' => 'bar', 'baz' => 'barbazork'];
        $this->_action->setAttributes($attrs);
        $this->assertEquals($attrs, $this->_action->getAttributes());
    }

    public function testappendAttribute() {
        $this->_action->appendAttribute('foo', 'lol');
        $this->_action->appendAttribute('foo', 'bar');
        $a = $this->_action->getAttribute('foo');
        $this->assertTrue(is_array($a));
        $this->assertEquals(['lol', 'bar'], $a);
    }

    public function testclearAttributeNames() {
        $this->_action->setAttribute('foo', 'bar');
        $this->_action->clearAttributes();
        $this->assertNull($this->_action->getAttribute('foo'));
    }

    public function testgetAttributeNames() {
        $this->_action->setAttribute('foo', 'bar');
        $this->_action->setAttribute('bar', 'baz');
        $this->assertEquals(['foo', 'bar'] ,$this->_action->getAttributeNames());
    }
}
?>