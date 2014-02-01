<? 

// if ($_SERVER['REMOTE_ADDR'] == "77.41.0.182" || $_SERVER['REMOTE_ADDR'] == "91.198.171.12") {

$templateDir = "templates";

if (@$_GET['v'] == "2") $templateDir = "templates2";

// Подключаем инициализатор сайта
include "./engine/init.php";

// Подключаем шаблон страниц(ы)
include "./$templateDir/$template.html";