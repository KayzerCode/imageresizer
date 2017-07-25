<?php
/* // Setting error reporting
ini_set('display_errors', 1);
error_reporting(55);
echo dirname(__FILE__).'/running/work_array.txt';
chmod(dirname(__FILE__).'/running/work_array.txt', 0777);
chmod(dirname(__FILE__).'/running/work_file.txt', 0777);
chmod(dirname(__FILE__).'/running/work_list.txt', 0777);
chmod(dirname(__FILE__).'/running/read_image.txt', 0777);
chmod(dirname(__FILE__).'/running/prew.log', 0777);
chmod(dirname(__FILE__).'/running/work_list_big_foto.txt', 0777);
chmod(dirname(__FILE__).'/running/work_work.txt', 0777); */

session_start();
define("conf", TRUE);
print_r("
	<head>
		<link type='text/css' rel='stylesheet' href='style/rekur1.css' />
		<link type='text/css' rel='stylesheet' href='style/jquery.arcticmodal-0.3.css' />
		<script src='style/jquery.min.js'></script>
		<script src='style/jquery.arcticmodal-0.3.min.js'></script>
		<script src='scripts.js'></script>
		<script src='inner.js'></script>
		<script>
		\$(document).ready(function () {
			// Pre-load section
			if (window.location.hash) {
				if (window.location.hash == '#vrResize') \$('#a2').click();
				else if (window.location.hash == '#vrResult') \$('#a3').click();
			}
		});
		</script>
	</head>
");

function config($file) {
    return include($file);
}
$conf = config('conf.php');
$login = FALSE;

if (isset($_POST['login']) and isset($_POST['name']) and isset($_POST['password'])) {
	$name = mysql_escape_string(htmlspecialchars(strip_tags($_POST['name'])));
	$password = mysql_escape_string(htmlspecialchars(strip_tags($_POST['password'])));
	if ($name == $conf['name'] and $password == $conf['password']) {
		$key = md5($conf['name'].$conf['password'].$conf['key']);
		//$time = time()+(60*60*24*92);
		$_SESSION['key'] = $key;
		$login = TRUE;
	}
	else {
		echo "<div class='auth_fault'>Неверный логин или пароль!</div>";
		$login = FALSE;
	}
}
else {
	if (isset($_SESSION['key'])) {
		$key = md5($conf['name'].$conf['password'].$conf['key']);
		if ($key == $_SESSION['key']) {
			$login = TRUE;
		}
		else {
			$_SESSION['key'] = null;
		}
	}
}
if (isset($_POST['logout'])) {
	$_SESSION['key'] = null;
	unset($_SESSION['key']);
	$login = FALSE;
}
else {

}
if ($login == FALSE) {
	print_r("
	<div class='block'>
		<form action='' method='POST'>
			<table>
				<tbody>
					<tr>
						<td>Логин:</td><td><input type='text' name='name' placeholder='Логин'><input type='hidden' name='auth' value='auth'></td>
					</tr>
					<tr>
						<td>Пароль:</td><td><input type='password' name='password' placeholder='Пароль'></td>
					</tr>
					<tr>
						<td colspan='2'><input style='float: right;' type='submit' name='login' value='Войти'></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	");
}
elseif ($login == TRUE) {
	$file = file_get_contents("stats.txt");
	$file = explode("|", $file);
	
	$work_work = 'running/work_work.txt';
	$work_array = 'running/work_array.txt';
	
	if (file_exists(dirname(__FILE__).'/'.$work_work)
		 || file_exists(dirname(__FILE__).'/'.$work_array)
	) {
		$file[0] = '-';
		$file[1] = '-';
		$file[2] = '-';		
	}
	
	if (!$_POST['loadStats']) {
		print_r("
			<div style='display: none;' class='popupbg'></div>
			<div style='display: none;' class='popup'></div>
			<div class='block'>
				<div class='menu'>
					<form method='POST' style='margin: 0px 0px 0px 5px; float: right;'><input style='border: none; height: 34px; padding: 0px;' class='menup' type='submit' name='logout' value='exit'></form>
					<ul>
						<li><p id='a1' class='menup active' href='/img/resize/'>Stats</p></li>
						<li><p id='a2' class='menup' href='/img/resize/pagin.php'>Resize</p></li>
						<li><p id='a3' class='menup' href='/img/resize/prew.php'>Result & Delete</p></li>
						<li><p id='a4' class='menup' href='/img/resize/pagin_big_foto.php'>Big Pics</p></li>
					</ul>
				</div>
				<div class='content'>
					<table style='color:#666;' id='tab'>
						<thead>
							<tr>
								<td class='td1'>Last Optimization</td>
								<td class='td2'>Optimized</td>
								<td class='td3'>Saved</td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class='td1'>".$file[0]."</td>
								<td class='td2'>".$file[1]."</td>
								<td class='td3'>".$file[2]."</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		");
	}
	else {
		print_r("
		<div>
			<table style='color:#666;' id='tab'>
				<thead>
					<tr>
						<td class='td1'>Last Optimization</td>
						<td class='td2'>Optimized</td>
						<td class='td3'>Saved</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class='td1'>".$file[0]."</td>
						<td class='td2'>".$file[1]."</td>
						<td class='td3'>".$file[2]."</td>
					</tr>
				</tbody>
			</table>	
		</div>
		");
	}
}

?>