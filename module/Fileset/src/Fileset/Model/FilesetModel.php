<?php

/**
 *
 * bareos-webui - Bareos Web-Frontend
 *
 * @link      https://github.com/bareos/bareos-webui for the canonical source repository
 * @copyright Copyright (c) 2013-2014 Bareos GmbH & Co. KG (http://www.bareos.org/)
 * @license   GNU Affero General Public License (http://www.gnu.org/licenses/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Fileset\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FilesetModel implements ServiceLocatorAwareInterface
{
	protected $serviceLocator;
	protected $director;

	public function __construct()
	{
	}

	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}

	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}

	public function getFilesets()
	{
		$cmd = 'list filesets';
		$this->director = $this->getServiceLocator()->get('director');
		$result = $this->director->send_command($cmd, 2, null);
		$filesets = \Zend\Json\Json::decode($result, \Zend\Json\Json::TYPE_ARRAY);
		return $filesets['result']['filesets'];
	}

	public function getFileset($id)
	{
		if(isset($id)) {
			$cmd = 'llist fileset filesetid='.$id.'';
			$this->director = $this->getServiceLocator()->get('director');
			$result = $this->director->send_command($cmd, 2, null);
			$fileset = \Zend\Json\Json::decode($result, \Zend\Json\Json::TYPE_ARRAY);
			return $fileset['result']['filesets'];
		}
		else {
			return false;
		}
	}
}
