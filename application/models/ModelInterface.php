<?php

include_once "euFunctions.php";
include_once "WaveGraph.php";
include_once "Images.php";

$Description = "основной модуль тестовой программы на PHP пользователя earthundead";
Out("попытка подключения $Description");

class Application_Model_ModelInterface
	{
	public $db;
	public $locationsList;
	public $startPoint;
	public $endPoint;
	public $textPathComment;
	
	private $path;
	private $pathFinder;
	

	public function Application_Model_ModelInterface()
		{
		//$this->init();
		}
		
	public function init()
		{

		}

	public function recreateAllDB()
		{
		$this->db->RecreateDB();
		}
	
	public function refresh()
		{
		//Подготовка
		if( !isset( $this -> db ))
			return;
		if( !isset( $this -> locationsList ))
			$this -> locationsList = $this->db->Get("AnuireLocations",-1,"Name");		//Получаем все строки из таблицы локаций с заданным именем колонки
		$this->pathFinder = new Application_Model_WaveGraph;
		$PointsTable = $this -> db -> Get("AnuireLocations");
		$RoadsTable  = $this -> db -> Get("Roads");
		$this->pathFinder->PointsTable = $PointsTable;
		$this->pathFinder->RoadsTable  = $RoadsTable;
		
		//Выполнение	
		$this -> findWayById();
		$this -> constructTextComment($this->path);
		$this -> redrawPicture();
		}
	
	private function findWayByName()
		{
		$startPoint = $this->startPoint;
		$endPoint   = $this->endPoint;
		if(is_numeric($startPoint))
			return;
		if(is_numeric($endPoint))
			return;
			
		$DBid1=$this->db->Find("AnuireLocations","Name=\"$startPoint\""); 	//Получаем id из таблицы локаций с заданным именем
		$DBid2=$this->db->Find("AnuireLocations","Name=\"$endPoint\"");

		if(isset($DBid1)&&isset($DBid2))
			{
			$this->startPoint = $DBid1;
			$this->endPoint   = $DBid2;
			$this->findWayById();  											//При помощи графов находим путь из начальной точки к конечной
			}
		}
		
	private function findWayById()
		{
		$startPoint = $this->startPoint;
		$endPoint   = $this->endPoint;
		if(! is_numeric($startPoint))
			return;
		if(! is_numeric($endPoint))
			return;
				
		$count = count($this->locationsList);
		$id1 = $this->startPoint;
		if($id1<1 || $id1>$count)
			{
			Out("Ошибка в нахождении пути. Несовпадение id локации ($id1)");
			return;
			}
		$id2 = $this->endPoint;
		if($id2<1 || $id2>$count)
			{
			Out("Ошибка в нахождении пути. Несовпадение id локации ($id2)");
			return;
			}
		if($id2==$id1)
			{
			Out("Ошибка в нахождении пути. совпадение id локации ($id1 == $id2 )");
			return;
			}

		$this -> path = $this -> pathFinder -> FindWay($this->startPoint,$this->endPoint);  				//При помощи графов находим путь из начальной точки к конечной
		}
			

	private function redrawPicture()
		{
		//Прерисуем и сохраним картинку, отметив маршрут
		$img = imagecreate(1100, 1100);
						
		$images = new Application_Model_Images;
		$images -> img = $img;

		$LocationsTable=$this->db->Get("AnuireLocations");
		$RoadsTable=$this->db->Get("Roads");
		$PointsTable=$this->db->Get("Points");

		//$images->DrawPoints($PointsTable);
		$images->DrawNamedPoints($LocationsTable);
		$images->DrawLines($RoadsTable);
		if(isset($this->path))
			{
			$PathLines = $this -> pathFinder -> ConstructLines($this->path);
			if(isset($PathLines))
				{
				$images->DrawArrows($PathLines);		
				}
			}

		$images->ImageOut("map");		//Сохраняет на диск если имя файла задано
		}
		
	private function constructTextComment($Path) //Преревод пути в осмысленную строку содержащую путь
		{
		if(!isset($Path))
			return null;

		$stringFull=
				"Если вы пойдёте по дороге, ваше путешествие будет проходить через следующие места:
				 Начало путешествия->";
		$PointsCount=count($Path);
		for ($i=$PointsCount-1; $i>-1; $i--)
			{
			$FrontRow=$Path[$i];
			$X=$FrontRow[0];
			$Y=$FrontRow[1];
			
			$id = $this->db->GetLocationID($X,$Y);
			$Name=$this->db->Get("AnuireLocations",$id,"Name");
			$stringFull=$stringFull . "$Name->";
			}
		$stringFull=$stringFull . "Конец путешествия.";
		
		$this -> textPathComment=$stringFull;
		return $stringFull;
		}
	}

Out("Успешно подключен $Description");
?>
