<?php
$Description = "функции для нахождения кратчайшего пути пользователя earthundead";
Out("Подключены $Description");

//Немного графов. Дальнейшее требует знания теории графов. (Волны и фронты) 
//Могут быть огрехи в реализации, но для данного случая они некритичны
class Application_Model_WaveGraph
{
public $PointsTable;
public $RoadsTable;
		
private $Front;
private $OldFront;
private $AllMatchedPoints;

//Важная функция. На её свойстве добавлять во фронт только точки с меньшими длинами основана суть алгоритма.
private function AddElementInFront($FrontElement)				
	{
	//$this->$Front; Это плохо, но так проще. Сюда добавляем.

	$X = $FrontElement[0];
	$Y = $FrontElement[1];
	$Step = $FrontElement[2];
	$Lenth = $FrontElement[3];
	
	$i=$this->FindPointXYInFront($X,$Y,$Front);
	if($i<0)
		$this->Front[]=$FrontElement;
	else 	//если точка с таким номером уже есть придётся помучиться и анализировать
		{
		$FrontElement=$this->Front[$i];
		$OldStep=$FrontElement[2];
		
		if($Step<$OldStep)
			{
			$FrontElement[2]=$Step;
			$this->Front[$i]=$FrontElement;
			}
		}
	}
	
private function AddPointXYInFront($PointX,$PointY,$PointStep,$LengthToPoint=1)
	{
	$FrontRow=array($PointX,$PointY,$PointStep,$LengthToPoint);
	$this->AddElementInFront($FrontRow);
	}
	
private function SavePointsFromFront($Front)
	{	
	$this->OldFront=$this->Front;					//Небольшая рокировка. Лень делать нормально и понятно.
	$this->Front=$this->AllMatchedPoints;
	
	$PointsCount=count($this->OldFront);
	for ($i=0; $i<$PointsCount; $i++)
		{
		$FrontRow=$this->OldFront[$i];
		$this->AddElementInFront($FrontRow);
		}
		
	$this->AllMatchedPoints=$this->Front;
	$this->Front=$this->OldFront;
	}

private function CreateNewFront($BaseFront)
	{
	$this->Front=NULL;												//Сохраняем и обнуляем фронт
	
	$PointsCount=count($BaseFront);
	for ($i=0; $i<$PointsCount; $i++)							//Перебираем точки старого фронта
		{
		$FrontRow=$BaseFront[$i];
				
		$CurrPointX=$FrontRow[0];
		$CurrPointY=$FrontRow[1];
		$CurrPointLength=$FrontRow[2];

		$NewPoints=$this->GetRoadsTo($CurrPointX,$CurrPointY);	//Получаем список точек, окружающих текущую
		
		$NewPointsCount=count($NewPoints);
		for ($j=0; $j<$NewPointsCount; $j++)					//Перебираем полученное и добавляем, если точка не входит в предыдущий фронт
			{
			$NewPoint=$NewPoints[$j];
			
			$NewPointX=$NewPoint[0];
			$NewPointY=$NewPoint[1];

			if($this->FindPointXYInFront($NewPointX,$NewPointY,$this->AllMatchedPoints)<0)	//Если не нашли точки в старом фронте
				$this->AddPointXYInFront($NewPointX,$NewPointY,$CurrPointLength+1);
			}
		}
		
	//return $Front;
	}
	
public function FindWay($Point1ID,$Point2ID)	//Основная функция
	{
	$Point1XY=$this->GetPointXY($Point1ID);
	Out("Point1XY : $Point1XY[0],$Point1XY[1]");
	$Point2XY=$this->GetPointXY($Point2ID);
	Out("Point2XY : $Point2XY[0],$Point2XY[1]");

	$this->AllMatchedPoints=null;
	$this->OldFront=null;
	$this->Front=null;
	$MaxAttemptCount=50;
	
	$this->AddPointXYInFront($Point1XY[0],$Point1XY[1],0);
	$this->PrintFront($this->Front);

	for ($i=0; $i<$MaxAttemptCount; $i++)
		{
		$this->OldFront=$this->Front;
		$this->SavePointsFromFront($this->Front);
		$this->CreateNewFront($this->OldFront);
		$this->PrintFront($this->Front);
		if($this->FindPointXYInFront($Point2XY[0],$Point2XY[1],$Front)>-1)
			break;
		}
	$this->SavePointsFromFront($this->Front);

	Out("Окончательно имеем точки и расстояния:");
	$this->PrintFront($this->AllMatchedPoints);

	Out("Находим дорогу назад");
	$this->FindPathBack($Point1XY[0],$Point1XY[1],$Point2XY[0],$Point2XY[1]);

	Out("Найденный путь назад:");
	$this->PrintFront($this->Front);

	return $this->Front;
	}

private function FindPathBack($X1,$Y1,$X2,$Y2)
	{
	$Front=NULL;										//Обнуляем здесь будет результат
	
	$i=$this->FindPointXYInFront($X2,$Y2,$this->AllMatchedPoints);	//Получаем информацию о конечной точке
	$CurrentPoint=$this->AllMatchedPoints[$i];			//и Запоминаем
	$this->AddElementInFront($CurrentPoint);
	$StepsCount=$CurrentPoint[2];
	Out("Path StepsCount = $StepsCount, CurrentPoint x = $CurrentPoint[0],CurrentPoint y = $CurrentPoint[1]");

	for ($Step=$StepsCount; $Step>-1; $Step--)
		{
		$CurrPointX=$CurrentPoint[0];
		$CurrPointY=$CurrentPoint[1];					//Получаем информацию о текущей точке	
		$CurrPointStep=$CurrentPoint[2];
		
		$NewPoints=$this->GetRoadsTo($CurrPointX,$CurrPointY);	//Получаем список точек, окружающих текущую
		
		$NewPointsCount=count($NewPoints);
		Out("Path Step = $Step, Количество, найденных точек для перебора = $NewPointsCount");
		for ($j=0; $j<$NewPointsCount; $j++)			//Перебираем все окружающие точки
			{
			$NewPoint=$NewPoints[$j];					//Получаем ещё информацию о новой точке
			$NewPointX=$NewPoint[0];
			$NewPointY=$NewPoint[1];
			
			$k=$this->FindPointXYInFront($NewPointX,$NewPointY,$this->AllMatchedPoints);
			$NewPoint=$this->AllMatchedPoints[$k];		//Получаем ещё информацию о новой точке
			
			$NewPointStep=$NewPoint[2];
			if($NewPointStep==$CurrPointStep-1)			//Если "шаг" новой точки нас устраивает добавляем её в результат и ищем следующую
				{
				$this->AddElementInFront($NewPoint);
				$CurrentPoint=$NewPoint;
				break;
				}
			}

		if($CurrentPoint[2]!=$Step-1)Out("Path steps error");
		if($CurrentPoint[2]==0)break;
		}
	}
	
private function GetPointXY($PointID)
	{
	
	$XY = NULL;	
	$PointsCount=count($this->PointsTable);
	for ($i=0; $i<$PointsCount; $i++)
		{
		$Row = $this->PointsTable[$i];
		$CurrID = $Row[0];
		if($CurrID == $PointID)
			{
			$XY[0]=$Row[1];
			$XY[1]=$Row[2];
			}
		}

	return $XY;
	}

private function GetPointName($PointX,$PointY)
	{
	
	$name=NULL;
	$PointsCount=count($this->PointsTable);
	for ($i=0; $i<$PointsCount; $i++)
		{
		$Row = $this->PointsTable[$i];
		if($Row[1]==$PointX && $Row[2]==$PointY)
			{
			$name=$Row[3];
			}
		}

	return $name;
	}

private function GetRoadsTo($PointX,$PointY)
	{
	
	$PointsArray = "";
	$Count = count($this->RoadsTable);
	for ($i=0; $i<$Count; $i++)
		{
		$Row = $this->RoadsTable[$i];
		if($Row[1] == $PointX && $Row[2] == $PointY)
			{
			$PointsArray[] = array($Row[3],$Row[4]);
			}
		if($Row[3] == $PointX && $Row[4] == $PointY)
			{
			$PointsArray[] = array($Row[1],$Row[2]);
			}
		}
	return $PointsArray;
	}

private function FindPointXYInFront($PointX,$PointY,$Front)
	{
	
	$Result = -1;
	$PointsCount = count($Front);
	for ($i = 0; $i < $PointsCount; $i++)
		{
		$FrontRow = $Front[$i];
		
		if($FrontRow[0] == $PointX && $FrontRow[1] == $PointY)
			$Result = $i;
		}

	return $Result;
	}

public function ConstructLines($Front)		//Makes array[2] (pointfrom, pointto) from array[1] (pointslist)
	{
	$LineId=0;
	$PointsCount=count($Front);
	for ($i=$PointsCount-1; $i>0; $i--)
		{
		$FrontPoint=$Front[$i];
		$NextFrontPoint=$Front[$i-1];

		$PointX=$FrontPoint[0];
		$PointY=$FrontPoint[1];
		$NextPointX=$NextFrontPoint[0];
		$NextPointY=$NextFrontPoint[1];

		$Line=array($PointX,$PointY,$NextPointX,$NextPointY,$LineId);
		$LinesTable[]=$Line;
		$LineId++;
		}
	return $LinesTable;
	}
	
public function PrintFront($PrintedFront)
	{
	$PointsCount=count($PrintedFront);
	if($PointsCount<1)
		return;
	
	Out("Выводимый Front : X,Y,Шаг,Длина,(Название локации) totalcount = $PointsCount");
	for ($i=0; $i<$PointsCount; $i++)
		{
		$FrontRow=$PrintedFront[$i];
		
		//Кроме фронта получаем и выводим другую информацию, для удобства восприятия
		$x=$FrontRow[0];
		$y=$FrontRow[1];
		$Name = $this->GetPointName($x,$y);

		Out("FrontRow : $FrontRow[0],$FrontRow[1],$FrontRow[2],$FrontRow[3],($Name)");
		}
	}

}
Out("Успешно отработали $Description");
?>
