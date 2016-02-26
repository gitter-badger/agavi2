<?php
namespace Agavi\Config;

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+


use Agavi\Config\Util\Dom\XmlConfigDomElement;
use Agavi\Util\Toolkit;
use Agavi\Config\Util\Dom\XmlConfigDomDocument;

/**
 * ModuleConfigHandler reads module configuration files to determine the
 * status of a module.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class ModuleConfigHandler extends XmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/module/1.1';
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      XmlConfigDomDocument $document The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>ParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.9.0
	 */
	public function execute(XmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'module');
		
		// remember the config file path
		$config = $document->documentURI;
		
		$enabled = false;
		$prefix = 'modules.${moduleName}.';
		$data = array();
		
		// loop over <configuration> elements
		foreach($document->getConfigurationElements() as $configuration) {
			$module = $configuration->getChild('module');
			if(!$module) {
				continue;
			}
			
			// enabled flag is treated separately
			$enabled = (bool) Toolkit::literalize($module->getAttribute('enabled'));
			
			// loop over <setting> elements; there can be many of them
			/** @var XmlConfigDomElement $setting */
			foreach($module->get('settings') as $setting) {
				$localPrefix = $prefix;
				
				// let's see if this buddy has a <settings> parent with valuable information
				if($setting->parentNode->localName == 'settings') {
					if($setting->parentNode->hasAttribute('prefix')) {
						$localPrefix = $setting->parentNode->getAttribute('prefix');
					}
				}
				
				$settingName = $localPrefix . $setting->getAttribute('name');
				if($setting->hasAgaviParameters()) {
					$data[$settingName] = $setting->getAgaviParameters();
				} else {
					$data[$settingName] = Toolkit::literalize($setting->getValue());
				}
			}
		}
		
		$code = array();
		$code[] = '$lcModuleName = strtolower($moduleName);';
		$code[] = 'Agavi\\Config\\Config::set(Agavi\\Util\\Toolkit::expandVariables(' . var_export($prefix . 'enabled', true) . ', array(\'moduleName\' => $lcModuleName)), ' . var_export($enabled, true) . ', true, true);';
		if(count($data)) {
			$code[] = '$moduleConfig = ' . var_export($data, true) . ';';
			$code[] = '$moduleConfigKeys = array_keys($moduleConfig);';
			$code[] = 'foreach($moduleConfigKeys as &$value) $value = Agavi\\Util\\Toolkit::expandVariables($value, array(\'moduleName\' => $lcModuleName));';
			$code[] = '$moduleConfig = array_combine($moduleConfigKeys, $moduleConfig);';
			$code[] = 'Agavi\\Config\\Config::fromArray($moduleConfig);';
		}
		
		return $this->generate($code, $config);
	}
}

?>