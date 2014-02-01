<?

include "lib/mad.init.php";

switch($_GET['action']) {

default:

	break;

case "unlinkfile":

	// Удаляем файл физически с диска
	unlink(IMAGESPATH_THUMBS.$_GET['filename']);
	unlink(IMAGESPATH_SOURCE.$_GET['filename']);
	unlink(IMAGESPATH_MIDDLE.$_GET['filename']);

	// Удаляем с базы данных
	delete_data("path", "objects_pics", $_GET['filename']);

	// Конец работы
	break;

case "createfile":

	SaveSourceIntofile($_GET['id']);

	break;

}

?>