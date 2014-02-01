<?php

global $link, $url, $siteurl, $loggedin;
$error_msg = $translation['95'];

if (isset($_SERVER['HTTP_REFERER'])) $url = $_SERVER['HTTP_REFERER'];

function sendNewPass() {

    global $error_msg, $Settings, $translation;

    $_POST['id'] = 1;
    $section = "settings";
    $error_msg = $translation['89'];

    if ($_POST['login'] == $Settings['login']) {
		$NewPass = substr(md5(rand()),0,8);
        include "lib/default.func.php";
        $_POST['password'] = md5($NewPass);
        unset($_REQUEST['phpbb2mysql_data']);
        unset($_REQUEST['PHPSESSID']);
        unset($_REQUEST['submit']);
        unset($_REQUEST['login']);
        unset($_REQUEST['action']);
        $_REQUEST['Ваш новый пароль'] = $NewPass;
        if (edit_data (&$_POST, $section)) {
            sendmail($translation['89']);
            $error_msg = $translation['86'];
        } else $error_msg = $translation['88'];

    }

}


function openCloseAccess() {

    global $url, $error_msg, $link, $db, $form, $Settings, $translation;

    // echo md5($_POST['password']) . "<br>" .  $Settings['password'];

    if (($_REQUEST['do'] == "allow" && $_POST['login'] == $Settings['login'] && md5($_POST['password'])== $Settings['password'])) {
        $_SESSION['loggedin'] = "yes";
        if(isset($_POST['remember']) && $_POST['remember'] == 1) {
            setcookie('MADlogin', $_POST['login'], time() + 14*3600*25);
            setcookie('MADpassword', $Settings['password'], time() + 14*3600*25);
            unset($_POST['remember']);
        }

		writeLog('4',$table,'0',"Выполнен вход");
        
        header("Location: $url");
    }

    elseif ($_GET['do'] == "logoff")	{
        session_destroy();
        setcookie('MADlogin', "", time() - 14*3600*25);
        setcookie('MADpassword', "", time() - 14*3600*25);
        header("Location: $url");
    }

    else {
        $error_msg = "<span style='color: red'>$translation[85]</span>";
    }

}
if (!empty($_REQUEST['do'])) openCloseAccess();
if (!empty($_REQUEST['action'])) @sendNewPass();

?>
<html>
<head>
<title><?php echo $translation['82'];?></title>
<link rel="stylesheet" href="<?php echo $siteurl; ?>img/_mad.css" type="text/css">
<SCRIPT type="text/javascript" language="JavaScript"> 
function toggle_visibility(id) {
    var e = document.getElementById(id);
    if(e != null) {
        if(e.style.display == 'none') {
            e.style.display = 'block';
        } else {
            e.style.display = 'none';
        }
    }
}
</script>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
</head>
<body id="login">
<H3 id="first"><?php echo $error_msg; ?></H3>
<form action="" name="getAccess" method="post">
	<input type='text' name='login' style='width: 200px' id="login"> <label for="login"><?php echo $translation['75']?></label><br>
	<input type="password" name="password" style="width: 200px" id="password"> <label for="password"><?php echo $translation['76']?> </label><br><br>
<!--     <input type="checkbox" name="remember" id="remember" value="1" style="width: auto; margin-right: 0;"> <label for="remember">Запомнить меня</label> -->
	<input type="submit" name="access" value="<?php echo $translation['91']?>" style='width: 200px'>
	<input type="hidden" name="do" value="allow">
	<br><br> &nbsp; &nbsp; &nbsp; <span onClick="toggle_visibility('reminder')" class="InnerLink"><?php echo $translation['83']?></span>

</form>
<form action="" name="Reminder" method="post" id="reminder" style="display:none;">
	<input type='text' name='login' style='width: 200px' id="loginf"> <label for="loginf"><?php echo $translation['84']?></label>
	<br><input type="submit" name="submit" value="<?php echo $translation['87']?>" style='width: 200px'>
	<input type="hidden" name="action" value="forgot">
</form>
</body>
</html>