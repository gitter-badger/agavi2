<?php

namespace Agavi\Filter;
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
use Agavi\Core\Context;
use Agavi\Controller\ExecutionContainer;
use Agavi\Exception\InitializationException;

/**
 * AgaviFilter provides a way for you to intercept incoming requests or outgoing
 * responses.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
interface FilterInterface
{
	/**
	 * Execute this filter.
	 *
	 * @param      FilterChain        $filterChain A FilterChain instance.
	 * @param      ExecutionContainer $container   The current execution container.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function execute(FilterChain $filterChain, ExecutionContainer $container);

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context The current Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function getContext();

	/**
	 * Initialize this Filter.
	 *
	 * @param      Context $context    The current application context.
	 * @param      array   $parameters An associative array of initialization parameters.
	 *
	 * @throws     InitializationException If an error occurs while initializing this Filter.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function initialize(Context $context, array $parameters = array());
}

?>