<?php
header('Content-type: text/html; charset=utf-8');
define("conf", TRUE);
function config($file) {
    return include($file);
}
$conf = config('conf.php');
session_start();
if ($_SESSION['key'] != md5($conf['name'].$conf['password'].$conf['key'])) header('location: index.php');
define('DS', DIRECTORY_SEPARATOR);
error_reporting(0);
set_time_limit(0);
$itemsperpage = 10; // кол-во эл-в на странице
$work_list = 'running/read_image.txt';
$work_inprogress = 'running/read_image.running';
$lines = 0;
$razmer_all = 0;
// функция сортировки
function _PriceCmp ( $a, $b ){ 
    if ( $a['price'] == $b['price'] ) {
	    return 0;
    }		
    if ( $a['price'] > $b['price'] ) return -1; return 1;
}

# тримаем строки массива
function trim_value(&$value){
    $value = trim($value);
}

# функция определения размера
function get_size_array($array){ 
	$kolp = count($array);
	$filesi = '';
	$fil_esi = 0;
	for($m = 0; $m < $kolp; $m++){
		$perem = '';
		$perem = trim($array[$m]);
		if(!empty($perem)){
			$mass8 = array();
			$mass8 = explode('|',$perem);
			$filesi = $filesi + $mass8[2];
		}
	}
	//размер файла
	if(!empty($filesi)){
		$fil_esi = sprintf("%0.3f", ($filesi/1024));
	}
	return $fil_esi;
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

//удаление фото в модальном окне и подгрузка следующего
if( $_POST['send_form'] == 4 ){
	$piks = $_REQUEST['puts'];
	$mass = array();
	$mass = explode("\n", file_get_contents(dirname(__FILE__).DS.$work_list));
	$kol = count ($mass);
	for($i = 0; $i < $kol; $i++){
		if (strripos(($mass[$i]), trim($piks)) !== false){
			unset($mass[$i]);
			$redf = $kol - 1;
			if($i == $redf){
				$ff = 0;
			}else{
				$ff = $i + 1;
			}
			
			$puttt = $mass[$ff];
			# удаляем фото
			@unlink($_SERVER['DOCUMENT_ROOT'].$piks);
			$mass = array_values($mass);
			array_walk($mass, 'trim_value');
			$fk = fopen(dirname(__FILE__).DS.$work_list,'w+');
			@chmod(dirname(__FILE__).DS.$work_list, 0777);
			fwrite( $fk,implode("\n",$mass));fclose($fk);
			break;
		}
	}
	
	# после удаления - готовим следующие фото
	//$putt = $_REQUEST['put'];
	$timer = explode('|',trim($puttt));
	if(!empty($timer[1])){
	$putt = str_replace($_SERVER['DOCUMENT_ROOT'],'',$timer[1]);
	$putt = str_replace('//','/',$putt);
	//получаем размер исходника
	$filesize = '';
	$filesize = @filesize ($_SERVER['DOCUMENT_ROOT'].$putt);
	$size1 = (int)($filesize/1024);
	//очищаем кэш
	clearstatcache ();
	
	$name_pi1 = basename($putt);
	
	$vremen_name = str_replace('_orginal999.','.',$putt);
	$name_pi2 = basename($vremen_name);
	//получаем размер обработанного изображения
	$filesize = '';
	$filesize = @filesize ($_SERVER['DOCUMENT_ROOT'].$vremen_name);
	//$filesize = (int)($filesize/1048576);

	$size2 = (int)($filesize/1024);
	$putt5 = '';
	$putt5 = $vremen_name;
	
		$code = '<div id="idall">
				<div id="id1"><button class="saveForm2" type="submit" id="view6" name="parses" value="'.$putt.'" style="cursor:pointer;">Delete and Next</button><span class="sp5">При нажатии кнопки картинка СРАЗУ удаляется!!!</span></div>
				<div id="id2">
				<span class="sp5">Обработанная: '.$putt5.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Размер: '.$size2.' Кб</span><img src="'.$putt5.'?reload='.rand(1000,9999).'" alt="Обработанная"   >
				
				</div>
			</div>';
		
	}else{
		$code = '<div id="idall">
				<div id="id1"><span class="sp5">Обработанных изображений больше нет!!!</span>
				</div>
			</div>';
	}
	print_r($code);
	
	
	print_r('&nbsp;');
    exit;
}

//модальное окно
if( $_POST['send_form'] == 2 ){
	$putt = $_REQUEST['put'];
	
	//получаем размер исходника
	$filesize = '';
	$filesize = @filesize ($_SERVER['DOCUMENT_ROOT'].$putt);
	$size1 = (int)($filesize/1024);
	//очищаем кэш
	clearstatcache ();
	
	$name_pi1 = basename($putt);
	
	$vremen_name = str_replace('_orginal999.','.',$putt);
	$name_pi2 = basename($vremen_name);
	//получаем размер обработанного изображения
	$filesize = '';
	$filesize = @filesize ($_SERVER['DOCUMENT_ROOT'].$vremen_name);
	//$filesize = (int)($filesize/1048576);
	$size2 = (int)($filesize/1024);
	$putt5 = '';
	$putt5 = $vremen_name;
	
	$code = '<div id="idall">
				<div id="id1"><button class="saveForm2" type="submit" id="view6" name="parses" value="'.$putt.'" style="cursor:pointer;">Delete and Next</button><span class="sp5">При нажатии кнопки картинка СРАЗУ удаляется!!!</span></div>
				<div id="id2">
				<span class="sp5">Обработанная: '.$putt5.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Размер: '.$size2.' Кб</span><img src="'.$putt5.'?free='.rand(1000,9999).'" alt="Обработанная"   >
				
				</div>
			</div>';
	print_r($code);
	
	
	
	
	print_r('&nbsp;');
	exit;
}

//удаление фото
if( $_POST['send_form'] == 3 ){
	$piks = $_REQUEST['puts'];
	// print_r($piks);
	$mass = array();
	$mass = explode("\n", file_get_contents(dirname(__FILE__).DS.$work_list));
	$kol = count ($mass);
	for($i = 0; $i < $kol; $i++){
		$mass[$i] = str_replace('//','/',$mass[$i]);
		if (strripos(($mass[$i]), trim($piks)) !== false){
			unset($mass[$i]);
			@unlink($_SERVER['DOCUMENT_ROOT'].$piks);
			$mass = array_values($mass);
			array_walk($mass, 'trim_value');
			$fk = fopen(dirname(__FILE__).DS.$work_list,'w+');
			@chmod(dirname(__FILE__).DS.$work_list, 0777);
			fwrite( $fk,implode("\n",$mass));fclose($fk);
			break;
		}
	
	}
}

//удаление всех фото
if( $_POST['send_form'] == 9 ){
	$mass = array();
	$mass = explode("\n", file_get_contents(dirname(__FILE__).DS.$work_list));
	$kol = count ($mass);
	for($i = 0; $i < $kol; $i++){
		$artt = array();
		$artt = explode('|',$mass[$i]);
		unset($mass[$i]);
		@unlink($artt[1]);
	}
	$fk = fopen(dirname(__FILE__).DS.$work_list,'w+');
	@chmod(dirname(__FILE__).DS.$work_list, 0777);
	fwrite( $fk,'');fclose($fk);
}

//вывод таблицы
if (isset($_REQUEST['page'])){
    $start = '';

    $start = $_REQUEST['page'];
    if( file_exists(dirname(__FILE__).DS.$work_list) ){
	    $massi = array();
	    $massi = explode("\n", file_get_contents(dirname(__FILE__).DS.$work_list));
		$razmer_all = get_size_array($massi);
		if(!empty($massi[0])){
			$lines = count($massi);
			//вывод данных в табличном виде
			$edc = '<table id="tab"><tr><td class="td1"><span class="sp">Имя</span></td><td class="td2"><span class="sp">Путь</span></td><td class="td3"><span class="sp">Размер, Кб</span></td><td class="td4"></td></tr>';
			$end_page = ($start * $itemsperpage) - 1;//по какую выводить
			$start_page = ($end_page - $itemsperpage) + 1;
			if( $start_page < 0 ){
				$start_page = 0;
			}
			for($q = $start_page; $q <= $end_page; $q++){
				$timer = array();
				if( !empty($massi[$q]) ){
					$na_me = '';
					$timer = explode('|',trim($massi[$q]));
					$timer[1] = str_replace($_SERVER['DOCUMENT_ROOT'],'',$timer[1]);
					$timer[1] = str_replace('//','/',$timer[1]);
					$edc .= '<tr>
					<td class="td1"><span class="sp">'.$timer[0].'</span></td>
					<td class="td2"><span class="sp">'.$timer[1].'</span></td>
					<td class="td3"><span class="sp">'.$timer[2].'</span></td>
					<td class="td4">
					<button class="saveForm" type="submit" id="view4" name="parsee" value="'.$timer[1].'" style="cursor:pointer;">Preview</button>
					<button class="saveForm1" type="submit" id="view5" name="parse" value="'.$timer[1].'" style="cursor:pointer;">Delete</button>
					</td>
					</tr>';
				}
			}
			$edc .= '</table>';
			$pagin = '';
			//пагинация*****************************************************************
			$itemscount=$lines; // количество элементов (статей)
			$cpage = $start; 
			// если странца не задана, то будем на 1й
			$pagedisprange=3; // соклько страниц до и после текущей выводить
			$pagescount=ceil($itemscount/$itemsperpage); // кол-во страниц
			$stpage=$cpage-$pagedisprange; // определим начиная с какого номера будем выводить страницы
			// если наше "начало" вылазит на отрицательные номера, то стави м в 1
			if ($stpage<1){ 
				$stpage=1; 
			} 
			$endpage=$cpage+$pagedisprange; // аналогично с номером, по который будем выводить
			// если больше чем страниц, то последняя выводимая страницы - самая последняя наша
			if ($endpage>$pagescount){ 
				$endpage=$pagescount; 
			} 
			if ($cpage>1){
				// first
				$pagin .= '<a onclick="showPagePrew(1, event)" href="?page=1"><<</a> ';
				// prev
				$pagin .= '<a onclick="showPagePrew('.($cpage-1).', event)" href="?page='.($cpage-1).'"><</a> ';
			}
			// если начало вывода не 1, то напечатаем три точки
			if ($stpage>1){
				$pagin .= '... ';
			}
			for ( $i = $stpage; $i <= $endpage; $i++){ 
				if ($i==$cpage){ 
					$pagin .= '<strong>'.$i.'</strong> '; 
				}else{ 
					$pagin .= '<a onclick="showPagePrew('.$i.', event)" href="?page='.$i.'">'.$i.'</a> '; 
				}
			}
			// если начало конец вывода не последняя страница, то напечатаем три точки
			if ($endpage<$pagescount){
				$pagin .= '... ';
			}
			if ($cpage<$pagescount){
				// next
				$pagin .= '<a onclick="showPagePrew('.($cpage+1).', event)" href="?page='.($cpage+1).'">></a> ';
				// last
				$pagin .= '<a onclick="showPagePrew('.$pagescount.', event)" href="?page='.$pagescount.'">>></a> ';
			}
        //*****************************************************************************
		}
	}
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html>
	<body>
	<?php print_r('<div><div id=\'lines\'>'.$lines.'</div><div id=\'data\'>'.$edc.$pagin.'</div><div id=\'title_left\'><p class="p3">Подлежат обработке: <b><span style="color:#FF0000;">'.$lines.' шт</span></b>, общим вессом: <b><span style="color:#FF0000">'.$razmer_all.' Мб</span></b></p>'.$wo1.'</div></div>') ?>
	</body>
	</html>
	<?php
	
}else{
	/*
	$keyy = 0;
	if( file_exists(dirname(__FILE__).DS.$work_list) ){
		$massiu = '';
		$massiu = explode("\n", file_get_contents(dirname(__FILE__).DS.$work_list));
		if(!empty($massiu[0])){
			$keyy = 1;
		}
	}
	if($keyy == 0){
	*/
	
	// Resort requested
	if (isset($_REQUEST['resort'])) {
		touch(dirname(__FILE__).DS.$work_inprogress);
		
		//file_put_contents(dirname(__FILE__).DS.'running/prew.log', "started\n", FILE_APPEND);
		
		// Close session
		session_write_close();
		
		//определяем полный путь
		$put = $_SERVER['DOCUMENT_ROOT'];
	
		//проверка на существования папки - но почему-то по корявому работает
		/*
		if( empty($put) ){
			echo 'Не указан путь';
			exit;
		}elseif(!is_dir($dir)) {   //проверяем наличие директории
			echo 'Папки не существует или неправильно указан путь';
			exit;
		}else{
	
		}
		*/

		$mas_file = $ARR = array();	
		$mas_file = GetFilesArr($put);
		
		$kol = count($mas_file);
		//file_put_contents(dirname(__FILE__).DS.'running/prew.log', "got: ".$kol." for: ".$put."\n", FILE_APPEND);
		
		for($i = 0; $i < $kol; $i++){
			if (strripos($mas_file[$i], '_orginal999.')){
				//размер файла
				//$fh = fopen(dirname(__FILE__).DS.'errerer.txt','a+');fwrite( $fh,$mas_file[$i]."\n");fclose($fh);
				//$fk = fopen(dirname(__FILE__).DS.'errerer.txt','w+');fwrite( $fk,implode("\n",$mas_file));fclose($fk);
				$filesize = @filesize ($mas_file[$i]);
				//$filesize = (int)($filesize/1048576);
				$filesize = (int)($filesize/1024);
			
				//загоняем в массив имя файла и имя его 
				$ARR[] = array ('price' =>$filesize, 'name' => $mas_file[$i]);
			}
		}	
		
		//file_put_contents(dirname(__FILE__).DS.'running/prew.log', "fetched: ".count($ARR).", last: ".$filesize."\n", FILE_APPEND);
		
		//очищаем кэш
		clearstatcache ();
		//сортируем 
		usort ($ARR, '_PriceCmp');
		//количество данных (строк) в массиве
		$koli = count($ARR);
		
		//file_put_contents(dirname(__FILE__).DS.'running/prew.log', "sorted: ".$koli."\n", FILE_APPEND);
		
		$new_arr = array();
		//собираем в массив для вывода в текстовик
		for($w = 0; $w < $koli; $w++){
			$name_file = $na_me = '';
			$name_file = basename($ARR[$w]['name']);
			$new_arr[] = trim($name_file).'|'.trim($ARR[$w]['name']).'|'.trim($ARR[$w]['price']);
		}
		
		//file_put_contents(dirname(__FILE__).DS.'running/prew.log', "new: ".count($new_arr)."\n", FILE_APPEND);
		
		if(!empty($new_arr)){
			
			array_walk($new_arr, 'trim_value');
			
			//file_put_contents(dirname(__FILE__).DS.'running/prew.log', "write: ".count($new_arr)."\n", FILE_APPEND);
			
			$fk = fopen(dirname(__FILE__).DS.$work_list,'w+');
			@chmod(dirname(__FILE__).DS.$work_list, 0777);
			fwrite( $fk,implode("\n",$new_arr));fclose($fk); 
		}
		
		unlink(dirname(__FILE__).DS.$work_inprogress);
	//}
	}
	
	if (file_exists(dirname(__FILE__).DS.$work_inprogress)) {
		$edc = '<div style="color:red">Идет обработка..</div>
		<script>
		if(typeof theTimeout != \'undefined\' && theTimeout) window.clearTimeout(theTimeout);
		theTimeout = setTimeout(function(){
		   $(\'#a3\').click();
		}, 10000);
		</script>';
		
	} else {
		// $edc = '<button id="butu" class="resort" type="submit" name="resort" style="cursor:pointer;">RESORT</button>';
		$edc = '';
		
		if( file_exists(dirname(__FILE__).DS.$work_list) ){
		    $massi = array();

		    $massi = explode("\n", trim(file_get_contents(dirname(__FILE__).DS.$work_list)));
			$razmer_all = get_size_array($massi);
			if(!empty($massi[0])){
				$lines = count($massi);
				//вывод данных в табличном виде
				$edc .= '<table id="tab"><tr><td class="td1"><span class="sp">Имя</span></td><td class="td2"><span class="sp">Путь</span></td><td class="td3"><span class="sp">Размер, Кб</span></td><td class="td4"></td></tr>';

				for($q = 0; $q < $itemsperpage; $q++){
					$timer = array();
					if( !empty($massi[$q]) ){
						$na_me = '';
						$timer = explode('|',trim($massi[$q]));
						$timer[1] = str_replace($_SERVER['DOCUMENT_ROOT'],'',$timer[1]);
						$timer[1] = str_replace('//','/',$timer[1]);
						$edc .= '<tr>
						<td class="td1"><span class="sp">'.$timer[0].'</span></td>
						<td class="td2"><span class="sp">'.$timer[1].'</span></td>
						<td class="td3"><span class="sp">'.$timer[2].'</span></td>
						<td class="td4">
						<button class="saveForm" type="submit" id="view4" name="parsee" value="'.$timer[1].'" style="cursor:pointer;">Preview</button>
						<button class="saveForm1" type="submit" id="view5" name="parse" value="'.$timer[1].'" style="cursor:pointer;">Delete</button>
						</td>
						</tr>';
					}
				}
				$edc .= '</table>';
				$pagin = '';
				//пагинация*****************************************************************

				$itemscount=$lines; // количество элементов (статей)
				$cpage=1; 
				// если странца не задана, то будем на 1й
				$pagedisprange=3; // соклько страниц до и после текущей выводить
				$pagescount=ceil($itemscount/$itemsperpage); // кол-во страниц
				$stpage=$cpage-$pagedisprange; // определим начиная с какого номера будем выводить страницы
				// если наше "начало" вылазит на отрицательные номера, то стави м в 1
				if ( $stpage<1 ){ 
					$stpage=1; 
				} 
				$endpage=$cpage+$pagedisprange; // аналогично с номером, по который будем выводить
				// если больше чем страниц, то последняя выводимая страницы - самая последняя наша
				if ( $endpage>$pagescount ){ 
					$endpage=$pagescount; 
				} 
				if ( $cpage>1 ) {
					// first
					$pagin .= '<a onclick="showPagePrew(1, event)" href="?page=1"><<</a> ';
					// prev
					$pagin .= '<a onclick="showPagePrew('.($cpage-1).', event)" href="?page='.($cpage-1).'"><</a> ';
				}
				// если начало вывода не 1, то напечатаем три точки
				if ( $stpage>1 ) {
					$pagin .= '... ';
				} 
				for ($i=$stpage;$i<=$endpage;$i++) { 
					if ($i==$cpage) { 
						$pagin .= '<strong>'.$i.'</strong> '; 
					}else { 
						$pagin .= '<a onclick="showPagePrew('.$i.', event)" href="?page='.$i.'">'.$i.'</a> '; 
					}
				}
				// если начало конец вывода не последняя страница, то напечатаем три точки
				if ( $endpage<$pagescount ){
					$pagin .= '... ';
				} 
				if ( $cpage<$pagescount ){
					// next
					$pagin .= '<a onclick="showPagePrew('.($cpage+1).', event)" href="?page='.($cpage+1).'">></a> ';
					// last
					$pagin .= '<a onclick="showPagePrew('.$pagescount.', event)" href="?page='.$pagescount.'">>></a> ';
				}
	        //*****************************************************************************
			}
		} else {
			$edc .= 'Подлежат просмотру и удалению: 0';
		}
	}
}


$po_ob = 'Подлежат просмотру и удалению: <b><span style="color:#FF0000;">'.$lines.' шт</span></b>, общим вессом: <b><span style="color:#FF0000">'.$razmer_all.' Мб</span></b>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<script>
	<?php
	echo file_get_contents('inner.js');
	?>
	</script>
	</head>
	<body link="#000000" vlink="#000000" alink="#FF0000">
		<div id="layout">
		    <?php
		    if ($lines) {
		    ?>
		    <p id="p1"><?php print_r($po_ob);?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button id="bututt" type="submit" name="dellall" class="saveForm9" style="cursor:pointer;">Dell ALL</button>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
		    <?php } ?>
		    <div id="pagin">
			<?php if( !empty($edc) ) {print_r($edc);}?>
			<?php if( $lines > $itemsperpage ) {print_r($pagin);}?>
			</div>
			
		</div>
		<div style="display: none;">
		    <div class="box-modal" id="exampleModal" style="height=1000px ; width=1000px">
		    <div class="box-modal_close arcticmodal-close">закрыть</div>
		    <div id="mod"></div>
		    </div>
		</div>
	</body>
</html>