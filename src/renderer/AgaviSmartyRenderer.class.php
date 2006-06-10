<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * A renderer produces the output as defined by a View
 *
 * @package    agavi
 * @subpackage renderer
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviSmartyRenderer extends AgaviRenderer
{
	const COMPILE_DIR = 'templates';
	const COMPILE_SUBDIR = 'smarty';
	const CACHE_DIR = 'content';

	protected $smarty = null;
	
	protected $extension = '.tpl';

	public function getEngine()
	{
		if($this->smarty) {
			return $this->smarty;
		}
		
		if(!class_exists('Smarty')) {
			if(defined('SMARTY_DIR') ) {
				// if SMARTY_DIR constant is defined, we'll use it
				require(SMARTY_DIR . 'Smarty.class.php');
			} else {
				// otherwise we resort to include_path
				require('Smarty.class.php');
			}
		}

		$this->smarty = new Smarty();
		$this->smarty->clear_all_assign();
		$this->smarty->clear_config();
		$this->smarty->config_dir = AgaviConfig::get('core.config_dir');
		
		$parentMode = fileperms(AgaviConfig::get('core.cache_dir'));

		$compileDir = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::COMPILE_DIR . DIRECTORY_SEPARATOR . self::COMPILE_SUBDIR;
		@mkdir($compileDir, $parentMode, true);
		$this->smarty->compile_dir = $compileDir;

		$cacheDir = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_DIR;
		@mkdir($cacheDir, $parentMode, true);
		$this->smarty->cache_dir = $cacheDir;

		$this->smarty->plugins_dir  = array("plugins","plugins_local");
		
		return $this->smarty;
	}

	public function & render()
	{
		$retval = null;

		// execute pre-render check
		$this->preRenderCheck();

		$engine = $this->getEngine();
		$view = $this->getView();

		// get the render mode
		$mode = $view->getContext()->getController()->getRenderMode();

		$attribs =& $view->getAttributes();
		
		foreach($attribs as $name => &$value) {
			$engine->assign_by_ref($name, $value);
		}
		
		if($mode == AgaviView::RENDER_CLIENT && !$view->isDecorator()) {
			// render directly to the client
			$this->getEngine()->display($view->getDirectory() . '/' . $view->getTemplate() . $this->getExtension());
		} elseif ($mode != AgaviView::RENDER_NONE) {
			// render to variable
			$retval = $this->getEngine()->fetch($view->getDirectory() . '/' . $view->getTemplate() . $this->getExtension());

			// now render our decorator template, if one exists
			if($view->isDecorator()) {
				$retval =& $this->decorate($retval);
			}

			if($mode == AgaviView::RENDER_CLIENT) {
				echo($retval);
				$retval = null;
			}
		}
		return $retval;
	}

	public function & decorate(&$content)
	{
		// call our parent decorate() method
		parent::decorate($content);
		
		$engine = $this->getEngine();
		$view = $this->getView();
		
		foreach($this->output as $name => &$value) {
			$engine->assign_by_ref($name, $value);
		}
		
		// render the decorator template and return the result
		$decoratorTemplate = $view->getDecoratorDirectory() . '/' . $view->getDecoratorTemplate() . $this->getExtension();

		$retval = $engine->fetch($decoratorTemplate);

		return $retval;
	}
}