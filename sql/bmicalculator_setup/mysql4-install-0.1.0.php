<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
DROP TABLE IF EXISTS yoma_bmi;
CREATE TABLE yoma_bmi (
  `bmi_id` int(11) unsigned NOT NULL auto_increment,
`customer_id` varchar(10) NOT NULL default '',
  `height` varchar(20) NOT NULL default '',
`weight` varchar(20) NOT NULL default '',
  `gender` varchar(1) NOT NULL default '',
  `waist` varchar(20) NOT NULL default '',
`email` varchar(100) NOT NULL default '',
`activity` varchar(100) NOT NULL default '',
`bmi` varchar(10) NOT NULL default '',
`bmiresult` varchar(100) NOT NULL default '',
`subscribedmyw` varchar(1) NOT NULL,
`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`target` varchar(10) NOT NULL DEFAULT '',
`age` varchar(3) NOT NULL,
`unit` varchar(10) NOT NULL default '',
  PRIMARY KEY (`bmi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 