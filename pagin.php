<?php
/*
	Functions
*/
{
	/**
	* Output result
	*/
	function _vr_jsout($msg) {
		?>
		<span style="color:red"><?php echo $msg;?></span>
		<script>
		if(typeof theTimeout != 'undefined' && theTimeout) window.clearTimeout(theTimeout);
		theTimeout = setTimeout(function(){
		   $('#a2').click();
		}, 10000);
		</script>
		<?php
		exit;
	}
	
	function config($file) {
	    return include($file);
	}
}

/*
	Settings and env.
*/
{
	// Define
	define("conf", TRUE);
	define('DS', DIRECTORY_SEPARATOR);
	
	// Header
	header('Content-type: text/html; charset=utf-8');
	
	// Errors
	error_reporting(0);
	//ini_set('display_errors', 1);
	//error_reporting(55);
	
	// Session
	session_start();
	
	$work_list = 'running/work_list.txt';
	$work_file = 'running/work_file.txt';
	$work_work = 'running/work_work.txt';

	$itemsperpage = 10; // кол-во эл-в на странице
	$lines = 0;
	$razmer_all = 0;
	
	$_vr_procfile = dirname(__FILE__).DS.'running/task.data';
}

/*
	Preparations
*/
{
	// Prepare
	$conf = config('conf.php');
	
	if ($_SESSION['key'] != md5($conf['name'].$conf['password'].$conf['key'])) header('location: index.php');
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

/*
	сбор путей на картинки и фильтрация
*/
if (isset($_REQUEST['sub'])) {
	
	// Settings
	unset($_SESSION['mass']);
    $put = $shir = $shir = $vis = $size = $work_param = $putt = '';
	$putt  = trim($_POST['zapros']);   //путь
    $shir = trim($_POST['shir']);     //ширина
    $vis  = trim($_POST['vis']);      //высота
    $size = (int)trim($_POST['size']);     //вес
	/*if( $size < 100 ){
		print_r('Введен недопустимый вес');
		exit;
	}*/
	//определяем полный путь
    $put = $_SERVER['DOCUMENT_ROOT'].$putt;
	$put = str_replace('//','/',$put);
	//$put = 'whitesquirrel.ru/';
	
	// Save last state
	$work_param = $putt.'|'.$shir.'|'.$vis.'|'.$size;
	$fp = fopen(dirname(__FILE__).DS.$work_file,'w+');
	@chmod(dirname(__FILE__).DS.$work_file, 0777);
	fwrite( $fp,$work_param);fclose($fp);
	
	/*
		Set cron-task
	*/
	{
		// Already running
		if (file_exists($_vr_procfile)) {
			_vr_jsout('Задача уже находится в обработке, ожидайте..');
		}
		
		// Save task data
		file_put_contents($_vr_procfile, serialize(
			Array(
				'path' => $put,
				'width' => $shir,
				'height' => $vis,
				'size' => $size,
			)
		));
		@chmod($_vr_procfile, 0777);
		
		// Fot it
		_vr_jsout('Задача принята к обработке..');
	}
	
	//print_r($put);
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
	
}else{
    if( file_exists(dirname(__FILE__).DS.$work_file) ){
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
	}
}	

if (isset($_REQUEST['page'])){
    $start = '';

	$allowedSize = intval($_REQUEST['sizeOfImages']);
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
        for ($q = $start_page; $q <= $end_page; $q++) {
            $timer = array();
	        if( !empty($massi[$q]) ){
	            $na_me = '';
	            $timer = explode('|',trim($massi[$q]));
	            $timer[1] = str_replace($_SERVER['DOCUMENT_ROOT'],'',$timer[1]);
				$timer[1] = str_replace('//','/',$timer[1]);
	            $edc .= '<tr>
	            <td class="td1"><span class="sp">'.$timer[0].'</span></td>
			    <td class="td2"><span class="sp" style="width: 100%">'.$timer[1].'</span></td>
			    <td class="td3"><span class="sp">'.$timer[2].'</span></td>
			    <td class="td4"><button class="preview" type="submit" id="view3" name="parsee" value="'.$timer[1].'" style="cursor:pointer;">Preview</button></td>
			    </tr>';
			}
        }
        $edc .= '</table>';
		$pagin = '';
        //пагинация*****************************************************************
        $itemscount=$lines; // количество элементов (статей)
        $cpage = $start; 
        // если странца не задана, то будем на 1й
        $pagedisprange=3; // сколько страниц до и после текущей выводить
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
	        $pagin .= '<a onclick="showPage(1, event)" href="?page=1"><<</a> ';
	        // prev
	        $pagin .= '<a onclick="showPage('.($cpage-1).', event)" href="?page='.($cpage-1).'"><</a> ';
        }
		// если начало вывода не 1, то напечатаем три точки
        if ($stpage>1){
		    $pagin .= '... ';
		}
        for ( $i = $stpage; $i <= $endpage; $i++){ 
            if ($i==$cpage){ 
	            $pagin .= '<strong>'.$i.'</strong> '; 
	        }else{ 
			    $pagin .= '<a onclick="showPage('.$i.', event)" href="?page='.$i.'">'.$i.'</a> '; 
			}
        }
		// если начало конец вывода не последняя страница, то напечатаем три точки
        if ($endpage<$pagescount){
		    $pagin .= '... ';
		}
        if ($cpage<$pagescount){
        // next
        $pagin .= '<a onclick="showPage('.($cpage+1).', event)" href="?page='.($cpage+1).'">></a> ';
        // last
        $pagin .= '<a onclick="showPage('.$pagescount.', event)" href="?page='.$pagescount.'">>></a> ';
        }
        //*****************************************************************************
	}

	if( file_exists(dirname(__FILE__).DS.$work_work) ){
		$wo1 = '<BR><p class="p3">CRON: </p><p class="p4"> &nbsp;&nbsp;работает</p>';
	}else{
		$wo1 = '<BR><p class="p3">CRON: </p><p class="p5"> &nbsp;&nbsp;НЕ работает';
	}

	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html>
	<body>
	<?php print_r('<div><div id=\'lines\'>'.$lines.'</div><div id=\'data\'>');?>
	<?php
	echo $edc;
	echo $pagin;
	// echo "No pagination available";
	?>
	<script>
	<?php
	echo file_get_contents('inner.js');
	?>
	</script>
	<?
	echo '</div><div id=\'title_left\'><p class="p3">Подлежат обработке: <b><span style="color:#FF0000;">'.$lines.' шт</span></b>, общим вессом: <b><span style="color:#FF0000">'.$razmer_all.' Мб</span></b></p>'.$wo1.'</div></div>';
	?>
	</body>
	</html>
	<?php
}

