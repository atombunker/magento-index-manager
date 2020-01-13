<?php

/**
 * @category 	  Upment
 * @package 	  Upment_Indexmanager
 * @copyright 	Copyright (c) 2019 Upment (https://www.upment.com/)
 */

/**
 * Indexmanager SQL Setup
 *
 * @category 	Upment
 * @package 	Upment_Indexmanager
 * @author  	Upment
 */

$installer = $this;
$installer->startSetup();

/**
 * Create upment_indexes table
 */

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('upment_indexes')};
CREATE TABLE {$this->getTable('upment_indexes')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `indexcode` varchar(80) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

/**
 * Create upment_indexlog table
 */

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('upment_indexlog')};
CREATE TABLE {$this->getTable('upment_indexlog')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `indexcode` varchar(80) NOT NULL,
	`started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `status` varchar(80) NOT NULL,
  `source` varchar(255) NOT NULL,
  `output` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->run($sql);

$installer->endSetup();
