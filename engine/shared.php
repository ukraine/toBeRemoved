<?php

/*

shared.php

Дата модификации 29.03.2009 23:44
Справочник функций файла

строка # название # описание

ConvertMysqlTimeStamp
ConvertMysqlTimeStampToUnixTime
GetTotalData - подсчет строк в таблице БД
getSettings
GetCatName
GetValueById() -  получить треубемое значение из БД по наличию определенного идентификатора
GetValueByIdV2 - Получить треубемое значение из БД по наличию неопределенного идентификатора
getOpenPath - Служебная функция для displayTreeV2
displayTreeV2 - Генератор дерева
ExecuteSqlGetArray
RunQueryReturnDataArray
ProcessSQL() - выполнение запроса в БД с возвращением рез-та
sendMail
utf8_substr
ucfirstUTF

*/

// Получить треубемое значение из БД по наличию определенного идентификатора
// 06.02.2009 - добавлено значение по умолчанию
function GetValueById ($table, $id, $name="name") {

    $result = ProcessSQL("SELECT `$name` FROM `" . PREFIX. "$table` WHERE id='$id' LIMIT 0,1");
    return $result['0'];

}

// Получить треубемое значение из БД по наличию неопределенного идентификатора
function GetValueByIdV2 ($table, $IDname, $IDvalue, $name="name") {

	$sql = "SELECT `$name` FROM `" . PREFIX. "$table` WHERE `$IDname`='$IDvalue' LIMIT 0,1";
    $result = ProcessSQL($sql);
	// echo $sql;
    return $result['0'];

}

function GenerateCheckBox($array,$separator=" &nbsp; ",$br="<br>", $js="",$var=""){

	global $checkedOrNot;

	foreach($array  as $key=>$description)	{

	@$var .= "<input type='checkbox' id='$key' " . $checkedOrNot[ifExistValueReturnIt($key)] . " onClick='ChangeHiddenFieldValue(\"$key\")' $js><label for='$key'> $separator $description &nbsp; </label>\n$br";

	}

	echo $var;

}


function ProcessSQL($sql) {
    // ufian 27.04.2008

	// echo $sql;

    $result = mysql_query( $sql );
    if( !$result ) return array();
    return mysql_fetch_array( $result );

    // /ufian 27.04.2008
}

// Получение базовых параметров системы
function getSettings()	{

	// 16.07.2007

    global $Settings;
    $Settings = ExecuteSqlGetArray("SELECT * FROM `" . PREFIX . "settings` WHERE id='1'");
}

// 12.10.2008
function utf8_substr($str,$from,$len){

	return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.
                       '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s',
                       '$1',$str);
}

/*
Ucfirst для UTF-8
*/

function ucfirstUTF($str, $e='utf-8') {
    if (function_exists('mb_strtoupper')) {
        $fc = mb_strtoupper(mb_substr($str, 0, 1, $e), $e); 
        return $fc.mb_substr($str, 1, mb_strlen($str, $e), $e);
    }
    else { 
        $str = utf8_decode($str);
        $str[0] = strtr($str[0],
            "abcdefghýijklmnopqrstuvwxyz".
            "\x9C\x9A\xE0\xE1\xE2\xE3".
            "\xE4\xE5\xE6\xE7\xE8\xE9".
            "\xEA\xEB\xEC\xED\xEE\xEF".
            "\xF0\xF1\xF2\xF3\xF4\xF5".
            "\xF6\xF8\xF9\xFA\xFB\xFC".
            "\xFE\xFF",
            "ABCDEFGHÝIJKLMNOPQRSTUVWXYZ".
            "\x8C\x8A\xC0\xC1\xC2\xC3\xC4".
            "\xC5\xC6\xC7\xC8\xC9\xCA\xCB".
            "\xCC\xCD\xCE\xCF\xD0\xD1\xD2".
            "\xD3\xD4\xD5\xD6\xD8\xD9\xDA".
            "\xDB\xDC\xDE\x9F");
        return utf8_encode($str);
    }
}

