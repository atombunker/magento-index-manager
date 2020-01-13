<?php

/**
 * @category 	  Upment
 * @package 	  Upment_Indexmanager
 * @copyright 	Copyright (c) 2019 Upment (https://www.upment.com/)
 */

/**
 * Indexmanager Resource Collection Model
 *
 * @category 	Upment
 * @package 	Upment_Indexmanager
 * @author  	Upment
 */

class Upment_Indexmanager_Model_Mysql4_Indexlog_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
		public function _construct()
    {
			$this->_init("indexmanager/indexlog");
		}
}
