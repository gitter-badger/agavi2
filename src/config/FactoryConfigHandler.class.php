<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

namespace Agavi\Config;

use Agavi\Config\Util\Dom\XmlConfigDomDocument;
use Agavi\Config\Util\Dom\XmlConfigDomElement;
use Agavi\Exception\ConfigurationException;

/**
 * FactoryConfigHandler allows you to specify which factory implementation
 * the system will use.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class FactoryConfigHandler extends XmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/factories/1.1';
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      XmlConfigDomDocument $document The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>ParseException</b> If a requested configuration file is
	 *                                   improperly formatted.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      0.11.0
	 */
	public function execute(XmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'factories');
		
		$config = $document->documentURI;
		$data = array();
		
		// The order of this initialization code is fixed, to not change
		// name => required?
		$factories = array(
			'execution_container' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
				),
			),
			
			'validation_manager' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
				),
			),
			
			'dispatch_filter' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
					'Agavi\\Filter\\GlobalFilterInterface',
				),
			),
			
			'execution_filter' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
					'Agavi\\Filter\\ActionFilterInterface',
				),
			),
			
			'security_filter' => array(
				'required' => Config::get('core.use_security', false),
				'var' => null,
				'must_implement' => array(
					'Agavi\\Filter\\ActionFilterInterface',
					'Agavi\\Filter\\SecurityFilterInterface',
				),
			),
			
			'filter_chain' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
				),
			),
			
			'response' => array(
				'required' => true,
				'var' => null,
				'must_implement' => array(
				),
			),
			
			'database_manager' => array(
				'required' => Config::get('core.use_database', false),
				'var' => 'databaseManager',
				'must_implement' => array(
				),
			),
			
			'database_manager', // startup()
			
			'logger_manager' => array(
				'required' => Config::get('core.use_logging', false),
				'var' => 'loggerManager',
				'must_implement' => array(
				),
			),
			
			'logger_manager', // startup()
			
			'translation_manager' => array(
				'required' => Config::get('core.use_translation', false),
				'var' => 'translationManager',
				'must_implement' => array(
				),
			),
			
			'request' => array(
				'required' => true,
				'var' => 'request',
				'must_implement' => array(
				),
			),
			
			'routing' => array(
				'required' => true,
				'var' => 'routing',
				'must_implement' => array(
				),
			),
			
			'controller' => array(
				'required' => true,
				'var' => 'controller',
				'must_implement' => array(
				),
			),
			
			'storage' => array(
				'required' => true,
				'var' => 'storage',
				'must_implement' => array(
				),
			),
			
			'storage', // startup()
			
			'user' => array(
				'required' => true,
				'var' => 'user',
				'must_implement' => (
					Config::get('core.use_security')
					? array(
						'Agavi\\User\\SecurityUserInterface',
					)
					: array(
					)
				),
			),
			
			'translation_manager', // startup()
			
			'user', // startup()
			
			'routing', // startup()
			
			'request', // startup()
			
			'controller', // startup()
		);
		
		foreach($document->getConfigurationElements() as $configuration) {
			foreach($factories as $factory => $info) {
				if(is_array($info) && $info['required'] && $configuration->hasChild($factory)) {
					/** @var XmlConfigDomElement $element */
					$element = $configuration->getChild($factory);
					
					$data[$factory] = isset($data[$factory]) ? $data[$factory] : array('class' => null, 'params' => array());
					$data[$factory]['class'] = $element->getAttribute('class', $data[$factory]['class']);
					$data[$factory]['params'] = $element->getAgaviParameters($data[$factory]['params']);
				}
			}
		}
		
		$code = array();
		$shutdownSequence = array();
		
		foreach($factories as $factory => $info) {
			if(is_array($info)) {
				if(!$info['required']) {
					continue;
				}
				if(!isset($data[$factory]) || $data[$factory]['class'] === null) {
					$error = 'Configuration file "%s" has missing or incomplete entry "%s"';
					$error = sprintf($error, $config, $factory);
					throw new ConfigurationException($error);
				}
				
				try {
					$rc = new \ReflectionClass($data[$factory]['class']);
				} catch(\ReflectionException $e) {
					$error = 'Configuration file "%s" specifies unknown class "%s" for entry "%s"';
					$error = sprintf($error, $config, $data[$factory]['class'], $factory);
					throw new ConfigurationException($error, 0,  $e);
				}
				foreach($info['must_implement'] as $interface) {
					if(!$rc->implementsInterface($interface)) {
						$error = 'Class "%s" for entry "%s" does not implement interface "%s" in configuration file "%s"';
						$error = sprintf($error, $data[$factory]['class'], $factory, $interface, $config);
						throw new ConfigurationException($error);
					}
				}
				
				if($info['var'] !== null) {
					// we have to make an instance
					$code[] = sprintf(
						'$this->%1$s = new %2$s();' . "\n" . '$this->%1$s->initialize($this, %3$s);',
						$info['var'],
						$data[$factory]['class'],
						var_export($data[$factory]['params'], true)
					);
				} else {
					// it's a factory info
					$code[] = sprintf(
						'$this->factories[%1$s] = %2$s;',
						var_export($factory, true),
						var_export(array(
							'class' => $data[$factory]['class'],
							'parameters' => $data[$factory]['params'],
						), true)
					);
				}
			} else {
				if($factories[$info]['required']) {
					$code[] = sprintf('$this->%s->startup();', $factories[$info]['var']);
					array_unshift($shutdownSequence, sprintf('$this->%s', $factories[$info]['var']));
				}
			}
		}
		
		$code[] = sprintf('$this->shutdownSequence = array(%s);', implode(",\n", $shutdownSequence));
		
		return $this->generate($code, $config);
	}
}

?>