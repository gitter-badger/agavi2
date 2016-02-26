<?php
namespace Agavi\Tests\Unit\Validator;

use Agavi\Request\RequestDataHolder;
use Agavi\Testing\UnitTestCase;
use Agavi\Validator\AndoperatorValidator;
use Agavi\Validator\Validator;

class AgaviAndoperatorValidatorTest extends UnitTestCase
{
	public function testExecute()
	{
		$vm = $this->getContext()->createInstanceFor('validation_manager');
		$vm->clear();
		/** @var AndoperatorValidator $o */
		$o = $vm->createValidator('Agavi\\Validator\\AndoperatorValidator', array(), array(), array('severity' => 'error'));

		/** @var \DummyValidator $val1 */
		$val1 = $vm->createValidator('Agavi\\Test\\Validator\\DummyValidator', array(), array(), array('severity' => 'error'));
		$val1->val_result = true;
		/** @var \DummyValidator $val2 */
		$val2 = $vm->createValidator('Agavi\\Test\\Validator\\DummyValidator', array(), array(), array('severity' => 'error'));
		$val2->val_result = true;
		
		$o->registerValidators(array($val1, $val2));
		
		$this->assertEquals($o->execute(new RequestDataHolder()), Validator::SUCCESS);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val1->validated);
		
		$val1->clear();
		$val2->clear();
		
		$o->setParameter('break', true);
		$val1->val_result = false;
		
		$this->assertEquals($o->execute(new RequestDataHolder()), Validator::ERROR);
		$this->assertTrue($val1->validated);
		$this->assertFalse($val2->validated);
		
		$val1->clear();
		$val2->clear();
		
		$o->setParameter('break', false);
		
		$this->assertEquals($o->execute(new RequestDataHolder()), Validator::ERROR);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		
		$val1->clear();
		$val2->clear();
		
		$val1->setParameter('severity', 'critical');
		
		$this->assertEquals($o->execute(new RequestDataHolder()), Validator::CRITICAL);
		$this->assertEquals($vm->getResult(), Validator::CRITICAL);
		$this->assertTrue($val1->validated);
		$this->assertFalse($val2->validated);
	}
}
?>
