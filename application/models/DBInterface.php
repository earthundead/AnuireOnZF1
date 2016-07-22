<?php
include_once "euFunctions.php";
$Description = "функции для работы с данной конкретной БД на базе MySql пользователя earthundead";
Out("Подключены $Description");

function SQLFormat($SqlResult)				//приводим результат к формату ПХП
	{
	$RowCount=mysql_num_rows($SqlResult);
	if($RowCount>0)
		$Sucsses=1;
	else
		$Sucsses=0;
	//Out("Успех команды SELECT = $Sucsses, $RowCount записей выбрано из таблицы ");
	
	if($RowCount>0)
		{
		$ArrLine = mysql_fetch_row($SqlResult);
		while ($ArrLine)
			{
			$ColCount=count($ArrLine);
			if($ColCount>0)
				{
				if($ColCount>1)
					for ($i=0; $i<$ColCount; $i++) 
						$TableRow[$i]=$ArrLine[$i];
				else
					$TableRow=$ArrLine;
				$Table[]=$TableRow;
				}
			$ArrLine = mysql_fetch_row($SqlResult);
			}
		}
		
	//Проанализируем результат. Упростим если надо
	if(count($Table)==1)
		$Table=$Table[0];
	if(count($Table)==1)
		$Table=$Table[0];
	
	$totalCount=count($Table);	
	for ($i=0; $i<$totalCount; $i++)
		{
		$element = $Table[$i];
		$count = count($element);	
		if($count == 1)
			$element = $element[0];
		$Table[$i] = $element;
		}
	//всё
	return $Table;
	}

