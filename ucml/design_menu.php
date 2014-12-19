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
if(!function_exists('sort_menu_links')){
	function sort_menu_links($a,$b){
		if(isset($a['order']) && isset($b['order'])){
			return $a['order'] > $b['order'];
		}
		return 1;
	}
}
?>
<div <?php echo $display_mode=='mobile' ? '  data-role="navbar"' : ' class="nav"';?>>
	<ul>
		<?php
		if(!isset($links)){
			$links = array();
		}
		if(!isset($menu_holder)){
			$menu_holder = (isset($load_page)) ? $load_page : 'main';
		}


		switch($menu_holder){
			case false:
			case 'main':
				// login is always there.
				if(!_UCM_INSTALLED){
					//$links [] = array("name"=>"Setup","url"=>"index.php?p=setup","icon"=>"images/icon_home.gif");
				}else if(!getcred()){
					$links [] = array("name"=>"Login","url"=>_BASE_HREF."index.php?p=home","icon"=>"images/icon_home.gif");
				}else{
                    if($display_mode != 'mobile' && !defined('_CUSTOM_UCM_HOMEPAGE')){
                        $home_link = array(
                            "name"=>"Dashboard",
                            "url"=>_BASE_HREF.'index.php?p=home',
                            "icon"=>"images/icon_home.gif",
                            'order' => 0,
                        );
                        if(!isset($_REQUEST['m']) || !$_REQUEST['m'] || $_REQUEST['m'][0] == 'index'){
                            $home_link['current'] = true;
                        }
                        $links[] = $home_link;
                    }
                }
				break;
		}

		if(!isset($menu_include_parent)){
			$menu_include_parent = false;
		}
		if(!isset($menu_allow_nesting)){
			$menu_allow_nesting = false;
		}
		$menu_type = false;
		// pull in menus from modules
		$current_module_name = (isset($module)) ? $module->module_name : false;
		foreach($plugins as $plugin_name => &$plugin){
            $links = array_merge($links,$plugin->get_menu($current_module_name,$menu_holder));
            
		}
		uasort($links,'sort_menu_links');

                           // echo '<pre>';print_r($load_modules);echo '</pre>';
		if(!isset($current_link))$current_link = false;
		// check for a current one.
		// separete loop so we only highlight a single link.
		// bit dodgy but works.
		if($current_link === false){
			foreach($links as $link_id => $link){
                if(isset($link['current'])){
                    if($link['current']){
                        $current_link = $link_id;
                        break;
                    }else{
                        continue;
                    }
                }
				// we highlight the current module
				if(isset($load_modules) && isset($link['p']) && isset($link['m']) && $keys = array_keys($load_modules,$link['m'])){
                    foreach($keys as $key){
                        if($load_pages[$key] == $link['p']){
					        $current_link = $link_id;
                            break;
                        }
                    }
					//break;// if there are menu "current" issues then the problem will be here. remove break;
				}
			}
		}
        // highlight home menu or best guess module menu item.
            
		if($current_link === false){
			foreach($links as $link_id => $link){
                if(isset($link['current'])){
                    if($link['current']){
                        $current_link = $link_id;
                    }else{
                        continue;
                    }
                }
				// we highlight the current module
				if(isset($load_modules) && isset($link['m']) && in_array($link['m'],$load_modules) && !isset($link['force_current_check'])){
					$current_link = $link_id;
					break;// if there are menu "current" issues then the problem will be here. remove break;
				}else if($link['name'] == 'Dashboard' && !$_REQUEST['m'] && isset($_REQUEST['p']) && in_array('home',$_REQUEST['p'])){
					$current_link = $link_id;
				}
			}
		}
		$current_selected_link = false;
		foreach($links as $link_id => &$link){
			$current = ($link_id === $current_link);
			if(isset($link['url']) && $link['url']){
				$link_url = $link['url'];
			}else if(isset($link['p']) && $link['m']){
				if(isset($link['menu_include_parent'])){
					$menu_include_parent = $link['menu_include_parent'];
				}
                $link_nest = $menu_allow_nesting;
                if(isset($link['allow_nesting'])){
                    $link_nest = $link['allow_nesting'];
                }
				$link_url = generate_link($link['p'],(isset($link['args'])?$link['args']:array()),$link['m'],$menu_include_parent,$link_nest);
			}else{
				$link_url = '#';
			}
			if($current){
				$current_selected_link = $link;
			}
			?>
			<li <?php if($current){ echo 'class="link_current"'; } ?>>
				<a href="<?php echo $link_url; ?>" <?php if($current){ echo 'class="ui-btn-active"'; } ?>> <?php /* <img src="<?php echo _BASE_HREF.$link['icon']; ?>" border="0" alt="Icon" /> */ ?>
					<?php echo _l($link['name']); ?>
				</a>
			</li>
		<?php }
		unset($menu_holder);
		unset($menu_type);
		unset($current_link);
		unset($menu_allow_nesting);
		?>
        <?php if(isset($show_quick_search) && $show_quick_search){ ?>
        <?php if(module_security::getcred() && module_security::can_user(module_security::get_loggedin_id(),'Show Quick Search') && $display_mode != 'mobile'){

                if(module_config::c('global_search_focus',1)==1){
                    module_form::set_default_field('ajax_search_text');
                }
                ?>
            <li>
                <div id="quick_search_box">
                    <input type="text" name="quick_search" id="ajax_search_text" size="10" value="">
                    <div id="ajax_search_result"></div>
                </div>
            </li>
         <?php } ?>
        <?php } ?>
	</ul>
    <div class="menu_clear"></div>
</div>