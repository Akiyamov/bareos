<?php

/**
 *
 * bareos-webui - Bareos Web-Frontend
 *
 * @link      https://github.com/bareos/bareos-webui for the canonical source repository
 * @copyright Copyright (c) 2013-2015 Bareos GmbH & Co. KG (http://www.bareos.org/)
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

namespace Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class ClientController extends AbstractActionController
{

	protected $clientModel;

	public function indexAction()
	{
		if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {

			$clients = $this->getClientModel()->getClients();

			return new ViewModel(
				array(
					'clients' => $clients,
				)
			);
		}
		else {
			return $this->redirect()->toRoute('auth', array('action' => 'login'));
		}
	}

	public function detailsAction()
	{
		if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {

			return new ViewModel(
				array(
					'client' => $this->params()->fromRoute('id')
				)
			);

		}
		else {
			return $this->redirect()->toRoute('auth', array('action' => 'login'));
		}
	}

	public function getDataAction()
	{
		if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {

			$data = $this->params()->fromQuery('data');
			$client = $this->params()->fromQuery('client');

			if($data == "all") {
				$result = $this->getClientModel()->getClients();
			}
			elseif($data == "details" && isset($client)) {
				$result = $this->getClientModel()->getClient($client);
			}
			elseif($data == "backups" && isset($client)) {
				$result = $this->getClientModel()->getClientBackups($client, null, 'desc');
			}
			else {
				$result = null;
			}

			$response = $this->getResponse();
			$response->getHeaders()->addHeaderLine('Content-Type', 'application/json');

			if(isset($result)) {
				$response->setContent(JSON::encode($result));
			}

			return $response;
		}
		else {
			return $this->redirect()->toRoute('auth', array('action' => 'login'));
		}
	}

	public function getClientModel()
	{
		if(!$this->clientModel) {
			$sm = $this->getServiceLocator();
			$this->clientModel = $sm->get('Client\Model\ClientModel');
		}
		return $this->clientModel;
	}

}
