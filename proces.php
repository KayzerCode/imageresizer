<?php
header('Content-type: text/html; charset=utf-8');
session_start();
define('conf', TRUE);
function config($file) {
	if (!file_exists($file)) exit('no conf file');
	$config = include($file);
	if (empty($config)) exit('config is empty');
    return $config;
}
$conf = config('conf.php');
if ($_SESSION['key'] != md5($conf['name'].$conf['password'].$conf['key'])) header('location: index.php');
define('DS', DIRECTORY_SEPARATOR);
include dirname(__FILE__).DS.'resize2.php';

$work_list = 'running/work_list.txt';
$work_file = 'running/work_file.txt';
$work_work = 'running/work_work.txt';
$work_array = 'running/work_array.txt';

// функция сортировки
function _PriceCmp ( $a, $b ){ 
    if ( $a['price'] == $b['price'] ) {
	    return 0;
    }		
    if ( $a['price'] > $b['price'] ) return -1; return 1;
}

//функция рекурсивного обхода
Function GetFilesArr($dir){
    $ListDir = Array();
    If ($handle = opendir($dir)){
        While (False !== ($file = readdir($handle))){
            If ($file == '.' || $file == '..'){
                Continue;
            }
        $path = $dir .DS. $file;
        If(Is_File($path)){
            $ListDir[] = $path;
        }ElseIf(Is_Dir($path)){
            $ListDir= array_merge($ListDir, GetFilesArr($path));
        }
        }
    CloseDir($handle);
    Return $ListDir;
    }
}