class Application_Model_DBInterface
{
//Переменные
private $User;
private $Password;
private $db;
private $ThisConnection;
private $DBinfo;
private $TextFilesDir;

public function Application_Model_DBInterface()
	{
	$this->Initialise();
	}
	
private function Initialise()
	{
	$registry = Zend_Registry::getInstance();
	$dataFilesPath 	= $registry -> get('dataFilesPath');
	$dbArray 		= $registry -> get('dbSettings');
	
	$this->TextFilesDir = $dataFilesPath;
	$this->User 	= $dbArray["user"];
	$this->Password = $dbArray["password"];
	$this->db 		= $dbArray["name"];
	
	$Tableinfo=array("AnuireLocations",
			"
		   	X	INT,
			Y	INT,
			Name	CHAR (255),
			Description	TEXT,
			Lvl	TINYINT,
			");
	$this->DBinfo[]=$Tableinfo;

	$Tableinfo=array("AnuireRoads","
		   	X0	INT,
			Y0	INT,
		   	X1	INT,
			Y1	INT
			"
			,"X0,Y0,X1,Y1");
	$this->DBinfo[]=$Tableinfo;

	$Tableinfo=array("Config",
			"
		   	StartPoint	INT,
			EndPoint	INT,
			"
			);
	$this->DBinfo[]=$Tableinfo;

	$this->ConnectDB();
	}

private function ConnectDB()
	{
	$this->ThisConnection = mysql_connect("localhost",$this->User,$this->Password);
	Out("Подключен mysql");

	$Sucsses=mysql_select_db($this->db,$this->ThisConnection);
	Out("Подключена БД = $Sucsses");
	}

public function CloseDB()
	{
	$Sucsses=mysql_close($this->ThisConnection);
	Out("Закрыта БД = $Sucsses");
	}

private function QueryToDB($QueryString)
	{
	$Sucsses=mysql_query($QueryString,$this->ThisConnection);
	return $Sucsses;
	}

//Пересоздание и заполнение имеющихся таблиц БД
/*private function RecreateTable($Tableinfo)
	{
	$TableName=$Tableinfo[0];
	$CreateQueryString=$Tableinfo[1];
	//Сбрасываем всё предыдущее
	$CurrQueryString="DROP TABLE $TableName";
	$Sucsses=QueryToDB($CurrQueryString);
	Out("Успех команды сброса таблицы $TableName = $Sucsses");
	//И создаём заново
	$CurrQueryString=		  "CREATE TABLE IF NOT EXISTS $TableName ";
	$CurrQueryString=$CurrQueryString." ( id INT NOT NULL AUTO_INCREMENT,";
	$CurrQueryString=$CurrQueryString.$CreateQueryString;
	$CurrQueryString=$CurrQueryString."PRIMARY KEY (id))";
	$Sucsses=QueryToDB($CurrQueryString);
	Out("Успех команды создания таблицы $TableName = $Sucsses");
	//Заполняем значениями из файла если находим файл с подходящим названием
	$TableFileName="$ProgramDir/Texts/$TableName"."_inital.txt";
	$Sucsses=file_exists($TableFileName);
	if(!$Sucsses)return;
	$FileHandle=fopen("$TableFileName","r");
	$ShortQueryString=$Tableinfo[2];
	$i=0;
	if($Sucsses)
		while (!feof($FileHandle)) 
			{
			$String = fgets($FileHandle);
			$X0 = strtok($String,"\t");
			$Y0 = strtok("\t");
			$X1 = strtok("\t");
			$Y1 = strtok("\t");
			$CurrQueryString=
				"
				INSERT
				INTO	$TableName ($ShortQueryString)
				VALUES	($ValuesQueryString)
				";
			$Sucsses=QueryToDB($CurrQueryString);	
			$Table[]=array($X0,$Y0,$X1,$Y1);
			if($Sucsses) $i++;
			}
	Out("В таблицу $TableName записаны данные из файла в количестве $i строк");
	}
*/

//Пересоздание и заполнение имеющихся таблиц БД
private function RecreateRoads()
	{
	$ProgramDir=$this->TextFilesDir;
	Out("Перезагружается таблица Roads");
	$CurrQueryString="DROP TABLE Roads";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды DROP TABLE  = $Sucsses");

	$CurrQueryString=
	"
	CREATE TABLE IF NOT EXISTS Roads
		(
		id	INT NOT NULL AUTO_INCREMENT,
	   	X0	INT,
		Y0	INT,
	   	X1	INT,
		Y1	INT,
		PRIMARY KEY (id)
		)
	";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды CREATE TABLE = $Sucsses");

	//Заполняем значениями из файла
	$Sucsses=file_exists("$ProgramDir/Anuire_Roads_inital.txt");
	$FileHandle=fopen("$ProgramDir/Anuire_Roads_inital.txt","r");

	$i=0;
	if($Sucsses)
		while (!feof($FileHandle)) 
			{
			$String = fgets($FileHandle);

			$X0 = strtok($String,"\t");
			$Y0 = strtok("\t");
			$X1 = strtok("\t");
			$Y1 = strtok("\t");

			$CurrQueryString=
				"
				INSERT
				INTO	Roads (X0, Y0, X1, Y1)
				VALUES	($X0, $Y0, $X1, $Y1)
				";
			$Sucsses=$this->QueryToDB($CurrQueryString);
	
			$Table[]=array($X0,$Y0,$X1,$Y1);
			if($Sucsses) $i++;
			}

	Out("Количество записей сделанных командой INSERT = $i");
	Out("Успех команды заргузки данных при создании таблицы = $Sucsses");
	//return $Table;
	}

/*private function RecreateRoadsView1()
	{
	Out("Перезагружается таблица RoadsView");
	$CurrQueryString="DROP VIEW RoadsView";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды DROP VIEW  = $Sucsses");

	$CurrQueryString=
	"
	CREATE VIEW RoadsFrom
	AS SELECT Roads.id, AnuireLocations.Name
	FROM	AnuireLocations, Roads
	WHERE  AnuireLocations.X = Roads.X0 AND AnuireLocations.Y = Roads.Y0
	ORDER BY Roads.id
	";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды CREATE VIEW = $Sucsses");
	$CurrQueryString=
	"
	CREATE VIEW RoadsTo
	AS SELECT Roads.id, AnuireLocations.Name
	FROM	AnuireLocations, Roads
	WHERE  AnuireLocations.X = Roads.X1 AND AnuireLocations.Y = Roads.Y1
	ORDER BY Roads.id
	";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды CREATE VIEW = $Sucsses");
	$CurrQueryString=
	"
	CREATE VIEW RoadsView
	AS SELECT RoadsFrom.id, RoadsFrom.Name, RoadsTo.Name AS Name2
	FROM	RoadsFrom, RoadsTo
	WHERE  RoadsFrom.id = RoadsTo.id
	ORDER BY RoadsFrom.id
	";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды CREATE VIEW = $Sucsses");
	}*/

private function RecreateRoadsView()
	{
	Out("Перезагружается таблица RoadsView");
	$CurrQueryString="DROP VIEW RoadsView";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды DROP VIEW  = $Sucsses");

	$CurrQueryString=
	"
	CREATE VIEW RoadsView
	AS SELECT Roads.id, AnuireLocations.Name AS NameFrom, AnuireLocationsCopy.Name AS NameTo
	FROM	AnuireLocations, AnuireLocations AnuireLocationsCopy, Roads
	WHERE  AnuireLocations.X = Roads.X0 AND AnuireLocations.Y = Roads.Y0
	AND  AnuireLocationsCopy.X = Roads.X1 AND AnuireLocationsCopy.Y = Roads.Y1
	ORDER BY Roads.id
	";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды CREATE VIEW = $Sucsses");

	}

