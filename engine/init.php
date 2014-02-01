<?php

session_start();

// Запрет/разрешение вывода ошибок интерпретатора
error_reporting(0);

// Глобализируем некоторые вещи
global $Settings, $error_msg, $objectCategories;




/* ### I. Подключение файлов ### */

// конфигурация для доступа к БД
include "configs.php";

// файл с общими функциями
include "shared.php";

// главная библиотека функций
include "main.func.php";

// кешированные переменные
include "metro-cache.php";




/* ### II. Инициализация переменных ### */

// Переменные-пустышки
$HomePageYes = $active = $activea = $actives = $activeas = "";
$template = "default";
$error_msg = array();

// Получение базовых параметров системы
getSettings();

// Первичная инициализация переменных и удаление ненужного из передаваемых параметров
if (empty($_REQUEST['page_path']))	$page_path	= "default";	else $page_path	= trimmer($_REQUEST['page_path']);
if (empty($_REQUEST['cat_path']))	$cat_path	= "default";	else $cat_path	= trimmer($_REQUEST['cat_path']);
if (empty($_REQUEST['action']))		$_REQUEST['action']="";
if (!empty($_GET['view']))			$view = trimmer($_GET['view']);
if (!empty($_GET['template']))		$template = $_GET['template'];

// Запоминать ГЕТ запрос
$urlPlusMySQLhwere = GenerateParams();
$endParams = $urlPlusMySQLhwere['qeuryforpaginator'];
$mySQLParams = $urlPlusMySQLhwere['mysql'];

// Раздел по умолчанию
$parent_id = "-1";
if (!empty($_GET['id']))			$parent_id=intval($_GET['id']); 

// Глобальные переменные для частого обращения
// Внимание! Две константы PREFIX (используется в
// названиях БД), SITEURL (урл сайта) назначаются 
// в файле configs.php, поскольку последний файл
// также подключается и из админки
define("CATEGORY_URL_PATH",$cat_path);
define("CATEGORY_ID",GetCategoryIdV2());
define("PAGE_URL_PATH",$page_path);

// В верстке будет использовться для определения таблицы стилей: главная или внутренняя
if (isHomePage())		$HomePageYes = "main";
define("HOME_PAGE_YES", $HomePageYes);





/* ### III. Запуск страницеобразующих функций и обработчика событий ### */


// Если присутствует POST переменная, запускаем парсер содержимого POST
if (!empty($_POST['dosometh'])) postParser();

// Генерируем страницу
pageGen();

// Если пользователь залогинен, отображаем ссылки на редактирование (фича не работает, поскольку нет ID страницы)
if ($_SESSION && $_SESSION['loggedin']== "yes") 
@$page['adminedit'] = "<div style='margin-bottom: 22px;'>&nbsp; <a href='/mad/objects/edit/$page[object_id]?parent_id=$page[parent_id]' style='color: white !important; background-color: red; padding: 3px 6px;' target='_blank'><B style='color: white !important; '>редактировать</B></a></div>";

?>