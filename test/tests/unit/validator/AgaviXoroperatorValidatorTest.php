<?php
namespace Agavi\Tests\Unit\Validator;

use Agavi\Exception\ValidatorException;
use Agavi\Request\RequestDataHolder;
use Agavi\Test\Validator\DummyValidator;
use Agavi\Testing\UnitTestCase;
use Agavi\Validator\Validator;
use Agavi\Validator\XoroperatorValidator;

class AgaviXoroperatorValidatorTest extends UnitTestCase
{
	public function testvalidate()
	{
		$vm = $this->getContext()->createInstanceFor('validation_manager');
		$vm->clear();
		/** @var XoroperatorValidator $o */
		$o = $vm->createValidator('Agavi\\Validator\\XoroperatorValidator', array(), array(), array('severity' => 'error'));

		/** @var DummyValidator $val1 */
		$val1 = $vm->createValidator('Agavi\\Test\\Validator\\DummyValidator', array(), array(), array('severity' => 'error'));
		/** @var DummyValidator $val2 */
		$val2 = $vm->createValidator('Agavi\\Test\\Validator\\DummyValidator', array(), array(), array('severity' => 'error'));
		$o->registerValidators(array($val1, $val2));
		
		// 1st test: both successful
		$val1->val_result = true;
		$val2->val_result = true;
		$this->assertEquals($o->execute(new RequestDataHolder()), Validator::ERROR);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$val1->clear();
		$val2->clear();

		// 2nd test: first successful
		$val1->val_result = true;
		$val2->val_result = false;
		$this->assertEquals($o->execute(new RequestDataHolder()), Validator::SUCCESS);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$val1->clear();
		$val2->clear();

		// 3rd test: last successful
		$val1->val_result = false;
		$val2->val_result = true;
		$this->assertEquals($o->execute(new RequestDataHolder()), Validator::SUCCESS);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$val1->clear();
		$val2->clear();

		// 4th test: none successful
		$val1->val_result = false;
		$val2->val_result = false;
		$this->assertEquals($o->execute(new RequestDataHolder()), Validator::ERROR);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$val1->clear();
		$val2->clear();

		// 5th test: first results critical
		$val1->val_result = false;
		$val1->setParameter('severity', 'critical');
		$val2->val_result = true;
		$this->assertEquals($o->execute(new RequestDataHolder()), Validator::CRITICAL);
		$this->assertTrue($val1->validated);
		$this->assertFalse($val2->validated);
		$val1->setParameter('severity', 'error');
		$val1->clear();
		$val2->clear();

		// 5th test: last results critical
		$val1->val_result = true;
		$val2->val_result = false;
		$val2->setParameter('severity', 'critical');
		$this->assertEquals($o->execute(new RequestDataHolder()), Validator::CRITICAL);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$val1->clear();
		$val2->clear();
	}
	
	public function testcheckValidSetup()
	{
		$vm = $this->getContext()->createInstanceFor('validation_manager');
		$vm->clear();
		/** @var XoroperatorValidator $o */
		$o = $vm->createValidator('Agavi\\Validator\\XoroperatorValidator', array(), array(), array('severity' => 'error'));

		/** @var DummyValidator $val1 */
		$val1 = $vm->createValidator('Agavi\\Test\\Validator\\DummyValidator', array(), array(), array('severity' => 'error'));
		/** @var DummyValidator $val2 */
		$val2 = $vm->createValidator('Agavi\\Test\\Validator\\DummyValidator', array(), array(), array('severity' => 'error'));
		/** @var DummyValidator $val3 */
		$val3 = $vm->createValidator('Agavi\\Test\\Validator\\DummyValidator', array(), array(), array('severity' => 'error'));
		
		$o->addChild($val1);
		try {
			$o->execute(new RequestDataHolder());
			$this->fail();
		} catch(ValidatorException $e) {
			$this->assertEquals($e->getMessage(), 'XOR allows only exact 2 child validators');
		}
		
		$o->addChild($val2);
		$o->execute(new RequestDataHolder());
		
		$o->addChild($val3);
		try {
			$o->execute(new RequestDataHolder());
			$this->fail();
		} catch(ValidatorException $e) {
			$this->assertEquals($e->getMessage(), 'XOR allows only exact 2 child validators');
		}
	}
}
?>
