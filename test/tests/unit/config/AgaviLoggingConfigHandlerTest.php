<?php
namespace Agavi\Tests\Unit\Config;

use Agavi\Config\Config;
use Agavi\Config\LoggingConfigHandler;
use Agavi\Core\Context;
use Agavi\Logging\Logger;
use Agavi\Logging\LoggerAppender;
use Agavi\Logging\LoggerInterface;
use Agavi\Logging\LoggerLayout;
use Agavi\Logging\LoggerMessage;

require_once(__DIR__ . '/ConfigHandlerTestBase.php');

class TestLogger extends Logger
{
	public $appenders;
	public $level;

	public function setAppender($name, LoggerAppender $appender)
	{
		$this->appenders[$name] = $appender;
	}

	public function setLevel($level)
	{
		$this->level = $level;
	}
}

class TestLogger1 extends TestLogger { }
class TestLogger2 extends TestLogger { }
class TestLogger3 extends TestLogger { }

class TestAppender extends LoggerAppender
{
	public $params = null;
	public $layout = null;

	public function initialize(Context $context, array $params = array())
	{
		$this->params = $params;
	}

	public function setLayout(LoggerLayout $layout)
	{
		$this->layout = $layout;
	}

	public function shutdown() {}
	public function write(LoggerMessage $message) {}
}

class TestAppender1 extends TestAppender { }
class TestAppender2 extends TestAppender { }
class TestAppender3 extends TestAppender { }

class TestLayout extends LoggerLayout
{
	public $params = null;

	public function initialize(Context $context, array $params = array())
	{
		$this->params = $params;
	}
	public function format(LoggerMessage $message) {}
}

class TestLayout1 extends TestLayout { }
class TestLayout2 extends TestLayout { }


class AgaviLoggingConfigHandlerTest extends ConfigHandlerTestBase
{
	protected $context;

	public function setUp()
	{
		$this->context = Context::getInstance();
	}

	/**
	 * Proxied because we include a compiled config that assumes it runs in the LM
	 *
	 * @see      AgaviLoggerManager::setLogger()
	 */
	protected function setLogger($name, LoggerInterface $logger)
	{
		return $this->context->getLoggerManager()->setLogger($name, $logger);
	}
	
	/**
	 * Proxied because we include a compiled config that assumes it runs in the LM
	 *
	 * @see      AgaviLoggerManager::setDefaultLoggerName()
	 */
	public function setDefaultLoggerName($name)
	{
		return $this->context->getLoggerManager()->setDefaultLoggerName($name);
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testLoggingConfigHandler()
	{
		$document = $this->parseConfiguration(
			Config::get('core.config_dir') . '/tests/logging.xml',
			Config::get('core.agavi_dir') . '/config/xsl/logging.xsl'
		);

		$LCH = new LoggingConfigHandler();
		$cfg = $this->includeCode($LCH->execute($document));

		$test1 = $this->context->getLoggerManager()->getLogger('test1');
		$test2 = $this->context->getLoggerManager()->getLogger('test2');
		$test3 = $this->context->getLoggerManager()->getLogger('test3');

		$this->assertInstanceOf('Agavi\\Tests\\Unit\\Config\\TestLogger1', $test1);
		$this->assertSame(TestLogger::INFO, $test1->level);
		$this->assertInstanceOf('Agavi\\Tests\\Unit\\Config\\TestAppender1', $test1->appenders['appender1']);
		$this->assertInstanceOf('Agavi\\Tests\\Unit\\Config\\TestAppender2', $test1->appenders['appender2']);
		$this->assertSame($test1->appenders['appender1'], $test2->appenders['appender1']);
		$this->assertSame($test1->appenders['appender2'], $test2->appenders['appender2']);


		$this->assertInstanceOf('Agavi\\Tests\\Unit\\Config\\TestLogger2', $test2);
		$this->assertSame(TestLogger::ERROR, $test2->level);
		$this->assertInstanceOf('Agavi\\Tests\\Unit\\Config\\TestAppender1', $test2->appenders['appender1']);
		$this->assertInstanceOf('Agavi\\Tests\\Unit\\Config\\TestAppender2', $test2->appenders['appender2']);
		$this->assertInstanceOf('Agavi\\Tests\\Unit\\Config\\TestAppender3', $test2->appenders['appender3']);

		$this->assertInstanceOf('Agavi\\Tests\\Unit\\Config\\TestLogger3', $test3);
		$this->assertSame(TestLogger::INFO | TestLogger::ERROR, $test3->level);

		$a1 = $test2->appenders['appender1'];
		$a2 = $test2->appenders['appender2'];
		$a3 = $test2->appenders['appender3'];

		$this->assertInstanceOf('Agavi\\Tests\\Unit\\Config\\TestLayout1', $a1->layout);
		$this->assertSame(array(
			'param1' => 'value1',
			'param2' => 'value2',
			),
			$a1->params
		);


		$this->assertInstanceOf('Agavi\\Tests\\Unit\\Config\\TestLayout1', $a2->layout);
		$this->assertEquals(array(), $a2->params);


		$this->assertInstanceOf('Agavi\\Tests\\Unit\\Config\\TestLayout2', $a3->layout);
		$this->assertSame(array(
			'file' => Config::get('core.app_dir') . '/log/myapp.log',
			),
			$a3->params
		);


		$this->assertSame($a1->layout, $a2->layout);

		$l1 = $a1->layout;
		$l2 = $a3->layout;

		$this->assertSame(array(
			'param1' => 'value1',
			'param2' => 'value2',
			),
			$l1->params
		);

		$this->assertSame(array(), $l2->params);

	}
}
?>