<?php

// Insertion
// 18.12.2007
// Запрещенные ключи идут отдельным массивом
// 04.04.2009 Добавлена запись в логи о действиях

function insert_data ($details, $table, $keys="", $values="") {

	global $ForbiddenChars, $AllowedChars, $db_pref;

		// Запрещенные ключи для вставки
		$ForbiddenKeysToInsert = array(
			"section",
			"action",
			"submit",
			"dosometh",
			"id",
			"do",
			"subject",
			"SWIFT_client",
			"SWIFT_remusername",
			"SWIFT_staffsettings",
			"PHPSESSID",
			"admin_pass",
			"file",
			"admin_name"
		);


	foreach($details as $key=>$val)

		{
			if (!in_array(strtolower($key), $ForbiddenKeysToInsert)) {
				$keys .="`$key`,"; $values .= "'".str_replace($ForbiddenChars, $AllowedChars, $val)."',";
			}
		}

	$strlenkey = strlen($keys);
	$keys = substr($keys, 0, $strlenkey-1);

	$strlenval = strlen($values);
	$values = substr($values, 0, $strlenval-1);

	$sql = "INSERT INTO `" . PREFIX . "$table` ($keys) VALUES ($values)";

	// echo $sql;

	writeLog('1',$table,'0',$sql);

	// Don't change here
	if (mysql_query($sql)) return 1; else return 0;
	// Don't change here */
	
}

// Edit data
// 18.12.2007
// Запрещенные ключи идут отдельным массивом

function edit_data ($details, $table, $sqlset="") {

	global $ForbiddenChars, $AllowedChars,$db_pref;

		// Запрещенные ключи для вставки
		$ForbiddenKeysToUpdate = array(
			"section",
			"action",
			"submit",
			"dosometh",
			"id",
			"sid",
			"admin_pass",
			"admin_name",
			"file",
			"PHPSESSID"
		);

	foreach($details as $key=>$val)

			{

			if (!in_array(strtolower($key), $ForbiddenKeysToUpdate)) 
				$sqlset .= "`$key` = '".str_replace($ForbiddenChars, $AllowedChars, $val)."',";

			}

	$strlenset = strlen($sqlset);
	$sqlset = substr($sqlset, 0, $strlenset-1);

		$sql	=	"UPDATE `" . PREFIX . "$table` SET $sqlset WHERE `id` ='$details[id]'";

	// echo $sql; print_r($_POST);

	writeLog('2',$table,$details['id'],$sql);

	// Don't change here
	if (mysql_query($sql)) return 1; else return 0;
	// Don't change here

}

// 21.10.2007
// Добавлена возможность множественного удаления
// Пока нет удаления вложенных страниц.
function delete_data ($columnname, $table, $ids, $multiple="0") {

	$sql	= array(
		"DELETE FROM `" . PREFIX . "$table` WHERE `$columnname` = '$ids'",
		"DELETE FROM `" . PREFIX . "$table` WHERE `$columnname` IN ($ids)"
	);

	// echo $sql[$multiple];

	writeLog('3',$table,$ids,$sql[$multiple]);

	// Don't change here
	if (mysql_query($sql[$multiple])) return 1; else return 0;
	// Don't change here

}

?>