function GetCatName ($id) {

    global $catPath, $db_pref;
    $respage = ExecuteSqlGetArray("SELECT `{$db_pref}cat_path` FROM `categories` WHERE `id`='$id'");
    $catPath =  $respage['cat_path']; return $catPath;

}

// Выполняем запрос и получаем массив с требуемыми данными
function ExecuteSqlGetArray($sql, $debugsql=0) {

	/*
	@param string $sql
	@return array
	
	14.07.2007
	ufian 27.04.2008 */

	if ($debugsql==1) echo $sql;

    $result = mysql_query( $sql );
    if( !$result ) return array();
    return mysql_fetch_array( $result, MYSQL_ASSOC );

}

// Выборки из БД. Если есть значения, то возвращаем их, если нет, то возвращаем bool об ошибке
// 12.10.2007
function RunQueryReturnDataArray ($table, $more="", $column="*") {

	$sql = "SELECT $column FROM `$table` $more";
	// echo $sql;
	return mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);

}


// Converting MySQL timestamp to datetime
// 16.09.2007
function ConvertMysqlTimeStamp($time, $separator="-", $seconds=false) {

	$timestamp = substr($time, 8,2).$separator;
	$timestamp .= substr($time, 5,2).$separator;
	$timestamp .= substr($time, 0,4)."&nbsp;";

	if ($seconds) {
		$timestamp .= substr($time, 8,2).":";
		$timestamp .= substr($time, 10,2).":";
		$timestamp .= substr($time, 12,2);
	}

	return($timestamp);
}


function ConvertMysqlTimeStampToUnixTime($timestamp)	{

	$year=substr($timestamp,0,4);
	$month=substr($timestamp,4,2);
	$day=substr($timestamp,6,2);
	$hour=substr($timestamp,8,2);
	$minute=substr($timestamp,10,2);
	$second=substr($timestamp,12,2);
	$newdate=mktime($hour,$minute,$second,$month,$day, $year);
	return($newdate);
}


// Служебная функция для displayTreeV2
function getOpenPath($open_id)
{
	$path = array();
	$path[] = $open_id;

	while(true) {
		$result = mysql_query("SELECT id, parent_id FROM `".PREFIX."objects` WHERE `id`='$open_id' LIMIT 0, 1");
		if(!($row = mysql_fetch_array($result)))
			return $path;
		if($row['parent_id'] == -1)
			return $path;
		$path[] = $row['parent_id'];
		$open_id = $row['parent_id'];
	}
}

/*
 * Функция выводит дерево.
 * Параметры:
 *	$open_id -- идентификатор меню который будет открыт
 *	$parent -- идентификатор узла с которого начинать строить дерево. Если -1 то с корня.
 *	$padding -- строка которой будут отделятся уровни.
 *	$level -- при вызове пользователя должно быть 0. Лучше опустить этот необязательный параметр.
 */

function displayTreeV2($open_id="-1", $parent="-1", $padding="&nbsp;&nbsp;&nbsp;&nbsp;", $level=0, $front=0)
{
	global $parent_id;	// DELETE ME!

	static $open_path;

	if($level == 0)	$open_path = getOpenPath($parent_id);
	
	$result = mysql_query("SELECT * FROM `".PREFIX."objects` WHERE `parent_id`='$parent' AND `visibility`='y' ORDER BY priority DESC");

	while($row = mysql_fetch_array($result)) {
		
		// echo(str_repeat($padding, $level));

		// print_r($row);
			
		// echo $parent_id;

		// echo $level;

		// echo "<br><br>level = $level; front = $front";

		$class=$listart=$liend=$subclass=$dash="";

		if ($parent_id == $row['id']) $class=" class='active'";


		if ($level<=1)	{$subclass	= " class='submenu'"; }
		if ($level==1)	{$dash	= " &mdash; "; }
		if ($level<=2)	{$listart	= "<ul$subclass>"; $liend = "</ul>"; } 
			
		echo "\t\t\t\t\t<li$class>$dash <a href='". SITEURL . "catalogue/$row[id]/'>$row[name]</a>$listart";
	
		// if(in_array($row['id'], $open_path))
			displayTreeV2($open_id, $row['id'], $padding, $level+1);		

		echo "$liend</li>\n";

		unset($class,$listart,$liend,$subclass,$dash);


		// elseif ($front == 0) echo "\t\t\t\t\t<li><a href='". SITEURL . "catalogue/$row[id]/'>$row[name]</a></li>\n" ;
		// else echo("<p><a href='{$siteurl}{$row['id']}/'>{$page_name}</a></p>\n"); 
		
		

	}
}


