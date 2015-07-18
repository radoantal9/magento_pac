<?php
class Yoma_Bmicalculator_Block_Adminhtml_Customer_Edit_Tab_Action extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        $this->setTemplate('bmicalculator/action.phtml');
    }
    
    public function getCustomtabInfo(){
        $customer = Mage::registry('current_customer');
        $customtab='My Custom tab Action Contents Here';
        return $customer;
    }

    public function getTabLabel()
    {
        return $this->__('MyW8');
    }
    
    public function getTabTitle()
    {
        return $this->__('MyW8');
    }
    
    /**
    * Can show tab in tabs
    *
    * @return boolean
    */
    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');
        return (bool)$customer->getId();
    }
    
    public function isHidden()
    {
    return false;
    }
    
    public function getAfter()
    {
        return 'tags';
    }
    
    public function getCollection() {
        $customerid=Mage::registry('current_customer')->getId();
        $filtered=Mage::getModel("bmicalculator/bmi")->getCollection()->addFieldToFilter("customer_id",$customerid);
        return $filtered;
    }
}
?>

