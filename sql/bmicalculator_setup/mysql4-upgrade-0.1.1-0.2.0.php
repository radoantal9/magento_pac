<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
ALTER TABLE  yoma_bmi ADD  
    `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP;
  ALTER TABLE  yoma_bmi ADD `target` varchar(10) NOT NULL default "";
SQLTEXT;


$installer->run($sql);

$installer->endSetup();
	 