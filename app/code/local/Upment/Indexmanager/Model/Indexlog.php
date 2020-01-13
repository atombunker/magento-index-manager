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

class Upment_Indexmanager_Model_Indexlog extends Mage_Core_Model_Abstract
{
    protected function _construct(){
       $this->_init("indexmanager/indexlog");
    }

    /**
     * Get duration
     * @return integer
     */

    public function getDuration()
    {
        $duration = false;
        if ($this->getStartedAt() && ($this->getStartedAt() != '0000-00-00 00:00:00')) {
            if ($this->getFinishedAt() && ($this->getFinishedAt() != '0000-00-00 00:00:00')) {
                $time = strtotime($this->getFinishedAt());
            } elseif ($this->getStatus() == 'running') {
                $time = time();
            } else {
                return false;
            }
            $duration = $time - strtotime($this->getStartedAt());
        }
        return $duration;
    }

    /**
     * Get Status
     * @return string
     */

    public function getStatusClass()
    {
      $statusClass = $this->getStatus();
      if ($statusClass == 'finished') $statusClass = 'success';
      if ($statusClass == 'failed'  ) $statusClass = 'gone';
      return $statusClass;
    }

    /**
     * Get Source
     * @return string
     */

    public function getFriendlySource()
    {
      $source = $this->getSource();
      if ($source == 'app/code/local/Upment/Indexmanager/Model/Cron.php') $source = 'Upment Indexer';
      if ($source == 'app/code/core/Mage/Index/Model/Process.php'       ) $source = 'System Indexer';
      if ($source == 'shell/indexer.php'                                ) $source = 'Shell';
      return $source;
    }

    /**
     * Get Friendly Name
     * @return string
     */

    public function getFriendlyName()
    {
      $codeName = $this->getIndexcode();
      $friendly = Mage::getSingleton('index/indexer')->getProcessByCode($codeName)->getName();
      return $friendly;
    }

}
