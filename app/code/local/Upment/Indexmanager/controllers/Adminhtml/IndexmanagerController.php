<?php

/**
 * @category 	  Upment
 * @package 	  Upment_Indexmanager
 * @copyright 	Copyright (c) 2019 Upment (https://www.upment.com/)
 */

/**
 * Indexmanager Index Controller
 *
 * @category 	Upment
 * @package 	Upment_Indexmanager
 * @author  	Upment
 */

class Upment_Indexmanager_Adminhtml_IndexmanagerController extends Mage_Adminhtml_Controller_Action
{
		protected function _isAllowed()
		{
				return true;
		}

		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("upment/indexmanager");
				return $this;
		}

		public function indexAction()
		{
		    $this->_title($this->__("Index Manager"));
				$this->_initAction();
				$this->renderLayout();
		}


		public function saveindexAction()
		{
			$post_data['indexcode']=$this->getRequest()->getParam('indexcode');
			$session = Mage::getSingleton("adminhtml/session");

			if ($post_data) {
			try {
						$model = Mage::getModel("indexmanager/indexmanager");
						$collection = $model->getCollection()->addFieldToFilter('indexcode', $post_data['indexcode']);
						if ($collection->getSize()) {
							$session->addError(Mage::helper("adminhtml")->__("Indexing already scheduled."));
						} else {
							$model->setData($post_data)->save();
					    $session->addSuccess(Mage::helper("adminhtml")->__("Indexing successfully scheduled."));
						}
						$this->_redirect("*/*/");
						return;
				} catch (Exception $e) {
				    $session->addError($e->getMessage());
						$this->_redirect("*/*/");
					return;
				}

			}
			$this->_redirect("*/*/");

		}

		public function reindexallAction()
		{

			$session = Mage::getSingleton("adminhtml/session");
			try {
						$model = Mage::getModel("indexmanager/indexmanager");
						$indexes = Mage::getSingleton('index/indexer')->getProcessesCollection()->load();
						$some=false;
						$sch=false;
	          foreach($indexes as $index){
							if ($index->getIndexer()->isVisible()) {
								$post_data['indexcode'] = $index->getIndexerCode();
								$collection = $model->getCollection()->addFieldToFilter('indexcode', $post_data['indexcode']);
								if ($collection->getSize()) {
									$some=true;
								} else {
									$sch=true;
									$model->setData($post_data)->save();
								}
							}
						}
						if (!$sch) {
							$session->addError(Mage::helper("adminhtml")->__("All indexes are already scheduled for reindexing."));
						} elseif ($some) {
							$session->addWarning(Mage::helper("adminhtml")->__("Some indexes were already scheduled for reindexing."));
						} else {
				    	$session->addSuccess(Mage::helper("adminhtml")->__("Indexing successfully scheduled."));
						}
						$this->_redirect("*/*/");
						return;
				} catch (Exception $e) {
				    $session->addError($e->getMessage());
						$this->_redirect("*/*/");
					return;
				}

			$this->_redirect("*/*/");

		}

}
