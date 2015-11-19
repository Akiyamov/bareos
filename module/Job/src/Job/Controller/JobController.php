<?php

/**
 *
 * bareos-webui - Bareos Web-Frontend
 *
 * @link      https://github.com/bareos/bareos-webui for the canonical source repository
 * @copyright Copyright (c) 2013-2015 Bareos GmbH & Co. KG (http://www.bareos.org/)
 * @license   GNU Affero General Public License (http://www.gnu.org/licenses/)
 * @author    Frank Bergkemper
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

namespace Job\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Job\Form\JobForm;

class JobController extends AbstractActionController
{

   protected $jobModel;

   public function indexAction()
   {
      if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {

         $status = "all";
         $period = 7;

         $period = $this->params()->fromQuery('period') ? $this->params()->fromQuery('period') : '7';
         $status = $this->params()->fromQuery('status') ? $this->params()->fromQUery('status') : 'all';

         $form = new JobForm($period, $status);

         return new ViewModel(
            array(
               'form' => $form,
               'status' => $status,
               'period' => $period
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

         $jobid = (int) $this->params()->fromRoute('id', 0);
         $job = $this->getJobModel()->getJob($jobid);
         $joblog = $this->getJobModel()->getJobLog($jobid);

         return new ViewModel(array(
            'job' => $job,
            'joblog' => $joblog,
            'jobid' => $jobid
         ));
      }
      else {
         return $this->redirect()->toRoute('auth', array('action' => 'login'));
      }
   }

   public function runningAction()
   {
      if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {

            $jobs_R = $this->getJobModel()->getJobsByStatus('R', null, null);
            $jobs_l = $this->getJobModel()->getJobsByStatus('l', null, null);

            $jobs = array_merge($jobs_R, $jobs_l);

            return new ViewModel(
               array(
                  'jobs' => $jobs
               )
            );
      }
      else {
            return $this->redirect()->toRoute('auth', array('action' => 'login'));
      }
   }

   public function waitingAction()
   {
      if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {
            return new ViewModel();
      }
      else {
            return $this->redirect()->toRoute('auth', array('action' => 'login'));
      }
   }

   public function unsuccessfulAction()
   {
      if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {
            return new ViewModel();
      }
      else {
            return $this->redirect()->toRoute('auth', array('action' => 'login'));
      }
   }

   public function successfulAction()
   {
      if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {
            return new ViewModel();
      }
      else {
            return $this->redirect()->toRoute('auth', array('action' => 'login'));
      }
   }

   public function rerunAction()
   {
      if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {
            $jobid = (int) $this->params()->fromRoute('id', 0);
            $result = $this->getJobModel()->rerunJob($jobid);
            return new ViewModel(
                  array(
                     'bconsoleOutput' => $result,
                     'jobid' => $jobid,
                  )
            );
      }
      else {
            return $this->redirect()->toRoute('auth', array('action' => 'login'));
      }
   }

   public function cancelAction()
   {
      if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {
            $jobid = (int) $this->params()->fromRoute('id', 0);
            $result = $this->getJobModel()->cancelJob($jobid);
            return new ViewModel(
                  array(
                     'bconsoleOutput' => $result
                  )
            );
      }
      else {
            return $this->redirect()->toRoute('auth', array('action' => 'login'));
      }
   }

   public function runAction()
   {
      if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {
            return new ViewModel();
      }
      else {
            return $this->redirect()->toRoute('auth', array('action' => 'login'));
      }
   }

   public function queueAction()
   {
      if($_SESSION['bareos']['authenticated'] == true && $this->SessionTimeoutPlugin()->timeout()) {
         $jobname = $this->params()->fromQuery('job');
         $result = $this->getJobModel()->runJob($jobname);
         return new ViewModel(
            array(
               'result' => $result
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
         $jobid = $this->params()->fromQuery('jobid');
         $status = $this->params()->fromQuery('status');
         $period = $this->params()->fromQuery('period');

         if($data == "jobs" && $status == "all") {
            $result = $this->getJobModel()->getJobs($status, $period);
         }
         elseif($data == "jobs" && $status == "successful") {
            $jobs_T = $this->getJobModel()->getJobsByStatus('T', $period, null); // Terminated
            $jobs_W = $this->getJobModel()->getJobsByStatus('W', $period, null); // Terminated with warnings
            $result = array_merge($jobs_T, $jobs_W);
         }
         elseif($data == "jobs" && $status == "unsuccessful") {
            $jobs_A = $this->getJobModel()->getJobsByStatus('A', $period, null); // Canceled jobs
            $jobs_E = $this->getJobModel()->getJobsByStatus('E', $period, null); //
            $jobs_e = $this->getJobModel()->getJobsByStatus('e', $period, null); //
            $jobs_f = $this->getJobModel()->getJobsByStatus('f', $period, null); //
            $result = array_merge($jobs_A, $jobs_E, $jobs_e, $jobs_f);
         }
         elseif($data == "jobs" && $status == "running") {
            $jobs_R = $this->getJobModel()->getJobsByStatus('R', $period, null);
            $jobs_l = $this->getJobModel()->getJobsByStatus('l', $period, null);
            $result = array_merge($jobs_R, $jobs_l);
         }
         elseif($data == "jobs" && $status == "waiting") {
            $jobs_F = $this->getJobModel()->getJobsByStatus('F', $period, null);
            $jobs_S = $this->getJobModel()->getJobsByStatus('S', $period, null);
            $jobs_m = $this->getJobModel()->getJobsByStatus('m', $period, null);
            $jobs_M = $this->getJobModel()->getJobsByStatus('M', $period, null);
            $jobs_s = $this->getJobModel()->getJobsByStatus('s', $period, null);
            $jobs_j = $this->getJobModel()->getJobsByStatus('j', $period, null);
            $jobs_c = $this->getJobModel()->getJobsByStatus('c', $period, null);
            $jobs_d = $this->getJobModel()->getJobsByStatus('d', $period, null);
            $jobs_t = $this->getJobModel()->getJobsByStatus('t', $period, null);
            $jobs_p = $this->getJobModel()->getJobsByStatus('p', $period, null);
            $jobs_q = $this->getJobModel()->getJobsByStatus('q', $period, null);
            $jobs_C = $this->getJobModel()->getJobsByStatus('C', $period, null);
            $result = array_merge(
               $jobs_F,$jobs_S,$jobs_m,$jobs_M,
               $jobs_s,$jobs_j,$jobs_c,$jobs_d,
               $jobs_t,$jobs_p,$jobs_q,$jobs_C
            );
         }
         elseif($data == "backupjobs") {
            $result = $this->getJobModel()->getBackupJobs();
         }
         elseif($data == "details") {
            $result = $this->getJobModel()->getJob($jobid);
         }
         elseif($data == "logs" && isset($jobid)) {
            $result = $this->getJobModel()->getJobLog($jobid);
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

   public function getJobModel()
   {
      if(!$this->jobModel) {
         $sm = $this->getServiceLocator();
         $this->jobModel = $sm->get('Job\Model\JobModel');
      }
      return $this->jobModel;
   }
}