private function RecreateLocations()
	{
	$ProgramDir=$this->TextFilesDir;
	$TableName="AnuireLocations";
	$CurrQueryString="DROP TABLE $TableName";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды DROP TABLE  = $Sucsses");

	$CurrQueryString=
	"
	CREATE TABLE IF NOT EXISTS $TableName
		(
		id	INT NOT NULL AUTO_INCREMENT,
	   	X	INT,
		Y	INT,
		Name	CHAR (255),
		Description	TEXT,
		Lvl	TINYINT,
		PRIMARY KEY (id)
		)
	";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды CREATE TABLE = $Sucsses");

	//Заполняем значениями из файла
	$Sucsses=file_exists($ProgramDir. "/Anuire_locations_inital.txt");
	$FileHandle=fopen("$ProgramDir/Anuire_locations_inital.txt","r");

	$i=0;
	if($Sucsses)
		while (!feof($FileHandle)) 
			{
			$String = fgets($FileHandle);

			$LocationName = strtok($String,"\t");
			$Description = strtok("\t");
			$Num1 = strtok("\t");
			$Num2 = strtok("\t");
			$Num3 = strtok("\t");

			$Sucsses=$this->ChangeRecordDB($LocationName,$Description,$Num2,$Num3,$Num1);
			if($Sucsses) $i++;
			}

	Out("Количество записей сделанных командой INSERT = $i");
	Out("Успех команды заргузки данных при создании таблицы = $FileHandle");
	}

private function RecreatePoints()
	{
	$ProgramDir=$this->TextFilesDir;

	$CurrQueryString="DROP TABLE Points";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды DROP TABLE  = $Sucsses");

	$CurrQueryString=
	"
	CREATE TABLE IF NOT EXISTS Points
		(
		No	INT NOT NULL AUTO_INCREMENT,
	   	X	INT,
		Y	INT,
		Z	iNT,
		PRIMARY KEY (No)
		)
	";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех команды CREATE TABLE = $Sucsses");

	//Заполняем значениями из файла
	$Sucsses=file_exists("$ProgramDir/Anuire_Points_inital.txt");
	$FileHandle=fopen("$ProgramDir/Anuire_Points_inital.txt","r");

	$i=0;
	if($Sucsses)
		while (!feof($FileHandle)) 
			{
			$String = fgets($FileHandle);

			$No = strtok($String,"\t");
			$X = strtok("\t");
			$Y = strtok("\t");
			$Z = strtok("\t");

			$CurrQueryString=
				"
				INSERT
				INTO	Points (X,Y,Z)
				VALUES	($X,$Y,$Z)
				";

			$Sucsses=$this->QueryToDB($CurrQueryString);
			if($Sucsses) $i++;
			}
	Out("Количество записей сделанных командой INSERT = $i");
	Out("Успех команды заргузки данных при создании таблицы = $Sucsses");
	}

private function ClearTable($TableName)
	{
	$CurrQueryString=
	"
	DELETE
	FROM	$TableName
	";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	Out("Успех очистки Таблицы $TableName = $Sucsses");
	}