function displayTreeV3($parent="-1")	{
	
	global $parent_id;
		
	$result = mysql_query("SELECT * FROM `".PREFIX."objects` WHERE `parent_id`='$parent' AND `visibility`='y' ORDER BY priority DESC");
	while($row = mysql_fetch_array($result)) {
	
		$class=$listart=$liend=$subclass=$dash=$activelist="";
		if ($parent_id == $row['id']) { $class=" class='active'"; $activelist = "y"; }
		
/*		if ($row['level'] == "-1")	$subclass	= " class='submenu'";
		if ($row['level'] == "1")	{ $listart	= "<ul$subclass>"; $liend = "</ul>"; }
		if ($row['level'] == "2")	$dash		= " &mdash; ";		*/

			
		echo "\t\t\t\t\t<li$class>$dash<a href='". SITEURL . "catalogue/$row[id]/'>$row[name]</a>$listart";
	
		// if($activelist=="y") displayTreeV3($row['id']);	

		echo "$liend</li>";
		unset($class,$listart,$liend,$subclass,$dash,$activelist);		

	}
}


/**

20.11.2008

 * Посылка email
 *
 * @param string $subject
 * @param string $content
 * @return bool
 */
function sendMail($content="") {

    global $Settings, $ForbiddenChars, $AllowedChars;

		unset($_POST['message']);

		$content .= "$_POST[text]\n\n";

		foreach($_POST as $key=>$val)
			{

				if  ($key!=="dosometh" 
					&& $key!=="Submit" 
					&& $key!=="action" 
					&& $key!== "message" 
					&& $key!=="PHPSESSID"
					&& $key!=="submit"
					&& $key!=="subject"
					&& $key!=="page_path"
					&& $key!=="cat_path"
					&& $key!=="name"
					&& $key!=="email"
					&& $key!=="textarea"
					&& $key!=="text"
					&& !empty($key))
				$content.= "$key: ".strip_tags(str_replace($ForbiddenChars, $AllowedChars, $val))." \n";
			}

    /* foreach($_POST as $key=>$val) {
        $content.= "{$key} : ".strip_tags(str_replace($ForbiddenChars, $AllowedChars, $val))." \n";
    } */

	if (PAGE_URL_PATH == "default") $service = CATEGORY_URL_PATH;
	else $service = PAGE_URL_PATH;

	
	$subject = "С сайта $Settings[sitename]";
	if (!empty($_POST['subject'])) 	$subject .= " --- $_POST[subject]";

	$content .= "\n---------------------------------------\nИнформация об отправителе:";
	$content .= "\nОтправлено со страницы: $_SERVER[REQUEST_URI]";
	$content .= "\nПосетитель попал на сайт: $_COOKIE[ReferredBy]\n";
	$content .= "IP адрес: http://www.ip2location.com/free.asp?ipaddresses=$_SERVER[REMOTE_ADDR]";

    $headers =
		"From: {$_POST['Имя']} <{$_POST['Емейл']}>\r\n" .
		"MIME-version: 1.0\n" .
		"Content-type: text/plain; charset=\"UTF-8\"";

    mail($Settings['email'], $subject, $content, $headers);

}

function GetTotalData ($where) {
    $res = ProcessSQL("SELECT COUNT(*) FROM $where");
    return $res['0'];
}



?>