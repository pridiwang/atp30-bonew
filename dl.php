<?php
extract($_GET);
$fileDir = $dir;
$fileName = $file;
// translate file name properly for Internet Explorer.
if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")){
// $fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
}
$fileName=addslashes($fileName);
$fileString=$fileDir.'/'.$fileName; // combine the path and file
// make sure the file exists before sending headers
if(!$fdl=@fopen($fileString,'r')){
die("Cannot Open File! $fileString ");
} else {
 header("Cache-Control: ");// leave blank to avoid IE errors
 header("Pragma: ");// leave blank to avoid IE errors
 header("Content-type: application/octet-stream");
 header("Content-Disposition: attachment; filename=\"".$filename."\"");
 header("Content-length:".(string)(filesize($fileString)));
sleep(1);
fpassthru($fdl);
}

?>