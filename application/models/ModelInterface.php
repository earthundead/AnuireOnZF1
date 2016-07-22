<?php

include_once "euFunctions.php";
include_once "WaveGraph.php";
include_once "Images.php";

$Description = "основной модуль тестовой программы на PHP пользователя earthundead";
Out("Подключен $Description");

class Application_Model_ModelInterface
	{
	public $db;
	public $locationsList;
	public $startPoint;
	public $endPoint;
	public $path;
	public $textPathComment;
	
	public function Application_Model_ModelInterface()
		{
		$this->init();
		}
		
	private function init()
		{
		$registry = Zend_Registry::getInstance();
		$this->db = $registry -> get('programDB');
		$this->locationsList = $this->db->Get("AnuireLocations",-1,"Name");		//Получаем все строки из таблицы локаций с заданным именем колонки
		}

	public function recreateAllDB()
		{
		$this->db->RecreateDB();
		}
	
	public function refresh()
		{
		$this->redrawPicture();
		}
	
	public function findWay($startPoint,$endPoint)
		{
		$DBid1=$this->db->Find("AnuireLocations","Name=\"$startPoint\""); 	//Получаем id из таблицы локаций с заданным именем
		$DBid2=$this->db->Find("AnuireLocations","Name=\"$endPoint\"");

		if(isset($DBid1)&&isset($DBid2))
			{
			$this->startPoint = $DBid1;
			$this->endPoint   = $DBid2;
			$this->path=FindWay2();  										//При помощи графов находим путь из начальной точки к конечной
			}
		}
		
	public function findWay2()
		{
		$registry = Zend_Registry::getInstance();
		$this->startPoint = $registry -> get('startPointId');
		$this->endPoint = $registry -> get('endPointId');
			
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
		
		$this->path=FindWay($this->startPoint,$this->endPoint);  							//При помощи графов находим путь из начальной точки к конечной
		$this->textPathComment=$this->constructTextComment($this->path);
		}
			

	public function redrawPicture()
		{
		//Прерисуем и сохраним картинку уже с отмеченным маршрутом
		$img = imagecreate(1100, 1100);
						
		$images = new Application_Model_Images;
		$images -> img =$img;

		$LocationsTable=$this->db->Get("AnuireLocations");
		$RoadsTable=$this->db->Get("Roads");
		$PointsTable=$this->db->Get("Points");

		//$images->DrawPoints($PointsTable);
		$images->DrawNamedPoints($LocationsTable);
		$images->DrawLines($RoadsTable);
		$images->DrawText("Anuire Map");
		if(isset($this->path))
			{
			$PathLines=ConstructLines($this->path);
			if(isset($PathLines))
				{
				$images->DrawArrows($PathLines);
				$images->DrawText("There is path on map from startPoint to endPoint.",15);			
				}
			}

		$images->ImageOut("map");		//Сохраняет на диск если имя файла задано
		}
		
	public function constructTextComment($Path)
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
			$id=$FrontRow[0];
			$Name=$this->db->Get("AnuireLocations",$id,"Name");
			$stringFull=$stringFull . "$Name->";
			}
		$stringFull=$stringFull . "Конец путешествия.";

		/*$FrontRow=$Path[0];
		$DBid0=$FrontRow[0];
		$FrontRow=$Path[$PointsCount-1];
		$DBid1=$FrontRow[0];
		if($DBid1!=""&&$DBid1!="")
			{
			$Arr=$this->db->Get("AnuireLocations",$DBid0);
			$DBid0=$Arr[0];$X0=$Arr[1];$Y0=$Arr[2];$Name0=$Arr[3];
			$Arr=$this->db->Get("AnuireLocations",$DBid1);
			$DBid1=$Arr[0];$X1=$Arr[1];$Y1=$Arr[2];$Name1=$Arr[3];
			$stringFull=
				"Начальная точка вашего путешествия:$Name1, id = $DBid1,X=$X1,Y=$Y1.
				 Конечная точка вашего путешествия:$Name0, id = $DBid0, X=$X0,Y=$Y0.";
			$Dist=CalculateDistanse($X0,$Y0,$X1,$Y1);
			$Time=$Dist/2;
			$stringFull= $stringFull.
				"Длина путешествия пешком = $Dist км. Примерное время, при скорости 2 км/ч = $Time ч.
		*/
		return $stringFull;
		}
	}
Out("Успешно отработал $Description");
?>
