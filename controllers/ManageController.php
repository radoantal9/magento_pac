<?php
require_once("Mage/Newsletter/controllers/ManageController.php");
class Yoma_Bmicalculator_ManageController extends Mage_Newsletter_ManageController
{
    public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('customer/account/');
        }
        try {
            Mage::getSingleton('customer/session')->getCustomer()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setIsSubscribed((boolean)$this->getRequest()->getParam('is_subscribed', false))
            ->save();
            if ((boolean)$this->getRequest()->getParam('is_subscribed', false)) {
                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been saved.'));
            } else {
                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
            }
            $firstname=Mage::getSingleton('customer/session')->getCustomer()->getFirstname();
            $lastname=Mage::getSingleton('customer/session')->getCustomer()->getLastname();
            $email=Mage::getSingleton('customer/session')->getCustomer()->getEmail();

            Mage::getModel("bmicalculator/bmi")->setMyw8status((boolean)$this->getRequest()->getParam('myw8', false));
            if ((boolean)$this->getRequest()->getParam('myw8', false)) {
                Mage::getSingleton('customer/session')->addSuccess($this->__('The MyW8 subscription has been saved.'));
                Mage::getModel("mywinvoices/mywinvoices")->createinvocie($firstname." ".$lastname,$email);
            } else {
                Mage::getSingleton('customer/session')->addSuccess($this->__('The MyW8 subscription has been removed.'));
            }
        }
        catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
        }
        $this->_redirect('newsletter/manage/');
    }
}