if( file_exists(dirname(__FILE__).DS.$work_work) ){
	$wo1 = '<BR><p class="p3">CRON: </p><p class="p4"> &nbsp;&nbsp;работает</p>';
}else{
	$wo1 = '<BR><p class="p3">CRON: </p><p class="p5"> &nbsp;&nbsp;НЕ работает';
}


$po_ob = '<p class="p3">Подлежат обработке: <b><span style="color:#FF0000;">'.$lines.' шт</span></b>, общим вессом: <b><span style="color:#FF0000">'.$razmer_all.' Мб</span></b></p>';

if (!isset($_POST['page']) and !isset($_POST['sub'])) { 
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
			// Still running
			if (!empty($_vr_procfile) && file_exists($_vr_procfile)) {
				?>
				<div align="center">
				<span style="color:red">Обработка еще не закончена, пожалуйста, ожидайте..</span>
				<script>
				if(typeof theTimeout != 'undefined' && theTimeout) window.clearTimeout(theTimeout);
				theTimeout = setTimeout(function(){
				   $('#a2').click();
				}, 10000);
				</script>
				</div>
				<?php
				
			} else {
				?>
			    <p id="p1">Ресайз изображения  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Внимание! Обрабатываются файлы от 100кб до 7.5 Мб!!!&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
			    <form name="form" method="post" action="">
			            <label id="la1"><?php print_r($_SERVER['DOCUMENT_ROOT']);?></label>
			            <input type="text" name="zapros" id="put" value="<?php echo ($putt !== '' ? $putt : '');?>" placeholder="foto/piks"/>
			            <div id="left">
			                <p class="p2">Уменьшить изображение до размеров:</p>
			                <span class="sp">Ширина</span><input type="text" id="shir" name="shir" class="inp" value="<?php echo ($shir !== '' ? $shir : '');?>" placeholder="px"/>
			                <span class="sp">Высота</span><input type="text" id="vis" name="vis" class="inp" value="<?php echo ($vis !== '' ? $vis : '');?>" placeholder="px"/>
			            </div>
			            <div id="right">
			                <p class="p2">Применить к изображениям чей вес больше:</p>
			                <span class="sp">Вес</span><input type="text" id="size" name="size" class="inp" value="<?php echo ($size !== '' ? $size : '');?>" placeholder="Кб"/>
			            </div>
			            <button id="but" class='showlist' type="submit" name="sub" style="cursor:pointer;">Show list</button>
				</form>
				
				<div id="_vr_msg" style="text-align:center;padding-bottom:10px;"></div>
				
				<?php /*
				<div id="title">
					<div id="title_left">
					<?php print_r($po_ob);?>
					<?php print_r($wo1);?>
					</div>
					<div id="title_right">
					<button id="butu" class='runall' type="submit" name="runall" style="cursor:pointer;">RUN ALL</button>
					</div>
				</div>
				*/ /* ?>
				
				<?php if( !empty($edc) ) {print_r($edc);}?>
				<div id="pagin"><?php if( $lines > $itemsperpage ) {print_r($pagin);}?></div>
				<?php */
				
				if( file_exists(dirname(__FILE__).DS.$work_list) ){
				    $massi = array();
					
					if (file_exists(dirname(__FILE__).DS.$work_work)) $massi = explode("\n", file_get_contents(dirname(__FILE__).DS.$work_work));
				    else $massi = explode("\n", file_get_contents(dirname(__FILE__).DS.$work_list));
					$razmer_all = get_size_array($massi);
				    if(!empty($massi[0])){
						$lines = count ($massi);
					}
				    //вывод данных в табличном виде
			        $edc = '<table id="tab"><tr><td class="td1"><span class="sp">Имя</span></td><td class="td2"><span class="sp">Путь</span></td><td class="td3"><span class="sp">Размер, Кб</span></td><td class="td4"></td></tr>';

			        for($q = 0; $q < 10; $q++){
			            $timer = array();
				        if( !empty($massi[$q]) ){
				            $na_me = '';
				            $timer = explode('|',trim($massi[$q]));
				            $timer[1] = str_replace($_SERVER['DOCUMENT_ROOT'],'',$timer[1]);
							$timer[1] = str_replace('//','/',$timer[1]);
							$edc .= '<tr>
							<td class="td1"><span class="sp">'.$timer[0].'</span></td>
							<td class="td2"><span class="sp" style="width: 100%;">'.$timer[1].'</span></td>
							<td class="td3"><span class="sp">'.$timer[2].'</span></td>
							<td class="td4"><button class="preview" type="submit" id="view3" name="parsee" value="'.$timer[1].'" style="cursor:pointer;">Preview</button></td>
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
			            $pagin .= '<a onclick="showPage(1, event)" href="?page=1"><<</a> ';
			            // prev
			            $pagin .= '<a onclick="showPage('.($cpage-1).', event)" href="?page='.($cpage-1).'"><</a> ';
			        }
					// если начало вывода не 1, то напечатаем три точки
			        if ( $stpage>1 ) {
					    $pagin .= '... ';
					} 
			        for ($i=$stpage;$i<=$endpage;$i++) { 
			            if ($i==$cpage) { 
				            $pagin .= '<strong>'.$i.'</strong> '; 
				        }else { 
						    $pagin .= '<a onclick="showPage('.$i.', event)" href="?page='.$i.'">'.$i.'</a> '; 
						}
			        }
					// если начало конец вывода не последняя страница, то напечатаем три точки
			        if ( $endpage<$pagescount ){
					    $pagin .= '... ';
					} 
			        if ( $cpage<$pagescount ){
			            // next
			            $pagin .= '<a onclick="showPage('.($cpage+1).', event)" href="?page='.($cpage+1).'">></a> ';
			            // last
			            $pagin .= '<a onclick="showPage('.$pagescount.', event)" href="?page='.$pagescount.'">>></a> ';
			        }
			        //*****************************************************************************

					if( file_exists(dirname(__FILE__).DS.$work_work) ){
						$wo1 = '<BR><p class="p3">CRON: </p><p class="p4"> &nbsp;&nbsp;работает</p>';
					}else{
						$wo1 = '<BR><p class="p3">CRON: </p><p class="p5"> &nbsp;&nbsp;НЕ работает';
					}

					//<div id=\'lines\'>'.$lines.'</div>
					?>
					<?php
					print_r('<div id="pagin"><div id=\'data\'>'.$edc);
					// echo $pagin;
					echo "No pagination available";
					print_r('</div></div><br />');
					print_r('<div><p class="p3">Подлежат обработке: <b><span style="color:#FF0000;">'.$lines.' шт</span></b>,<br />
					общим весом: <b><span style="color:#FF0000">'.$razmer_all.' Мб</span></b></p><br />'.$wo1.'</div>')
					?>
					<br /><br />
					<?php
					if (file_exists(dirname(__FILE__).DS.$work_work)) {?>
					<script>
					if(typeof theTimeout != 'undefined' && theTimeout) window.clearTimeout(theTimeout);
					theTimeout = setTimeout(function(){
					   $('#a2').click();
					}, 10000);
					</script>
					<?php } else { ?>
					<button id="butu" class='runall' type="submit" name="runall" style="cursor:pointer;">RUN ALL</button>
					<input class='fg' type='hidden' name='fg' value='<?php echo $lines; ?>'>
					<?php
					}	
				}
			}
			?>
		</div>
		
		<div style="display: none;">
		    <div class="box-modal" id="exampleModal" style="height=1000px ; width=1000px">
		    <div class="box-modal_close arcticmodal-close">закрыть</div>
		    <div id="mod"></div>
		    </div>
		</div>
	</body>
</html>
<?php } ?>