<?php

class Yoma_Bmicalculator_Model_Observer {

        public function createbmi() {
             //$data = Mage::app()->getRequest()->getParams();
             $postData = Mage::app()->getRequest()->getParams();
             if (isset($postData["gender"])):
                    $customerid=Mage::getSingleton("customer/session")->getCustomer()->getId();
                    $response=array();
                    $bmi=null;
                    if (!$postData["measure"]) :
                                $unit="metric";
                                $height=(float)$postData["height"]/100;
                                $weight=$postData["weight"];
                                $bmi=round($weight/($height*$height),2);

                                $waist=$postData["waist"];

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

                    else:
                                $unit="imperial";
                                $height1=$postData["height"]*12;
                                $height2=$postData["height-inches"];
                                $height=$height1+$height2;

                                $weight1=$postData["weight"]*14;
                                $weight2=$postData["weight-inches"];
                                $weight=$weight1+$weight2;
                                $bmi=round((($weight/($height*$height))*703),2);
                                
                                $waist=$postData["waist"]*2.54;

                                $convertedheight=$height*2.54;
                                $convertedweight=$weight*0.453592;

                                                    $response["weight"] = $postData['weight'].'st ';

                                                    if($postData['weight-pounds'] > 0) :
                                                            $response["weight"] .= $postData['weight-pounds'].'lbs';
                                                    endif;

                    endif;

                                /* show imperial height */

                                $inches = $convertedheight/2.54;
                                $feet = intval($inches/12);
                                $inches = $inches%12;

                                $response["height"] = sprintf('%dft %dins', $feet, $inches);

                                $response["bmi"]=$bmi;

                                $response["sex"]=$postData["gender"];
                        $block="";
                        switch ($bmi) :
                            case $bmi<18.5 : $response["bmiresult"]="Underweight"; break;
                            case ($bmi>=18.5 && $bmi < 25 ) : $response["bmiresult"]="Normal"; break;
                            case ($bmi>=25.0 && $bmi < 30 ) : if ($bmi<27): $response["bmiresult"]="Overweight";$block="Overweightsmall";
                                                            else: $response["bmiresult"]="Overweight";$block="Overweightlarge";
                                                                endif;
                                                            break;
                            case $bmi>=30 : $response["bmiresult"]="Obese"; break;
                        endswitch;
                        $subscribed=false;
                        //$subscribed=true; // remove subscribe page
                        if ($postData["subscribed"]) :
                            $subscribed=true;
                        	Mage::getModel("mywinvoices/mywinvoices")->createinvocie($postData["firstname"]." ".$postData["lastname"],$postData["email"]);
                        endif;
                        
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
                            $model->setUnit($unit);
                            $model->setCreatedAt(date("Y-m-d"));

                                    //var_dump($convertedheight); die();	
                        $model->save();                                                           
                 
             endif;
             
             return $this;
        }
        
        public function createBmiWithNoData() 
        {
            $customer = Mage::getSingleton("customer/session")->getCustomer();
            $model = Mage::getModel("bmicalculator/bmi");
            $model->setCustomerId($customer->getId());
            $model->setEmail($customer->getEmail());                              
            $model->setHeight(0);
            $model->setWeight(0);            
            $model->setWaist(0);
            $model->setEmail('');
            $model->setActivity($postData["activity"]);
            $model->setSubscribedmyw(0);
            $model->setBmi(0);
            $model->setBmiresult(0);
            $model->setUnit('imperial');
            $model->setCreatedAt(date("Y-m-d"));
            $model->save();
        }

}
