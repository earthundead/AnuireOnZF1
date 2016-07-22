<?php
// Указание пути к директории приложения
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH',
              realpath(dirname(__FILE__) . '/../application'));
 
// Определение текущего режима работы приложения (Начисто/Тестирование/Отладка)
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
              (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
                                         : 'production'));

//Создание объекта приложения, начальная загрузка, запуск
require_once 'Zend/Application.php';

$appOptions=APPLICATION_PATH . '/configs/application.ini';			//Опции без поддежки ресурсов
$application = new Zend_Application(APPLICATION_ENV, $appOptions);

$bootstrap = $application->getBootstrap();
$registry = Zend_Registry::getInstance();							//Отковыряем и сохраним несколько переменных

$string = $bootstrap->getOption("path");
$dataFilesPath = $string["data"];
$registry -> set('dataFilesPath', $dataFilesPath);
$applicationFilesPath = $string["application"];
$registry -> set('applicationFilesPath', $applicationFilesPath);
$string = $bootstrap->getOption("db");
$registry -> set('dbSettings', $string);

$stream = @fopen($dataFilesPath . '/logfile.log', 'a', false);		//Создание логфайла и организация логгинга
$logWriter = new Zend_Log_Writer_Stream($stream);
$logger = new Zend_Log($logWriter);
$logger->info("Запущен index.php");									//Официальное начало работы
$registry -> set('logger', $logger);

$db  = new Application_Model_DBInterface;
$registry -> set('programDB', $db);
$model = new Application_Model_ModelInterface;
$registry -> set('model', $model);

$controller = Zend_Controller_Front::getInstance();
$controller->setDefaultAction("viewform");
$string = $controller->getDefaultAction();
$logger->info("Default Action is : " . $string);

$application->bootstrap()
            ->run();
                
if (Zend_Registry::isRegistered('startPointId'))
	{
	$startPointId = Zend_Registry::get('startPointId');
	$logger->info("Form startPointId : $startPointId");
	}
if (Zend_Registry::isRegistered('endPointId'))
	{
	$endPointId = Zend_Registry::get('endPointId');
	$logger->info("Form endPointId : $endPointId");
	}
                     
$logger->info("Успешно отработал index.php");
?>
