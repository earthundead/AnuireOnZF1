<?php
$Description = "функции для нахождения кратчайшего пути пользователя earthundead";
Out("Подключены $Description");

//Немного графов. Дальнейшее требует знания теории графов. (Волны и фронты) 
//Могут быть огрехи в реализации, но для данного случая они некритичны
function PrintFront($PrintedFront)
	{
	$PointsCount=count($PrintedFront);
	if($PointsCount<1)
		return;
	
	$registry = Zend_Registry::getInstance();
	$LocalDB = $registry -> get('programDB');
	
	Out("CurrFront : X,Y,Шаг,Длина(Название локации) totalcount = $PointsCount");
	for ($i=0; $i<$PointsCount; $i++)
		{
		$FrontRow=$PrintedFront[$i];
		
		//Кроме фронта получаем и выводим другую информацию, для удобства восприятия
		$x=$FrontRow[0];
		$y=$FrontRow[1];
		$id = GetPointID($x,$y);
		if($id>1)
			$Name=$LocalDB->Get("AnuireLocations",$id,"Name");	
		
		Out("FrontRow : $FrontRow[0],$FrontRow[1],$FrontRow[2],$FrontRow[3],($Name)");
		}
	}

//Важная функция. На её свойстве добавлять во фронт только точки с меньшими длинами основана суть алгоритма.
function AddElementInFront($FrontElement)				
	{
	global $Front;//Это плохо, но так проще. Сюда добавляем.

	$X = $FrontElement[0];
	$Y = $FrontElement[1];
	$Step = $FrontElement[2];
	$Lenth = $FrontElement[3];
	
	$i=FindPointXYInFront($X,$Y,$Front);
	if($i<0)
		$Front[]=$FrontElement;
	else 	//если точка с таким номером уже есть придётся помучиться и анализировать
		{
		$FrontElement=$Front[$i];
		$OldStep=$FrontElement[2];
		
		if($Step<$OldStep)
			{
			$FrontElement[2]=$Step;
			$Front[$i]=$FrontElement;
			}
		}
	}
	
function SavePointsFromFront($Front)
	{
	global $Front,$AllMatchedPoints;
	
	$OldFront=$Front;					//Небольшая рокировка. Лень делать нормально и понятно.
	$Front=$AllMatchedPoints;
	
	$PointsCount=count($OldFront);
	for ($i=0; $i<$PointsCount; $i++)
		{
		$FrontRow=$OldFront[$i];
		AddElementInFront($FrontRow);
		}
		
	$AllMatchedPoints=$Front;
	$Front=$OldFront;
	}

function CreateNewFront($BaseFront)
	{
	global $Front,$AllMatchedPoints;

	$Front=NULL;												//Сохраняем и обнуляем фронт
	
	$PointsCount=count($BaseFront);
	for ($i=0; $i<$PointsCount; $i++)							//Перебираем точки старого фронта
		{
		$FrontRow=$BaseFront[$i];
				
		$CurrPointX=$FrontRow[0];
		$CurrPointY=$FrontRow[1];
		$CurrPointLength=$FrontRow[2];

		$NewPoints=GetRoadsTo($CurrPointX,$CurrPointY);	//Получаем список точек, окружающих текущую
		
		$NewPointsCount=count($NewPoints);
		for ($j=0; $j<$NewPointsCount; $j++)					//Перебираем полученное и добавляем, если точка не входит в предыдущий фронт
			{
			$NewPoint=$NewPoints[$j];
			
			$NewPointX=$NewPoint[0];
			$NewPointY=$NewPoint[1];

			if(FindPointXYInFront($NewPointX,$NewPointY,$AllMatchedPoints)<0)	//Если не нашли точки в старом фронте
				AddPointXYInFront($NewPointX,$NewPointY,$CurrPointLength+1);
			}
		}
		
	//return $Front;
	}
	
function FindWay($Point1ID,$Point2ID)	//Основная функция
	{
	global $Front,$OldFront,$AllMatchedPoints;
	
	$Point1XY=GetPointXY($Point1ID);
	Out("Point1XY : $Point1XY[0],$Point1XY[1]");
	$Point2XY=GetPointXY($Point2ID);
	Out("Point2XY : $Point2XY[0],$Point2XY[1]");

	$AllMatchedPoints="";
	$OldFront="";
	$Front="";
	$MaxAttemptCount=50;
	
	AddPointXYInFront($Point1XY[0],$Point1XY[1],0);
	PrintFront($Front);

	for ($i=0; $i<$MaxAttemptCount; $i++)
		{
		$OldFront=$Front;
		SavePointsFromFront($Front);
		CreateNewFront($OldFront);
		PrintFront($Front);
		if(FindPointXYInFront($Point2XY[0],$Point2XY[1],$Front)>-1)
			break;
		}
	SavePointsFromFront($Front);

	Out("Окончательно имеем точки и расстояния:");
	PrintFront($AllMatchedPoints);

	Out("Находим дорогу назад");
	FindPathBack($Point1XY[0],$Point1XY[1],$Point2XY[0],$Point2XY[1]);

	Out("Найденный путь назад:");
	PrintFront($Front);

	return $Front;
	}

