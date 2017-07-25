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
$work_list = 'running/work_list_big_foto.txt';
$work_inprogress = 'running/work_list_big_foto.running';
$work_file = 'running/work_file.txt';
$work_work = 'running/work_work.txt';

$itemsperpage = 10; // кол-во эл-в на странице
$lines = 0;
$razmer_all = 0;
// функция сортировки
function _PriceCmp ( $a, $b ){ 
    if ( $a['price'] == $b['price'] ) {
	    return 0;
    }		
    if ( $a['price'] > $b['price'] ) return -1; return 1;
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


// Resort requested
if (isset($_REQUEST['resort'])) {
	touch(dirname(__FILE__).DS.$work_inprogress);
	
	// Close session
	session_write_close();
	
	//сбор путей на картинки и фильтрация

    $put = $shir = $shir = $vis = $size = $work_param = $putt = '';
	$massi = '';
	$mas_param = array();
	$massi = file_get_contents(dirname(__FILE__).DS.$work_file);
	$massi = trim($massi);
	$mas_param = explode('|',trim($massi));
	$putt  = $mas_param[0];
	$shir = $mas_param[1];
	$vis  = $mas_param[2];

    $size = 7500;     //вес

	//определяем полный путь
    $put = $_SERVER['DOCUMENT_ROOT'].$putt;
	$put = str_replace('//','/',$put);
	//$put = 'whitesquirrel.ru/';

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
				
					if( $filesize > $size ){ 
						$ARR[] = array ('price' =>$filesize, 'name' => $mas_file[$i]);
					}	
				
			}
		}
    }	
    //очищаем кэш
    clearstatcache ();
    //сортируем 
    usort ($ARR, '_PriceCmp');
    //количество данных (строк) в массиве
	$koli = count($ARR);
	$new_arr = array();
	//собираем в массив для вывода в текстовик
	for($w = 0; $w < $koli; $w++){
	    $name_file = $na_me = '';
	    $name_file = basename($ARR[$w]['name']);
	    $new_arr[] = trim($name_file).'|'.trim($ARR[$w]['name']).'|'.trim($ARR[$w]['price']);
	}
	$fk = fopen(dirname(__FILE__).DS.$work_list,'w+');
	@chmod(dirname(__FILE__).DS.$work_list, 0777);
	fwrite( $fk,implode("\n",$new_arr));fclose($fk);
	 
	unlink(dirname(__FILE__).DS.$work_inprogress);
}

if (file_exists(dirname(__FILE__).DS.$work_inprogress)) {
	$edc = '<div style="color:red">Идет обработка..</div>
	<script>
	if(typeof theTimeout != \'undefined\' && theTimeout) window.clearTimeout(theTimeout);
	theTimeout = setTimeout(function(){
	   $(\'#a4\').click();
	}, 10000);
	</script>';
	
} else {
	// $edc = '<button id="butu" class="rebig" type="submit" name="rebig" style="cursor:pointer;">re-READ</button>';
	$edc = '';
	
	if( file_exists(dirname(__FILE__).DS.$work_list) ){
	    $massi = array();
		
	    $massi = explode("\n", file_get_contents(dirname(__FILE__).DS.$work_list));
		$razmer_all = get_size_array($massi);
	    if(!empty($massi[0])){
			$lines = count ($massi);
		}
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
				<td class="td4"><button class="saveForm_big" type="submit" id="view3" name="parsee" value="'.$timer[1].'" style="cursor:pointer;">Big</button></td>
			    <td class="td4"><button class="saveForm_small" type="submit" id="view3" name="parsee" value="'.$timer[1].'" style="cursor:pointer;">Small</button></td>
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
            $pagin .= '<a onclick="showPageBig(1, event)" href="?page=1"><<</a> ';
            // prev
            $pagin .= '<a onclick="showPageBig('.($cpage-1).', event)" href="?page='.($cpage-1).'"><</a> ';
        }
		// если начало вывода не 1, то напечатаем три точки
        if ( $stpage>1 ) {
		    $pagin .= '... ';
		} 
        for ($i=$stpage;$i<=$endpage;$i++) { 
            if ($i==$cpage) { 
	            $pagin .= '<strong>'.$i.'</strong> '; 
	        }else { 
			    $pagin .= '<a onclick="showPageBig('.$i.', event)" href="?page='.$i.'">'.$i.'</a> '; 
			}
        }
		// если начало конец вывода не последняя страница, то напечатаем три точки
        if ( $endpage<$pagescount ){
		    $pagin .= '... ';
		} 
        if ( $cpage<$pagescount ){
            // next
            $pagin .= '<a onclick="showPageBig('.($cpage+1).', event)" href="?page='.($cpage+1).'">></a> ';
            // last
            $pagin .= '<a onclick="showPageBig('.$pagescount.', event)" href="?page='.$pagescount.'">>></a> ';
        }
        //*****************************************************************************
	}
}
	

if (isset($_REQUEST['page'])){
    $start = '';
	
    $start = $_REQUEST['page'];
    if( file_exists(dirname(__FILE__).DS.$work_list) ){
	    $massi = array();
	    $massi = explode("\n", file_get_contents(dirname(__FILE__).DS.$work_list));
		$razmer_all = get_size_array($massi);
		if(!empty($massi[0])){
			$lines = count ($massi);
		}
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
			    <td class="td4"><button class="saveForm_big" type="submit" id="view3" name="parsee" value="'.$timer[1].'" style="cursor:pointer;">Big</button></td>
			    <td class="td4"><button class="saveForm_small" type="submit" id="view3" name="parsee" value="'.$timer[1].'" style="cursor:pointer;">Small</button></td>
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
        $pagin .= '<a onclick="showPageBig(1, event)" href="?page=1"><<</a> ';
        // prev
        $pagin .= '<a onclick="showPageBig('.($cpage-1).', event)" href="?page='.($cpage-1).'"><</a> ';
        }
		// если начало вывода не 1, то напечатаем три точки
        if ($stpage>1){
		    $pagin .= '... ';
		}
        for ( $i = $stpage; $i <= $endpage; $i++){ 
            if ($i==$cpage){ 
	            $pagin .= '<strong>'.$i.'</strong> '; 
	        }else{ 
			    $pagin .= '<a onclick="showPageBig('.$i.', event)" href="?page='.$i.'">'.$i.'</a> '; 
			}
        }
		// если начало конец вывода не последняя страница, то напечатаем три точки
        if ($endpage<$pagescount){
		    $pagin .= '... ';
		}
        if ($cpage<$pagescount){
        // next
        $pagin .= '<a onclick="showPageBig('.($cpage+1).', event)" href="?page='.($cpage+1).'">></a> ';
        // last
        $pagin .= '<a onclick="showPageBig('.$pagescount.', event)" href="?page='.$pagescount.'">>></a> ';
        }
        //*****************************************************************************
	}
	
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html>
	<body>
	<?php print_r('<div><div id=\'lines\'>'.$lines.'</div><div id=\'data\'>'.$edc.$pagin.'</div><div id=\'title_left\'><p class="p3">Подлежат обработке: <b><span style="color:#FF0000;">'.$lines.' шт</span></b>, общим вессом: <b><span style="color:#FF0000">'.$razmer_all.' Мб</span></b></p>'.$wo1.'</div></div>') ?>
	</body>
	</html>
	<?php
}

