<?php
//Ultimate Client Manager - config file

define('_DB_SERVER','localhost');
define('_DB_NAME','ucml_');
define('_DB_USER','root');
define('_DB_PASS','root');
define('_DB_PREFIX','ucml_');

define('_UCM_VERSION',2);
define('_UCM_FOLDER',preg_replace('#includes$#','',dirname(__FILE__)));

define('_EXTERNAL_TUNNEL','ext.php');
define('_EXTERNAL_TUNNEL_REWRITE','external/');
define('_ENABLE_CACHE',true);
define('_DEBUG_MODE',false);
define('_DEMO_MODE',false);
if(!defined('_REWRITE_LINKS'))define('_REWRITE_LINKS',true);

ini_set('display_errors',true);
ini_set('error_reporting',E_ALL);

