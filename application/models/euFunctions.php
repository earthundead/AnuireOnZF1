<?php
$Description = "функции пользователя earthundead";
	
$registry = Zend_Registry::getInstance();
$logger = $registry -> get('logger');

//Простой доступ к организации журнала событий
function Out($Srtring)
	{
	global $logger;
	$logger->info($Srtring);
	if(! $logger)
		{
		echo("Совсем неуспешная запись в лог. Лог глючит.<br>");
		echo("Пытались вывести : $Srtring.<br>");
		}
	}

Out("Подключаем $Description");

function awesomeToString($ComplexData)
	{
	ob_start();
	var_dump($ComplexData);
	$String=ob_get_contents();
	ob_end_clean() ;
	return $String;
	}

function elementToString($item, $key) 
	{
	global $string;
	$string = $string .  "Ключ $key содержит $item. ";
	}
	
function arrayToString($array)
	{
	global $string;	
	$string = "Начало массива. ";	
	array_walk_recursive($array, 'elementToString');
	$string = $string .  "Конец массива.";
	return $string;
	}

//Разное трудноклассифицируемое
function CalculateDistanse($X1,$Y1,$X2,$Y2)
	{
	$Dist=-1;
	$Dist=($X1-$X2)**2+($Y1-$Y2)**2;
	$Dist=sqrt($Dist);
	return $Dist;
	}
	
function getPoints($count,$size)
	{
	$side=sqrt($count);
	
	if($size<$count)
		return;
	$div = $size/$side;
	if(!$div)
		return;
		
	for ($i=0; $i<$side+1; $i++)
		{
		$X = $i*$div;
		for ($j=0; $j<$side+1; $j++)
			{
			$Y = $j*$div;
			$points[]=array($X,$Y);
			}
		}
	return $points;
	}

/*//не удалять
function QuickHull($LeftPoint,$RightPoint)
{
	global $HullPoints,$Points;
	$MostRightPoint=FindMostRight($LeftPoint,$RightPoint);
	if($MostRightPoint)
		{
		$HullPoints[]=$MostRightPoint;
		QuickHull($LeftPoint,$MostRightPoint);
		QuickHull($MostRightPoint,$RightPoint);
		}
}

function QuickHullStart()
{
	global $HullPoints,$Points;
	$HullPoints="";
	$MaxMin=FindMaxMin($Points);
	$MaxX=$MaxMin["Max"];
	$MinX=$MaxMin["Min"];
	QuickHull($MinX,$MaxX);
	$HullPoints[]=$MaxX;
	QuickHull($MaxX,$MinX);
	$HullPoints[]=$Maxy;
}

function FindMostRight($LeftPoint,$RightPoint)
{
	$MostRightPoint="";
	return $MostRightPoint;
}

function FindMaxMin($Points)
{
	$Count=count($Points);
	$MaxX=0;
	$MinX=1000;
	if($Count>0)
		for ($i=0; $i<$Count; $i++)
		{
			$CurrPoint =$Points[$i];
			$X=$CurrPoint["X"];
			if($X>$MaxX)
				{
				$MaxX=$X;
				$iMaxX=$i;
				}
			if($X<$MinX)
				{
				$MinX=$X;
				$iMinX=$i;
				}
		}
	$MaxMin = array("Max"=>$iMaxX, "Min"=>$iMinX);
	//Выведем похвастаемся
	$i=$MaxMin["Max"];
	$CurrPoint=$Points[$i];
	$X=$CurrPoint["X"];
	$Y=$CurrPoint["Y"];
	Out( "MaxX - точка № $i ($X,$Y)");
	$i=$MaxMin["Min"];
	$CurrPoint=$Points[$i];
	$X=$CurrPoint["X"];
	$Y=$CurrPoint["Y"];
	Out("MinX - точка № $i ($X,$Y)");
	//Конец вывода
	return $MaxMin;
}
*/
Out("$Description успешно подключены");
?>
