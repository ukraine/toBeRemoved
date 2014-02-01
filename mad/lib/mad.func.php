<?

/* 

mad.func.php

Дата модификации 07.02.2009 15:21
Справочник функций файла 

строка # название # описание

AutoSendNewsToSubscribers - Автоматическая отправка новостей подписчикам при добавлении
ChangeValue - изменения значения в строке(ах) таблицы. Связано с аякс
generateSubHeaderUrl - Генерация ссылки в субменю
getAnIDfromTable - получение ID одного объекта из таблицы
GetCategory - получение ID категори (есть аналог в shared)
ErrorMsg - вывод системных сообщ
ifExistValueReturnIt - возврат значения если оно есть в поле
ifExistGetValue - аналог ф-ции выше
HighLight() - подсветка
limitVisiblePart - лимитирование видимой части (есть аналог в shared)
PairedLineOrNot - парность строки в таблице
SubHeaderTabsHighlight() - подсветка табов в субхедере
sendnewsletters() - отправка инфобюллетеня
writeLog($action,$section,$sql) - запись в лог файл

-- Работа с аттачами

insertFilesIntoDBandFS - вставка файлов
getAttachedFiles - генератор списка файлов
delete_files - удаление файлов

-- Генераторы полей формы

GenerateSelectList - генератор тега select на основе данных из таблицы
GenerateCheckBoxList - генератор списка чекбоксов
GenerateInputTag - генератор тега Input на основе данных
GenerateInputTagWithJS - генератор тега Input на основе данных с JS внутри
GenerateTextAreaTag- генератор тега TextArea на основе данных
selectv2 - выбрано ли значение в селекте
checkbox($field) - выбрано ли значение в чекбоксе
select($field, $number)	- выбрано ли значение в селекте
radiobox($field, $number) - выбрано ли значение в радиобоксе


*/

// Выборки из БД. Если есть значения, то возвращаем их, если нет, то возвращаем bool об ошибке
function getAnIDfromTable ($table, $more="", $column="*") {

	$sql = "SELECT $column FROM $table $more";
	// echo $sql;

	$res = mysql_fetch_array(mysql_query($sql));
	if ($res['0']) {
		return $res['0'];
	}
	else return 0;
}


// 07.02.2009 Генерация ссылки в субменю
function generateSubHeaderUrl($section,$getAction="",$translation,$urlparam="",$realTemplate="default") {

	echo "<a href='". SITEURL . "mad/$section/$getAction{$urlparam}'"; 
	SubHeaderTabsHighlight($section,$realTemplate); 
	echo ">$translation</a>";

}


function SubHeaderTabsHighlight($currentSection, $currentAction="default") {
    global $section, $action;
    if ($section == $currentSection && $action  == $currentAction)		echo 'class=current';
}

function HighLight($value, $SectionAction) {

    global $section, $action;
    if ($$SectionAction==$value)	echo "class=current";

}


function writeLog($actionID,$sectionID,$objectID, $sql) {

	global $ForbiddenChars, $AllowedChars;

	$keys = "ip,actionID,sectionID,objectID,sqlquery";
	$values = "'$_SERVER[REMOTE_ADDR]','$actionID','$sectionID','$objectID',\"" . strip_tags(substr($sql,0,255)) . "\"";
	$sql = "INSERT INTO `" .PREFIX. "history` ($keys) VALUES ($values)";

	// echo $sql;

	mysql_query($sql);

}

// Такая функция есть в shared
function GetCategory ($id) {

    global $category;
    $category =  GetCatName($id);
}

function limitVisiblePart($fieldname, $limitto,$threedots = "") {

    global $f; if (strlen($f[$fieldname]) > $limitto) $threedots = "...";
    $fieldname = strip_tags(utf8_substr(stripslashes($f[$fieldname]), 0, $limitto)).$threedots;
    return $fieldname;

}

// Вывод класса для парной строки
function PairedLineOrNot($number) {

    $pair=" class='pair'";
    if ($number%2 !== 0) $pair="";
    return $pair;

}


