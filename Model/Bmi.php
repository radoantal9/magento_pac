<?php

class Yoma_Bmicalculator_Model_Bmi extends Mage_Core_Model_Abstract
{
    protected function _construct(){

       $this->_init("bmicalculator/bmi");

    }
    
    public function getMyw8status() {
        $customerid=Mage::getSingleton("customer/session")->getCustomer()->getId();
        $collection=Mage::getModel("bmicalculator/bmi")->getCollection()->addFieldToFilter("customer_id",$customerid);
        $enabled=false;
        foreach ($collection as $item) :
            if ($item->getSubscribedmyw()) :
                $enabled=true;
            endif;
        endforeach;
        
        return $enabled;
    }
    
    public function setMyw8status($data) {
        $customerid=Mage::getSingleton("customer/session")->getCustomer()->getId();
        $collection=Mage::getModel("bmicalculator/bmi")->getCollection()->addFieldToFilter("customer_id",$customerid);
        $enabled=false;
        foreach ($collection as $item) :
            $item->setSubscribedmyw($data);
            $item->save();
            
        endforeach;
        if (count($collection)<1):
                        $model=Mage::getModel("bmicalculator/bmi");
                        $model->setCustomerId($customerid);
                        $model->setSubscribedmyw(1);
        endif;
        
        return true;
    }

}
	 