//запуск на создание рабочего файла и файла ключа для крона
if( $_POST['send_form'] == 3 ){
	
	/*
		Send e-mail
	*/
	{
		// Include mailer
		require_once('../../application/libraries/smtp.php');
		require_once('../../application/libraries/PHPMailer.php');
		
		$mail = new PHPMailer();

        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->Host = $conf['smtp_host']; // SMTP server
        $mail->SMTPDebug = false;                     // enables SMTP debug information (for testing)
        $mail->CharSet = "utf-8"; // или др.
        $mail->SMTPAuth = true;                  // enable SMTP authentication
        $mail->Port = 25;                    // set the SMTP port for the GMAIL server
        $mail->Username = $conf['smtp_login']; // SMTP account username
        $mail->Password = $conf['smtp_pass'];        // SMTP account password
        $mail->SetFrom($conf['smtp_from']);
        $mail->Subject = 'RESIZER, Started';
        $mail->MsgHTML('Image Optimizing Process has been started<br><a href="http://'.$conf['host'].'/img/resize/#vrResult">View result</a>');
        $mail->AddAddress($conf['notification_email']);
        
        $mail->Send();
	}
	
	//проверяем наличие рабочего файла
	if( file_exists(dirname(__FILE__).DS.$work_file) && file_exists(dirname(__FILE__).DS.$work_array) ){
	    /*
	    $massi = '';
		$mas_param = array();
	    $massi = file_get_contents(dirname(__FILE__).DS.$work_file);
		$massi = trim($massi);
		if( !empty($massi) ){
		    $mas_param = explode('|',trim($massi));
			$putt  = $mas_param[0];
			$shir = $mas_param[1];
			$vis  = $mas_param[2];
			$size = $mas_param[3];
		}
		
		//определяем полный путь
		$put = $_SERVER['DOCUMENT_ROOT'].DS.$putt;
		$put = str_replace('//','/',$put);
		$mas_file = $ARR = array();	
		$mas_file = GetFilesArr($put);	
		$kol = count($mas_file);
		for($i = 0; $i < $kol; $i++){
			if (strripos($mas_file[$i], '.jpg') !== false or strripos($mas_file[$i], '.jpeg') !== false){
				if (strripos($mas_file[$i], '_orginal999') == false ){
					//размер файла
					$filesize = @filesize ($mas_file[$i]);
					//$filesize = (int)($filesize/1048576);
					$filesize = (int)($filesize/1024);
					//загоняем в массив имя файла и имя его 
				
					if( $filesize >= $size and $filesize <= 7500 ){ 
						$ARR[] = array ('price' =>$filesize, 'name' => $mas_file[$i]);
					}	
				
				}
			}
		}
		//очищаем кэш
		clearstatcache ();
		//сортируем 
		usort ($ARR, '_PriceCmp');
		*/
		
		$ARR = unserialize(file_get_contents(dirname(__FILE__).DS.$work_array));
		if (!$ARR) echo 'Нет данных';
		else {
			//количество данных (строк) в массиве
			$koli = count($ARR);
			$new_arr = array();
			//собираем в массив для вывода в текстовик
			for($w = 0; $w < $koli; $w++){
				$name_file = $na_me = '';
				$name_file = basename($ARR[$w]['name']);
				$new_arr[] = trim($name_file).'|'.trim($ARR[$w]['name']).'|'.trim($ARR[$w]['price']);
			}
			//создаем файл ключа для разрешения работы из под крона и он же список картинок для обработки
			$fk = fopen(dirname(__FILE__).DS.$work_work,'w+');
			@chmod(dirname(__FILE__).DS.$work_work, 0777);
			fwrite( $fk,implode($new_arr,"\n"));
			fclose($fk);

			$i = 0;
			while ($i < count($new_arr)) {
				$explode = explode("|", $new_arr[$i]);
				$explode = $explode[2];
				$saved += $explode;
				$i++;
			}

			$stats = Array(date('j.m.Y G.i', time()), count($new_arr), $saved);
			$fk = fopen(dirname(__FILE__).DS.'stats.txt', 'w+');
			@chmod(dirname(__FILE__).DS.'stats.txt', 0777);
			fwrite($fk, implode($stats, '|'));
			fclose($fk);
		}
		
	}else{
		echo 'Не найден рабочий файл с параметрами';
	}
}
/*
//если нажата кнопка загрузки из файла
if( $_POST['send_form'] == 1 ){
	$mass = array();
	$dannue = '';
	$dannue = trim($_POST['chek']);
	$mass = $_SESSION['mass'];
	if ( !in_array($dannue, $mass) ){
		$mass[] = $dannue;
		$_SESSION['mass'] = $mass;
	}else{
		$kol = count($mass);
		for($i = 0; $i < $kol; $i++){
			if( $mass[$i] == $dannue ){
				unset($mass[$i]);
	            $mass = array_values($mass);
			}
		}
		$_SESSION['mass'] = $mass;
	}

}
*/
//прием данным и ресайз изображения
if( $_POST['send_form'] == 2 ){
	$putt  = trim($_POST['put']);     //путь к изображению
    $shir = trim($_POST['shir']);     //ширина
    $vis  = trim($_POST['vis']);      //высота
    $put1 = $putt;
	
	$v_put = $name_pikss = $raschir = '';
	$v_put = basename($putt);
	if (strripos($v_put, '.jpg') !== false){
		$raschir = '.jpg';
		$name_pikss = str_replace('.jpg','',$v_put);
	}elseif(strripos($mas_file[$i], '.jpeg') !== false){
		$raschir = '.jpeg';
		$name_pikss = str_replace('.jpeg','',$v_put);
	}
	
	$path_parts = $new_put_fi = $vremen_name = '';
	
	$path_parts = pathinfo($putt);
	//$new_put_fi  = $path_parts['dirname'].DS.$name_pikss.'_orginal999'.$raschir;
	$vremen_name = $path_parts['dirname'].DS.'work999'.$raschir;
	
	$params = array(
    'aspect_ratio' => false,
    'rgb' => '0x000000',
    'crop' => false,
	'quality' => 80,
	'constraint' => array('width'=>$shir,'height'=>$vis)
	);
	
	if(img_resize($_SERVER['DOCUMENT_ROOT'].$putt, $_SERVER['DOCUMENT_ROOT'].$vremen_name, $params)){
		$_SESSION['vremen'] = '';
		$_SESSION['vremen'] = $vremen_name;
		//переименовываем оиригинал
		//rename($_SERVER['DOCUMENT_ROOT'].$putt,$_SERVER['DOCUMENT_ROOT'].$new_put_fi);
		//rename($vremen_name,$_SERVER['DOCUMENT_ROOT'].$putt);
	
		//получаем размер исходника
		$filesize = '';
		$filesize = @filesize ($_SERVER['DOCUMENT_ROOT'].$putt);
		//$filesize = (int)($filesize/1048576);
		$size1 = (int)($filesize/1024);
		//очищаем кэш
		clearstatcache ();
		//получаем размер обработанного изображения
		$filesize = '';
		$filesize = @filesize ($_SERVER['DOCUMENT_ROOT'].$vremen_name);
		//$filesize = (int)($filesize/1048576);
		$size2 = (int)($filesize/1024);
		$putt5 = '';
		$putt5 = $vremen_name;
		//$size1 = '104 Кб';
		//$size2 = '45 Кб';
	
		$code = '<div id="idall">
			<div id="id1"><span class="sp">'.$size1.'</span><img src="'.$putt.'" alt="Оригинал"  ></div>
			<div id="id2"><span class="sp">'.$size2.'</span><img src="'.$putt5.'" alt="Обработанная"   ></div>
			</div>';
	 
		//$_SESSION['re'] = $put1;
		print_r($code);
	
	
	}else{
		print_r('ERROR');
	}
	
	
	
}

