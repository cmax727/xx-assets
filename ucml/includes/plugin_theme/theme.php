<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:20 
  * IP Address: 127.0.0.1
  */

define('_THEME_CONFIG_PREFIX','_theme_');

class module_theme extends module_base{
	
	var $links;
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
    function module_theme(){
        $display_mode = get_display_mode();
        if($display_mode!='mobile'){
            module_config::register_css('theme','theme.php');
        }
    }
    
	function init(){
        $this->version = 2.11;
		$this->links = array();
		$this->module_name = "theme";
		$this->module_position = 8882;

        if(file_exists('includes/plugin_theme/pages/theme_settings.php') && module_security::has_feature_access(array(
                'name' => 'Settings',
                'module' => 'config',
                'category' => 'Config',
                'view' => 1,
                'description' => 'view',
        ))){
            $this->links[] = array(
                "name"=>"Theme",
                "p"=>"theme_settings",
                'args'=>array(),
                'holder_module' => 'config', // which parent module this link will sit under.
                'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                'menu_include_parent' => 0,
            );
        }

	}

    public static function get_theme_styles($theme='default'){
        // return an array of the css styles to display on the page, pretty simple.

        $styles = array();
        $styles [] = array(
            'r' => 'body',
            'd' => 'Overall page settings',
            'v'=>array(
                'background-color' => '#E7E7E7',
                'background-image' => 'none',
		        'font-family' => 'Arial, Helvetica, sans-serif',
		        'font-size' => '12px',
            ),
        );
        $styles [] = array(
            'r' => 'body,#profile_info a',
            'd' => 'Main font color',
            'v'=>array(
                'color' => '#000000',
            ),
        );
        $styles [] = array(
            'r' => '#header,#page_middle,#main_menu',
            'd' => 'Content width',
            'v'=>array(
                'width' => '1294px',
            ),
        );
        $styles [] = array(
            'r' => '#header',
            'd' => 'Header height',
            'v'=>array(
                'height' => '76px',
            ),
        );
        $styles [] = array(
            'r' => '#header_logo',
            'd' => 'Logo padding',
            'v'=>array(
                'padding' => '10px 0 0 12px',
            ),
        );
        $styles [] = array(
            'r' => '.nav>ul>li>a,#quick_search_box',
            'd' => 'Menu items',
            'v'=>array(
                'color' => '#FFFFFF',
                'background-color' => '#A7A5A5',
            ),
        );
        $styles [] = array(
            'r' => '.nav>ul>li>a:hover',
            'd' => 'Menu items (when hovering)',
            'v'=>array(
                'color' => '#000000',
                'background-color' => '#FFFFFF',
            ),
        );
        $styles [] = array(
            'r' => '#page_middle>.content,.nav>ul>li>a,#page_middle .nav,#quick_search_box',
            'd' => 'Menu outline color',
            'v'=>array(
                'border-color' => '#CBCBCB',
            ),
        );
        $styles [] = array(
            'r' => 'h2',
            'd' => 'Main Page Title',
            'v'=>array(
                'color' => '#333333',
                'background-color' => '#EEEEEE',
                'border' => '1px solid #cbcbcb',
                'font-size' => '19px',
            ),
        );
        $styles [] = array(
            'r' => 'h3',
            'd' => 'Sub Page Title',
            'v'=>array(
                'color' => '#666666',
                'background-color' => '#DFDFDF',
                'font-size' => '15px',
            ),
        );

        foreach($styles as &$style){
            foreach($style['v'] as $k=>$v){
                $style['v'][$k] = array(self::get_config($style['r'].'_'.$k,$v),$v);
            }
        }
        return $styles;
    }

    public function get_config($key,$default=''){
        return module_config::c(_THEME_CONFIG_PREFIX.$key,$default);
    }
}