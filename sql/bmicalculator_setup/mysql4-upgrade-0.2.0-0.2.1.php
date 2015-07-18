<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
ALTER TABLE  yoma_bmi ADD  
    `age` varchar(3) NOT NULL;
SQLTEXT;


$installer->run($sql);

$installer->endSetup();
	 