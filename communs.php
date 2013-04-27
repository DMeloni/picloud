<?php
function store($file,$datas){
    return file_put_contents($file,gzdeflate(json_encode($datas)));
}
function unstore($file){return json_decode(gzinflate(file_get_contents($file)),true);}

function aes_store($file, $datas, $key, $jsonEncode = false, $gz = false){
//     if (get_magic_quotes_gpc()) {
//         $datas =  stripslashes($datas);
//     }
	if(is_string($datas)){
    	$datas = mb_check_encoding($datas, 'UTF-8') ? $datas : utf8_encode($datas);
	}
	
    if(true === $jsonEncode){
    	$datas = json_encode($datas);
    }
    if(null !== $key){
		$datas = aes_encrypt($datas, $key);
    }
    if(true === $gz){
    	$datas = gzdeflate($datas);
    }
    return file_put_contents($file,$datas);
}

function aes_unstore($file, $key, $jsonEncode = false, $gz = false){
	$datas = file_get_contents($file);
	
	if($datas === false){
		return '';
	}
	
	if(true === $gz){
		$datas = @gzinflate($datas);
	}
	
	if(null !== $key){
		$datas = aes_decrypt($datas, $key);
	}

	if(true === $jsonEncode){
	    $datas = json_decode($datas,true);
	}
	
	if(is_string($datas)){
		$datas = mb_check_encoding($datas, 'UTF-8') ? utf8_decode($datas) : ($datas);
	}
	
    return $datas;
}

function aes_encrypt($val, $ky){
	$key="\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
	for($a=0;$a<strlen($ky);$a++){
		$key[$a%16]=chr(ord($key[$a%16]) ^ ord($ky[$a]));
	}
	//var_export('$key : ' . var_export($key, true));
	$mode = MCRYPT_MODE_ECB;
	//var_export('$mode : ' . var_export($mode, true));
	$enc = MCRYPT_RIJNDAEL_128;
	//var_export('$enc : ' . var_export($enc, true));
	$val=str_pad($val, (16*(floor(strlen($val) / 16) + (strlen($val) % 16 == 0?2:1))), chr(16- (strlen($val) % 16)));
	//var_export('$val val : ' . var_export($val, true));
	return mcrypt_encrypt($enc, $key, $val, $mode, mcrypt_create_iv(mcrypt_get_iv_size($enc, $mode), MCRYPT_DEV_URANDOM));
}

function aes_decrypt($val, $ky){
    $key="\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
    for($a=0;$a<strlen($ky);$a++){
        $key[$a%16]=chr(ord($key[$a%16]) ^ ord($ky[$a]));
    }
    $mode = MCRYPT_MODE_ECB;
    $enc = MCRYPT_RIJNDAEL_128;
    $dec = @mcrypt_decrypt($enc, $key, $val, $mode, @mcrypt_create_iv(@mcrypt_get_iv_size($enc, $mode), MCRYPT_DEV_URANDOM));
    return rtrim($dec, ((
            ord(substr(strlen($dec) - 1, 1)) >=0 and
            ord(substr($dec, strlen($dec) - 1, 1)) <= 16)? chr(ord(
                    substr($dec, strlen($dec)-1, 1))):null));
}



function rrmdir($dir) {
   if (is_dir($dir)) {
	 $objects = scandir($dir);
	 foreach ($objects as $object) {
	   if ($object != "." && $object != "..") {
		 if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
	   }
	 }
	 reset($objects);
	 rmdir($dir);
   }
 }

function purgeFolderName($folderName){
    $folderName = str_replace('/', '', $folderName);
    $folderName = str_replace('.', '', $folderName);
    return $folderName;
}

function checkisDir($dossier){
	if(is_dir($dossier)){
		return true;
	}
    return mkdir($dossier);
}
function verifieDossierProtege($dossier, $key, $jsonEncode, $gz){
    $secure = false;
    $pathFichierSecure = sprintf('%s/%s', $dossier, '@secure');
    $clefVerification = 'secure';
    if(! is_file($pathFichierSecure)){
        aes_store($pathFichierSecure, $clefVerification, $key, $jsonEncode, $gz);
        $secure = true;
    }else{
        if($clefVerification === aes_unstore($pathFichierSecure, $key, $jsonEncode, $gz)){
            $secure = true;
        }
    }
    return $secure;
}

function getExtension($fileName, $extensionsAutorisees = array()){
	$extensionExploded = explode('.', $fileName);
	if(count($extensionExploded)> 1){
		$extension=strtolower(end($extensionExploded));
		if(empty($extensionsAutorisees) || in_array($extension, $extensionsAutorisees)){
		    return $extension;
		}
	}
	return 'plain';
}

