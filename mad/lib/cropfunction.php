<?

function resizeAnyPolyGon($img, $thumb_width, $thumb_height, $newfilename, $watermark=0,$sourceimg="")
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

    if ($thumb_width/$w_src > $thumb_height/$h_src)
    {
        // вырезаем квадратную серединку по x, если фото горизонтальное
        // Как бы высота больше, чем длина, поэтому горизонт
        $new_width = $w_src;
        $new_height = round($thumb_height * ($w_src/$thumb_width));
    }
    else
    {
    	// вырезаем квадратную верхушку по y, если фото вертикальное (хотя можно тоже серединку)
        $new_height = $h_src;
        $new_width = round($thumb_width * ($h_src/$thumb_height));
    }

	// Создаем картинку
	imagecopyresampled(

		// целевое изображение, исходное
		$newImg, $im,

		// Точка на изображении назначения, которая определяет левый верхний угол прямоугольника, в который будет вставляться копируемая область.
		0, 0,

		// ширина и высота прямоугольника в который будет вписана копируемая область
		($w_src - $new_width)/2, ($h_src - $new_height)/2,

		// на изображении-источнике, которая определяет левый верхний угол прямоугольника, содержащего копируемую.
		$thumb_width, $thumb_height,

		// ширина и высота копируемой области на изображении-источнике.
		$new_width, $new_height
	);

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

?>