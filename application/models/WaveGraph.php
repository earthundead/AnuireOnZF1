<?php
$Description = "функции для нахождения кратчайшего пути пользователя earthundead";
Out("Подключены $Description");

$registry = Zend_Registry::getInstance();
$LocalDB = $registry -> get('programDB');
$PointsTable=$LocalDB->Get("AnuireLocations");
$RoadsTable=$LocalDB->Get("Roads");

/*$string = AwesomeToString($PointsTable);
Out("PointsTable: $string");
		
$string = AwesomeToString($RoadsTable);
Out("RoadsTable: $string");*/

//Немного графов. Дальнейшее требует знания теории графов. (Волны и фронты) 
//Могут быть огрехи в реализации, но для данного случая они некритичны
function PrintFront($PrintedFront)
	{
	$PointsCount=count($PrintedFront);
	if($PointsCount<1)
		return;
	
	$registry = Zend_Registry::getInstance();
	$LocalDB = $registry -> get('programDB');
	
	Out("CurrFront : id,step,(Name) totalcount = $PointsCount");
	for ($i=0; $i<$PointsCount; $i++)
		{
		$FrontRow=$PrintedFront[$i];
		$id=$FrontRow[0];				//Кроме фронта получаем и выводим другую информацию, для удобства восприятия
		$Name=$LocalDB->Get("AnuireLocations",$id,"Name");	
		Out("FrontRow : $FrontRow[0],$FrontRow[1],($Name)");
		}
	}



