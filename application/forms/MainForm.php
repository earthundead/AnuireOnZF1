<?php

class Application_Form_MainForm extends Zend_Form
{
	public $textIntro;
	public $textAfter;
	public $options;
	
	public $select1Start;
	public $select2Start;
	
    public function init()
		{
        // Set the method for the display form to POST
        $this->setMethod('post');
        
		//Создание элементов формы
		$element = new Zend_Form_Element_Hidden('intro');
		$element->setRequired(true);
		$element->setValue("1");
		$this->addElement($element);
		
		$element = new Zend_Form_Element_Select('select1');
		$element->setRequired(true);
		$element->setLabel('Начальный пункт:');
		$element->setDescription('Это город или порт отправления');
		//$element->setValue("1");
		$this->addElement($element);
		
		$element = new Zend_Form_Element_Select('select2');
		$element->setRequired(true);
		$element->setLabel('Конечный пункт:');
		$element->setDescription('Это город или порт назначения');
		//$element->setValue("0");
		$this->addElement($element);
		
		$element  = new Zend_Form_Element_Hidden('after');
		$element -> setRequired(true);
		$element -> setValue("1");
		$this    -> addElement($element);
   
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Получить маршрут',
        ));
 
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
        
		$this->refresh();
    }
    
    public function refresh()
	{
	//Заполнение полей дефолтное
	if(!isset($this->textIntro))
		$this->textIntro=
			"Приветствуем благородного дона, путешествующего по нашей стране. 
			Ануир это лучшая страна в мире. Все остальные страны нам завидуют. 
			Никакие проблемы и неприятности никак не могут изменить это.
			Этот путеводитель предоставит краткую информацию о Ануире и поможет вам спланировать ваше путешествие.
			";
	if(!isset($this->textAfter))
		$this->textAfter=
			'Уважаемый дон, извольте выбрать пункт отправления и пункт назначения из списка в полях вверху , 
			нажать кнопку "Получить маршрут" и маршрут вашего путешествия по нашей великой стране отобразится на карте. 
			';
	if(!isset($this->options))
		$this->options=array
			(
			"none","Option1","Option1","Option1","Option1","Option1","Option1","Option1",
			"Option1","Option1","Option1","Option1","Option1","Option1","Option1","Option1",
			"Option2","Option1","Option1","Option1","Option1","Option1","Option1","Option1",
			"Option3","Option1","Option1","Option1","Option1","Option1","Option1","Option1",
			"Option4","Option1","Option1","Option1","Option1","Option1","Option1","Option1",
			);
			
	//обновление		
	$element = $this->getElement("intro");
	$element->setLabel($this->textIntro);
	
	$element = $this->getElement("select1");
	$element->clearMultiOptions();
	$element->addMultiOptions($this->options);
	
	$element = $this->getElement("select2");
	$element->clearMultiOptions();
	$element->addMultiOptions($this->options);
	
	$element = $this->getElement("after");
	$element->setLabel($this->textAfter);
	}
}
