<?php
require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action {

	public function indexAction() 
	{
    }
        
	public function viewtestAction() {

    }
        
    public function viewimageAction() {

    }
    
    public function viewlogfileAction() {

    }
    
    public function viewformAction() 
        {
	    $request = $this->getRequest();
        $form    = new Application_Form_MainForm();
        $form->setAction('viewform');
 
        if ($this->getRequest()->isPost()) 
            {
            if ($form->isValid($request->getPost())) 
                {
				$values = $form->getValues();
				$startPointNo  = $values['select1'];
				$endPointNo  = $values['select2'];
				$registry = Zend_Registry::getInstance();
				$registry -> set('startPointId', $startPointNo);	//Здесь id совпадает с номером
				$registry -> set('endPointId', $endPointNo);
				
				$model = $registry -> get('model');
				$model -> findWay2();
				$model -> redrawPicture();
				
				$form->textAfter = $model->textPathComment;
                //return $this->_helper->redirector('viewform');
                }
            }
 
        $this->view->form = $form;
        }
        
    public function viewtableAction() {

    }
    
    public function recreatedbAction() {

    }
}
