<?php

include('communs.php');
include('config.php');

// Upload destination folder
global $DATA_ROOT, $SECURE_KEY;
$rootUri = getRootUri('upload.php');


error_reporting(-1); // report ALL the errors.

function bytesToSize1024($bytes) {
	$unit = array('B','KB','MB');
	return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 1).' '.$unit[$i];
}

if (isset($_FILES['myfile']) && isValidName($_FILES['myfile']['name'])) {
	$sFileName = $_FILES['myfile']['name'];
	$sFileType = $_FILES['myfile']['type'];
	$sFileSize = bytesToSize1024($_FILES['myfile']['size']);


	$pathName = $_GET['pathname'];
	$pathCut = explode('/', $pathName);
	array_pop($pathCut);
	$pathCut = implode('/', $pathCut);
    $folder = $DATA_ROOT. str_replace($rootUri . $DATA_ROOT, '',  $pathCut) . '/';

	if(is_dir($folder)){
		move_uploaded_file($_FILES['myfile']['tmp_name'], $folder . $sFileName);
		$data = file_get_contents($folder . $sFileName);
		aes_store($folder . $sFileName, $data, $SECURE_KEY, $JSON_ENCODING, $GZ_INFLATING);
	}

	echo <<<EOF
<div class="success">
	<p>
		Your file: {$sFileName} has been successfully received. (<a href="{$folder}">link</a>)<br/>
		Type: {$sFileType}<br/>
		Size: {$sFileSize}
	</p>
</div>
EOF;
} else {
	echo '<div class="failure">An error occurred</div>';
}