// Отправка newsletter 23.11.2007
function sendnewsletters() {

    global $Settings, $db_pref;

    $headers =
    "From: $Settings[admin_name] <$Settings[email]>\r\n" .
    "Reply-To: $Settings[admin_name] <$Settings[email]>\r\n" .
    "Organization: $Settings[company_name]\r\n".
    "MIME-version: 1.0\n" .
    "Content-type: text/html; charset=\"UTF-8\"\r\n\r\n";

    $result = mysql_query("SELECT name,email FROM `{$db_pref}subscribers`");

    for ($i=0; $i < mysql_num_rows($result); $i++)

    {

        $data = mysql_fetch_array($result);
        $startpoint = "Hello $data[name]\n\n";

        // mail("$data[firstname] $data[lastname]<$data[email]>", stripslashes($_POST['subject']), $startpoint.$_POST['content'], $headers);

    }

    mysql_query("UPDATE `{$db_pref}newsletters` SET `status_id`='1', `date_sent` = NOW()");

    return 1;

}


function ChangeValue() {

    global $details, $table;

    $details["id"] = $_REQUEST['id'];
    edit_data (&$details, &$table);

    return 1;

}


function GenerateSelectListStatic($array,$name, $description, $separator=" &nbsp; ",$br="<br>", $js="") {

	$select = "<select name='$name' id='label$name' $js>";

	foreach($array  as $key=>$value)	{

		$select .= "\t\t<option value='".$key."'";
		$select .= selectv2($name, $key);
		$select .= ">$value</option>\n";

	}
		
	$select .="</select>  $separator <label for='label$name'>$description</label><br>";

	echo $select;

}

function GenerateCheckBoxList($array,$separator=" &nbsp; ",$br="<br>", $js="",$var=""){

	global $checkedOrNot;

	foreach($array  as $key=>$description)	{

	$var .= "<input type='checkbox' id='$key' " . $checkedOrNot[ifExistValueReturnIt($key)] . " onClick='ChangeHiddenFieldValue(\"$key\")'><label for='$key'> &#151; $description &nbsp; </label>\n$br";

	}

	echo $var;

}




// Генерация тега select
// 03.08.2007
// 07.03.2008 Added custom query
function GenerateSelectList($whatWhatTableToSelect, $nameOfIdentificatorAutoToSelect, $nameofvaluetoshow, $query="", $emptyrow="0")	{

    global $translation;

    $sql = "SELECT * FROM `$whatWhatTableToSelect` $query";

    // Debug SQL
    // echo $sql;

    $emptyoption = array(

    "",
    "\n\t\t<option value='0'> -- $translation[valueNotSelected]</option>",

    );

    $res = mysql_query($sql);

    $select = "<select name='$nameOfIdentificatorAutoToSelect'>$emptyoption[$emptyrow]";

    while($col = mysql_fetch_array($res))	{
        $select .= "\t\t<option value='".$col['id']."'";
        $select .= selectv2($nameOfIdentificatorAutoToSelect, $col['id']);
        $select .= ">$col[$nameofvaluetoshow]</option>\n";
    }

    return $select."</select>";

}

// Генерация тега input
// 25.08.2007
function GenerateInputTag($name,$description, $type="text", $separator=" &nbsp; ",$br="<br>", $js="")	{

    echo "\n<input type='$type' name='$name' value='" . ifExistValueReturnIt($name) . "' id='label$name' $js> $separator <label for='label$name'>$description</label>$br";

}

// Генерация тега input с использованием JS внутри
// 13.10.2008
function GenerateInputTagWithJS($name, $description,$js) {
	GenerateInputTag($name,$description, $type="text", $separator=" &nbsp; ",$br="<br>", $js);
}


// Генерация тега textarea
// 03.10.2007
function GenerateTextAreaTag($name,$rows="10",$cols="250")	{

    echo "<textarea name='$name' rows='$rows' cols='$cols'>" . ifExistValueReturnIt($name) . "</textarea>";

}


// Автоматически выбирать требуемые поля тега select
// 25.08.2007
function selectv2($field, $number)	{

    global $f; if($f[$field] == $number) return " selected";

}

function checkbox($field)	{
    global $f; if($f[$field] == "y") echo " checked";
}

