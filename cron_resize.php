<?php
$server = ''; // put here config.sitename

// if (!isset($_GET['_manual'])) exit('halted');

/*
	Preparations
*/
{
	ob_start();
	define('DS', DIRECTORY_SEPARATOR);
}

/*
	Error processing
*/
{
	// All errors
	ini_set('display_errors', 1);
	error_reporting(55);
	
	/**
	 * Inner exception
	 */
	class ResizerException extends Exception {
		/**
		 * Creates new exception
		 * @param string $message
		 */
		public function __construct($message) {
			// notify("Import exception error: $message");
			parent::__construct($message);
		}
	}

	/**
	 * Temporary to screen
	 * @param      $message
	 */
	function notify($message, $errno = 0) {
		echo "ERROR: ".$message."\n";
		file_put_contents(dirname(__FILE__).DS.'running/error.log', date('Y-m-d H:i:s').' '.$message."\n----------------\n", FILE_APPEND);
		@chmod(dirname(__FILE__).DS.'running/error.log', 0777);
		innerMailer('Error', $message);
		
		// Return or die?
		if ($errno != E_USER_ERROR) return false;
		exit;
	}

	/**
	* Set error handler
	* @param mixed $errno
	* @param mixed $errorString
	* @param mixed $errorFile
	* @param mixed $errorLine
	*/
	function innerErrorHandler($errno, $errorString, $errorFile, $errorLine, $errorContext) {
		if (!is_int(strpos($errorString, 'imagecreatefrom'))) return notify("PHP error: $errorString\nAt file: $errorFile\nLine: $errorLine\nContext: ".var_export($errorContext, true), $errno);
		return true;
	}
	set_error_handler("innerErrorHandler");
	
	function innerShutdown()
	{
	    $processing_result = ob_get_flush();
	    file_put_contents(dirname(__FILE__).'/running/exec-'.rand(10000,99999).'.log', $processing_result);
	}

	register_shutdown_function('innerShutdown');
}
// session_start();
// if ($_SESSION['key'] != md5($conf['name'].$conf['password'].$conf['key'])) header('location: index.php');

include dirname(__FILE__).DS.'resize2.php';

$work_list = 'running/work_list.txt';
$work_file = 'running/work_file.txt';
$work_work = 'running/work_work.txt';
$work_array = 'running/work_array.txt';
$work_big = 'running/work_list_big_foto.txt';
$work_processed = 'running/read_image.txt';

// Read config
function config($file) {
	if (!file_exists($file)) exit('no conf file');
	$config = include($file);
	if (empty($config)) exit('config is empty');
    return $config;
}
$conf = config('conf.php');

