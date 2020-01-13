<?php

/**
 * @category 	  Upment
 * @package 	  Upment_Indexmanager
 * @copyright 	Copyright (c) 2019 Upment (https://www.upment.com/)
 */

/**
 * Indexmanager Process Model
 *
 * @category 	Upment
 * @package 	Upment_Indexmanager
 * @author  	Upment
 */

class Upment_Indexmanager_Model_Index_Process extends Mage_Index_Model_Process
{

    /**
     * Reindex all data what this process is responsible for
     *
     */
    public function reindexAll()
    {
        if ($this->isLocked()) {
            Mage::throwException(Mage::helper('index')->__('%s Index process is working now. Please try run this process later.', $this->getIndexer()->getName()));
        }

        // check if there's an indexer already running
        $canrun = true;
        $indexes = Mage::getSingleton('index/indexer')->getProcessesCollection()->load();
        foreach($indexes as $index){
          if ( $index->getStatus() == self::STATUS_RUNNING ) {
            $canrun = false;
          }
        }

        // if there's no other indexer running, start reindexing

        if ($canrun) {
          $processStatus = $this->getStatus();
          $this->_getResource()->startProcess($this);
          $log_model = Mage::getModel("indexmanager/indexlog");
          $key = $log_model->setData($this->initializeLogData())->save()->getId();
          $this->lock();
          try {
              $eventsCollection = $this->getUnprocessedEventsCollection();
              $totalnum = $eventsCollection->count();
              $eventResource = Mage::getResourceSingleton('index/event');

              if ($totalnum > 0 && $processStatus == self::STATUS_PENDING
                  || $this->getForcePartialReindex()
              ) {
                  $this->_getResource()->beginTransaction();
                  try {
                      $this->_processEventsCollection($eventsCollection, false);
                      $this->_getResource()->commit();
                  } catch (Exception $e) {
                      $this->_getResource()->rollBack();
                      throw $e;
                  }
              } else {
                  //Update existing events since we'll do reindexAll
                  $eventResource->updateProcessEvents($this);
                  $this->getIndexer()->reindexAll();
              }
              $this->unlock();

              $newStat = "finished";
              $unprocessedEvents = $eventResource->getUnprocessedEvents($this);
              if (count($unprocessedEvents) > 0) {
                $this->lock();
                $eventsCollection = $this->getUnprocessedEventsCollection();
                try {
                    $this->_processEventsCollection($eventsCollection, false);
                    $this->_getResource()->commit();
                } catch (Exception $e) {
                    $this->_getResource()->rollBack();
                    throw $e;
                }
                $this->unlock();
                $newStat = "retried";
                $unprocessedEvents = $eventResource->getUnprocessedEvents($this);
              }
              if ($this->getMode() == self::MODE_MANUAL && (count($unprocessedEvents) > 0)) {
                  $this->_getResource()->updateStatus($this, self::STATUS_REQUIRE_REINDEX);
              } else {
                  $this->_getResource()->endProcess($this);
              }

              if (count($unprocessedEvents) > 0) {
                $newStat = "partial";
              if (count($unprocessedEvents) >= $totalnum) $newStat = "failed";
              }

              $log_data_output = 'Total: ' . $totalnum . "\nUnprocessed: " . count($unprocessedEvents);
              $log_model->load($key)->addData($this->addLogData($newStat, $log_data_output))->save();
          } catch (Exception $e) {
              $this->unlock();
              $this->_getResource()->failProcess($this);
              $newStat = "failed";
              $log_data_output = $e->getMessage();
              $log_model->load($key)->addData($this->addLogData($newStat, $log_data_output))->save();
              throw $e;
          }
          Mage::dispatchEvent('after_reindex_process_' . $this->getIndexerCode());
        } else {
          // if there is an index running, put the request in queue
          $model = Mage::getModel("indexmanager/indexmanager");
          $post_data['indexcode'] = $this->getIndexerCode();
          $collection = $model->getCollection()->addFieldToFilter('indexcode', $post_data['indexcode']);
          if ($collection->getSize()) {

          } else {
            $model->setData($post_data)->save();
          }
          return;
        }
        return $this;
    }

    /**
     * Index pending events addressed to the process
     *
     * @param   null|string $entity
     * @param   null|string $type
     * @return  Mage_Index_Model_Process
     */
    public function indexEvents($entity=null, $type=null)
    {
        /**
         * Check if process indexer can match entity code and action type
         */
        if ($entity !== null && $type !== null) {
            if (!$this->getIndexer()->matchEntityAndType($entity, $type)) {
                return $this;
            }
        }

        if ($this->getMode() == self::MODE_MANUAL) {
            return $this;
        }

        if ($this->isLocked()) {
            return $this;
        }

        $log_model = Mage::getModel("indexmanager/indexlog");
        $log_data['indexcode'] = $this->getIndexerCode();
        $log_data['status'] = "running";
        $chkCollection = $log_model->getCollection()
            ->addFieldToFilter('indexcode', $log_data['indexcode'])
            ->addFieldToFilter('status', "finished")
            ->addFieldToFilter('source', "Auto-save")
            ->addFieldToFilter('finished_at', array('from'=> strtotime('-20 seconds', time()),'to'=> time(),'datetime' => true));
        if ($chkCollection->getSize()) {
          $existTask = $chkCollection->getFirstItem();
          $key = $existTask->getId();
          $eventCount = intval($existTask->getOutput());
          if ($eventCount == 0) $eventCount = 1;
          $eventCount++;
          $log_data['output'] = $eventCount . " separate events.";
          $existTask->addData($log_data)->save();
        } else {
          $log_data['source'] = "Auto-save";
          $log_data['started_at'] = time();
          $key = $log_model->setData($log_data)->save()->getId();
        }
        $this->lock();
        try {
            /**
             * Prepare events collection
             */
            $eventsCollection = $this->getUnprocessedEventsCollection();
            if ($entity !== null) {
                $eventsCollection->addEntityFilter($entity);
            }
            if ($type !== null) {
                $eventsCollection->addTypeFilter($type);
            }

            $this->_processEventsCollection($eventsCollection);
            $this->unlock();
            $newStat = "finished";
            $log_data_output = "";
            $log_model->load($key)->addData($this->addLogData($newStat, $log_data_output))->save();
        } catch (Exception $e) {
            $this->unlock();
            $newStat = "failed";
            $log_data_output = $e->getMessage();
            $log_model->load($key)->addData($this->addLogData($newStat, $log_data_output))->save();
            throw $e;
        }
        return $this;
    }


    /**
     * Setting default data to log
     *
     * @return  array
     */

    public function initializeLogData()
    {
      $source = debug_backtrace()[2]['file'];
      $source = str_replace(Mage::getBaseDir('base').'/', '', $source);
      $initial_data = array();
      $initial_data['indexcode'] = $this->getIndexerCode();
      $initial_data['status'] = "running";
      $initial_data['source'] = $source;
      $initial_data['started_at'] = time();

      return $initial_data;
    }

    /**
     * Adding additional data to log
     *
     * @param string $log_stat
     * @param string $log_out
     * @return array
    */

    public function addLogData($log_stat, $log_out)
    {
      $additional_data = array();
      $additional_data['finished_at'] = time();
      $additional_data['status'] = $log_stat;
      $additional_data['output'] = $log_out;

      return $additional_data;
    }

}