function select($field, $number)	{
    global $f; if($f[$field] == $number) echo " selected";
}

function radiobox($field, $number)	{
    global $f; if($f[$field] == $number) echo " checked";
}


function ErrorMsg () {
    global $error_msg; if (!empty($error_msg))	echo "<div class='error_msg'>$error_msg</div>";
}


// Если переменная существует - выводим ее
// 29.08.2007
// 05.04.2009
function ifExistValueReturnIt($valuename) {

    global $f;

    if (isset($f[$valuename])) return $f[$valuename];
    else return @$_REQUEST[$valuename];


}



function ifExistGetValue($valuename) {

    global $f;

    if (isset($_POST[$valuename]))
    echo $_POST[$valuename];
    else echo $f[$valuename];
}



// Автоматическая отправка новостей подписчикам при добавлении
// 03.08.2007
function AutoSendNewsToSubscribers () {

    global $db_pref;
    $headers =
    "From: <$Settings[email]>\r\n" .
    "MIME-version: 1.0\n" .
    "Content-type: text/plain; charset=\"UTF-8\"\r\n\r\n";

    $content = $_POST['content'];
    $subject = "Новости сайта: " . $_POST['title'];

    $result = mysql_query("SELECT email,name FROM `{$db_pref}subscribers`");

    for ($i=0; $i < mysql_num_rows($result); $i++){

        $data = mysql_fetch_array($result);
        if (mail($data['email'], $subject, $content, $headers)) echo "$data[email] - OK<br>";
        else echo "$email - failed<br>";
    }

    return 1;

}

function resize_kvadrat($img, $thumb_width, $thumb_height, $newfilename, $type="1", $watermark=false) 
{ 

    // Проверка наличия GD
    if (!extension_loaded('gd') && !extension_loaded('gd2')) 
    {
        trigger_error("GD отсутствует", E_USER_WARNING);
        return false;
    }

    // Получение данных о размере нашей картинки
    list($w_src, $h_src, $image_type) = getimagesize($img);
    
    switch ($image_type) 
    {
        case 1: $im = imagecreatefromgif($img); break;
        case 2: $im = imagecreatefromjpeg($img);  break;
        case 3: $im = imagecreatefrompng($img); break;
        default:  trigger_error('Неподдерживаемый файл!', E_USER_WARNING);  break;
    }
    
	// создаём пустую квадратную картинку 
    // важно именно truecolor!, иначе будем иметь 8-битный результат 
    $newImg = imagecreatetruecolor($thumb_width, $thumb_height);
    
    /* Check if this image is PNG or GIF, then set if Transparent*/  
    if(($image_type == 1) OR ($image_type==3))
    {
        imagealphablending($newImg, false);
        imagesavealpha($newImg,true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $thumb_width, $thumb_height, $transparent);
    }

	/* Правильный ресайз YY 05.04.2009 22:41 */

	$ratio = $h_src/$w_src;

	$ratioThumb = $h_src/$thumb_height;

	// вырезаем квадратную серединку по x, если фото горизонтальное 
    if ($w_src>$h_src) {

		// Первое значение - это для прямоугольников, второе для квадратных тамбов
		$kvadratCoordsX = array($thumb_width/3, $thumb_width);
		$kvadratCoordsY = array($thumb_width/3, $thumb_height);

		// echo $kvadratToCropX[0];

		// echo $thumb_width-$thumb_height/2 . "<br>";
		// echo $h_src/$ratioThumb;

		// echo ($w_src-$h_src)/2;

		imagecopyresampled(
			$newImg, $im, // целевое изображение, исходное
			0, 0, // Точка на изображении назначения, которая определяет левый верхний угол прямоугольника в который будет вставляться копируемая область.
			($w_src-$h_src)/2, 0, // ширина и высота прямоугольника в который будет вписана копируемая область
			$thumb_width, $thumb_height, // на изображении-источнике, которая определяет левый верхний угол прямоугольника, содержащего копируемую.
			$h_src, $h_src // ширина и высота копируемой области на изображении-источнике. 
		); 


	}

	// вырезаем квадратную верхушку по y, если фото вертикальное (хотя можно тоже серединку) 
    if ($w_src<$h_src) 
	     imagecopyresampled(
			$newImg, $im, 
			0, 0, 
			0, ($h_src-$w_src)/2, 
			$thumb_width, $thumb_height,
			$w_src, $w_src
	); 

	// квадратная картинка масштабируется без вырезок 
	if ($w_src==$h_src) 
		imagecopyresampled($newImg, $im, 0, 0, 0, 0, $thumb_width, $thumb_height, $w_src, $w_src); 

	/* Правильный ресайз YY 05.04.2009 22:41 */
    
    //Generate the file, and rename it to $newfilename
    switch ($image_type) 
    {
        case 1: imagegif($newImg,$newfilename); break;
        case 2: imagejpeg($newImg,$newfilename);  break;
        case 3: imagepng($newImg,$newfilename); break;
        default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
    }
	imagedestroy($newImg);
	imagedestroy($im); 
 
    return $newfilename;
}


