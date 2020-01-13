<?php

/**
 * @category 	Upment
 * @package 	Upment_Indexmanager
 * @copyright 	Copyright (c) 2019 Upment (https://www.upment.com/)
 */

/**
 * Indexmanager Indexdata Renderer Block
 *
 * @category 	Upment
 * @package 	Upment_Indexmanager
 * @author  	Upment
 */

class Upment_Indexmanager_Block_Adminhtml_Indexmanager_Renderer_Indexdata extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
    * Render
    *
    * @param Varien_Object $filter
    * @return string
    */
    public function render(Varien_Object $filter)
    {
        $code = $filter->getIndexerCode();
        $collection = Mage::getModel('indexmanager/indexmanager')->getCollection()->addFieldToFilter('indexcode', $code);

        if ($collection->getSize()) {
          return "<span class=\"grid-severity-notice\"><span>Scheduled</span></span>";
        } else {
          $url = Mage::helper('adminhtml')->getUrl('*/*/saveindex', array('indexcode' => $filter->getIndexerCode()));
          return '<a href="' . $url . '"><button type="button">Index</button></a>';
        }
    }

}
