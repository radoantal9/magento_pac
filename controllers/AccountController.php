<?php
require_once("Mage/Customer/controllers/AccountController.php");
class Yoma_Bmicalculator_AccountController extends Mage_Customer_AccountController
{
    

    /**
     * Define target URL and redirect customer after logging in
     */
    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
            // Set default URL to redirect customer to
            if (Mage::getModel("bmicalculator/bmi")->getMyw8status()):
                $session->setBeforeAuthUrl(Mage::getUrl("bmicalculator/index/myw8"));
            else:
                $session->setBeforeAuthUrl(Mage::getUrl("customer/account/edit"));
            endif;
            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag(
                    Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                )) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        // Rebuild referer URL to handle the case when SID was changed
                        $referer = Mage::getModel('core/url')
                            ->getRebuiltUrl(Mage::helper('core')->urlDecode($referer));
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else if ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else { 
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
            
        }
 
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }

    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        $this->_getSession()->addSuccess(
            $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
        );
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation', Mage::getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation', Mage::getUrl('customer/address/edit'));
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            Mage::app()->getStore()->getId()
        );

        if (Mage::getModel("bmicalculator/bmi")->getMyw8status()):
                $successUrl = Mage::getUrl("bmicalculator/index/myw8", array('_secure'=>true));
            else:
                $successUrl = Mage::getUrl("customer/account/edit", array('_secure'=>true));
  
            endif;
            
  //      $successUrl = Mage::getUrl("bmicalculator/index/myw8", array('_secure'=>true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }
    
    public function createPostAction()
    {
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if ($this->getRequest()->isPost()) {
            $errors = array();

            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }

            /* @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('customer_account_create')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $customer->setIsSubscribed(1);
            }

            /**
             * Initialize customer group id
             */
            $customer->getGroupId();

            if ($this->getRequest()->getPost('create_address')) {
                /* @var $address Mage_Customer_Model_Address */
                $address = Mage::getModel('customer/address');
                /* @var $addressForm Mage_Customer_Model_Form */
                $addressForm = Mage::getModel('customer/form');
                $addressForm->setFormCode('customer_register_address')
                    ->setEntity($address);

                $addressData    = $addressForm->extractData($this->getRequest(), 'address', false);
                $addressErrors  = $addressForm->validateData($addressData);
                if ($addressErrors === true) {
                    $address->setId(null)
                        ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                        ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                    $addressForm->compactData($addressData);
                    $customer->addAddress($address);

                    $addressErrors = $address->validate();
                    if (is_array($addressErrors)) {
                        $errors = array_merge($errors, $addressErrors);
                    }
                } else {
                    $errors = array_merge($errors, $addressErrors);
                }
            }
            
            try {
                $customerErrors = $customerForm->validateData($customerData);
                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                } else {
                    $customerForm->compactData($customerData);
                    $customer->setPassword($this->getRequest()->getPost('password'));
                    $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
                    $customerErrors = $customer->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($customerErrors, $errors);
                    }
                }

                $validationResult = count($errors) == 0;

                if (true === $validationResult) {
                    $customer->save();

                    Mage::dispatchEvent('customer_register_success',
                        array('account_controller' => $this, 'customer' => $customer)
                    );

                    /* 3. Register the bmi measurements */
                    /*if ($this->getRequest()->getParam('subscribed')) {
                        $this->_calculate($this->getRequest()->getPost(), $customer);
                    }*/

                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail(
                            'confirmation',
                            $session->getBeforeAuthUrl(),
                            Mage::app()->getStore()->getId()
                        );
                        $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                        $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
                        return;
                    } else {
                        $session->setCustomerAsLoggedIn($customer);
                        $url = $this->_welcomeCustomer($customer); 
                        
                        Mage::getModel("bmicalculator/observer")->createbmi();
                        $this->_redirectSuccess($url);
                        return;
                    }
                } else {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if (is_array($errors)) {
                        foreach ($errors as $errorMessage) {
                            $session->addError($errorMessage);
                        }
                    } else {
                        $session->addError($this->__('Invalid customer data'));
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $url = Mage::getUrl('customer/account/forgotpassword');
                    $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                    $session->setEscapeMessages(false);
                } else {
                    $message = $e->getMessage();
                }
                $session->addError($message);
            } catch (Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
            }
        }

        $this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
    }
    
    protected function _calculate($postData, $customer) {
        
        $model = Mage::getModel("bmicalculator/bmi");

        $customerid=$customer->getId();

        $collection=$model->getCollection()->addFieldToFilter("customer_id",$customerid);
        if ($collection->getData() ) {
            return false;
        }

        $response=array();
        $bmi=null;
        if ($postData["measure"]=="0") {
            $height=(float)$postData["height"]/100;
            $weight=$postData["weight"];
            $waist=$postData["waist"];
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
            $unit=$response["unit"]="metric";
        } else {
            $height1=$postData["height"]*12;
            $height2=$postData["height-inches"];
            $height=$height1+$height2;
            $idealheight=$height;
            
            $weight1=$postData["weight"]*14;
            $weight2=$postData["weight-inches"];
            $weight=$weight1+$weight2;
            $bmi=round((($weight/($height*$height))*703),2);
            
            $waist=$postData['waist']*2.54;
            
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
            $unit=$response["unit"]="imperial";
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

        /*if ($postData["is_subscribed"]) {
            $status = Mage::getModel('newsletter/subscriber')->subscribe($postData["email"]);
        }*/

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
        $model->setCreatedAt(date('Y-m-d'));
        $model->save();
    }
}