<?php

/**
 * @category 	  Upment
 * @package 	  Upment_Indexmanager
 * @copyright 	Copyright (c) 2019 Upment (https://www.upment.com/)
 */

/**
 * Indexmanager Adminhtml Block
 *
 * @category 	Upment
 * @package 	Upment_Indexmanager
 * @author  	Upment
 */

class Upment_Indexmanager_Block_Adminhtml_TimelineDetail extends Mage_Adminhtml_Block_Template
{

    protected $_template = 'upment/timelinedetail.phtml';
    protected $index;

    /**
    * Set Index
    *
    * @param string $index
    * @return string
    */

    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    public function getIndex()
    {
        return $this->index;
    }

}
