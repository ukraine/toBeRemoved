<?php

// Данные для доступа к БД
$db_host = "localhost";
$db_user = "";
$db_pass = "";
$db_name = "";
$db_pref = "";

define("PREFIX", $db_pref);
define("SITEURL", "http://$_SERVER[HTTP_HOST]/");
define("FILESTORAGEPATH", $_SERVER['DOCUMENT_ROOT'] . "/_images/");

define("IMAGESPATH_THUMBS", FILESTORAGEPATH . "thumbs/");
define("IMAGESPATH_SOURCE", FILESTORAGEPATH . "source/");
define("IMAGESPATH_MIDDLE", FILESTORAGEPATH . "middle/");

// Соединяемся и выбираем нужную базу данных
mysql_select_db($db_name, mysql_connect($db_host, $db_user, $db_pass));

mysql_query('SET OPTION CHARSET UTF8');
mysql_query('SET NAMES \'UTF8\'');

// Запрещенные символы и их замена
// $ForbiddenChars = array("'", "‘ ", "`",  "'", "</textarea>", "<textarea");
// $AllowedChars = array("&#39;", "&lsquo;", "&#96;", "&#39;", "&lt;/textarea&gt;", "&lt;textarea");

$ForbiddenChars = array("'", "‘ ", "`",  "'");
$AllowedChars = array("&#39;", "&lsquo;", "&#96;", "&#39;");

// Обязательные к заполнению поля для формы обратной связи
$FeedbackRequiredFields = array("email"=>"Адрес электронной почты", "name"=>"Имя", "text"=>"Текст сообщения");

// Соотв. русским месяцам
$Months = array("Января","Февраля","Марта","Апреля","Мая","Июня","Июля","Августа","Сентября","Октября","Ноября","Декабря");

// Переменные для конкретного сайта


// Типы цены
$Priceper = array("сутки","месяц","сезон");

// Для генерации чекбоксов
$AdvertiseOnTheLeft = array("advertise"=>"Поставьте галочку слева, если хотите рекламировать объект");
$ObjectOfficeCenter = array("objecttype"=>"Это офисный центр");
$ObjectWarehouse = array("objecttype"=>"Это складской комплекс");

// Регион
$Regions = array("Подмосковье","Москва");

$checkedOrNot = array("","checked");

?>