function resizeAnyPolyGon_old($img, $thumb_width, $thumb_height, $newfilename, $watermark=0,$sourceimg="") 
{ 

	// I. Проверка

    // Проверка наличия GD
    if (!extension_loaded('gd') && !extension_loaded('gd2'))	{trigger_error("GD отсутствует", E_USER_WARNING); return false;}

    // Получение данных о размере нашей картинки и о ее типе, последний нужен для строки ниже
    list($w_src, $h_src, $image_type) = getimagesize($img);

	// Определяем тип картинки
    switch ($image_type) 
    {
        case 2: $im = imagecreatefromjpeg($img);  break;
        default: die('Вы пытаетесь загрузить неподдерживаемый тип файла! Включена поддержка только JPG/JPEG файлов. Вернитесь назад и попробуйте загрузить другой файл');
    }
    

	// II. Инициализация

	// создаём пустую truecolor картинку заданной формы согласно входящих параметров
    $newImg = imagecreatetruecolor($thumb_width, $thumb_height);

	// вырезаем квадратную серединку по x, если фото горизонтальное 
	// Как бы высота больше, чем длина, поэтому горизонт
    if ($w_src>$h_src) {

		// Создаем картинку
		imagecopyresampled(
			
			// целевое изображение, исходное
			$newImg, $im,					
			
			// Точка на изображении назначения, которая определяет левый верхний угол прямоугольника, в который будет вставляться копируемая область.
			0, 0,							
			
			// ширина и высота прямоугольника в который будет вписана копируемая область
			($w_src-$h_src)/2, 0,	
			
			// на изображении-источнике, которая определяет левый верхний угол прямоугольника, содержащего копируемую.
			$thumb_width, $thumb_height,
			
			// ширина и высота копируемой области на изображении-источнике. 
			$h_src, $h_src					
		); 


	}

	// вырезаем квадратную верхушку по y, если фото вертикальное (хотя можно тоже серединку) 
    if ($w_src<$h_src) 
	     imagecopyresampled(
			$newImg, $im, 
			0, 0, 
			0, ($h_src-$w_src)/2, 
			$thumb_width, $thumb_height,
			$w_src, $w_src
	); 

	// квадратная картинка масштабируется без вырезок 
	if ($w_src==$h_src) imagecopyresampled($newImg, $im, 0, 0, 0, 0, $thumb_width, $thumb_height, $w_src, $w_src); 

	/* Правильный ресайз YY 05.04.2009 22:41 */

	// Вставка водяного знака начало
	if ($watermark == 1) {

		// Путь к изображению
		$watermark = imagecreatefrompng(FILESTORAGEPATH . "watermark{$sourceimg}.png");

		// Высота водяного знака по Х
		$watermark_width = imagesx($watermark);

		// Высота водяного знака по У
		$watermark_height = imagesy($watermark);

		// Правый нижний угол
		// $dest_x = $size[0] - $watermark_width - 5;
		// $dest_y = $size[1] - $watermark_height - 5;

		// Точно по центру
		$dest_x = ($thumb_width-$watermark_width)/2;
		$dest_y = ($thumb_height-$watermark_height)/2;
		 
		imagecopy($newImg, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height);

	}
	// Вставка водяного знака конец
    
    // Генерация JPG и только JPG картинки
	imagejpeg($newImg,$newfilename); 

	// Снос рабочего изображения
	imagedestroy($newImg);

	// Снос исходного изображения
	imagedestroy($im); 
 
    return $newfilename;
}