private function ChangeRecordDB($StringValue,$Description,$X,$Y,$Lvl)
	{
	$CurrQueryString=
	"
		UPDATE	AnuireLocations
		SET	Description = '$Description', X = $X, Y = $Y, Lvl = $Lvl
		WHERE	Name = '$StringValue'
	";
	$Sucsses=$this->QueryToDB($CurrQueryString);
	$Count=mysql_affected_rows($this->ThisConnection);
	//Out("Успех команды UPDATE Record DB = $Sucsses, затронуто $Count записей");

	//Out( "Sucsses mysql affected rows Count = $Count");
	//$String=mysql_info($ThisConnection);
	//Out( "Sucsses mysql_info = $String");// Лень просто лень

	if($Count<1)
		{
		$CurrQueryString=
		"
			INSERT
			INTO	AnuireLocations (Name, Description, X, Y, Lvl)
			VALUES	( '$StringValue','$Description', $X, $Y, $Lvl)
		";
		$Sucsses=$this->QueryToDB($CurrQueryString);
		$Count=mysql_affected_rows($this->ThisConnection);
		//Out( "Успех команды INSERT Record to DB = $Sucsses, затронуто $Count записей");	
		}
	return $Sucsses;
	}

public function GetRoadsToPoint($X,$Y)
	{
	$CurrQueryString=
	"
		SELECT	*
		FROM	Roads
		WHERE	( X0 = $X AND Y0 = $Y ) OR ( X1 = $X AND Y1 = $Y )
	";
	$Result=$this->QueryToDB($CurrQueryString);
	$Count=mysql_num_rows($Result);

	if($Count>0)
		$Sucsses=1;
	else
		$Sucsses=0;
	//Out("Успех команды SELECT = $Sucsses, $Count записей выбрано из таблицы ");

	$Table="";
	if($Count>0)
		{
		$ArrLine = mysql_fetch_row($Result);
		while ($ArrLine)
			{
			$TableRow[0]=$ArrLine[1];
			$TableRow[1]=$ArrLine[2];
			$TableRow[2]=$ArrLine[3];
			$TableRow[3]=$ArrLine[4];
			$TableRow[4]=$ArrLine[0];

			if(($TableRow[0]!=$X)&&($TableRow[1]!=$Y))		// Результат должен быть нормализован	
				{
				$TableRow[2]=$TableRow[0];
				$TableRow[3]=$TableRow[1];
				$TableRow[0]=$X;
				$TableRow[1]=$Y;
				}

			$Table[]=$TableRow;
			$ArrLine = mysql_fetch_row($Result);
			}
		}
	return $Table;
	}

public function GetLocationID($X,$Y)
	{
	$CurrQueryString=
	"
		SELECT	id
		FROM	AnuireLocations
		WHERE	X = $X AND Y = $Y
	";
	$Result=$this->QueryToDB($CurrQueryString);
	$id=SQLFormat($Result);
	if($id=="")$id=-1;
	return $id;
	}


//Основной интерфейс
public function RecreateDB()
	{
	Out("пересоздаём БД");
	$this->RecreateLocations();
	$this->RecreateRoads();
	$this->RecreatePoints();
	$this->RecreateRoadsView();
	Out("пересоздана БД");
	}


public function Get($FromTable,$Row=-1,$Column="*")		//глобальная функция поиска. лаги. не работает
	{
	$CurrQueryString=
	"
		SELECT	$Column
		FROM	$FromTable
	";
	if($Row>0)
		{
		$AddQueryString=" WHERE	id = $Row";
		$CurrQueryString=$CurrQueryString.$AddQueryString;
		}
	$Result=$this->QueryToDB($CurrQueryString);
	$Table=SQLFormat($Result);
	return $Table;
	}

public function Find($InTable,$Condition)			//глобальная функция поиска. Проще чем предыдущая , но возвращает только id
//Это вообще из рук вон плохо, но пока пусть так. В идеале Надо оставить только 1 переменную : $TextToFind
	{
	$CurrQueryString=
	"
		SELECT	id
		FROM	$InTable
		WHERE	$Condition
	";
	$Result=$this->QueryToDB($CurrQueryString);
	$Table=SQLFormat($Result);
	return $Table;
	}

}

Out("Успешно отработали $Description");
?>