//прием данным большого размера для простого вывода в модальном окне
if( $_POST['send_form'] == 5 ){
	$putt  = trim($_POST['put']);     //путь к изображению
    $put1 = $putt;
	//получаем размер исходника
	$filesize = '';
	$filesize = @filesize ($_SERVER['DOCUMENT_ROOT'].$putt);
	//$filesize = (int)($filesize/1048576);
	$size1 = (int)($filesize/1024);

	$code = '<div id="idall">
		<div id="id1"><span class="sp">'.$size1.'</span><img src="'.$putt.'" alt="Оригинал"  ></div>
		</div>';
 
	//$_SESSION['re'] = $put1;
	print_r($code);

}

//обработка после закрытие модального окна - удаление временного файла
//прием данным и ресайз изображения
if( $_POST['send_form'] == 4 ){
	$vremen_name = '';
	$vremen_name = $_SESSION['vremen'];
	@unlink($_SERVER['DOCUMENT_ROOT'].$vremen_name);
	unset($_SESSION['vremen']);

}

// Small preview
if( $_POST['send_form'] == 6 ){
	$massi = '';
	$mas_param = array();
	$massi = trim(file_get_contents(dirname(__FILE__).DS.$work_file));
	if( !empty($massi) ){
		$mas_param = explode('|',trim($massi));
		$shir = $mas_param[1];
		$vis  = $mas_param[2];
		$size = $mas_param[3];
	}
	
	$putt  = trim($_POST['put']);     //путь к изображению
    
	$v_put = $name_pikss = $raschir = '';
	$v_put = basename($putt);
	if (strripos($v_put, '.jpg') !== false){
		$raschir = '.jpg';
		$name_pikss = str_replace('.jpg','',$v_put);
	}elseif(strripos($mas_file[$i], '.jpeg') !== false){
		$raschir = '.jpeg';
		$name_pikss = str_replace('.jpeg','',$v_put);
	}
	
	$path_parts = $new_put_fi = $vremen_name = '';
	
	$path_parts = pathinfo($putt);
	//$new_put_fi  = $path_parts['dirname'].DS.$name_pikss.'_orginal999'.$raschir;
	$vremen_name = '/img/resize/running/temp.jpg'; // $path_parts['dirname'].DS.'work999'.$raschir;
	
	$params = array(
    'aspect_ratio' => false,
    'rgb' => '0x000000',
    'crop' => false,
	'quality' => 80,
	'constraint' => array('width'=>$shir,'height'=>$vis)
	);
	
	if(img_resize($_SERVER['DOCUMENT_ROOT'].$putt, $_SERVER['DOCUMENT_ROOT'].$vremen_name, $params)){
		//$_SESSION['vremen'] = '';
		//$_SESSION['vremen'] = $vremen_name;
		//переименовываем оиригинал
		//rename($_SERVER['DOCUMENT_ROOT'].$putt,$_SERVER['DOCUMENT_ROOT'].$new_put_fi);
		//rename($vremen_name,$_SERVER['DOCUMENT_ROOT'].$putt);
	
		//получаем размер исходника
		$filesize = '';
		$filesize = @filesize ($_SERVER['DOCUMENT_ROOT'].$putt);
		//$filesize = (int)($filesize/1048576);
		$size1 = (int)($filesize/1024);
		//очищаем кэш
		clearstatcache ();
		//получаем размер обработанного изображения
		$filesize = '';
		$filesize = @filesize ($_SERVER['DOCUMENT_ROOT'].$vremen_name);
		//$filesize = (int)($filesize/1048576);
		$size2 = (int)($filesize/1024);
		$putt5 = '';
		$putt5 = $vremen_name;
		//$size1 = '104 Кб';
		//$size2 = '45 Кб';
	
		$code = '<div id="idall">
			<div id="id2"><span class="sp">'.$size2.'</span><img src="'.$putt5.'?rand='.rand(1000,9999).'" alt="Обработанная"   ></div>
			</div>';
	 
		//$_SESSION['re'] = $put1;
		print_r($code);
	
	
	}else{
		print_r('ERROR');
	}
	
	
	
}

print_r('&nbsp;');



?>