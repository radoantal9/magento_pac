<?php

class Yoma_Bmicalculator_Adminhtml_BmiController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("bmicalculator/bmi")->_addBreadcrumb(Mage::helper("adminhtml")->__("Bmi  Manager"),Mage::helper("adminhtml")->__("Bmi Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Bmicalculator"));
			    $this->_title($this->__("Manager Bmi"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Bmicalculator"));
				$this->_title($this->__("Bmi"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("bmicalculator/bmi")->load($id);
				if ($model->getId()) {
					Mage::register("bmi_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("bmicalculator/bmi");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Bmi Manager"), Mage::helper("adminhtml")->__("Bmi Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Bmi Description"), Mage::helper("adminhtml")->__("Bmi Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("bmicalculator/adminhtml_bmi_edit"))->_addLeft($this->getLayout()->createBlock("bmicalculator/adminhtml_bmi_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("bmicalculator")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Bmicalculator"));
		$this->_title($this->__("Bmi"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("bmicalculator/bmi")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("bmi_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("bmicalculator/bmi");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Bmi Manager"), Mage::helper("adminhtml")->__("Bmi Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Bmi Description"), Mage::helper("adminhtml")->__("Bmi Description"));


		$this->_addContent($this->getLayout()->createBlock("bmicalculator/adminhtml_bmi_edit"))->_addLeft($this->getLayout()->createBlock("bmicalculator/adminhtml_bmi_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("bmicalculator/bmi")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Bmi was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setBmiData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setBmiData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("bmicalculator/bmi");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('bmi_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("bmicalculator/bmi");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'bmi.csv';
			$grid       = $this->getLayout()->createBlock('bmicalculator/adminhtml_bmi_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'bmi.xml';
			$grid       = $this->getLayout()->createBlock('bmicalculator/adminhtml_bmi_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
