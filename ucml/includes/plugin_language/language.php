<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:59 
  * IP Address: 127.0.0.1
  */

$labels = array();
global $labels;

class module_language extends module_base{
	
    public $version = 2.11;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){

        $this->module_name = "language";
        global $labels;

        if(module_security::is_logged_in()){
            $user = module_user::get_user(module_security::get_loggedin_id());
            if($user && $user['user_id'] && $user['language']){
                $language = basename($user['language']);
                if(@include('custom/'.$language.'.php')){
                    //define('_UCM_LANG',$language);
                }else if(@include('labels/'.$language.'.php')){
                    //define('_UCM_LANG',$language);
                }

            }
        }

        if(_DEBUG_MODE && isset($_REQUEST['export_lang'])){
            ob_end_clean();
            echo '<pre>';
            echo '$labels = array('."\n\n";

            foreach($_SESSION['ll'] as $file_name => $data){
                echo "\n".'/** '.$file_name.' **/'."\n\n";
                foreach($data as $key => $val){
                    //echo "   '".str_replace("'","\'",htmlspecialchars($key))."' => '".str_replace("'","\'",htmlspecialchars($key))."',\n";
                    echo "   '".str_replace("'","\'",htmlspecialchars($key))."' => '',\n";
                }
            }
            echo "); \n";

            echo '</pre>';
            exit;
        }

	}
    public static function get_languages_attributes(){
        $all = array();
        foreach(glob(_UCM_FOLDER.'includes/plugin_language/custom/*.php') as $language){
            $language = str_replace('.php','',basename($language));
            if($language[0]=='_')continue;
            $all[$language] = $language;
        }
        foreach(glob(_UCM_FOLDER.'includes/plugin_language/labels/*.php') as $language){
            $language = str_replace('.php','',basename($language));
            if($language[0]=='_')continue;
            $all[$language] = $language;
        }
        return $all;
    }

}