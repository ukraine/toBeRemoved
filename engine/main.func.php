<?php
/* 

main.func.php

Дата модификации 
29.03.2009 23:44
25.03.2009 14:40
06.02.2009 15:35

Справочник функций файла 

строка # название # описание

convertSQLtimeV2 - преобразование даты из mySQL в читаемую
GetCategoryIdV2 - получение ID категории
GetCurrentPath - вывод текущего пути страницы
getFAQ - генерация списока частых вопросов и ответов
GetPageName - определение имени страницы по пути и ид категории
getNewsByID - Вывод определенной новости, согласно идентификатору из строки адреса
getObjectByID($content) - Вывод определенного объекта, согласно идентификатору из строки адреса
getSiteMap - вывод карты сайты
getSiteMapLinks - вывод карты сайты служ.
getSettings() -  базовые параметры системы
getSubLinks - подссылки
getThisSectionPages - Отображение списка страниц в конкретном разделе

GenerateMenu - генерация разнообразных меню
GenerateListOfSomeThing - генерация разнообразных списков

ifExistGetValue - отображение значения в поле формы, если оно есть
IsHomePage - определение домашней страницы
IsRequiredFieldsFilled - Проверка правильности заполнения полей
isTopActive - определение текущего раздела в меню
limitVisiblePart() - ограничитель количества выводимых символов
pageGen - генерация страницы
postParser - обработчик пост-запросов
trimmer() - очистки переменных
ShowValueFromSettings() - получение значения из параметров
ShowErrorMessage - проверка и вывод системных сообщений
sutki - определение поры суток
validateEmailAddress - проверка емейл адреса

*/

// Функции ядра

// Очистка url от html и прочих тегов
function trimmer($variable) {

	/*
	@param unknown_type $variable
	@return unknown
	16.12.2007 */
	
	return trim(strip_tags(stripslashes($variable)));	

}

// Ограничитель количества символов
function limitVisiblePart($fieldname, $limitto="22", $threedots = "") {

	/*
	@param string $fieldname
	@param int $limitto
	@return string

	// 03.08.2007 */

    if (strlen($fieldname) > $limitto) $threedots = "...";
    return strip_tags(substr(stripslashes($fieldname), 0, $limitto)).$threedots;
}

/**
 * Вывод значения из параметров, если он есть
 *
 * @param mix $value
 */
function ShowValueFromSettings($value) {
    
	global $Settings;
    if (!empty($Settings[$value]) ) echo $Settings[$value];
}

/**
 * Проверка наличия переменных в формах. Если есть - выводим
 *
 * @param string $valuename
 */
// 16.07.2007
function ifExistGetValue($valuename) {
    global $f;

    if (isset($_POST[$valuename])) echo $_POST[$valuename];
    else echo $f[$valuename];
}

/**
 * Функция проверки наличия ошибок
 */
// 16.07.2007
// 16.12.2007 Added status
// ufian 27.04.2008
function ShowErrorMessage() {

    global $error_msg;

    if (!empty($error_msg))	{

        // ufian 27.04.2008
        if(!isset($error_msg['status'])) $error_msg['status'] = '';
        if(!isset($error_msg['message'])) $error_msg['message'] = '';
        // /ufian 27.04.2008

        echo "<div class='error_msg' id='{$error_msg['status']}'>{$error_msg['message']}</div>";
    }
}

// преобразование даты из mySQL в читаемую
function convertSQLtimeV2($time) {

	// 27.09.2008
	// SQL timestamp to normal date and time

	$timestamp = substr($time, 8,2).".";
	$timestamp .= substr($time, 5,2).".";
	$timestamp .= substr($time, 0,4);

	return $timestamp;

}

// получение ID категории
function GetCategoryIdV2 () {

	// 27.09.2008 Get Category ID by its URL path

	$rescat = ExecuteSqlGetArray("SELECT * FROM `" . PREFIX . "categories` WHERE cat_path='". CATEGORY_URL_PATH ."'");
	return $rescat['id'];

}

/**
 * Определение имени страницы по пути и ид категории
 * 
 * @param string $page_path
 * @param int $cat_id
 * @return string
 */
// 03.08.2007
// ufian 27.04.2008
function GetPageName ($page_path, $cat_id) {
    global $db_pref;
    $respage = ExecuteSqlGetArray("SELECT * FROM `{$db_pref}pages` WHERE `page_path`='{$page_path}' AND `cat_id` = '$cat_id'");

	// echo $respage['page_name'];

    // ufian 27.04.2008
    return  isset($respage['page_name'])? $respage['page_name'] : "";
    // /ufian 27.04.2008
}



/**
 * Определение домашней страницы
 * 
 * @return bool
 */
// 03.08.2007
function IsHomePage($category="default",$page="default") {
    return (CATEGORY_URL_PATH == $category && PAGE_URL_PATH == $page);
}

/**
 * Проверка email адреса
 *
 * @return bool
 */
