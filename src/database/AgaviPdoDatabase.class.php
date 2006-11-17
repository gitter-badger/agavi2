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
 * AgaviPdoDatabase provides connectivity for the PDO database abstraction 
 * layer.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     Daniel Swarbrick (daniel@pressure.net.nz)
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviPdoDatabase extends AgaviDatabase
{
	/**
	 * Connect to the database.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be 
	 *                                           created.
	 *
	 * @author     Daniel Swarbrick (daniel@pressure.net.nz)
	 * @since      0.9.0
	 */
	public function connect()
	{
		// determine how to get our parameters
		$method = $this->getParameter('method', 'dsn');

		// get parameters
		switch($method) {
			case 'dsn' :
				$dsn = $this->getParameter('dsn');
				if($dsn == null) {
					// missing required dsn parameter
					$error = 'Database configuration specifies method ' .
						 '"dsn", but is missing dsn parameter';
					throw new AgaviDatabaseException($error);
				}
				break;
		}

		try {
			$pdo_username = $this->getParameter('username');
			$pdo_password = $this->getParameter('password');

			$pdo_options = array();

			// let's see if we need a persistent connection
			// take special care because the postgresql pdo driver bitterly complains
			// when getting options passed.
			if($this->hasParameter('persistent')) {
				$persistent = $this->getParameter('persistent', false);
				$pdo_options[PDO::ATTR_PERSISTENT] = $persistent;
			}

			if($this->hasParameter('options') && is_array($opts = $this->getParameter('options'))) {
				foreach($opts as $key => $value) {
					$pdo_options[constant($key)] = $value;
				}
			}

			$this->connection = new PDO($dsn, $pdo_username, $pdo_password, $pdo_options);

		} catch(PDOException $e) {
			throw new AgaviDatabaseException($e->getMessage());
		}

		// lets generate exceptions instead of silent failures
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     Daniel Swarbrick (daniel@pressure.net.nz)
	 * @since      0.9.0
	 */
	public function shutdown()
	{
		// assigning null to a previously open connection object causes a disconnect
		$this->connection = null;
	}
}
?>