if( file_exists(dirname(__FILE__).DS.$work_work) ){
	$wo1 = '<BR><p class="p3">CRON: </p><p class="p4"> &nbsp;&nbsp;работает</p>';
}else{
	$wo1 = '<BR><p class="p3">CRON: </p><p class="p5"> &nbsp;&nbsp;НЕ работает';
}


$po_ob = '<p class="p3">Найдено: <b><span style="color:#FF0000;">'.$lines.' шт</span></b>,<br> общим вессом: <b><span style="color:#FF0000">'.$razmer_all.' Мб</span></b></p>';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="style/rekur1.css">
	<script type="text/javascript" src="style/jquery.min.js"></script>

<!-- arcticModal -->
<script src="style/jquery.arcticmodal-0.3.min.js"></script>
<link rel="stylesheet" href="style/jquery.arcticmodal-0.3.css">

<!-- arcticModal theme -->
<link rel="stylesheet" href="style/themes/simple.css">
<script>
	<?php
	echo file_get_contents('inner.js');
	?>
	</script>
</head>
<body link="#000000" vlink="#000000" alink="#FF0000">
<div id="layout">
	<?php
	if( $lines) {
		?>
    <p id="p1">Изображения, чей вес больше 7.5 Мб  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
    <?php /* <form name="form" method="post" action="">
            <label id="la1"><?php print_r($_SERVER['DOCUMENT_ROOT']);?></label>
            <input type="text" name="zapros" id="put" value="<?php echo ($putt !== '' ? $putt : '');?>" placeholder="foto/piks"/>

	</form> */ ?>
	<div id="title">
		<div id="title_left">
		<?php print_r($po_ob);?>
		<?php print_r($wo1);?>
		</div>
		<div id="title_right">
		</div>
	</div>
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
<script type="text/javascript">
/*
function get(checkbox) {
var value = $(checkbox).val();
//alert(value)
    $.ajax({
		type: "POST",
		url: "proces.php",
		data: "chek="+value+"&send_form=1",

	});
}
 */  
function saveForm(submit){
//var value = $(this).text();
var value = $(submit).val();
var idshir = $("#shir").val();
var idvis = $("#vis").val();

//alert(value)
	$.ajax({
		type: "POST",
		url: "proces.php",
		data: "shir="+idshir+"&vis="+idvis+"&put="+value+"&send_form=2",
		success: function(html){
				$("#mod").html(html);
				}
	});
	
		$('#exampleModal').arcticmodal({
			afterClose: function(data, el) {
						$.ajax({
							type: "POST",
							url: "proces.php",
							data: "send_form=4",
						});
						}
		});
	
}
/* функция модал окна без ресайза*/
function saveForm_big(submit){
//var value = $(this).text();
	var value = $(submit).val();

//alert(value)
	$.ajax({
		type: "POST",
		url: "proces.php",
		data: "put="+value+"&send_form=5",
		success: function(html){
				$("#mod").html(html);
				}
	});
	$('#exampleModal').arcticmodal({
			
		});

	
}

/* function saveForm1(fg){
	if (window.confirm('Будет обработано '+ fg +' файлов. Продолжить?')){
		$.ajax({
			type: "POST",
			url: "proces.php",
			data: "send_form=3",
		});
	}
	
} */

</script>
</body>
</html>