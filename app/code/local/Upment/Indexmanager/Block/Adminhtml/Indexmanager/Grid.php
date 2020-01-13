<?php
/**
 * @category 	  Upment
 * @package 	  Upment_Indexmanager
 * @copyright 	Copyright (c) 2019 Upment (https://www.upment.com/)
 */

/**
 * Indexmanager Indexdata Grid Block
 *
 * @category 	Upment
 * @package 	Upment_Indexmanager
 * @author  	Upment
 */
class Upment_Indexmanager_Block_Adminhtml_Indexmanager_Grid extends Mage_Index_Block_Adminhtml_Process_Grid
{
		/**
	 	* prepare columns for this grid
	 	*
	 	* @return Upment_Indexmanager_Block_Adminhtml_Indexmanager_Grid
	 	*/

		protected function _prepareColumns()
		{
				$this->addColumn("indexer_code", array(
				"header" => Mage::helper("indexmanager")->__("Index"),
				"align" => "left",
				"width" => "180",
				"index" => "name",
				'sortable'  => false,
				));
        $this->addColumn('description', array(
            'header'    => Mage::helper('index')->__('Description'),
            'align'     => 'left',
            'index'     => 'description',
            'sortable'  => false,
        ));
				$this->addColumn('status', array(
            'header'    => Mage::helper('index')->__('Status'),
						"width"     => "120",
            'align'     => 'left',
            'index'     => 'status',
						'type'      => 'options',
            'options'   => $this->_processModel->getStatusesOptions(),
            'frame_callback' => array($this, 'decorateStatus')
        ));
				$this->addColumn('update_required', array(
            'header'    => Mage::helper('index')->__('Update Required'),
						"width"     => "120",
            'align'     => 'left',
            'index'     => 'update_required',
						'type'      => 'options',
            'options'   => $this->_processModel->getUpdateRequiredOptions(),
						'sortable'  => false,
            'frame_callback' => array($this, 'decorateUpdateRequired')
        ));
				$this->addColumn('ended_at', array(
		            'header'    => Mage::helper('index')->__('Updated At'),
		            'type'      => 'datetime',
		            'width'     => '180',
		            'align'     => 'left',
		            'index'     => 'ended_at',
		            'frame_callback' => array($this, 'decorateDate')
		        ));
				$this->addColumn("schedule", array(
				"header" => Mage::helper("indexmanager")->__("Actions"),
				'renderer'  => 'indexmanager/adminhtml_indexmanager_renderer_indexdata',
				'width'     => '120',
				'align'     => 'center',
				'sortable'  => false,
				'confirm' => true
				));

				return $this;
		}

		/**
		 * get url for each row in grid
		 *
		 * @return boolean
		 */

		public function getRowUrl($row)
		{
			   return false;
		}

		/**
		 * prepare mass action for this grid
		 *
		 * @return Upment_Indexmanager_Block_Adminhtml_Indexmanager_Grid
		 */

		protected function _prepareMassaction()
    	{
    	return false;
    	}


}