function resize_old($img, $thumb_width, $newfilename, $watermark=1,$sourceimg="") 
{ 
  $max_width=$thumb_width;

    //Check if GD extension is loaded
    if (!extension_loaded('gd') && !extension_loaded('gd2')) 
    {
        trigger_error("GD is not loaded", E_USER_WARNING);
        return false;
    }

    //Get Image size info
    list($width_orig, $height_orig, $image_type) = getimagesize($img);
    
    switch ($image_type) 
    {
        case 1: $im = imagecreatefromgif($img); break;
        case 2: $im = imagecreatefromjpeg($img);  break;
        case 3: $im = imagecreatefrompng($img); break;
        default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
    }

    /*** calculate the aspect ratio ***/
    $aspect_ratio = (float) $height_orig / $width_orig;

    /*** calulate the thumbnail width based on the height ***/
    $thumb_height = round($thumb_width * $aspect_ratio);
    
    $newImg = imagecreatetruecolor($thumb_width, $thumb_height);

    /* Check if this image is PNG or GIF, then set if Transparent*/  

        imagealphablending($newImg, true);
        imagesavealpha($newImg,true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $thumb_width, $thumb_height, $transparent);

		imagecopyresampled($newImg, $im, 0, 0, 0, 0, $thumb_width, $thumb_height, $width_orig, $height_orig);

	// Вставка водяного знака начало
	if ($watermark == 1) {

		// Путь к изображению
		$watermark = imagecreatefrompng(FILESTORAGEPATH . "watermark{$sourceimg}.png");

		// Высота водяного знака по Х
		$watermark_width = imagesx($watermark);

		// Высота водяного знака по У
		$watermark_height = imagesy($watermark);

		// Правый нижний угол
		// $dest_x = $size[0] - $watermark_width - 5;
		// $dest_y = $size[1] - $watermark_height - 5;

		// Точно по центру
		$dest_x = ($thumb_width-$watermark_width)/2;
		$dest_y = ($thumb_height-$watermark_height)/2;
		 
		imagecopy($newImg, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height);

	}
	// Вставка водяного знака конец

    //Generate the file, and rename it to $newfilename
    switch ($image_type) 
    {
        case 1: imagegif($newImg,$newfilename); break;
        case 2: imagejpeg($newImg,$newfilename);  break;
        case 3: imagepng($newImg,$newfilename); break;
        default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
    }

	imagedestroy($im); 
    imagedestroy($newImg); 
 
    return $newfilename;
}



// Обработка изображений
function resizeImageFileAndCopyToFS($userFileTempPath,$url) {

	$fileDestinationPath = IMAGESPATH_SOURCE . $url;
	$size = getimagesize($userFileTempPath);

	move_uploaded_file($userFileTempPath, $fileDestinationPath);

	resizeAnyPolyGon($fileDestinationPath, "136","91", IMAGESPATH_THUMBS . $url,0);
	resizeAnyPolyGon($fileDestinationPath, "356","237", IMAGESPATH_MIDDLE . $url,$_POST['watermark']);
	// resize_old($fileDestinationPath, "220", IMAGESPATH_MIDDLE . $url,$_POST['watermark']);
	resize_old($fileDestinationPath, $size['0'], IMAGESPATH_SOURCE . $url,$_POST['watermark'],"_source");


}

