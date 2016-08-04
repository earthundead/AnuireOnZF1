<?php
include_once "euFunctions.php";
$Description = "функции Для работы с изображениями пользователя earthundead";
Out("Подключаем $Description");

class Application_Model_Images
{
public $img;

public function GetLocationPoints($Table)
	{
	$Count=count($Table);
	if($Count>0)
		for ($i=0; $i<$Count; $i++)
			{
			$CurrStr=$Table[$i];

			$X=$CurrStr["X"];
			$Y=$CurrStr["Y"];
			$Name=$CurrStr["Name"];

			$Result["X"]=$X;
			$Result["Y"]=$Y;
			$Result["Name"]=$Name;

			$LocationPointsTable[]=$Result;
			}
	return $LocationPointsTable;
	}


//Изображения
public function DrawLocation($X,$Y,$Color="")
	{
	$black = imagecolorallocate($this->img,0,0,0);
	$Size = 8;
	imageline($this->img,$X-$Size/4,$Y-$Size/4,$X+$Size/4,$Y+$Size/4,$black);
	imageline($this->img,$X+$Size/4,$Y-$Size/4,$X-$Size/4,$Y+$Size/4,$black);
	imageellipse($this->img,$X,$Y,$Size,$Size,$black);
	}

public function DrawNamedPoints($Points)
	{
	Out("Ссылка на картинку, где будем рисовать Локации: $this->img");
	//Подготовка
	$white = imagecolorallocate($this->img,255,255,255);
	$red = imagecolorallocate($this->img,255,0,0);
	$black = imagecolorallocate($this->img,0,0,0);
	$CharHeight=imagefontheight(2);
	$CharWidth=imagefontwidth(2);

	$Count=count($Points);
	if($Count>0)
		for ($i=0; $i<$Count; $i++)
			{
			$CurrPoint = $Points[$i];
			$X=$CurrPoint[1];
			$Y=$CurrPoint[2];
			$Description=$CurrPoint[3];

			$this->DrawLocation($X,$Y);
			if($Description!="")
				{
				$CharCount=strlen($Description);
				$HalfWidth=$CharWidth*$CharCount/2;
				imagestring($this->img,2,$X-$HalfWidth,$Y,$Description,$black);			
				}
			}
	}

public function DrawText($Text,$Indent=0)		//Рисует подпись
	{
	$black = imagecolorallocate($this->img,0,0,0);
	imagestring($this->img,5,0,10+$Indent,$Text,$black);
	}

public function DrawLines($Lines) //Roads
	{
	Out("Ссылка на картинку, где будем рисовать линии: $this->img");
	//allocate some colors
	$white = imagecolorallocate($this->img,255,255,255);
	$red = imagecolorallocate($this->img,255,0,0);
	$black = imagecolorallocate($this->img,0,0,0);
	//$Keys=array_keys();

	$Count=count($Lines);
	if($Count>0)
		for ($i=0; $i<$Count; $i++)
			{
			$CurrLine =$Lines[$i];
			$LCount=count($CurrLine);
			
			//Отбирает 4 последних элемента
			$X0=$CurrLine[1];
			$Y0=$CurrLine[2];
			$X1=$CurrLine[3];
			$Y1=$CurrLine[4];

			imageline($this->img,$X0,$Y0,$X1,$Y1,$red);
			}	
	}

public function DrawArrows($Lines)
	{
	Out("Ссылка на картинку, где будем рисовать стрелки: $this->img");
	// allocate some colors
	$white = imagecolorallocate($this->img,255,255,255);
	$blue = imagecolorallocate($this->img,0,0,255);
	$black = imagecolorallocate($this->img,0,0,0);

	$Count=count($Lines);
	if($Count>0)
		for ($i=0; $i<$Count; $i++)
			{
			//Получаем данные
			$CurrLine =$Lines[$i];

			$X0=$CurrLine[0];
			$Y0=$CurrLine[1];
			$X1=$CurrLine[2];
			$Y1=$CurrLine[3];

			//Вычисляем параметры
			$D=CalculateDistanse($X0,$Y0,$X1,$Y1);
			$SinA=1.0*($X1-$X0)/$D;
			$CosA=1.0*($Y1-$Y0)/$D;
			$A=acos($CosA);
			if($SinA<0)$A=-$A;
			$A1=$A+0.1;
			$A2=$A-0.1;
			$SinA1=1.0*sin($A1);
			$SinA2=1.0*sin($A2);
			$CosA1=1.0*cos($A1);
			$CosA2=1.0*cos($A2);
			$D=min(30,$D/5);			//Уменьшаем расстояние
			$X01=1.0*$X1-$D*$SinA1;
			$X02=1.0*$X1-$D*$SinA2;
			$Y01=1.0*$Y1-$D*$CosA1;
			$Y02=1.0*$Y1-$D*$CosA2;

			//Рисуем стрелку
			imageline($this->img,$X0,$Y0,$X1,$Y1,$blue);
			imageline($this->img,$X01,$Y01,$X1,$Y1,$blue);
			imageline($this->img,$X02,$Y02,$X1,$Y1,$blue);
			}	
	}

public function DrawPoints($Points)
	{
	//Рисует цветную картину высот по точкам
	$White=imagecolorallocate($this->img,255,255,255);
	Out("Ссылка на картинку, где будем рисовать точки: $this->img");
	//Анализируем входные данные
	if(!isset($Points))
		return;
	$Count=count($Points);
	if($Count<1)
		return;
	
	if($Count>0)
		for ($i=0; $i<$Count; $i++)
			{
			$CurrPoint = $Points[$i];
			$X=$CurrPoint[1];
			$Y=$CurrPoint[2];
			$Z=$CurrPoint[3];
			
			while($Z>225||$Z<-225)
				$Z=$Z/2;
			$WaterColor=imagecolorallocate($this->img,0,0,$Z);			//Градации голубого
			$MountainColor=imagecolorallocate($this->img,$Z,$Z,$Z);		//Градации чёрного
			//$GrassColor=imagecolorallocate($this->img,225-$Z,$Z,0);	//Градации зелёно-красного
			if($Z<0)$PointColor=$WaterColor;
			else $PointColor=$MountainColor;
			
			imageellipse($this->img,$X,$Y,2,2,$PointColor);
			}
	}

public function ImageOut($Filename = NULL)
	{
	$registry = Zend_Registry::getInstance();
	$dataFilesPath 	= $registry -> get('dataFilesPath');
	$ProgramDir = $dataFilesPath;
	
	if(isset($Filename))
		{
		$Succses=imagepng($this->img,"$ProgramDir/$Filename.png");
		Out("Успех сохранения картинки $this->img в файл $Filename.png c помощью png : $Succses");
		}
	else
		{
		header('Content-Type: image/png');
		$Succses=imagepng($this->img);
		Out("Успех вывода картинки $this->img в браузер : $Succses");
		}
	}
}
Out("$Description успешно подключены");
?>
