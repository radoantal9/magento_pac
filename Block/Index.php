<?php
class Yoma_Bmicalculator_Block_Index extends Mage_Core_Block_Template{

    public function getActionUrl() {
        $url=Mage::getUrl('bmicalculator/index/calculate');
        return $url;
    }

    public function getTargetUrl() {
        $url=Mage::getUrl('bmicalculator/index/target');
        return $url;
    }

    public function getUserData() {
        $customerid=Mage::getSingleton("customer/session")->getCustomer()->getId();
        $collection=Mage::getModel("bmicalculator/bmi")->getCollection()->addFieldToFilter("customer_id",$customerid)->setOrder('created_at', 'ASC');
        return $collection;
    }

    public function getMeasureUnit() {
        $customerid=Mage::getSingleton("customer/session")->getCustomer()->getId();
        $collection=Mage::getModel("bmicalculator/bmi")->getCollection()->addFieldToFilter("customer_id",$customerid)->addFieldToFilter("unit", array('in' => array('imperial', 'metric')));
        $unit = '';
        if ($collection->getData() ) {
            $data = $collection->getFirstItem()->getData();
            if ($data['unit']!='') {
                $unit = $data['unit'];
            }else {
                $item = $collection->getFirstItem();
                $item->setUnit('imperial');
                $item->save();
            }
        }
        if ($unit == '') {
            $unit = 'imperial';            
        }
        return $unit;
    }

    public function getPlans() {
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orders = $orderCollection->addAttributeToFilter("customer_id", Mage::getSingleton('customer/session')->getCustomer()->getId())->addAttributeToFilter('state', 'complete');

        $purchased = array(); // will contain IDs of purchased items
        foreach ($orders as $order){
            $items = $order->getAllVisibleItems();
            foreach ($items as $item) :
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                Mage::log($item->debug(),null,"sandor.log",true);
            if ($item->getProductType() == "bundle"):
                if($product->getBackgroundColour()) {
                    $background = 'style="background-color: '. $product->getBackgroundColour() .';"';
                } else {
                    $background = '';
                }
				if($product->getPlanType()) {
					$pdfDownload = '<a class="plan-pdf" href="/media/plan-pdf/'. $product->getAttributeText('plan_type') .'.pdf" target="_blank"><span></span>'. $this->__('Download PDF') .'</a>';
				} else {
					$pdfDownload = '';
				}


                if ( stripos( $item->getName(), 'rapid' ) !== FALSE ) {
                    $itemName = 'Rapid';
                } elseif ( stripos( $item->getName(), 'regular' ) !== FALSE ) {
                    $itemName = 'Regular';
                } elseif ( stripos( $item->getName(), '50/50' ) !== FALSE ) {
                    $itemName = '50 / 50';
                } elseif ( stripos( $item->getName(), 'd2' ) !== FALSE ) {
                    $itemName = 'D2';
                } elseif ( stripos( $item->getName(), 'new you' ) !== FALSE ) {
                    $itemName = 'New You';
                } else {
                    $itemName = $item->getName();
                }

                $purchased[] = '
                <div id="myplan" '. $background .'>
                    <p>'.$itemName.'</p>
                </div>
				'. $pdfDownload;

            endif;
            endforeach;
        }
        return $purchased;
    }



}
 