// Прикрепление файлов
function insertFilesIntoDBandFS($object_id, $imageWithResize=false, $table="objects_pics") {

		// Разбираем массив с данными о загруженных файлах
		foreach($_FILES['uploadfile']['name'] as $key=>$val) {

			// Массив с данными для файлов конкретных категорий
			foreach($_FILES['uploadfile']['name'][$key] as $keyint=>$valint) {

				// Если конкретный файл был загружен
				if ($valint) {

					// Подготовливаем данные для вставки в БД
					$details['object_id'] = $object_id;
					$details['name'] = $valint;

					// Физический путь к файлу
					if ($key == "1") $details['path'] = "$object_id-$valint";

					$userFileTempPath = $_FILES['uploadfile']['tmp_name'][$key][$keyint];

					// Если делаем ресайзинг изображений, то делаем две дополнительные к оригиналу копии
					if ($imageWithResize==true) resizeImageFileAndCopyToFS($userFileTempPath, $details['path']);
					
					// Копируем файл физически на сервере
					else move_uploaded_file($userFileTempPath, FILESTORAGEPATH . $details['path']);

					unset($userFileTempPath);
					
					// Вставляем данные о нем в таблицу БД
					insert_data($details, $table);
				
				}

			}	
			
		}

}

// Вывод списка файлов, связанных с заказом.

// $translationtype_id означает тип документа: 1-оригинал, 2-перевод
// $listfiles = вывести просто список файлов без ссылок на них
// $translation = слово, вставляемое в название переводенного файла
// 17.10.2007
// 30.03.2009 - added translation
function getAttachedFiles($object_id, $listfiles="0",$unsetattachfiles="") {

	global $f, $filelisting, $translation;

	// echo "RUN";

	// Если запрос существует
	if ($f) {

		$data = mysql_query("
			SELECT * FROM `objects_pics` 
			WHERE `object_id` = $object_id 
			ORDER BY id ASC
		");
	
		// echo mysql_num_rows($data);

		if($data) {

			// Получение массива с данными о файлах, прикрепленных к заказу
			for ($i=0; $i < mysql_num_rows($data); $i++)
				
				{

					// Получаем массив с данными о прикрепленных файлах
					$files = mysql_fetch_array($data);

					// print_r($files);

					// Вывод ссылки "Удалить" на странице редактирования объекта
					$delete = array(
						"edit" => "<span onclick=\"unlinkfile('$files[path]','$files[id]','fileid$files[id]')\" class=\"redlink\">[X] $translation[32]</span>"
					);

					// Получение массива с именами файлов для прикрепления к файлу
					if ($unsetattachfiles > "0") $filelisting .= $files['name'].";";

					// Вывод обычныго списка файлов, прикрепленных к этому объекту...
					if ($listfiles !="0") echo "<div>$files[name]</div>";

					// ...вместо ссылок на него, как здесь
					else echo 
					"\n<div id='fileid$files[id]' class='imagethumb'>
					 
					<div><B>$translation[27]:</B><br> $files[name]</div>
					<a href='". SITEURL. "_images/middle/$files[path]' target='_blank'>
					<img src='". SITEURL. "_images/thumbs/$files[path]' border='0' title='$translation[33]' alt='$translation[33]'></a>
					<div>" . @$delete[$_GET['action']] . "</div>
					</div>";


					// Просто список
					
					// ...вместо ссылок на него, как здесь
					/* else echo 
					"\n<div id='fileid$files[id]'>
					<a href='". SITEURL. "_images/middle/$files[path]' target='_blank'>
					<img src='/mad/img/icons/image.gif' border='0' title='$translation[33]' alt='$translation[33]'> 
					$files[name]</a>" . @$delete[$_GET['action']] . "</div>"; */

				}	// Конец for(...$data)

				return true;

		} // Конец if($data)

		

	}	// Конец if ($f)

	else return false;

				// if ($translationtype_id == "3") unset($filelisting);


}

// Удаление файлов
function delete_files($object_id) {

	$data = mysql_query("
			SELECT name FROM `objects_pics` 
			WHERE `object_id` = $object_id 
	");

	// Получение массива с данными о файлах, прикрепленных к заказу
	for ($i=0; $i < mysql_num_rows($data); $i++) {
	
		// Получаем массив с данными о прикрепленных файлах
		$files = mysql_fetch_array($data);

		// Удаляем файл физически с сервера
		unlink(FILESTORAGEPATH.$files['name']);
	
	}

	// А теперь удаление всех файлов, прикрепленых к заказу одним махом
	delete_data("object_id", "files", $_GET['id']);

}

?>