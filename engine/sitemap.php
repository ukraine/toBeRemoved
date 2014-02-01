<?
header('Content-Type: text/xml; charset=utf-8');
?><<? echo ('?') ?>xml version="1.0" encoding="UTF-8"?>
<urlset 
	xmlns="http://www.google.com/schemas/sitemap/0.84"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84
	http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">
<?

error_reporting(0);

include "configs.php";
include "shared.php";
include "main.func.php";

getSettings();

function isTitleEmpty($variable) {

	$value = "... Имя элемента не предоставлено ...";
	if (!empty($variable)) $value = strip_tags($variable);
	return $value;

}

function isPagePathDefault($path) {
	if ($path!="default" && !empty($path)) return $path."/";
}

function isDescriptionEmpty($variable) {

	$limitchars = "255";
	$value = " ... Описание элемента не предоставлено ... ";

	if (!empty($variable)) $value = utf8_substr(strip_tags($variable),0,$limitchars);
	return $value;

}


function GenerateSiteMapXML($table,$and="") {

	global $siteurl;

	$sql = mysql_query("SELECT * FROM `".PREFIX."$table` WHERE `visibility` = 'y' $and");
	$num =  mysql_num_rows($sql);

    for ($i=0; $i<$num+1; $i++){

		$RSSItems = "";
		$itemtemplate = array(
	
			"pages"	=>

"<url>
	<loc>". SITEURL . GetCatName ($row['cat_id']) ."/". isPagePathDefault($row['page_path']) . "</loc>
	<lastmod>".substr($row['timestamp'],0,10)."</lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.$row[priority]</priority>
</url>\n",
				


			"news"	=>
				
"<url>
	<loc>" . SITEURL . "news/$row[id]/</loc>
	<lastmod>".substr($row['timestamp'],0,10)."</lastmod>
	<changefreq>monthly</changefreq>
	<priority>0.$row[priority]</priority>
</url>\n"

			);

        $row = mysql_fetch_array($sql);
		if ($i!=0) eval("\$RSSItems = \"$itemtemplate[$table]\";");
		echo $RSSItems;
    
	}

}

GenerateSiteMapXML("pages", "AND `cat_id` != '1'");
GenerateSiteMapXML ("news");

?>
</urlset>