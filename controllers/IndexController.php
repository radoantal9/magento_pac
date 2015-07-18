<?php
class Yoma_Bmicalculator_IndexController extends Mage_Core_Controller_Front_Action{
    /**
 * Checking if user is logged in or not
 * If not logged in then redirect to customer login
 */
    public function preDispatch()
    {
        parent::preDispatch();

        $action = $this->getRequest()->getActionName();
        $openActions = array(
            'index',
            'calculate',
            'target',
            'return',
            'returncustomer'
        );
        $pattern = '/^(' . implode('|', $openActions) . ')/i';

        if (!preg_match($pattern, $action)) {
            if (!Mage::getSingleton('customer/session')->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        } 
    }

    public function IndexAction() {
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("BMI Calculator"));
	  $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
	  ));

      $breadcrumbs->addCrumb("bmi calculator", array(
                "label" => $this->__("BMI Calculator"),
                "title" => $this->__("BMI Calculator")
	  ));
      $this->renderLayout();
    }
    
    public function CalculateAction () {
         if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
			$current = Mage::getModel("bmicalculator/bmi");

            if (Mage::getSingleton("customer/session")->getCustomer()->getId()) {
                $customerid=Mage::getSingleton("customer/session")->getCustomer()->getId();
            } else {
                $customerid="guest";
            }

            $response=array();
            $bmi=null;
            $entryDate = $postData['date_measurement'];
            $entryDate = explode('/', $entryDate);
            if ($postData["measure"]) {
                    $height=(float)$postData["height"]/100;
                    $waist=$postData["waist"];
                    $weight=$postData["weight"];
                    $bmi=round($weight/($height*$height),2);
                    
                    $convertedheight=$postData["height"];
                    $convertedweight=$weight;
					$response["weight"] = $convertedweight;
					
					$weightPounds = $convertedweight * 2.20462;
					$weightStone = round($weightPounds * 0.0714286,0);
					$weightPounds = round((($weightPounds * 0.0714286) - $weightStone) * 14,0);
					$response["weight"] = $weightStone.'st ';
					
					if($weightPounds > 0):
						$response["weight"] .= $weightPounds.'lbs';
					endif;
					$idealheight=$postData["height"]/2.54;
					$idealminWeightPounds = (18.5/703)*$idealheight * $idealheight;
					$idealmaxWeightPounds = (24.99/703)*$idealheight * $idealheight;
					$idealminWeightKg = round(($idealminWeightPounds/2.2) ,2);
					$idealmaxWeightKg = round(($idealmaxWeightPounds/2.2),2);
					$response["idealWeightmin"]=$idealminWeightKg." kg";
					$response["idealWeightmax"]=$idealmaxWeightKg." kg";
                    $response["unit"]="metric";
            } else {
                    $height1=$postData["height"]*12;
                    $height2=$postData["height-inches"];
                    $height=$height1+$height2;
                    $idealheight=$height;
                    
                    $waist=$postData['waist']*2.54;
                    
                    $weight1=$postData["weight"]*14;
                    $weight2=$postData["weight-pounds"];
                    $weight=$weight1+$weight2;
                    $bmi=round((($weight/($height*$height))*703),2);
                    
                    $convertedheight=$height*2.54;
                    $convertedweight=$weight*0.453592;
					
					$response["weight"] = $postData['weight'].'st ';
					
					if($postData['weight-pounds'] > 0) {
						$response["weight"] .= $postData['weight-pounds'].'lbs';
					}
					
					$idealminWeightPounds = round(((18.5/703)*$idealheight * $idealheight),2);
					$idealmaxWeightPounds = round(((24.99/703)*$idealheight * $idealheight),2);
					$response["idealWeightmin"]=$idealminWeightPounds." lbs";
					$response["idealWeightmax"]=$idealmaxWeightPounds." lbs";
                    $response["unit"]="imperial";
            }
			
			/* show imperial height */
			
			$inches = $convertedheight/2.54;
			$feet = intval($inches/12);
			$inches = $inches%12;
			
			$response["height"] = sprintf('%dft %dins', $feet, $inches);

			$response["bmi"]=$bmi;
			
			$response["sex"]=$postData["gender"];
			$response["overweightsmall"] = '';
            $block="";
            switch ($bmi) {
                case $bmi<18.5 : 
                    $response["bmiresult"]="Underweight"; 
                    break;
                case ($bmi>=18.5 && $bmi < 25 ) : 
                    $response["bmiresult"]="Normal"; 
                    break;
                case ($bmi>=25.0 && $bmi < 30 ) : 
                    if ($bmi<27) { 
                        $response["bmiresult"]="Overweight";
                        $block="Overweightsmall"; 
                        $response["overweightsmall"] = 'Overweightsmall';
                    } else { 
                        $response["bmiresult"]="Overweight";
                        $block="Overweightlarge";
                    }
                    break;
                case $bmi>=30 : 
                    $response["bmiresult"]="Obese"; 
                    break;
			}
			
			$response['diabetic'] = 'no';
			
			if($postData["diabetic"] == 'yes') {
				$response["diabetic"] = 'yes';
			}
			
            
            $subscribed=false;
            $subscribed=true; // remove subscribe page
            if ($postData["subscribed"]) {
                $subscribed=true;
            }
            if ($postData["tcs"]) {
                $model=Mage::getModel("bmicalculator/bmi");
                $model->setCustomerId($customerid);
                $model->setHeight($convertedheight);
                $model->setWeight($convertedweight);
                $model->setGender($postData["gender"]);
                $model->setWaist($waist);
                $model->setEmail($postData["email"]);
                $model->setActivity($postData["activity"]);
                $model->setSubscribedmyw($subscribed);
                $model->setBmi($response["bmi"]);
                $model->setBmiresult($response["bmiresult"]);
                $model->setUnit($response["unit"]);
                $model->setCreatedAt($entryDate[2] . '-' . $entryDate[0] . '-' . $entryDate[1]);
                $model->save();
                $status = Mage::getModel('newsletter/subscriber')->subscribe($postData["email"]);
		    }
			/* using the The Lorentz's formula (ideal bmi of 21.5
			 * Man = 50 + [(height in cm – 150) x 0.6]
			   Female = 50 + [(height in cm – 150) x 0.7] 
			 */
			/* ld ideal weight
			$idealWeightMetric = 50 + (($convertedheight - 150) * 0.7);
			
			$idealWeightPounds = $idealWeightMetric * 2.20462;
			
			$idealWeightStone = round($idealWeightPounds * 0.0714286,0);
			
			$idealWeightPounds = round((($idealWeightPounds * 0.0714286) - $idealWeightStone) * 14,0);
			
			$response["idealWeight"] = $idealWeightStone.'st ';
			
			if($idealWeightPounds > 0) {
				$response["idealWeight"] .= $idealWeightPounds.'lbs';
			}
			old ideal weight end */

			if($response["diabetic"] == 'yes' AND ($response["bmiresult"]!="Underweight")) {
				$response["bmitext"]=$this->getLayout()->createBlock('cms/block')->setBlockId("diabetic_bmi")->toHtml();	
            } elseif ($response["bmiresult"]=="Overweight") {
                $response["bmitext"]=$this->getLayout()->createBlock('cms/block')->setBlockId($block."_bmi")->toHtml();
			} else {
                $response["bmitext"]=$this->getLayout()->createBlock('cms/block')->setBlockId($response["bmiresult"]."_bmi")->toHtml();
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));         
         }
    }
    
    public function TargetAction () {
         if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $current=Mage::getModel("bmicalculator/bmi");
            
            $response=array();
            $bmi=null;
            if ($postData["targetweight"]) {
                $customerid=Mage::getSingleton("customer/session")->getCustomer()->getId();
                $collection=Mage::getModel("bmicalculator/bmi")->getCollection()->addFieldToFilter("customer_id",$customerid);
                foreach ($collection as $item) {
                    $item->setTarget((float)$postData["targetweight"]);
                    $item->setAge((float)$postData["age"]);
                    $item->save();
                    $height=$item->getHeight();
                    $gender=$item->getGender();
                }
                $height=(float)$height/100;
                $weight=$postData["targetweight"];
                $age=$postData["age"];
                $bmi=round($weight/($height*$height),2);
                //$calorie=round((2.20462*$weight)*13,0);
                if ($gender) {
                    $calorie=(10*$weight)+(6.25*$height*100)-(5*$age)+5;
                } else {
                    $calorie=(10*$weight)+(6.25*$height*100)-(5*$age)-161;
                }
            }
            $pounds=2.20462*$weight;
            $stones=(int)($pounds/14);
            $pounds=(int)($pounds-($stones*14));
            
            $response["stones"]=$stones . "st " . $pounds."lbs";
            $response["calorie"]=$calorie;
            $response["bmi"]=$bmi;
                               
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
         }
    }
   
    public function ShowAction() {
      
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("My Weight"));

      $this->renderLayout(); 
	  
    }
    public function Myw8Action() {
      
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Access to MYW8"));

      $this->renderLayout(); 
	  
    }
    public function clearallAction() {
        $customerid=Mage::getSingleton("customer/session")->getCustomer()->getId();
        $collection=Mage::getModel("bmicalculator/bmi")->getCollection()->addFieldToFilter("customer_id",$customerid);
        foreach ($collection as $item) {
          $item->delete();
        }
        $model=Mage::getModel("bmicalculator/bmi");
        $model->setCustomerId($customerid);
        $model->setSubscribedmyw(1);

		//var_dump($convertedheight); die();	
        $model->save();
        Mage::getSingleton('customer/session')->addSuccess($this->__('Data cleared.'));
        $this->_redirectUrl(Mage::getUrl("bmicalculator/index/myw8"));
        return true;
    }

	public function UpdatesubscriptionAction() {        
        $customerid=Mage::getSingleton("customer/session")->getCustomer()->getId();
        $collection=Mage::getModel("bmicalculator/bmi")->getCollection()->addFieldToFilter("customer_id",$customerid);                
        if ( $collection->count() == 0 ) {
            Mage::getModel("bmicalculator/observer")->createBmiWithNoData();
        }
		Mage::getModel("bmicalculator/bmi")->setMyw8status(true);        
		$this->_redirectUrl(Mage::getUrl("bmicalculator/index/myw8"));
		return true;
	}
    
    public function clearlastAction() {   
        $customerid=Mage::getSingleton("customer/session")->getCustomer()->getId();
        $collection=Mage::getModel("bmicalculator/bmi")->getCollection()->addFieldToFilter("customer_id",$customerid);
        $collection->getLastItem()->delete();
        $collection=Mage::getModel("bmicalculator/bmi")->getCollection()->addFieldToFilter("customer_id",$customerid);
        if (count($collection)<1) {
            $model=Mage::getModel("bmicalculator/bmi");
            $model->setCustomerId($customerid);
            $model->setSubscribedmyw(1);
        }
        
        Mage::getSingleton('customer/session')->addSuccess($this->__('Last Bmi Data cleared.'));
        $this->_redirectUrl(Mage::getUrl("bmicalculator/index/myw8"));
        return true;	  
    }
    
    public function returncustomerAction() {
        $postData = $this->getRequest()->getPost();
        $customerCollection = Mage::getModel('customer/customer')->getCollection();
        /* @var $customerCollection Mage_Customer_Model_Entity_Customer_Collection */
        $customerCollection->addAttributeToSelect(array(
        'username', 'password', 'email'
        ));                                                                                                    
        $customerCollection->addAttributeToFilter('username',$postData["usern"]);
        //$customerCollection->addAttributeToFilter("password",$postData["passw"] );
        $response["count"]=count($customerCollection);
        $response["status"] = false;
        if (count($customerCollection)) {
            $response["email"]=$customerCollection->getFirstItem()->getEmail();
            try{  
                $id=1;
                $response["status"] = Mage::getModel('customer/customer')->setWebsiteId($id)->authenticate($response["email"], $postData["passw"]);  
            }catch( Exception $e ){  
                $response["status"] = false;  
            } 
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
    
    public function returnAction() {
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Return Customers Username Reveal"));

      $this->renderLayout(); 
    }
	
	public function updatehistoryAction() {
        $unit = $this->getRequest()->getParam('unit', false);
		$weight = $this->getRequest()->getParam('weight', false);
        $weight_lb = $this->getRequest()->getParam('weight_lb', false);
        $waist = $this->getRequest()->getParam('waist', false);
        $date = $this->getRequest()->getParam('bmi_date', false);
		$bmi_id = $this->getRequest()->getParam('bmi_id', false);
        $bmi_type = $this->getRequest()->getParam('bmi_type', false);
		$response = array();
        
        $customerid=Mage::getSingleton("customer/session")->getCustomer()->getId();
        $collection=Mage::getModel("bmicalculator/bmi")->getCollection()->addFieldToFilter("customer_id",$customerid)->addFieldToFilter("bmi_id", $bmi_id);
        $height = 1;
        if ($collection->getData() ) {
            $data = $collection->getFirstItem()->getData();
            if ($data['height']!='') {
                $height = $data['height'] / 100;
            }
        }
        
		try{
			$model = Mage::getModel("bmicalculator/bmi")->load($bmi_id);
            
            if ($weight != "") {
                if ($unit == "imperial") {
                    $weight = round($weight*6.3503, 2) + round($weight_lb*0.4536, 2);
                    $bmi=round(($weight/($height*$height)),2);
                    $waist*=2.54;
                } else { //metric
                    $bmi=round($weight/($height*$height),2);
                }
                
            }
            if ($date) {
                $datetime = DateTime::CreateFromFormat('d/m/Y', $date);
                $datetime = $datetime->format('Y-m-d');
            }

            switch($bmi_type) {
                case 'default' :
                    $model->setWeight($weight)->setBmi($bmi)->setWaist($waist)->setCreated_at($datetime)->save();
                    break;
                    
                case 'target_weight' :
                    $model->setTarget($weight)->save();
                    $response['bmi_type'] = $bmi_type;
                    break;
            }
            $response['status'] = true;
            $response['weight'] = $weight;
            $response['weight_lb'] = $weight_lb;
            $response['datetime'] = $date;
            $response['unit'] = $unit;
            $response['bmi'] = $bmi;
		} catch( Exception $e) {
			$response['status'] = false;
		}
      	
      	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
	}
	
	public function deletehistoryAction() {
		$bmi_id = $this->getRequest()->getParam('bmi_id', false);
		$response = array();
		try{
			$model = Mage::getModel("bmicalculator/bmi")->load($bmi_id);
			$model->setId($bmi_id)->delete();
			$response['bmi_id'] = $bmi_id;
			$response['status'] = true;
		} catch( Exception $e) {
			$response['bmi_id'] = $bmi_id;
			$response['status'] = false;
		}
      	
      	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
	}
	
	
} 