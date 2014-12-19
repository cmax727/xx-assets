<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:18:46 
  * IP Address: 127.0.0.1
  */



class module_cache extends module_base{

	private static $cache_store = array();
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->module_name = "cache";
		$this->module_position = 0;
	}
	public static function clear_cache($cache_key=false) {
        if(!_ENABLE_CACHE)return false;
        module_debug::log(array(
            'title' => 'Clear Cache',
            'data' => "Key: $cache_key",
         ));
		if($cache_key){
			if(isset(self::$cache_store[$cache_key])){
				unset(self::$cache_store[$cache_key]);
			}
		}else{
			// clear all
			self::$cache_store = array();
		}
	}

    public static function time_get($cache_key){
        if(!isset($_SESSION['_cache_time_save'])){
            return false;
        }
        if(!isset($_SESSION['_cache_time_save'][$cache_key])){
            return false;
        }
        if($_SESSION['_cache_time_save'][$cache_key]['expiry'] < time()){
            unset($_SESSION['_cache_time_save'][$cache_key]);
            return false;
        }
        return $_SESSION['_cache_time_save'][$cache_key]['data'];

    }
    public static function time_save($cache_key,$data,$seconds=30){
        // just save in session for amount of time.
        if(!isset($_SESSION['_cache_time_save'])){
            $_SESSION['_cache_time_save'] = array();
        }
        $_SESSION['_cache_time_save'][$cache_key] = array(
            'expiry' => time()+$seconds,
            'data'=>$data,
        );
    }

	public static function get_cached_item($cache_key,$cache_item='') {
        if(!_ENABLE_CACHE)return false;
		if(isset(self::$cache_store[$cache_key])){
			module_debug::log(array(
				'title' => 'Return cache',
				'data' => "For: $cache_key = ".substr($cache_item,0,50).'...',
			 ));
			return self::$cache_store[$cache_key];
		}
		return false;
	}
	public static function save_cached_item($cache_key,$data) {
        if(_ENABLE_CACHE){
			/*module_debug::log(array(
				'title' => 'SAVE cache',
				'data' => "key: $cache_key",
			 ));*/
		    self::$cache_store[$cache_key] = $data;
        }
	}

	public static function get_perm_cache($cache_key,$time_limit=3600) { // 1 hour
        $cache_file = _UCM_FOLDER . "temp/cache_".basename($cache_key);
        if(is_file($cache_file) && filemtime($cache_file) > time()-$time_limit){
            return unserialize(file_get_contents($cache_file));
        }
        return false;
	}
	public static function save_perm_cache($cache_key,$data) {
        $cache_file = _UCM_FOLDER . "temp/cache_".basename($cache_key);
        file_put_contents($cache_file,serialize($data)); // fixed
	}

}