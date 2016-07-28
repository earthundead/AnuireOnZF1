<?php
require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action {

	public function indexAction() 
	{
    }
        
	public function viewtestAction() 
	{
		$registry = Zend_Registry::getInstance();
		$applicationFilesPath = $registry -> get('applicationFilesPath');
		$this->view->filesPath = $applicationFilesPath;
    }
        
    public function viewimageAction() {

    }
    
    public function viewlogfileAction() 
    {
		$registry = Zend_Registry::getInstance();
		$dataFilesPath = $registry -> get('dataFilesPath');
		$this->view->filesPath = $dataFilesPath;
    }
    
    public function viewformAction() 
        {
	    $request = $this->getRequest();
        $form    = new Application_Form_MainForm();
        $form -> setAction('viewform');
 
        if ($this->getRequest()->isPost()) 
            {
            if ($form->isValid($request->getPost())) 
                {
				$values = $form->getValues();
				$startPointNo  = $values['select1'];
				$endPointNo  = $values['select2'];
				
				$registry  = Zend_Registry::getInstance();
				$programDb = $registry -> get('programDB');	
				$model     = $registry -> get('model');
				
				$model -> db = $programDb;
				$model -> startPoint = $startPointNo;
				$model -> endPoint = $endPointNo;
				$model -> refresh();
				$modelText = $model->textPathComment;
				
				if(isset($modelText))
					$form->textAfter = $modelText;
                //return $this->_helper->redirector('viewform');
                }
            }
 
        $this->view->form = $form;
        }
        
    public function viewtableAction() 
    {
	$registry  = Zend_Registry::getInstance();
	$programDb = $registry -> get('programDB');	
	$model     = $registry -> get('model');
	
	$model -> db = $programDb;
	
	$Table=$model->db->Get("AnuireLocations");
	$Header=array("No","X","Y","Название","Описание","Размер");
	$Name="Локации Ануира";
	$Headers[]=$Header;
	$Tables[]=$Table;
	$Names[]=$Name;

	$Table=$model->db->Get("RoadsView");
	$Header=array("No","Откуда","Куда");
	$Name="Дороги Ануира";
	$Headers[]=$Header;
	$Tables[]=$Table;
	$Names[]=$Name;

	$Table=$model->db->Get("Points");
	$Header=array("No","X","Y","Z");
	$Name="Точки на карте";
	$Headers[]=$Header;
	$Tables[]=$Table;
	$Names[]=$Name;
	
	//$view = new Zend_View();
	$this->view->Tables = $Tables;
	$this->view->Headers = $Headers;
	$this->view->Names = $Names;
	
	//echo $view->render('viewtable.phtml');
    }
    
    public function recreatedbAction() 
    {
	$registry  = Zend_Registry::getInstance();
	$programDb = $registry -> get('programDB');	
	$model     = $registry -> get('model');
	
	echo "Запуск пересоздания БД <br>";
	$programDb ->RecreateDB();
	$model -> db = $programDb;
	echo " БД была пересоздана";
    }
}
