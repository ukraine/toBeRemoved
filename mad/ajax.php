<?

include "lib/mad.init.php";

switch($_GET['action']) {

default:

	break;

case "unlinkfile":

	// ������� ���� ��������� � �����
	unlink(IMAGESPATH_THUMBS.$_GET['filename']);
	unlink(IMAGESPATH_SOURCE.$_GET['filename']);
	unlink(IMAGESPATH_MIDDLE.$_GET['filename']);

	// ������� � ���� ������
	delete_data("path", "objects_pics", $_GET['filename']);

	// ����� ������
	break;

case "createfile":

	SaveSourceIntofile($_GET['id']);

	break;

}

?>