function FindPathBack($X1,$Y1,$X2,$Y2)
	{
	global $Front,$AllMatchedPoints;
	$Front=NULL;										//Обнуляем здесь будет результат
	
	$i=FindPointXYInFront($X2,$Y2,$AllMatchedPoints);	//Получаем информацию о конечной точке
	$CurrentPoint=$AllMatchedPoints[$i];				//и Запоминаем
	AddElementInFront($CurrentPoint);
	$StepsCount=$CurrentPoint[2];
	Out("Path StepsCount = $StepsCount, CurrentPoint x = $CurrentPoint[0],CurrentPoint y = $CurrentPoint[1]");

	for ($Step=$StepsCount; $Step>-1; $Step--)
		{
		$CurrPointX=$CurrentPoint[0];
		$CurrPointY=$CurrentPoint[1];				//Получаем информацию о текущей точке	
		$CurrPointStep=$CurrentPoint[2];
		
		$NewPoints=GetRoadsTo($CurrPointX,$CurrPointY);	//Получаем список точек, окружающих текущую
		
		$NewPointsCount=count($NewPoints);
		Out("Path Step = $Step, Количество, найденных точек для перебора = $NewPointsCount");
		for ($j=0; $j<$NewPointsCount; $j++)			//Перебираем все окружающие точки
			{
			$NewPoint=$NewPoints[$j];					//Получаем ещё информацию о новой точке
			$NewPointX=$NewPoint[0];
			$NewPointY=$NewPoint[1];
			
			$k=FindPointXYInFront($NewPointX,$NewPointY,$AllMatchedPoints);
			$NewPoint=$AllMatchedPoints[$k];			//Получаем ещё информацию о новой точке
			
			$NewPointStep=$NewPoint[2];
			if($NewPointStep==$CurrPointStep-1)			//Если "шаг" новой точки нас устраивает добавляем её в результат и ищем следующую
				{
				AddElementInFront($NewPoint);
				$CurrentPoint=$NewPoint;
				break;
				}
			}

		if($CurrentPoint[2]!=$Step-1)Out("Path steps error");
		if($CurrentPoint[2]==0)break;
		}
	}

function AddPointXYInFront($PointX,$PointY,$PointStep,$LengthToPoint=1)
	{
	$FrontRow=array($PointX,$PointY,$PointStep,$LengthToPoint);
	AddElementInFront($FrontRow);
	}
	
function GetPointXY($PointID)
	{
	$registry = Zend_Registry::getInstance();
	$LocalDB = $registry -> get('programDB');
	$PointsTable=$LocalDB->Get("AnuireLocations");
	
	$XY = NULL;	
	$PointsCount=count($PointsTable);
	for ($i=0; $i<$PointsCount; $i++)
		{
		$Row = $PointsTable[$i];
		$CurrID = $Row[0];
		if($CurrID == $PointID)
			{
			$XY[0]=$Row[1];
			$XY[1]=$Row[2];
			}
		}

	return $XY;
	}

function GetPointID($PointX,$PointY)
	{
	$registry = Zend_Registry::getInstance();
	$LocalDB = $registry -> get('programDB');
	$PointsTable=$LocalDB->Get("AnuireLocations");
	//$RoadsTable=$LocalDB->Get("Roads");
	
	$id=NULL;
	$PointsCount=count($PointsTable);
	for ($i=0; $i<$PointsCount; $i++)
		{
		$Row = $PointsTable[$i];
		if($Row[1]==$PointX && $Row[2]==$PointY)
			{
			$id=$Row[0];
			}
		}

	return $id;
	}

function GetRoadsTo($PointX,$PointY)
	{
	
	$registry = Zend_Registry::getInstance();
	$LocalDB = $registry -> get('programDB');
	//$PointsTable=$LocalDB->Get("AnuireLocations");
	$RoadsTable=$LocalDB->Get("Roads");
	
	$PointsArray = "";
	$Count = count($RoadsTable);
	for ($i=0; $i<$Count; $i++)
		{
		$Row = $RoadsTable[$i];
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

function FindPointXYInFront($PointX,$PointY,$Front)
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

function ConstructLines($Front)		//Makes array[2] (pointfrom, pointto) from array[1] (pointslist)
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

Out("Успешно отработали $Description");
?>
