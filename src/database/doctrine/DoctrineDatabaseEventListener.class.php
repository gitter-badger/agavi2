<?php
namespace Agavi\Database\Doctrine;

use Agavi\Database\DoctrineDatabase;

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

/**
 * An event listener for AgaviDoctrineDatabase.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.4
 *
 * @version    $Id$
 */
class DoctrineDatabaseEventListener extends Doctrine_EventListener
{
	/**
	 * @var        DoctrineDatabase The database adapter instance.
	 */
	protected $database;
	
	/**
	 * Constructor, accepts the AgaviDoctrineDatabase instance to operate on.
	 *
	 * @param      DoctrineDatabase $database The corresponding database adapter.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.4
	 */
	public function __construct(DoctrineDatabase $database)
	{
		$this->database = $database;
	}
	
	/**
	 * Return the DoctrineDatabase instance associated with this listener.
	 *
	 * @return     DoctrineDatabase
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	public function getDatabase()
	{
		return $this->database;
	}
	
	/**
	 * Post-connect listener. Will set charset and run init queries if configured.
	 *
	 * @param      Doctrine_Event $event The Doctrine event object.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.4
	 */
	public function postConnect(Doctrine_Event $event)
	{
		$database = $this->getDatabase();
		
		if($database->hasParameter('charset')) {
			$event->getInvoker()->setCharset($database->getParameter('charset'));
		}
		
		foreach((array)$database->getParameter('init_queries') as $query) {
			$event->getInvoker()->exec($query);
		}
	}
}

?>