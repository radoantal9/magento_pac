<?php

class Yoma_Bmicalculator_Block_Adminhtml_Bmi_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("bmiGrid");
				$this->setDefaultSort("bmi_id");
				$this->setDefaultDir("ASC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("bmicalculator/bmi")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("bmi_id", array(
				"header" => Mage::helper("bmicalculator")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "bmi_id",
				));
                
				$this->addColumn("customer_id", array(
				"header" => Mage::helper("bmicalculator")->__("Customer Id"),
				"index" => "customer_id",
				));
				$this->addColumn("height", array(
				"header" => Mage::helper("bmicalculator")->__("Height"),
				"index" => "height",
				));
				$this->addColumn("weight", array(
				"header" => Mage::helper("bmicalculator")->__("Weight"),
				"index" => "weight",
				));
				$this->addColumn("gender", array(
				"header" => Mage::helper("bmicalculator")->__("Gender"),
				"index" => "gender",
				));
				$this->addColumn("waist", array(
				"header" => Mage::helper("bmicalculator")->__("Waist"),
				"index" => "waist",
				));
				$this->addColumn("email", array(
				"header" => Mage::helper("bmicalculator")->__("Email"),
				"index" => "email",
				));
                                $this->addColumn("activity", array(
				"header" => Mage::helper("bmicalculator")->__("Activity"),
				"index" => "activity",
				));
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return '#';
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('bmi_id');
			$this->getMassactionBlock()->setFormFieldName('bmi_ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_bmi', array(
					 'label'=> Mage::helper('bmicalculator')->__('Remove Bmi'),
					 'url'  => $this->getUrl('*/adminhtml_bmi/massRemove'),
					 'confirm' => Mage::helper('bmicalculator')->__('Are you sure?')
				));
			return $this;
		}
			

}