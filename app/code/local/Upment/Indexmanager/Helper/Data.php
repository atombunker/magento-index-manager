<?php

/**
 * @category 	  Upment
 * @package 	  Upment_Indexmanager
 * @copyright 	Copyright (c) 2019 Upment (https://www.upment.com/)
 */

/**
 * Indexmanager Helper
 *
 * @category 	Upment
 * @package 	Upment_Indexmanager
 * @author  	Upment
 */

class Upment_Indexmanager_Helper_Data extends Mage_Core_Helper_Abstract
{

  /**
   * Decorate status values
   *
   * @param $status
   * @return string
   */
  public function decorateStatus($status)
  {
      switch ($status) {
          case 'finished':
              $result = '<span class="bar-green"><span>' . $status . '</span></span>';
              break;
          case 'running':
              $result = '<span class="bar-yellow"><span>' . $status . '</span></span>';
              break;
          case 'partial':
              $result = '<span class="bar-orange"><span>' . $status . '</span></span>';
              break;
          case 'failed':
              $result = '<span class="bar-red"><span>' . $status . '</span></span>';
              break;
          case 'retried':
              $result = '<span class="bar-blue"><span>' . $status . '</span></span>';
              break;
          default:
              $result = $status;
              break;
      }
      return $result;
  }

  /**
   * Get friendly name
   *
   * @param $codeName
   * @return string
   */

  public function getFriendlyName($codeName)
  {
    if ($codeName == 'orders') {
      $friendly = "Orders";
    } else {
      $collection = Mage::getSingleton('index/indexer')->getProcessesCollection();
      foreach ($collection as $collect) {
        if ($collect->getIndexerCode() == $codeName) $friendly = $collect->getIndexer()->getName();
      }
    }
    return $friendly;
  }

  /**
   * Decorate time values
   *
   * @param string $value
   * @param bool $echoToday if true "Today" will be added
   * @param string $dateFormat make sure Y-m-d is in it, if you want to have it replaced
   * @return string
   */
  public function decorateTime($value, $echoToday = false, $dateFormat = null)
  {
      if (empty($value) || $value == '0000-00-00 00:00:00') {
          $value = '';
      } else {
          $value = Mage::getModel('core/date')->date($dateFormat, $value);
          $replace = array(
              Mage::getModel('core/date')->date('Y-m-d ', time()) => $echoToday ? Mage::helper('indexmanager')->__('Today') . ', ' : '', // today
              Mage::getModel('core/date')->date('Y-m-d ', strtotime('-1 day')) => Mage::helper('indexmanager')->__('Yesterday') . ', ',
          );
          $value = str_replace(array_keys($replace), array_values($replace), $value);
      }
      return $value;
  }

  /**
   * Diff between to times;
   *
   * @param $time1
   * @param $time2
   * @return int
   */
  public function dateDiff($time1, $time2 = null)
  {
      if (is_null($time2)) {
          $time2 = Mage::getModel('core/date')->date();
      }
      $time1 = strtotime($time1);
      $time2 = strtotime($time2);
      return $time2 - $time1;
  }

}
