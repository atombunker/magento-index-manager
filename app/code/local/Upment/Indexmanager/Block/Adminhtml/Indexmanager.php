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

class Upment_Indexmanager_Block_Adminhtml_Indexmanager extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{
	$currentUrl = Mage::helper('core/url')->getCurrentUrl();
	$url = Mage::helper('adminhtml')->getUrl('*/*/reindexall');
	$this->_controller = "adminhtml_indexmanager";
	$this->_blockGroup = "indexmanager";
	$this->_headerText = Mage::helper("indexmanager")->__("Index Manager");
	parent::__construct();
	$this->_addButton("Reindex All", array(
            "label" => Mage::helper("core")->__("Reindex All"),
            "onclick" => "location.href = '" . $url . "';",
            "class" => "btn btn-danger",
        ));
	$this->_addButton("Refresh", array(
            "label" => Mage::helper("core")->__("Refresh"),
            "onclick" => "location.href = '" . $currentUrl . "';",
            "class" => "btn btn-danger",
        ));
	$this->_removeButton('add');

	}

}
