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
 * AgaviModel provides a convention for separating business logic from 
 * application logic. When using a model you're providing a globally accessible
 * API for other modules to access, which will boost interoperability among 
 * modules in your web application.
 *
 * @package    agavi
 * @subpackage model
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
interface AgaviIModel
{
	public function getContext();
}

?>