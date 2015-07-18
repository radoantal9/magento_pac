<?php


class Yoma_Bmicalculator_Block_Adminhtml_Bmi extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_bmi";
	$this->_blockGroup = "bmicalculator";
	$this->_headerText = Mage::helper("bmicalculator")->__("Bmi Manager");
	$this->_addButtonLabel = Mage::helper("bmicalculator")->__("Add New Item");
	parent::__construct();
	$this->_removeButton('add');
	}

}