/*
	Folder scanner
*/
{
	// Settings
	$_vr_procfile = dirname(__FILE__).DS.'running/task.data';
	$_vr_lockfile = dirname(__FILE__).DS.'running/task.running';
	
	// Should we run?
	if (file_exists($_vr_procfile)) {
		echo "scanner init\n";
		
		// Starting up if not already running
		$lock_pointer = fopen($_vr_lockfile, 'w');
		if (flock($lock_pointer, LOCK_EX | LOCK_NB)) {
			echo "scanner locked\n";
			
			// Read task file
			$task_data = unserialize(file_get_contents($_vr_procfile));
			
			// Re-set
			$put = $task_data['path'];
			$shir = $task_data['width'];
			$vis = $task_data['height'];
			$size = $task_data['size'];
			
			// Processing logics
			if (!empty($task_data['path'])) {
				$mas_file = $ARR = $ARR_BIG = array();	
			    $mas_file = GetFilesArr($put);	
			    // echo "--files--\n";
			    // var_dump($mas_file);
			    $kol = count($mas_file);
			    for($i = 0; $i < $kol; $i++){
					if (strripos($mas_file[$i], '.jpg') !== false or strripos($mas_file[$i], '.jpeg') !== false){
						if (strripos($mas_file[$i], '_orginal999') == false ){
							//размер файла
							$filesize = @filesize ($mas_file[$i]);
							//$filesize = (int)($filesize/1048576);
							$filesize = (int)($filesize/1024);
							//загоняем в массив имя файла и имя его 
							// echo $mas_file[$i].' > '.$filesize." <> ".$size."\n";
							
								if( $filesize >= $size and $filesize <= 7500 ){ 
									echo 'add to task: '.$mas_file[$i].' > '.$filesize." >= ".$size."\n";
									$ARR[] = array ('price' =>$filesize, 'name' => $mas_file[$i]);
								}
								
								if( $filesize > 7500 ){ 
									$ARR_BIG[] = array ('price' =>$filesize, 'name' => $mas_file[$i]);
								}	
							
						}
					}
			    }

			    //очищаем кэш
			    clearstatcache();
			    //сортируем 
			    usort($ARR, '_PriceCmp');
			    file_put_contents(dirname(__FILE__).DS.$work_array, serialize($ARR));
			    @chmod(dirname(__FILE__).DS.$work_array, 0777);
			    
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
				fwrite( $fk,implode("\n",$new_arr));
				fclose($fk);
				
				usort($ARR_BIG, '_PriceCmp');
				$koli = count($ARR_BIG);
				$new_arr = array();
				//собираем в массив для вывода в текстовик
				for($w = 0; $w < $koli; $w++){
				    $name_file = $na_me = '';
				    $name_file = basename($ARR_BIG[$w]['name']);
				    $new_arr[] = trim($name_file).'|'.trim($ARR_BIG[$w]['name']).'|'.trim($ARR_BIG[$w]['price']);
				}
				$fk = fopen(dirname(__FILE__).DS.$work_big,'w+');
				@chmod(dirname(__FILE__).DS.$work_big, 0777);
				fwrite( $fk,implode("\n",$new_arr));
				fclose($fk);
				
				// E-mail notification on completion
				innerMailer('Show list complete', 'Image scan complete<br><a href="http://'.$conf['host'].'/img/resize/#vrResize">View result</a>');
			}
			
			// Remove lock
			flock($lock_pointer, LOCK_UN);
			fclose($lock_pointer);
			
			// Remove proc files
			if (file_exists($_vr_procfile)) unlink($_vr_procfile);
			if (file_exists($_vr_lockfile)) unlink($_vr_lockfile);
			
		} else {
			echo "already running\n";
		}
		
	} else {
		echo "no scanner task\n";
	}
}

echo "\n";

