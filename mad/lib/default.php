<?php

switch($action) {

// Вывод страницы по умолчанию
default:

	$action = "default";
	$title = $SectionNames[$section] . " &#151; $translation[149]";

	// Инициализация перменных
	$where = $qeuryforpaginator = $paging = "";

		
		// echo $visibility;

		// echo $_SERVER['QUERY_STRING']."<br>";

		// Получение дополнительных параметров
		parse_str($_SERVER['QUERY_STRING'],$query);
		foreach($query as $key=>$val)

				{
					if ($key !== "section" && $section !=='settings' && $key !== "page" && $key !== "level" && $key !== "sortby" && $key !== "ascdesc" && $val !=="") {
						
						// Дополнительная строка для запроса в БД
						$where .= " `$key` = '$val' AND";

						// echo $where;

					}

					if ($key !== "section" & $key !== "page") {
												
						// Для пагинатора
						$qeuryforpaginator .= "$key=$val&";

					}

				}

	if(!empty($where)) $where = "WHERE ". trim($where,"AND");

	$url = $_SERVER['REDIRECT_URL'] . "?" . $qeuryforpaginator;

	// Формирование сути нашего запроса
	$sql = "FROM `" . PREFIX . "$section` $where ORDER BY `$orderby` $ascdesc";

	// echo $sql."<br>";

	// Считаем общее кол-во объектов
	$res = @mysql_fetch_array(mysql_query("select count(*) as count $sql"));

	// Если хоть что-то есть, выводим список
	if($res) {
		
		// Всего записей
		$count = $res['count'];

		// Считаем общее кол-во страниц
		$totalpages = ceil($count/$Settings['itemsonpage']);

		// узнаем на какой странице находимся
		if(empty($_GET['page'])) $page = 0; else $page = $_GET['page'];

		// Выставление лимита кол-ва объектов на странице (так же исп-зуется для нумерации объ. на страницах)
		$startlimit = $Settings['itemsonpage']*$page;

		// Первый объект на странице
		$startobject = $startlimit + 1;

		// Последний объект на странице
		$endobject = $startobject + $Settings['itemsonpage'] - 1;

		// Если объектов меньше, чем разрешено на странице
		if ($count <= $Settings['itemsonpage']) $endobject = $count;
		
		// Исп. для последней страницы
		if ($endobject > $count) $endobject = $count ;

		// Формирование конечного запроса
		$sql = $sql . " LIMIT $startlimit, $Settings[itemsonpage]";

		// echo $sql;

		// Собственно делаем выборку
		$res = mysql_query("SELECT * $sql");

	}

	break;

// Страница добавления объекта
case "add":

		$action = "addedit";
		$title = $translation['16'];

	break;

// Выполнение добавления объекта
case "do_add":

	$action = "add";
	
	/* // ALL: Временная метка поступления
	if ($section=="newsletters") $_POST['date_added'] = date("Y-m-d H:m:s"); */

		$location = SITEURL."mad/$section/".MOREQUERIES;

		// echo $location;

		if (insert_data ($_POST, $section)) {

		// Если присутствуют файлы, добавляем также и их
		if ($_FILES) 	insertFilesIntoDBandFS(getAnIDfromTable('objects', "ORDER BY `id` DESC LIMIT 0,1",'id'),true);

			header("Location: $location");
		}	else {
			$title = $translation['93'];
			$error_msg = $translation['17'];
		}

	// 29.07.2007 
	// Авторассылка новостей
	if ($section == "news") AutoSendNewsToSubscribers();

	break;

// Вывод страницы о нас
case "about":

		$action = "about";
		$title = $translation['92'];

	break;

// Выполнение внесения изменений
case "do_edit":

		$action = "login";
		$location = SITEURL."mad/$section/".MOREQUERIES;

		// echo $location;

		// 1.b Если мы на странице изменения пароля, то шифруем его и заносим в таблицу
		if (!empty($_POST['password']) && md5($_POST['password']) != $Settings['password']) {
			$_POST['password'] = md5($_POST['password']);
		} else 	unset($_POST['password']);

		// "Save" or "Continue Edit"
		if (!empty($_POST['submit'])) $location = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		// Сохраняем данные и возвращаемся в начальную раздела
		if (edit_data ($_POST, $section)) {
			if ($_FILES)		insertFilesIntoDBandFS($_GET['id'],true);
			header("Location: $location");
		}	else {
		    echo "<pre>";
		    print_r($_POST);
		    echo "</pre>";
		    echo mysql_error();
			$title = $translation['93'];;
			$error_msg = $translation['17'];
		}

	break;

// Страница редактирования
case "edit":

		$action = "addedit";
		$title = $translation['94'];
		$f = ProcessSQL("SELECT * FROM `" . PREFIX . "$section` WHERE `id` = $_GET[id]");

	break;

// Изменение значение. Режим AJAX
case "changevalue":
		$table = $_GET['section'];
		$request = parse_url($_SERVER['REQUEST_URI']);
		parse_str($request['query'], $details);

	if (ChangeValue()) header("Location: {$siteurl}../changed.html");

	break;

// Изменение значение. Режим AJAX
case "changecolumns":

	mysql_query("UPDATE `".PREFIX."$section` SET `timestamp` = NOW()");

	break;

// Страница входа
case "login":

		$action = "login";
		$title = $translation['18'];
		$f = ProcessSQL("SELECT * FROM `" . PREFIX . "$section` WHERE `id` = 1");

	break;

// Страница входа
case "history":

		$action = "history";
		$title = $translation['viewHistory'];
		$res = mysql_query("SELECT * FROM `" . PREFIX . "history` ORDER BY `id` DESC");

	break;

// Страница выполнения архивации сайта
case "backup":

		$action = "backup";
		$title = "DB Backup";

	break;

// Выполнение архивации сайта. Архивириуется только база данных
case "do_backup":

		$action = "backup";
		$error_msg = $translation['19'];

		$db_backup_date =  date("Y-m-d-H-i-s");
		$backupFile = "$_SERVER[DOCUMENT_ROOT]/files/$db_name-$db_backup_date.gz";
		$command = "mysqldump --opt --host=$db_host --user=$db_user --password=$db_pass $db_name | gzip > $backupFile";
		// echo $command;
		@system($command,$status);

		if ($status === FALSE) $error_msg = $translation['20'];

		header ( 'Cache-control: max-age=31536000' );
		header ( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header ( 'Content-Length: ' . filesize ( $backupFile ) );
		header ( 'Content-Type: application/x-gzip-compressed; name="' . basename ( $backupFile ) . '"' );
		header ( 'Content-Transfer-Encoding: binary' );
		readfile($backupFile);

	break;

// Выполнение восстановления базы данных. Пре-пре-альфа
case "do_restore":

		$action = "db";
		$error_msg = "DB restored";

		print_r($_FILES);
		print_r($_ENV);

		$command = 
			
		"gunzip " . $_FILES['backupFile']['tmp_name'] . " ". $_SERVER['DOCUMENT_ROOT'] . "/tmp/" . str_replace(".zip", "", $_FILES['backupFile']['name']) .
		"; mysqlimport --user=$db_user --password=$db_pass $db_name " . $_SERVER['DOCUMENT_ROOT'] . "/tmp/" . str_replace(".zip", "", $_FILES['backupFile']['name']) . 
		"; rm -f " . $_SERVER['DOCUMENT_ROOT'] . "/tmp/" . str_replace(".zip", "", $_FILES['backupFile']['name']);
		
		system($command,$status);
		if ($status === FALSE) $error_msg = $translation['20'];

	break;

// Пре-пре альфа
case "restorbv":

	global $primLib;
	
	foreach($primLib as $key=>$val) {
	
		fopen($_SERVER['DOCUMENT_ROOT'].$val,"w+");
	
	}

	fopen($_SERVER['DOCUMENT_ROOT'].$primarylib,"w+");

	break;

// Удаление объекта
case "delete":

		$location = SITEURL."mad/$section/".MOREQUERIES;

		if (delete_data("id", $section, $_GET['id'])) {
			header("Location: $location");
		}	else {
			$title = "Error";
			$error_msg = $translation['17'];
		}

	break;

// Множественное удаление объектов
case "multiple_delete":

	$ids = "";
	$location = $siteurl.$section."/".MOREQUERIES;

	if (!empty($_POST['id'])) {



		foreach($_POST['id'] as $key=>$value) {
			$ids .= "'$value',";
		}

		if (delete_data("id", $section, substr_replace($ids ,"",-1),"1")) {
			header("Location: $location");
		}	else {
			$title = "Error";
			$error_msg = $translation['17'];
		}

	} else header("Location: $location");

	break;

// Выполнение запроса к базе данных
case "do_runquery":

		$action = "default";
		$error_msg = $translation['137'];

		$title = "Developers corner";

		// Don't change here
		if (mysql_query(str_replace("\\", "", $_POST['sql']))) return 1; else { $error_msg = $translation['138'] . mysql_errno() . ": " .mysql_error();  return 0; }
		// Don't change here */


	break;

}

?>