function GetPointXY($PointID)
	{
	$registry = Zend_Registry::getInstance();
	$LocalDB = $registry -> get('programDB');
	$PointsTable=$LocalDB->Get("AnuireLocations");
		
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
	$RoadsTable=$LocalDB->Get("Roads");
	
	$id="";
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
	$PointsTable=$LocalDB->Get("AnuireLocations");
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

function FindPointInFront($PointNo,$Front)
	{
	$Result = -1;
	$PointsCount = count($Front);
	for ($i = 0; $i < $PointsCount; $i++)
		{
		$FrontRow = $Front[$i];
		if($FrontRow[0] == $PointNo)
			$Result = $i;
		}
	return $Result;
	}

function AddPointInFront($PointNo,$LengthToPoint)		//С учётом "веса". Т.е добавляем только меньшие длины
	{
	global $Front;						//Это плохо, но так проще. Сюда добавляем.
	if(!isset($PointNo))
		return;
	$FrontRow=array($PointNo,$LengthToPoint);
	$i=FindPointInFront($PointNo,$Front);
	if($i<0)$Front[]=$FrontRow;
	else								//если точка с таким номером уже есть придётся помучиться и анализировать
		{
		$FrontRow=$Front[$i];
		$OldLengthToPoint=$FrontRow[1];
		if($LengthToPoint<$OldLengthToPoint)
			{
			$FrontRow[1]=$LengthToPoint;
			$Front[$i]=$FrontRow;
			}
		}
	}

function SavePointsFromFront()
	{
	global $Front,$AllMatchedPoints;
	$OldFront=$Front;					//Небольшая рокировка. Лень делать нормально и понятно.
	$Front=$AllMatchedPoints;
	$PointsCount=count($OldFront);
	for ($i=0; $i<$PointsCount; $i++)
		{
		$FrontRow=$OldFront[$i];
		AddPointInFront($FrontRow[0],$FrontRow[1]);
		}
	$AllMatchedPoints=$Front;
	$Front=$OldFront;
	}

function CreateNewFront()
	{
	global $Front,$OldFront,$AllMatchedPoints;
	SavePointsFromFront();
	$OldFront=$Front;
	$Front="";													//Сохраняем и обнуляем фронт
	$PointsCount=count($OldFront);
	//Out("PointsCount=$PointsCount");
	for ($i=0; $i<$PointsCount; $i++)							//Перебираем точки старого фронта
		{
		$FrontRow=$OldFront[$i];
		//Out("FrontRow[0]:". $FrontRow[0]);				
		$CurrPointID=$FrontRow[0];
		$CurrPointLength=$FrontRow[1];
		$CurrPointXY=GetPointXY($CurrPointID);
		//Out("CurrPointXY:". $CurrPointXY[0],$CurrPointXY[1]);
		$NewPoints=GetRoadsTo($CurrPointXY[0],$CurrPointXY[1]);	//Получаем список точек, окружающих текущую
		$NewPointsCount=count($NewPoints);
		//Out("NewPointsCount=$NewPointsCount");
		for ($j=0; $j<$NewPointsCount; $j++)					//Перебираем полученное и добавляем, если точка не входит в предыдущий фронт
			{
			$NewPoint=$NewPoints[$j];
			$NewPointX=$NewPoint[0];
			$NewPointY=$NewPoint[1];
			$NewPointID=GetPointID($NewPointX,$NewPointY);
			if($NewPointID>0)
				if(FindPointInFront($NewPointID,$AllMatchedPoints)<0)	//Если не нашли id точки в старом фронте
					AddPointInFront($NewPointID,$CurrPointLength+1);
			}
		}
	}

function FindPath($Point1ID,$Point2ID)
	{
	global $Front,$AllMatchedPoints;
	$Front="";											//Обнуляем здесь будет результат
	$i=FindPointInFront($Point2ID,$AllMatchedPoints);	//Получаем информацию о конечной точке		
	$CurrentPoint=$AllMatchedPoints[$i];				//и Запоминаем
	AddPointInFront($CurrentPoint[0],$CurrentPoint[1]);
	$StepsCount=$CurrentPoint[1];
	Out("Path StepsCount = $StepsCount, CurrentPoint id = $CurrentPoint[0]");

	for ($Step=$StepsCount; $Step>-1; $Step--)
		{
		$CurrPointID=$CurrentPoint[0];					//Получаем информацию о текущей точке	
		$CurrPointStep=$CurrentPoint[1];
		$CurrPointXY=GetPointXY($CurrPointID);
		$CurrPointX=$CurrPointXY[0];
		$CurrPointY=$CurrPointXY[1];
		$NewPoints=GetRoadsTo($CurrPointX,$CurrPointY);	//Получаем список точек, окружающих текущую
		$NewPointsCount=count($NewPoints);
		Out("Path Step = $Step, Количество, найденных точек для перебора = $NewPointsCount");

		for ($j=0; $j<$NewPointsCount; $j++)			//Перебираем все окружающие точки
			{
			$NewPoint=$NewPoints[$j];					//Получаем ещё информацию о новой точке
			$NewPointX=$NewPoint[0];
			$NewPointY=$NewPoint[1];
			$NewPointID=GetPointID($NewPointX,$NewPointY);
			if($NewPointID<0)Out("PointID error");

			$k=FindPointInFront($NewPointID,$AllMatchedPoints);
			$NewPoint=$AllMatchedPoints[$k];			//Получаем ещё информацию о новой точке
			if($NewPointID!=$NewPoint[0])Out("Road Back error");
			$NewPointStep=$NewPoint[1];

			if($NewPointStep==$CurrPointStep-1)			//Если "шаг" новой точки нас устраивает добавляем её в результат и ищем следующую
				{
				AddPointInFront($NewPointID,$NewPointStep);
				$CurrentPoint=$NewPoint;
				break;
				}
			}
		if($CurrentPoint[1]!=$Step-1)Out("Path steps error");
		if($CurrentPoint[1]==0)break;
		}
	}

function FindWay($Point1ID,$Point2ID)	//Основная функция
	{
	global $Front,$OldFront,$AllMatchedPoints;

	$AllMatchedPoints="";
	$OldFront="";
	$Front="";
	$MaxAttemptCount=50;
	AddPointInFront($Point1ID,0);
	PrintFront($Front);

	for ($i=0; $i<$MaxAttemptCount; $i++)
		{
		CreateNewFront();
		PrintFront($Front);
		if(FindPointInFront($Point2ID,$Front)>-1)
			break;
		}
	SavePointsFromFront();

	Out("Окончательно имеем точки и расстояния:");
	PrintFront($AllMatchedPoints);

	Out("Находим дорогу назад");
	FindPath($Point1ID,$Point2ID);

	Out("Найденный путь назад:");
	PrintFront($Front);

	return $Front;
	}

function ConstructLines($Front)				//Makes array[2] (pointfrom, pointto) from array[1] (pointslist)
	{
	$LineId=0;
	$PointsCount=count($Front);
	for ($i=$PointsCount-1; $i>0; $i--)
		{
		$FrontPoint=$Front[$i];
		$NextFrontPoint=$Front[$i-1];
		$id1=$FrontPoint[0];
		$id2=$NextFrontPoint[0];

		$CurrPointXY=GetPointXY($id1);
		$PointX=$CurrPointXY[0];
		$PointY=$CurrPointXY[1];
		$NextPointtXY=GetPointXY($id2);
		$NextPointX=$NextPointtXY[0];
		$NextPointY=$NextPointtXY[1];

		$Line=array($PointX,$PointY,$NextPointX,$NextPointY,$LineId);

		$LineId++;
		$LinesTable[]=$Line;
		}
	return $LinesTable;
	}

Out("Успешно отработали $Description");
?>