function validateEmailAddress() {

    global $error_msg, $_POST;

    if(!isset($_POST['email'])) return false;

    $email = $_POST['email'];

    $err = "Пожалуйста, введите верный адрес";

    // First, we check that there's one @ symbol, and that the lengths are right
    if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
        // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
        $error_msg = $err;

        return false;
    }

    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);

    for ($i = 0; $i < sizeof($local_array); $i++) {
        if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
            $error_msg = $err;

            return false;
        }
    }

    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name

        $domain_array = explode(".", $email_array[1]);

        if (sizeof($domain_array) < 2) {
            $error_msg = $err;

            return false; // Not enough parts to domain
        }

        for ($i = 0; $i < sizeof($domain_array); $i++) {

            if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
                $error_msg .= $err;

                return false;
            }

        }
    }

    return true;
}

/**
 * Получение текущего пути страницы
 */
function GetCurrentPath($nameAndUrlToParent="") {

    global $Settings, $page, $BuildingClass;

	$rarr = $darr = $LevelUPCat = "";

	$PageName = GetPageName(PAGE_URL_PATH, CATEGORY_ID);

	if (CATEGORY_URL_PATH == "news" && !empty($_GET['id'])) $PageName = $page['title'];
	if (CATEGORY_URL_PATH == "catalogue" && PAGE_URL_PATH == "view" && !empty($_GET['id'])) {

		if ($page['parent_id'] !="-1") $LevelUPCat = "<a href='/catalogue/?parent_id=$page[parent_id]'>" . mb_strtoupper($BuildingClass[$page['parent_id']]) . "</a>";
		
		$PageName =  $LevelUPCat . $page['name'];

	}

	if (!empty($_GET['parent_id'])) $PageName =  "<a href='/catalogue/'>Каталог</a>" . mb_strtoupper($BuildingClass[$_GET['parent_id']]);

    if (PAGE_URL_PATH == "default") {
        $CurrentPath = "<a class='small_1' href='" . SITEURL . "'>{$Settings['sitename']}</a> $rarr $PageName $darr";

    }	else {
        $row = ExecuteSqlGetArray("SELECT * FROM `" . PREFIX . "categories` WHERE `cat_path`='" . CATEGORY_URL_PATH . "'");
        $CurrentPath = "<a class='small_1' href='" . SITEURL . "'>{$Settings['sitename']}</a> $rarr
                        <a class='small_1' href='" . SITEURL . "{$row['cat_path']}/'>" .  ucfirstUTF($row['cat_name']) . "</a> $rarr
                        $nameAndUrlToParent
						<span class='small_1'>$PageName</span> $darr";
    }

   echo $CurrentPath;
}

/**
 * Вывод меню
 *
 * @param string $toprobottom
 * @param string $separator
 * @param string $table
 * @param int $id
 * @param string $orderby
 * @param string $sortresults
 * @param string $position
 * @param string $MenuLinks
 */
function GenerateMenu(

// Config
$toprobottom="",		// Внизу или вверху страницы
$table="categories",	// Из какой таблицы генерировать
$id="",					// ID родительского сервиса
$orderby="priority",	// Сортировка по полю
$sortresults="desc",	// Сортировка по алфавиту
$position="",			// Не знаю зачем

$MenuLinks=""			// Инициализация переменной
)	{
    /* DATE ADDED:
    08.10.2006

    MOD DATE:
    16.12.2007	templates for menu links
    07.03.2008	custom query
    */

    global $Settings;


    $MenuSettings = array(
    "top" => array($Settings['itemsontopmenu'],""),
    "bottom" =>	array($Settings['itemsonbottommenu'],""),
    );
    // print_r($MenuSettings[$toprobottom]);

    $sql = "SELECT *
            FROM `$table`
            WHERE `visibility`='y'". $MenuSettings[$toprobottom]['1'] ."
            ORDER BY `{$orderby}` {$sortresults}
			LIMIT 0," . $MenuSettings[$toprobottom]['0'];
    
	// echo $sql;

    $sqlresult = mysql_query($sql);

    // ufian 27.04.2008
    if(!$sqlresult) return;
    // /ufian 27.04.2008

    $num_links =  mysql_num_rows($sqlresult);

	// echo $num_links;

    for ($i=0; $i<$num_links; $i++){

        $row = mysql_fetch_array($sqlresult);

        if ($table == "categories") {
            $cat_path = "{$row['cat_path']}/";
            if ($row['cat_path'] == "default") $cat_path = "";
        }

		// Active link
        $ahrefstart = "<li class='active'><span>—</span><a href='".SITEURL. "{$cat_path}'>"; $ahrefend = "</a>";

        @$template = array(
        "bottom"		=> "<a href='".SITEURL. "{$cat_path}'>".ucfirst($row['cat_name'])."</a>{$position}\n",
        "top"			=> "<li><span>—</span><a href='".SITEURL."{$cat_path}'>".ucfirst($row['cat_name'])."</a></li>\n",
        "top_current"	=> "$ahrefstart".ucfirst($row['cat_name'])."$ahrefend\n". getThisSectionPages()."</li>\n",
        );

		// echo $row['cat_path'] . ' AND ' . CATEGORY_URL_PATH;

		if ($row['cat_path'] == CATEGORY_URL_PATH) $toprobottom = "{$toprobottom}_current";

		// echo $toprobottom;

        // ufian 27.04.2008
        $MenuLinks = isset($template[$toprobottom])? $template[$toprobottom] : "";
        // /ufian 27.04.2008

		$toprobottom = str_replace("_current","",$toprobottom);

		echo $MenuLinks;
    }
}



