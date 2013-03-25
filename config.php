<?php


/**
 * Allowed Extensions for SyntaxHighlighter
 */
$GLOBALS['ALLOWED_EXTENSIONS'] = array('php', 'bash', 'sql', 'xml', 'atom', 'js', 'plain', 'html', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'css', 'nbk');

/**
 * Allowed Extensions for image
 */
$GLOBALS['IMAGE_EXTENSIONS'] = array('png', 'jpg', 'jpeg', 'gif', 'svg');

/**
 * Allowed Extensions for Picloud
 */
$GLOBALS['ALLOWED_PICLOUD_EXTENSIONS'] = array('', 'php', 'bash', 'sql', 'xml', 'atom', 'js', 'plain', 'html', 'css', 'pk', 'ppk', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'log', 'bat','txt', 'youtube', 'uploadhero');

/**
 * Allowed Extensions for SyntaxHighlighter
 */
$GLOBALS['TEXT_EXTENSIONS'] = array('', 'php', 'bash', 'sql', 'xml', 'atom', 'js', 'plain', 'html', 'css', 'pk', 'ppk', 'svg', 'txt', 'log', 'youtube', 'uploadhero');

/**
 * Native browser Extensions
 */
$GLOBALS['NATIVE_EXTENSIONS'] = array('pdf', 'ogg');


/*
 * Name of data directory
*/
$GLOBALS['DATA_ROOT'] ='data';

/**
 * Storage method : please DO NOT modify theirs after first upload
 * 
 */
/*
 * AES Encryption key
 * null : no encryption
 * 'my_key' : encryption with 'my_key'
*/
$GLOBALS['SECURE_KEY'] = 'pko';

/*
 * Active file json_encoding
 */
$GLOBALS['JSON_ENCODING'] = true;

/*
 * Active file gzip compression
*/
$GLOBALS['GZ_INFLATING'] = true;


