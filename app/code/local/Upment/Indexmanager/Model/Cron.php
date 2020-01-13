<?php

/**
 * @category 	  Upment
 * @package 	  Upment_Indexmanager
 * @copyright 	Copyright (c) 2019 Upment (https://www.upment.com/)
 */

/**
 * Indexmanager Model
 *
 * @category 	Upment
 * @package 	Upment_Indexmanager
 * @author  	Upment
 */


ini_set('memory_limit','6G');
ini_set('max_execution_time', 3600);
set_time_limit(3600);

class Upment_Indexmanager_Model_Cron {

	public function customReindex() {
    try{
      $collection = Mage::getModel('indexmanager/indexmanager')->getCollection();
      if ($collection->getSize()) {
        foreach ($collection as $job) {
          $canrun = true;
					$process = Mage::getSingleton('index/indexer')->getProcessByCode($job->getIndexcode());
					if ($process->isLocked()) {
						$canrun = false;
					}
          $indexes = Mage::getSingleton('index/indexer')->getProcessesCollection()->load();
          foreach($indexes as $index){
            if($index->getStatus() == Mage_Index_Model_Process::STATUS_RUNNING) {
              $canrun = false;
            }
          }
          if ($canrun) {
            $process->reindexEverything();
            $job->delete();
          }
        }
      }
    }
    catch (Exception $e){
      Mage::logException($e);
    }
	}

	/**
   * Run Reindex
   *
   * @param $schedule
	 * Create log
   */

	public function runReindex($schedule) {
		$jobsRoot = Mage::getConfig()->getNode('crontab/jobs');
		$jobConfig = $jobsRoot->{$schedule->getJobCode()};
		$indexCode = (string) $jobConfig->indexcode;
		$process = Mage::getSingleton('index/indexer')->getProcessByCode($indexCode);
		$process->reindexEverything();
		Mage::log('Scheduled: ' . $indexCode, null, 'upmentindex.log', true);
	}

}
