<?php
class Yoma_Bmicalculator_Model_Mysql4_Bmi extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("bmicalculator/bmi", "bmi_id");
    }
}