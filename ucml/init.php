<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:36 
  * IP Address: 127.0.0.1
  */


require_once("includes/config.php");

define('_APPLICATION_ID',47626);

// some hosting accounts dont have default session settings that work :-/
ini_set('error_reporting',E_ALL);
ini_set('display_errors',true);
if(is_dir(_UCM_FOLDER . "/temp/") && is_writable(_UCM_FOLDER . "/temp/")){
    ini_set("session.save_handler", "files");
    session_save_path (_UCM_FOLDER . "/temp/");
}
// oldschool setups:
if(get_magic_quotes_gpc()){
    function stripslashes_deep(&$value){
        $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
        return $value;
    }
	stripslashes_deep($_GET);
    stripslashes_deep($_POST);
}
session_start();


// include all our plugin files:
require_once("includes/plugin.php");
foreach(glob("includes/plugin_*") as $plugin_dir){
    $plugin_name = str_replace("plugin_", "", basename($plugin_dir));
    if(is_dir($plugin_dir) && is_file($plugin_dir."/".$plugin_name.".php")){
        require_once($plugin_dir."/".$plugin_name.".php");
    }
}

require_once("includes/functions.php");
require_once("includes/database.php");
require_once("includes/links.php");

define('_UCM_INSTALLED',is_installed());


$plugins = array();
if(_UCM_INSTALLED){
    $db = db_connect();
}



// init all our plugins.
global $plugins;
$uninstalled_plugins = $upgradable_plugins = array();
foreach(glob("includes/plugin_*") as $plugin_dir){
    $plugin_name = str_replace("plugin_", "", basename($plugin_dir));
    if(is_dir($plugin_dir) && is_file($plugin_dir."/".$plugin_name.".php") && class_exists('module_'.$plugin_name,false)){
        eval('$plugins[$plugin_name] = new module_'.$plugin_name.'();');
        // this is a hack for php 5.2 to get the can_i() thing working
        //eval('module_'.$plugin_name.'::$module_name_hack = module_'.$plugin_name.'::get_class();');
        if(_UCM_INSTALLED){
            $plugins[$plugin_name]->init();
            if(!$plugins[$plugin_name]->get_installed_plugin_version()){
                $uninstalled_plugins[$plugin_name] = &$plugins[$plugin_name];
                //unset($plugins[$plugin_name]);
            }
        }
        /*global ${'module_'.$plugin_name};
        ${'module_'.$plugin_name} = &$plugins[$plugin_name];*/
    }
}

/*foreach($plugins as $plugin_name => &$p){
    echo $plugin_name.'<br>';
    eval('echo module_'.$plugin_name.'::$module_name_hack;');
    echo '<br>';
}*/

define('_UCM_HOST',module_config::c('system_base_href','http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '' && $_SERVER['HTTPS']!='off')?'s':'').'://'.$_SERVER['HTTP_HOST']));
$default_base_dir = str_replace('\\\\','\\',str_replace('//','/',dirname($_SERVER['REQUEST_URI'].'?foo=bar').'/'));
$default_base_dir = preg_replace('#includes/plugin_[^/]*/css/#','',$default_base_dir);
$default_base_dir = preg_replace('#includes/plugin_[^/]*/#','',$default_base_dir);
define('_BASE_HREF',module_config::c('system_base_dir',$default_base_dir));


if(!function_exists('sort_plugins')){
    function sort_plugins($a,$b){
        return $a->module_position > $b->module_position;
    }
}
uasort($plugins,'sort_plugins');


if(isset($_REQUEST['auto_login'])){
	// try to process an auto login.
	module_security::auto_login();
}
if(isset($_REQUEST['_process_login'])){
	module_security::process_login();
}
if(isset($_REQUEST['_logout'])){
	module_security::logout();
	header("Location: index.php");
	exit;
}
if(!_UCM_INSTALLED && module_security::getcred()){
    module_security::logout();
}

// a quick hack to put the re-write mode into $_REQUEST['m'] mode
if(_REWRITE_LINKS){
    $url = preg_replace('#^'.preg_quote(_BASE_HREF,'#').'#i','',$_SERVER['REQUEST_URI']);
    $url = preg_replace('#\?.*$#','',$url);
    if($url){
        $parts = explode("/",$url);
        $module_number = 0;
        foreach($parts as $part){
            if($part=='index.php')continue;
            $m = explode(".",$part);
            if(count($m) == 2){
                $_REQUEST['m'][$module_number] = $m[0];
                $_REQUEST['p'][$module_number] = $m[1];
                $module_number++;
            }
        }
    }
}


// wrap the module loading request into an array
// this way we can load multiple modules and pages around eachother.
// awesome.
$inner_content = array();
$page_title_delim = ' &raquo; ';
$page_title = '';
$load_modules = (isset($_REQUEST['m'])) ? $_REQUEST['m'] : false;
$load_pages = (isset($_REQUEST['p'])) ? $_REQUEST['p'] : false;

if((!isset($noredirect) || !$noredirect) && !$load_modules && !$load_pages && defined('_CUSTOM_UCM_HOMEPAGE')){
    redirect_browser(_CUSTOM_UCM_HOMEPAGE);
}

if(!is_array($load_modules))$load_modules = array($load_modules);
if(!is_array($load_pages))$load_pages = array($load_pages);

if(!isset($_REQUEST['m']))$_REQUEST['m'] = array();
if(!isset($_REQUEST['p']))$_REQUEST['p'] = array();
if(!is_array($_REQUEST['m']))$_REQUEST['m'] = array($_REQUEST['m']);
if(!is_array($_REQUEST['p']))$_REQUEST['p'] = array($_REQUEST['p']);

$load_modules = array_reverse($load_modules,true);