function getRealExtension($fileName){
    $extensionExploded = explode('.', $fileName);
    if(count($extensionExploded)> 1){
        return strtolower(end($extensionExploded));
    }
    return '';
}

function removeDocument($dirName, $fileName){
	if($fileName == '.' || $fileName == '..'){
		return;
	}

	$filePath = $dirName .'/'. $fileName;
	if(is_file($filePath)){
			unlink($filePath);
	}elseif(is_dir($filePath)){
		rrmdir($filePath);
	}
}

function readDocument($filePath, $keyEncryption, $jsonEncode = false, $gz = false){
	if($filePath == null || is_dir($filePath)){
		return null;
	}

	if($filePath !== null && is_file($filePath)){
		return aes_unstore($filePath, $keyEncryption, $jsonEncode, $gz);
	}

	// New document
	return '';
}

function openDossier($repertoireReel, $nomFichierSecure = '@secure', $documents = 'documents'){
	$listeFichiers = array();
	if ($handle = opendir($repertoireReel)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != $nomFichierSecure) {
				$pathFile = sprintf('%s/%s', $repertoireReel, $file);
				if(is_file($pathFile)){
					if(!isset($listeFichiers['fichiers'])){
						$listeFichiers['fichiers'] = array();
					}
					$listeFichiers['fichiers'][] = $file;
				}else{
					if(!isset($listeFichiers['dossiers'])){
						$listeFichiers['dossiers'] = array();
					}

					if(!($file == '.' || ($file == '..' && (($repertoireReel == $documents . '/.') || ($repertoireReel == $documents)))))
					{
						$listeFichiers['dossiers'][] = $file;
					}
				}
			}
		}
		closedir($handle);
	}
	return $listeFichiers;
}

function isValidName($titreDocument){
	if(!(strlen($titreDocument) >= 3 && strlen($titreDocument) <= 100))
		return false;
	if($titreDocument == '.htaccess' || $titreDocument == '.htpassword' || $titreDocument == 'robots.txt'){
	    return false;
	}

	return preg_match('#^([&a-zA-Zéèàç\(\)0-9_\[\]\-\.: ])+$#', $titreDocument);
}

function protectUrlDocument($urlDocument){
	$urlDocument = explode('/', $urlDocument);
	foreach($urlDocument as &$morceauUrl){
		$morceauUrl = urlencode($morceauUrl);
	}
	return implode('/', $urlDocument);
}

/**
 * Tries to determine Servers' SCRIPT_URL, if it doesn't exist.
 * The missing superglobal $_SERVER array element will be fixed.
 *
 * Example: client requests ... 
 * http://www.hostname.com/testpage.html?param=value&foo=bar
 *
 * ...$_SERVER['SCRIPT_URL'] is (or becomes)
 * "/testpage.html"
 *
 * @return string 
 * @author Carsten Witt <carsten.witt@gmail.com>
 * @version 20100206
 */
function get_script_url() 
{
    $script_url = null;

    if (!empty($_SERVER['SCRIPT_URL']))   
        $script_url = $_SERVER['SCRIPT_URL'];

    elseif (!empty($_SERVER['REDIRECT_URL'])) 
        $script_url = $_SERVER['REDIRECT_URL'];

    elseif (!empty($_SERVER['REQUEST_URI'])) {
        $p = parse_url($_SERVER['REQUEST_URI']);
        $script_url = $p['path'];
    }
    
    else die (__FILE__." / ".__FUNCTION__.':Couldn\'t determine $_SERVER["SCRIPT_URL"].');

    $_SERVER['SCRIPT_URL'] = $script_url;
    return $script_url;
    
}

/*
 * Get the root uri for css template
*/
function getRootUri($dirData){
	$scriptUrl = get_script_url();
	$scriptUrl = explode($dirData, $scriptUrl);
	if(count($scriptUrl) > 0)
		return $scriptUrl[0];
	return null;
}

/**
 * Eq : php_flag magic_quotes_gpc Off
 */
function unMagicQuote(){
	if (get_magic_quotes_gpc()) {
		$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
		while (list($key, $val) = each($process)) {
			foreach ($val as $k => $v) {
				unset($process[$key][$k]);
				if (is_array($v)) {
					$process[$key][stripslashes($k)] = $v;
					$process[] = &$process[$key][stripslashes($k)];
				} else {
					$process[$key][stripslashes($k)] = stripslashes($v);
				}
			}
		}
		unset($process);
	}
}
