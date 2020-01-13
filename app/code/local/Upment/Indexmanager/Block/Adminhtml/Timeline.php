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

class Upment_Indexmanager_Block_Adminhtml_Timeline extends Mage_Adminhtml_Block_Widget_Container
{

    const XML_PATH_HOURS        = 'indexmanager/settings/hours';
    const XML_PATH_PX           = 'indexmanager/settings/px';
    const XML_PATH_TIMELINE     = 'indexmanager/settings/timeline';
    const XML_PATH_ORDERS       = 'indexmanager/settings/orders';

    protected $zoom = 6;
    protected $starttime;
    protected $endtime;
    protected $indexes = array();
    protected $view_last = 12;
    protected $line_width = 3;
    protected $timeline_visible = 'Hide';
    protected $orders_visible = 'Hide';

    protected function _construct()
    {
        $this->_headerText = $this->__('Indexing Timeline');
        $this->view_last = Mage::getStoreConfig(self::XML_PATH_HOURS, null);
        if (intval($this->getRequest()->getParam('hours')) > 0) {
          $this->view_last = intval($this->getRequest()->getParam('hours'));
          Mage::getModel('core/config')->saveConfig(self::XML_PATH_HOURS, $this->view_last);
          Mage::getModel('core/config')->cleanCache();
        }

        if (intval($this->view_last) < 1) {
          $this->view_last = 12;
        }


        $this->line_width = Mage::getStoreConfig(self::XML_PATH_PX, null);
        if (intval($this->getRequest()->getParam('px')) > 0) {
          $this->line_width = intval($this->getRequest()->getParam('px'));
          Mage::getModel('core/config')->saveConfig(self::XML_PATH_PX, $this->line_width);
          Mage::getModel('core/config')->cleanCache();
        }

        if (intval($this->line_width) < 1) {
          $this->line_width = 3;
        }

        $this->timeline_visible = Mage::getStoreConfig(self::XML_PATH_TIMELINE, null);

        if (strval($this->getRequest()->getParam('timeline')) <> ''){
          $this->timeline_visible = strval($this->getRequest()->getParam('timeline'));
        }
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_TIMELINE, $this->timeline_visible);
        Mage::getModel('core/config')->cleanCache();

        $this->orders_visible = Mage::getStoreConfig(self::XML_PATH_ORDERS, null);

        if (strval($this->getRequest()->getParam('orders')) <> ''){
          $this->orders_visible = strval($this->getRequest()->getParam('orders'));
        }
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_ORDERS, $this->orders_visible);
        Mage::getModel('core/config')->cleanCache();

        $this->loadIndexes();
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $this->removeButton('add');
        return parent::_prepareLayout();
    }

    /**
    * Get Floor Hour
    *
    * @param integer $timestamp
    * @return integer
    */

    protected function hourFloor($timestamp)
    {
        return mktime(date('H', $timestamp), 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp));
    }

    /**
    * Get Ceil Hour
    *
    * @param integer $timestamp
    * @return integer
    */

    protected function hourCeil($timestamp)
    {
        return mktime(date('H', $timestamp) + 1, 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp));
    }

    protected function loadIndexes()
    {
        $collection = Mage::getModel('indexmanager/indexlog')->getCollection()->addFieldToFilter('started_at', array('from'=> strtotime('-'.$this->view_last.' hours', time()),'to'=> time(),'datetime' => true));

        $minDate = null;
        $maxDate = null;

        foreach ($collection as $schedule) {
            $startTime = $schedule->getStartedAt();
            if (empty($startTime)) {
                continue;
            }
            $minDate = is_null($minDate) ? $startTime : min($minDate, $startTime);
            $maxDate = is_null($maxDate) ? $startTime : max($maxDate, $startTime);
            $this->indexes[$schedule->getIndexcode()][] = $schedule;
        }

        $sort_by = array('catalog_product_attribute', 'catalog_product_price', 'catalog_url', 'catalog_category_product', 'catalogsearch_fulltext', 'cataloginventory_stock', 'tag_summary');
        $temp_arr = array();
        foreach ($sort_by as $key) {
            $temp_arr[$key] = $this->indexes[$key];
        }
        foreach ($this->indexes as $ikey => $ival) {
          if (!isset($temp_arr[$ikey])) {
            $temp_arr[$ikey] = $ival;
          }
        }
        $this->indexes = $temp_arr;
        $orders = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('created_at', array('from'=>strtotime('-'.$this->view_last.' hours', time()), 'to'=>time(),'datetime' => true));
        $ordInd=0;
        foreach ($orders as $order) {
          $this->indexes['orders'][$ordInd] = $order;
          $this->indexes['orders'][$ordInd]->setStartedAt($order->getCreatedAt());
          $this->indexes['orders'][$ordInd]->setFinishedAt($order->getCreatedAt());
          $this->indexes['orders'][$ordInd]->setStatus('finished');
          $this->indexes['orders'][$ordInd]->setStatusClass('success');
          $this->indexes['orders'][$ordInd]->setFriendlySource('N/A');
          $this->indexes['orders'][$ordInd]->setIndexcode('orders');
          $ordInd++;
        }

        $this->starttime = $this->hourFloor(strtotime('-'.$this->view_last.' hours', time()));
        $this->endtime = $this->hourCeil(time());
    }

    /**
    * Get Panel Width
    *
    * @return integer
    */

    public function getTimelinePanelWidth()
    {
        return ($this->endtime - $this->starttime) / $this->zoom;
    }

    /**
    * Get Now Line Position
    *
    * @return integer
    */

    public function getNowline()
    {
        return (time() - $this->starttime) / $this->zoom;
    }

    public function getAvailableIndexCodes()
    {
        return array_keys($this->indexes);
    }

    public function getTimelineForCode($code)
    {
        return $this->indexes[$code];
    }

    public function getStarttime()
    {
        return $this->starttime;
    }

    public function getEndtime()
    {
        return $this->endtime;
    }

    /**
    * Get Div Attributes
    *
    * @param string $schedule
    * @return string
    */

    public function getGanttDivAttributes($schedule)
    {

        if ($schedule->getStatus() == 'running') {
            $duration = time() - strtotime($schedule->getStartedAt());
        } else {
            $duration = $schedule->getDuration() ? $schedule->getDuration() : 0;
        }
        $duration = $duration / $this->zoom;
        $duration = ceil($duration / 4) * 4 - 1; // round to numbers dividable by 4, then remove 1 px border
        $duration = max($duration, $this->line_width);

        $offset = (strtotime($schedule->getStartedAt()) - $this->starttime) / $this->zoom;

        if ($offset < 0) { // cut bar
            $duration += $offset;
            $offset = 0;
        }

        $result = sprintf(
            '<div class="task %s" id="id_%s" style="width: %spx; left: %spx;" ></div>',
            $schedule->getStatusClass(),
            $schedule->getId(),
            $duration,
            $offset
        );

        if ($schedule->getStatus() == 'running') {
            $offset += $duration;

            $duration = strtotime($schedule->getEta()) - time();
            $duration = $duration / $this->zoom;

            $result = sprintf(
                '<div class="estimation" style="width: %spx; left: %spx;" ></div>',
                $duration,
                $offset
            ) . $result;
        }

        return $result;
    }
}
