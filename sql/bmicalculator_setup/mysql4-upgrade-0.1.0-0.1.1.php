<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
ALTER TABLE  yoma_bmi ADD  `subscribedmyw` varchar(1) NOT NULL		
SQLTEXT;


$installer->run($sql);

$installer->endSetup();
	 