/*
	Pic processing
*/
try {
	// Should we run?
	if (!file_exists(dirname(__FILE__).DS.$work_work)) {
		echo "no pic.proc task, stopping\n";
		exit;
	}
	
	echo "init pic.proc\n";
	
	// Settings file missing?
	if (!file_exists(dirname(__FILE__).DS.$work_file)) {
		throw new ResizerException("Missing settings file");
	}
	
	// Fetch settings
	if (!($settings = trim(file_get_contents(dirname(__FILE__).DS.$work_file)))
		|| !is_int(strpos($settings, '|'))
	) {
		throw new ResizerException("Settings file is empty");
	}
	
	// Invalid settings
	if (!($settings = explode('|', $settings))
		|| empty($settings[1])
		|| empty($settings[2])
	) {
		throw new ResizerException("Invalid settings");
	}
	
	echo "read settings\n";
	$settings['width'] = $settings[1];
	$settings['height'] = $settings[2];
	// $settings['size'] = $settings[3];
	// print_r($settings);
	
	// Reading tasks
	$tasks_list = explode("\n", trim(file_get_contents(dirname(__FILE__).DS.$work_work)));
	
	// No tasks, stop processing
	echo "reading tasks\n";
	if (empty($tasks_list[0])) {
		echo "no tasks found, stopping..\n";
		
		// Remove task-files
		if (file_exists(dirname(__FILE__).DS.$work_work)) @unlink(dirname(__FILE__).DS.$work_work);
		if (file_exists(dirname(__FILE__).DS.$work_list)) @unlink(dirname(__FILE__).DS.$work_list);
		if (file_exists(dirname(__FILE__).DS.$work_array)) @unlink(dirname(__FILE__).DS.$work_array);
		
		// Send e-mail
		innerMailer('Complete', 'Image Optimizing Process completed<br><a href="http://'.$server.'/img/resize/#vrResult">View result</a>');
		
		exit;
	}
	
	// Get the first image to process
	$current_task = explode('|', $tasks_list[0]);
	
	// Write the result to log-file
	echo "save log\n";
	$processed_img = str_replace('.jpg','_orginal999.jpg',$tasks_list[0]);
	file_put_contents(dirname(__FILE__).DS.$work_processed, trim($processed_img)."\n", FILE_APPEND);
	if (!isset($_GET['_manual'])) @chmod(dirname(__FILE__).DS.$work_processed, 0777);
	
	// Re-save the tasks list
	echo "update tasks list\n";
	unset($tasks_list[0]);
	$tasks_list = array_values($tasks_list);
	$fks = fopen(dirname(__FILE__).DS.$work_work, 'w+');
	/* if (!isset($_GET['_manual'])) @chmod(dirname(__FILE__).DS.$work_work, 0777); */
	fwrite($fks, implode($tasks_list,"\n"));
	fclose($fks);
	
	// Validate the source file
	if (!file_exists($current_task[1])) {
		throw new ResizerException("Missing source file: ".$current_task[1]);
	}
	
	// Set file names
	echo "prepare\n";
	$extension = '';
	$fbase_name = basename($current_task[1]);
	$file_name = '';
	if (strripos($fbase_name, '.jpg') !== false){
		$extension = '.jpg';
		$file_name = str_replace('.jpg', '', $fbase_name);
	} elseif (strripos($mas_file[$i], '.jpeg') !== false){
		$extension = '.jpeg';
		$file_name = str_replace('.jpeg', '', $fbase_name);
	}
	
	// Paths
	$path_parts = pathinfo($current_task[1]);
	$new_file_path  = $path_parts['dirname'].DS.$file_name.'_orginal999'.$extension;
	$temp_file = $path_parts['dirname'].DS.'work999'.$extension;
	
	// Resize params
	$params = array(
		'aspect_ratio' => false,
		'rgb' => '0x000000',
		'crop' => false,
		'quality' => 80,
		'constraint' => array('width' => $settings['width'], 'height' => $settings['height'])
	);
	
	// Resize
	echo "resizing: ".$current_task[1]." > ".$temp_file." > ".$new_file_path."\n";
	if (!img_resize($current_task[1], $temp_file, $params)) {
		$lasterr_data = error_get_last();
		unlink($current_task[1]);
		throw new ResizerException("Could not resize: ".$current_task[1].(!empty($lasterr_data['message']) ? ' ('.$lasterr_data['message'].')' : '')."!");
		
	// Success
	} else {
		// Rename files
		rename($current_task[1], $new_file_path); // original
		rename($temp_file, $current_task[1]); // resized
	}
	exit;
	
} catch(Exception $e) {
	notify($e->getMessage());
}

/*
	Functions
*/

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

// функция сортировки
function _PriceCmp ( $a, $b ){ 
    if ( $a['price'] == $b['price'] ) {
	    return 0;
    }		
    if ( $a['price'] > $b['price'] ) return -1; return 1;
}

/**
* Send e-mail
* 
* @param string Subject
* @param string Message
*/
function innerMailer($subject, $message) {
	global $conf;
	
	// Include mailer
	require_once(dirname(__FILE__).'/../../application/libraries/smtp.php');
	require_once(dirname(__FILE__).'/../../application/libraries/PHPMailer.php');
	
	// Init mailer
	$mail = new PHPMailer();
	
	// Compile email
	$mail->IsSMTP(); // telling the class to use SMTP
	$mail->Host = $conf['smtp_host']; // SMTP server
	$mail->SMTPDebug = false;                     // enables SMTP debug information (for testing)
	$mail->CharSet = "utf-8"; // или др.
	$mail->SMTPAuth = true;                  // enable SMTP authentication
	$mail->Port = 25;                    // set the SMTP port for the GMAIL server
	$mail->Username = $conf['smtp_login']; // SMTP account username
	$mail->Password = $conf['smtp_pass'];        // SMTP account password
	$mail->SetFrom($conf['smtp_from']);
	$mail->Subject = 'RESIZER, '.$subject;
	$mail->MsgHTML($message);
	$mail->AddAddress($conf['notification_email']);
	
	// Send e-mail
	$mail->Send();
}