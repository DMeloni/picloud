<?php

include('communs.php');
include('config.php');

global $SECURE_KEY, $DATA_ROOT;
global $JSON_ENCODING;
global $GZ_INFLATING;

$detExt = explode('.', $_GET['kNimage']);
if(count($detExt)> 1){
    $ext = array_pop($detExt);
}

$fileName = implode('.', $detExt);

// Determine Content Type
switch ($ext) {
    case "pdf": $ctype="application/pdf"; break;
    case "exe": $ctype="application/octet-stream"; break;
    case "zip": $ctype="application/zip"; break;
    case "doc": $ctype="application/msword"; break;
    case "xls": $ctype="application/vnd.ms-excel"; break;
    case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
    case "gif": $ctype="image/gif"; break;
    case "png": $ctype="image/png"; break;
    case "svg": $ctype="image/svg+xml"; break;
    case "jpeg":
    case "jpg": $ctype="image/jpg"; break;
    default: $ctype="application/force-download";
}
$unstored = readDocument($DATA_ROOT . '/' . $_GET['kNimage'], $SECURE_KEY, $JSON_ENCODING, $GZ_INFLATING);

header('Content-Type:' . $ctype);
echo $unstored;