// Отображение списка страниц в конкретном разделе
function getThisSectionPages($ThisSectionPages="<div id='submenu'><div><ul>",$URLpath="") {

	/*
	Version: 1.0
	Date: 27.09.2008
	*/

	$current = "";

	// Выполняем запрос
	$res = mysql_query ("

	SELECT * FROM `".PREFIX."pages` 
	WHERE `visibility`='y' 
	AND `cat_id` = '" . CATEGORY_ID . "' 
	AND `cat_id` != '9' 
	AND `page_path` !=  'view' 
	ORDER BY `priority` DESC

	");

	// Получили кол-во строк
	$numberOfItems =  mysql_num_rows($res);

	// Если страниц меньше равно единице
	if ($numberOfItems >= "2")	{

		// Начинаем разбирать полученный массив
		for ($i=0; $i<$numberOfItems; $i++){

			// Получаем данные о странице
			$row = mysql_fetch_array($res);

			// Инициализируем пустышку
			$current="";

			// Указатель на главную страницу, прописывать дефольт не нужно
			if ($row['page_path']!=="default") $URLpath = $row['page_path']."/";

			// Формирование ссылок
			$starthref = "<a href='". SITEURL . CATEGORY_URL_PATH."/$URLpath'>";
			$endhref = "</a>";

			// Если сейчас открыта эта страница, применяем к ней класс текущей страницы
			if (PAGE_URL_PATH == $row['page_path']) {
				$current=" class='current'";
				$starthref = $endhref = "";
			}

			// Собственно закатываем полученные данные в переменную отформатировнный список страниц
			$ThisSectionPages .= "\t\t\t\t\t\t<li$current>{$starthref}$row[page_name]{$endhref}</li>\n";

			// Сносим класс текущей страницы
			unset($current, $URLpath);
		}

			return $ThisSectionPages."</ul></div></div>";

	}
}

/**
 * Времена суток
 */
function sutki() {
    $time = date("G", time()+18000);
    if ($time > 9 && $time < 18 ) echo "day";
    else echo "night";
}

// Генерация 404 страницы
function return404() {

		$_SERVER['REDIRECT_STATUS'] = 404;
		echo file_get_contents(SITEURL . "index.php?cat_path=errors&page_path=404&template=errors");
		exit();

}

// Вывод определенной новости, согласно идентификатору из строки адреса
function getNewsByID() {

    /*
    Version:	1.1	WITH CNVRTSQLTME (MySQL v4 support)
    Module:		An article generator
    04.10.2006	removed convertSQLtime
    18.11.2006	Added convertSQLtime
	10.10.2008	Упрощены некоторые вещи
    */

	$page = array();

    $row = ExecuteSqlGetArray("SELECT * FROM `" . PREFIX . "news` WHERE `id`='" . intval($_GET['id']) . "' AND `visibility` IN ('y','d')");

    if ($row != false) {

        $page['title'] = $page['h1'] = ucfirst($row['title']);
        $page['keywords'] = $row['keywords'];
        $page['description'] = $row['description'];
        $page['timestamp'] = $row['timestamp'];
        $page['content'] = nl2br($row['content']);
		
		return $page;

    }

	else Return404();

}


// Вывод определенного объекта, согласно идентификатору из строки адреса
function getObjectByID($content="") {

    /*
    Version:	1.1	WITH CNVRTSQLTME (MySQL v4 support)
    Module:		An article generator
    04.10.2006	removed convertSQLtime
    18.11.2006	Added convertSQLtime
	10.10.2008	Упрощены некоторые вещи
    */

	$id = intval(trimmer($_GET['id']));
	$whereFilter = array ("o.id = '$id'","`parent_id` = '$id'","id = '$id'");

	$page = array();
    $page = ExecuteSqlGetArray("

		SELECT 
		
			o.name,p.id,parent_id,advcontent,
			description,metro,price,priceper,
			path,totalarea,transportby,region,
			remont,minutestometro,livingarea,
			furniture, phone, refrigerator,
			district, dealtype, brokerName, brokerContact,
			podmosk,o.timestamp, commissionType,
			storonamira,parent_id

		FROM `" . PREFIX . "objects` o, `" . PREFIX . "objects_pics` p
		WHERE $whereFilter[0]
		AND p.object_id ='$id'
		AND o.visibility IN ('y','d')
		ORDER BY p.id DESC

		");

    if ($page != false && !empty($page['price'])) {

		$page['object_id'] = $id;
		$page['adminedit'] ="";
		$page['title'] = $page['h1'] = $page['name'];
		// $page['name'] = getParentsAndGenerateCurrentPath($page["parent_id"],$page['name']);
		$page['keywords'] = str_replace(" ",",", $page['title']);
        $page['description'] = limitVisiblePart($page['description'], $limitto="250");
        $page['timestamp'] = $page['timestamp'];
		$page['description'] = $page['description'];
		$page['entity'] = "object";
		$page['content'] = $content;		
		// print_r($page);

	}

	else  {

    $page = ExecuteSqlGetArray("

		SELECT name,description,timestamp,parent_id 
		FROM `" . PREFIX . "objects`
		WHERE $whereFilter[2]
		AND visibility='y'

		");

		$page['photos'] = getPhotos($id);
		$page['title'] = $page['h1'] = $page['name'];
		// $page['name'] = getParentsAndGenerateCurrentPath($page["parent_id"],$page['name']);
		$page['keywords'] = str_replace(" ",",", $page['title']);
        $page['description'] = limitVisiblePart($page['description'], $limitto="250");
		$page['content'] = $content;
		$page['entity'] = "category";
		//	print_r($page);
		
	}

	return $page;


}

function getParentsAndGenerateCurrentPath($parent_id,$name="",$start="") {

		$array = ExecuteSqlGetArray("

		SELECT id,name,parent_id 
		FROM `" . PREFIX . "objects`
		WHERE `id` = '$parent_id'
		AND visibility='y'

		");

		if ($array) $start = " <a href='/catalogue/$array[id]/'>$array[name]</a> &rarr; ";


	if ($array['parent_id'] != 0) $name = getParentsAndGenerateCurrentPath($array["parent_id"],$name,$start); 
	return $start.$name;

}


function getPhotos( $id, $photos="" ){

	global $Settings;

	$st_ids = array( $id );
	$ids = $st_ids;
	while( count( $st_ids ) ){
		foreach( $st_ids as $sid ){

			$qres = mysql_query("SELECT `id` FROM `" . PREFIX . "objects` WHERE `parent_id`=$sid ORDER BY `name` ASC" );
			$tmp_ids = array();
			while( $row = mysql_fetch_assoc( $qres ) ) {
				$tmp_ids[] = $row['id'];
			}
			$ids = array_merge( $ids, $tmp_ids );
			$st_ids = $tmp_ids;
		}
	}

	$ids_str = '(';
	foreach( $ids as $sid ){
		$ids_str .= $sid . ', ';
	}
	$ids_str = substr($ids_str, 0 , ( strlen( $ids_str )-2 ) ) . ')';

	$sql0 = 	"

	FROM `" . PREFIX . "objects` WHERE `parent_id` IN $ids_str AND `price` > 0 ORDER BY `parent_id` ASC, `id` ASC, `name` ASC
	
	";


	/* $sql1 = 	"

	FROM `objects` o, `objects_pics` p WHERE `parent_id` IN $ids_str AND  p.object_id = o.id AND `priceperitem`>0 ORDER BY `parent_id` ASC, `name` ASC, id ASC
	
	"; */

	$sql1 = 	"

	FROM `" . PREFIX . "objects` o, `" . PREFIX . "objects_pics` p WHERE `parent_id` IN $ids_str AND  p.object_id = o.id AND `price` > 0 GROUP BY `id` ORDER BY `name` ASC, `parent_id` ASC, id ASC
	
	";


	// echo $sql0;

	// Считаем общее кол-во объектов
	$res = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS counter $sql0"));

	// Если хоть что-то есть, выводим список
	if($res) {
		
		// Всего записей
		$count = $Settings['count'] =$res['counter'];

		// echo $count;

		// Считаем общее кол-во страниц
		$totalpages = $Settings['totalpages'] = ceil($count/$Settings['itemsPerPage']);

		// узнаем на какой странице находимся
		if(empty($_GET['page'])) $Settings["pagina"] = $pagina = 0; else $pagina = $Settings["pagina"] = $_GET['page'];

		// Выставление лимита кол-ва объектов на странице (так же исп-зуется для нумерации объ. на страницах)
		$startlimit = $Settings['itemsPerPage']*$pagina;

		// Первый объект на странице
		$startobject = $Settings['startobject'] = $startlimit + 1;

		// Последний объект на странице
		$endobject = $Settings['endobject'] = $startobject + $Settings['itemsPerPage'] - 1;

		// Если объектов меньше, чем разрешено на странице
		if ($count <= $Settings['itemsPerPage']) $Settings['endobject'] = $count;
		
		// Исп. для последней страницы
		if ($endobject > $count) $Settings['endobject'] = $count ;

		// Формирование конечного запроса
		$sql1 .= " LIMIT $startlimit, $Settings[itemsPerPage]";

		// echo $sql1;

		// Собственно делаем выборку
		$qres = mysql_query("SELECT o.id, o.name, totalarea,price,metro,priceper,parent_id, object_id, path AS imageUrl $sql1");

		$photos = array();
		while( $row = mysql_fetch_assoc( $qres ) ){
			$photos[] = $row;
		}

	return $photos;

	}
	
}



/**
 *
 * @param string $section
 */
function getSubLinks($section="default") {
    global $CategoryId, $cat_path, $page_path, $db_pref;

    if ($section == $cat_path) {
        GetCategoryId ($section);

        $sql_result = mysql_query(
        "SELECT *
             FROM `{$db_pref}pages` 
             WHERE `visibility`='y' AND `cat_id` = '{$CategoryId}' AND `page_path` !=  'default' 
             ORDER BY `priority` desc");
        $num_links =  mysql_num_rows($sql_result);

        if ($num_links > 0) {
            $SubLinks="<ul>";

            for ($i=0; $i<$num_links; $i++){
                $row = mysql_fetch_array($sql_result);

                if ($row['page_path'] == $page_path) $SubLinks .= "<li>{$row['page_name']}</li>";
                else $SubLinks .= "<li><a href='/{$cat_path}/{$row['page_path']}/'>{$row['page_name']}</a></li>";
            }
            echo $SubLinks."</ul>";
        }
    }
}

/**
 * 10.10.2008 Сокращение кода
 *
 */
function postParser() {

    global $error_msg;

    $do = isset($_POST['dosometh'])? $_POST['dosometh'] : "default";
    
	switch($do) {

        default: break;

        case "do_contact":

			$error_msg['message'] = "Не забудьте заполнить форму перед ее отправкой";
            $error_msg['status']  = "false";

			if (empty($_POST['message']) && strlen($_POST['text'])>6) {

               @sendMail();
               $error_msg['message'] = "Информация отправлена";
               $error_msg['status']  = "true";

            }

		break;
    }
}

/**
 * Проверка правильности заполнения полей
 *
 * @param array $RequiredFielsArray
 * @return bool
 */
// 17.07.2007
function IsRequiredFieldsFilled($RequiredFielsArray) {
    global $error_msg, $translation, $_POST;

    foreach($RequiredFielsArray as $key=>$value)	{
        if (empty($_POST[$key])) $error_msg['message'] .= $translation['131'] . "\"<B>{$value}</B>\"" . $translation['132'];
    }

    if (empty($error_msg)) return true;
    else return false;
}


/**
 * Вывод страницы
 * 
 * 21.10.2007	Добавлены настройки сео по умолчанию
 * 10.10.2008	Обработка новостей по ID
 *				Упрощена вывода контента
 *				Упрощен код на несколько строк
 *
 * @param string $cat_path
 * @param string $page_path

	08.02.2009 23:48

 */
function pageGen($content = "",$pageUrlPath=PAGE_URL_PATH, $CategoryIDpath="") {

    global $page, $AllowedChars, $ForbiddenChars, $Settings;

	// Получаем массив с данными о странице
    $page = ExecuteSqlGetArray(
    "SELECT *
                 FROM `pages` 
                 WHERE `" . PREFIX . "page_path`='" . $pageUrlPath . "' AND `cat_id` = '" . CATEGORY_ID . "' AND (`visibility`='y' OR `visibility`='d')");

				 $page['CategoryIDpath'] = $CategoryIDpath;

	// print_r($page);

	// Если результат получен
	if (!empty($page['cat_id'])) {

		// Обрабатываем контент, конвертируюя попутно опасные символы
		if (!empty($page['content'])) $page['content'] = str_replace($AllowedChars, $ForbiddenChars, $page['content']);

		// Заточка. Если мы просматриваем конкретный объект
		if (CATEGORY_URL_PATH==="catalogue" && !empty($_GET['id'])) { $page = getObjectByID($page['content']); $pageUrlPath = "view";}

		// Втыкаем сеотаги в массив для дальнейшей проверки на пустоту
		@$seotags = array("title" => $page['title'], "description" => $page['description'], "keywords" =>$page['keywords']);

		// Сео-таги по умолчанию срабатывают, если в странице они пустышки
		foreach($seotags as $key=>$value) {
			if (empty($page[$key])) $page[$key] = $Settings['default_'.$key];
		}
		
		// Для сео отображаем дату последней модификации если таковое есть
		// @header('Last-Modified: '.$page['timestamp']);
	
	}

	// Если мы просматриваем конкретную новость
	elseif (CATEGORY_URL_PATH==="news" && PAGE_URL_PATH==="view") $page = getNewsByID();

	else { 
		
		return404();

		}

}


/**
 *  08.10.2006
 * 17.02.2007 Removing the link to the same page as section root goes
 * 26.10.2007 A bit simplified 
 *
 * @param int $CategoryId
 * @return string
 */
function getSiteMapLinks($CategoryId,$catPath,$SiteMapLinks="") {

    global $Settings;

    $sql = mysql_query (
					"SELECT *
					FROM `" . PREFIX . "pages` 
					WHERE `visibility`='y' AND `cat_id` = '{$CategoryId}'  AND `servicepage`='0'
					ORDER BY `priority` desc");

    if($CategoryId == 4) {

        if($Settings['allownewsonsitemap'] == 1) {

            $sql_res = mysql_query ("SELECT * FROM `" . PREFIX . "news` WHERE `visibility`='y' ORDER BY `priority` desc");
            if($sql_res && mysql_num_rows($sql_res) > 0) {
                for ($i=0; $i<mysql_num_rows($sql_res); $i++){
                    $row = mysql_fetch_array($sql_res);
                    $SiteMapLinks .="<li><a href='/news/{$row['id']}/'>{$row['title']}</a></li>\n";
                } // end for
            } // end if sql_res
        } // end if allowed
	} // end if news

    elseif($CategoryId == 22) {

        if($Settings['allownewsonsitemap'] == 1) {
            $sql_res = mysql_query ("SELECT * FROM `" . PREFIX . "objects` WHERE `visibility`='y' AND `parent_id` = '0' ORDER BY `priority` desc");

            if($sql_res && mysql_num_rows($sql_res) > 0) {
                for ($i=0; $i<mysql_num_rows($sql_res); $i++){
                    $row = mysql_fetch_array($sql_res);
                    $SiteMapLinks .="<li><a href='/catalogie/{$row['id']}/'>{$row['name']}</a></li>\n";
                }
            }
        }

    } else {
        for ($i=0; $i<mysql_num_rows($sql); $i++){
            $row = mysql_fetch_array($sql);
            if ($row['page_path']!= "default" ) {
                $SiteMapLinks .= "<li><a href='" . SITEURL . "{$catPath}/{$row['page_path']}/'>{$row['page_name']}</a></li>\n";
            }
        }
    }

    return "<ul>".$SiteMapLinks."</ul>";
}


/**
 * Сгенерировать карту сайта
 *
 * 08.10.2006
 */
function getSiteMap () {

    global $catPath, $Settings;

    $CategoryAndPages = "<a href='" . SITEURL . "'>{$Settings['sitename']}</a><ul>";

    $sql_result = mysql_query (
    "SELECT *
                     FROM `" . PREFIX . "categories` 
                     WHERE `visibility`='y' AND `cat_path` !='default'
                     ORDER BY `priority` desc");

    $num_links =  mysql_num_rows($sql_result);

    for ($i=0; $i<$num_links; $i++)	{

        $SiteMapLinks = "";
        $row = mysql_fetch_array($sql_result);
		
		$catPath = $row['cat_path'];
        $CategoryAndPages .= "<li><a href='" . SITEURL . "{$catPath}/'>" . $row['cat_name'] . "</a></li>\n";

        $SiteMapLinks =  getSiteMapLinks($row['id'],$row['cat_path']);

        if($SiteMapLinks != "") $CategoryAndPages .= "{$SiteMapLinks}";
    }

    echo $CategoryAndPages."</ul>";
}



function GenerateListOfSomeThing($for, $limit="", $orderby = "`priority` DESC",$morequery="",$opened="") {

	//	DATE ADDED: 13.02.2008
	//	DATE MODED:	17.02.2008
	//	DATE MODED:	25.07.2010

	global $cat_path, $page_path, $page, $Settings, $ForbiddenChars, $AllowedChars, 
		$objectCategories, $metros, $regions, $districts, $commissionTypes, $BuildingClassTypes,
		$BuildingClass, $dealTypes, $warehousesClasses, $officesClasses, $districtS;

	$commissionTypes['0']=$districts['0'] = $regionDistricts['0'] = "";

	$morequery = str_replace($AllowedChars, $ForbiddenChars, $morequery);

	$queries[$for]['4'] = "*";
	$queries[$for]['5'] = "";

	@$queries = array(

		/*	
			#1 - выборка из ТАБЛИЦЫ
			#2 - ФИЛЬТРАЦИЯ выборки
			#3 - ЛИМИТИРОВАНИЕ выборки
			#3 - СОРТИРОВКА выборки
		*/

		"news"				=> array("news",$morequery,$Settings['newsperpage'],"`timestamp` DESC"," "," "),
		"newsarchive"		=> array("news",$morequery,"","`timestamp` DESC","",""),
		"generateindex"		=> array("pages","AND `cat_id` = '" . CATEGORY_ID . "' AND `page_path` != 'default'"),
		"leftmenu"			=> array("objects",$morequery,$Settings['newsperpage']),

		"catalogueObjects"	=> array(
			"objects o,objects_pics p",
			"AND servicepage !='1' AND o.id = p.object_id " . $morequery,
			$Settings['itemsPerPage'],
			"`timestamp` DESC",
			"	o.id,p.object_id,parent_id,
				p.path,totalarea,
				price,visibility,
				timestamp,advcontent,
				metro,region,district,
				dealtype,commissionType,
				realestateClass,brokerName,
				brokerContact",
			"GROUP BY `id`"	
		),

		"advertisedObjects"	=> array(
			"objects o,objects_pics p",
			"AND servicepage !='1' AND o.id = p.object_id AND `advertise`='1'",
			"3",
			"RAND()",
			"	o.id,p.object_id,parent_id,
				p.path,totalarea,
				price,visibility,advertise,
				metro,region,district,
				dealtype,commissionType,
				realestateClass",
			"GROUP BY `id`"	
		),

		"relatedObjects"	=> array("objects","AND parent_id = '$page[parent_id]' AND servicepage !='1' AND `id` != $_GET[id]", $Settings['newsperpage'],"`timestamp` DESC","","GROUP BY `id`"),
		"submenu"			=> array("pages","AND `cat_id` = '" . CATEGORY_ID . "' AND `page_path` != 'default'"),

		);

	// print_r($queries);

	// Если сказано лимитировать кол-во объектов - лимитируем
	if (!empty($queries[$for]['2'])) $limit = "LIMIT 0," . $queries[$for]['2'];

	// Специально для каталога
	if ($for == "catalogueObjects" && !empty($Settings['startlimit'])) $limit = "LIMIT $Settings[startlimit]," . $queries[$for]['2'];

	// Если сказано сортировать объекты, то сортируем
	if (!empty($queries[$for]['3'])) $orderby = $queries[$for]['3'];

	// Если сказано сортировать объекты, то сортируем (НАДО СДЕЛАТЬ!)
	// if ($for == "objects") $categories = RunQueryReturnDataArray("objects_cats", $more="", $column="*");

	// Если указано что выбирать, то 
	if (empty($queries[$for]['4'])) ;

	// Если указано что выбирать, то 
	if (empty($queries[$for]['4'])) $queries[$for]['4'] = "*";

	// print_r($categories);

	// Дебаггинг запроса
	$sql = 
	"
	
	SELECT " . $queries[$for]['4'] . " FROM " . PREFIX . $queries[$for]['0']."
	WHERE `visibility`='y' 
	" . $queries[$for]['1'] ." ". $queries[$for]['5'] . "
	ORDER BY $orderby
	$limit
	
	";

	// echo $sql;

	// Получаем массив
	$res = mysql_query($sql);

	// Получаем общее число объектов
	$num_links =  mysql_num_rows($res);

    for ($i=0; $i<$num_links; $i++){

		$row = mysql_fetch_array($res);

		if (@$row["page_path"] == PAGE_URL_PATH) $opened = "opened";

		@$metros[0][$row['metro']] = $row['metro'];

		@$itemtemplate = array(

		// Шаблон для вывода списка новостей на первой странице
		"news"		=> 
			"\n<li>" . convertSQLtimeV2($row['timestamp']) . 
			"\n<a href='" . SITEURL . "news/$row[id]/'>". utf8_substr(htmlspecialchars(strip_tags(trim($row['title']))),0,80) . "...</a>
			</li>
			",

		// Шаблон для вывода списка новостей на первой странице
		"newsarchive"=> 
			"\n<li>" . convertSQLtimeV2($row['timestamp']) . 
			"\n &nbsp; <a href='" . SITEURL . "news/$row[id]/'>". utf8_substr(htmlspecialchars(strip_tags(trim($row['title']))),0,80) . "...</a>
			</li>
			",

		// Шаблон для вывода таблицы со списком языков
		"generateindex"	=> 
			"<div>
				<a href='".SITEURL."$cat_path/$row[page_path]/'><B>$row[page_name]</B></a>
				<p>$row[description]</p>
			</div>",

		// Шаблон для вывода таблицы со списком языков
		"submenu"	=> 
			"<li class='$opened'><span>&nbsp;</span>
				<div><a href='".SITEURL."$cat_path/$row[page_path]/' class='large_5'>$row[page_name]</a></div>
			</li>\n",

		// Шаблон для вывода таблицы со списком языков
		"catalogueObjects"	=>"
				<tr>
					<td class='prevTov' rowspan='2'><span>". mb_strtoupper($BuildingClass[$row['parent_id']]) . "</span> <br /> " . 
						mb_strtoupper($dealTypes[$row['dealtype']],'UTF-8') . "<br />
						<a href='".SITEURL."catalogue/$row[id]/'><img src='/_images/thumbs/$row[path]' alt='' title='' width='136' height='91' /></a></td>
					<td class='descTov'><a href='".SITEURL."catalogue/$row[id]/'>". 
						$BuildingClass[$row['parent_id']] . " класса " . $BuildingClassTypes[$row['parent_id']][$row['realestateClass']] . "</a>
						<p class='loc'>" . $regions[$row['region']] . " |  "  . $districtS[$row['region']][$row['district']] . "  | " . $metros[$row['region']][$row['metro']] . "</p>
						<p>" . utf8_substr($row['advcontent'],0,200) . "...</p></td>
					<td class='priceTov'><ul>
							<li><span class='Comission$row[commissionType]'>" . $commissionTypes[$row['commissionType']]. "</span></li>
							<li class='priceYear'><span>$row[price]</span> ye/м&sup2;</li>
							<li class='areaM'><span>" . ceil($row['totalarea']) . "</span>м&sup2;</li>
						</ul>
						<a href='".SITEURL."catalogue/$row[id]/'>Подробнее</a> </td>
				</tr>
				<tr class='broker'>
					<td>Брокер — <span>$row[brokerName]</span> тел.: <span>$row[brokerContact]</span></td>
					<td>Размещен — <span>" . ConvertMysqlTimeStamp($row['timestamp'],".") . "</span></td>
				</tr>\n",


		// Реклама
		"advertisedObjects" =>
			"
		
				<div class='Comission$row[commissionType]'>
					<p><a href='".SITEURL."catalogue/$row[id]/'>Ангар, аренда, МО, Восток,</a><br />
						пл. " . ceil($row['totalarea']) . " м&sup2;<br />
						$row[price] ye / за м&sup2;
					</p>
					<p style='text-align:center; padding: 19px 0'>
						<a href='".SITEURL."catalogue/$row[id]/'><img src='/_images/thumbs/$row[path]' alt='' title='' width='136' height='91' /></a>
					</p>
				</div>		
		
		
		",

		// Шаблон для вывода таблицы со списком языков
		"relatedObjects"	=>"


					<tr>

						<td class='obj'><a href='".SITEURL."catalogue/$row[id]/'>" . $BuildingClass[$row['parent_id']] . " класса " . 
							$BuildingClassTypes[$row['parent_id']][$row['realestateClass']] . "</a></td>
						<td>" . $regions[$row['region']] . ", " . $districtS[$row['region']][$row['district']] . ", " . $metros[$row['region']][$row['metro']] . "</td>
						<td class='priceYear'><span>$row[price]</span> ye/м&sup2;</td>
						<td class='areaM'><span>" . ceil($row['totalarea']) . "</span> м&sup2;</td>

						<td><span class='Comission$row[commissionType]'>" . $commissionTypes[$row['commissionType']]. "</span></td>
						<td><a href='".SITEURL."catalogue/$row[id]/'>подробнее</a></td>
					</tr>\n",


		// Шаблон для вывода таблицы со списком объектов
		"leftmenu"	=> 
			"\t\t\t\t\t<li><a href='". SITEURL . "catalogue/$row[id]/'>$row[name]</a></li>\n",
		
		);

		/* 
		was 
		eval("\$items = \"$itemtemplate[$for]\";");
		echo $items; */

		eval("echo \"$itemtemplate[$for]\";");

		$opened="";


    }

}


// User Friendly Function Names
function GenerateTopMenu() { GenerateMenu("top");}

/**
 * FAQ
 *
 * Version:	1.0
 * Module:		FAQ generator
 * Date:		17.09.2006
 * 
 * @param string $showanswers
 * @param string $shownumbers
 * @param string $category
 * @param string $howmanyfaq
 */
function getFAQ($showanswers, $shownumbers, $category, $howmanyfaq) {
    global $siteurl, $db_pref;

    $questions = "<a name=\"questions\"></a><br>\n";
    $faqcategory = "";
    $number = "";
    $shownumber = "";
    $answers = "<br><br>\n";

    if ($category !== "") $faqcategory = "AND cat_id = {$category}";
    if ($howmanyfaq !== "") $howmanyfaq = "LIMIT 0,{$howmanyfaq}";


    $sql_res = mysql_query (
    "SELECT * FROM `{$db_pref}faq`
        WHERE `visibility`='y' {$faqcategory} 
        ORDER BY `priority` desc {$howmanyfaq}");

    $num_faq = mysql_num_rows($sql_res);

    for ($i=0; $i<$num_faq; $i++){

        $row = mysql_fetch_array($sql_res);
        $question = ucfirst($row['question']);
        $answer = ucfirst($row['answer']);
        $number = $i+1;

        // if ($popup == "y");

        if ($shownumbers == "y") $shownumber = $number.".";

        $questions .= "<div class='justquestion'>$shownumber <a href='#$number'>$question</a></div>\n";

        if ($showanswers == "y") $answers .= "<div class='questionandanswer'>
			<div class='question'><a name='$number'></a>$shownumber <b>$question</B></div>\n
			<div class='answer'> $answer</div>\n
			<div class='backtotop'><a href='#questions'>to the top</a></div>\n
			</div>";
    }

	echo $questions;
	echo $answers;
}

// определение текущего раздела в меню
function isTopActive($parent,$child="default") {

	// echo CATEGORY_URL_PATH.PAGE_URL_PATH;
	if (CATEGORY_URL_PATH === $parent && PAGE_URL_PATH === $child) return "_active";

}

// определение текущего раздела в меню
function isMenuActive($parent) {

	// echo CATEGORY_URL_PATH.PAGE_URL_PATH;
	if (CATEGORY_URL_PATH === $parent) return "opened";

}

// определение текущего раздела в меню
function isTabActive($id,$key) {

	// echo CATEGORY_URL_PATH.PAGE_URL_PATH;
	if (!empty($_GET[$key]) && $id == $_GET[$key]) return "active";
	if (empty($_GET[$key]) && ($id == "0")) return "active";

}

function expandMenu($category) {

	if (CATEGORY_URL_PATH == $category) {
	
	echo "<ul>"; 
	GenerateListOfSomeThing("submenu");
	echo "</ul>";
	
	}
	
}

function GenerateObjectListForHomePage() {

	GenerateListOfSomeThing("catalogueObjects");

}

function GenerateObjectListForCatalogue($mySQLParams,$morequery="") {

	$mySQLParams = ltrim($mySQLParams," WHERE `visibility` ='y'");

	if (!empty($mySQLParams)) $morequery = $mySQLParams;

	// echo $morequery;

	GenerateListOfSomeThing("catalogueObjects","","",$morequery);

}

function GenerateParams() {

	// Получение дополнительных параметров
	parse_str($_SERVER['QUERY_STRING'],$query);

	$urlPlusMySQLhwere['qeuryforpaginator'] = $urlPlusMySQLhwere['mysql'] = "";
	
	foreach($query as $key=>$val) {
	
		// Дополнительная строка для запроса в БД
		if ($key !== "cat_path" & $key !== "page" & $key !== "sortby" & $key !== "ascdesc") $urlPlusMySQLhwere['mysql'] .= " AND `$key` = '$val' ";

		// Для пагинатора
		if ($key !== "cat_path" & $key !== "page") $urlPlusMySQLhwere['qeuryforpaginator'] .= "$key=$val&";

	}

	$urlPlusMySQLhwere['mysql'] = "WHERE `visibility` = 'y' $urlPlusMySQLhwere[mysql]";

	return $urlPlusMySQLhwere;

}

function ClearParams($params,$keyToRemove,$urlPlusMySQLhwere="") {

	// Получение дополнительных параметров
	parse_str($params,$query);

	foreach($query as $key=>$val) {
	
	if ($key !== $keyToRemove) $urlPlusMySQLhwere .= "&$key=$val";

	}

	return $urlPlusMySQLhwere;

}

?>