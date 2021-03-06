<?php
namespace Agavi\Test\Config;

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

use Agavi\Config\ConfigHandler;

class ImportTestOnceHandler extends ConfigHandler
{
	public function execute($config, $context = null)
	{
		$code = '$GLOBALS["ConfigCacheImportTestOnce_included"] = true;';

		// compile data
		$retval = "<?php\n" .
				  "// auto-generated by AutoloadConfigHandler\n" .
				  "// date: %s\n%s\n?>";
		$retval = sprintf($retval, date('m/d/Y H:i:s'), $code);

		return $retval;

	}

}

?>