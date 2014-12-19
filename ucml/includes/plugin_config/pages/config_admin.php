
<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:18:53 
  * IP Address: 127.0.0.1
  */

$module->page_title = 'Admin';

if(module_config::can_i('view','Settings')){
    $links = array(
        array(
            "name"=>"System Settings",
            'm' => 'config',
            'p' => 'config_basic_settings',
            'force_current_check' => true,
            //'default_page' => 'config_basic_settings',
            'order' => 1, // at start.
            'menu_include_parent' => 0,
            'allow_nesting' => 1,
        ),
        array(
            "name"=>"Payments",
            'm' => 'config',
            'p' => 'config_payment',
            'force_current_check' => true,
            'order' => 9995,
            'menu_include_parent' => 0,
            'allow_nesting' => 1,
        ),
        array(
            "name"=>"Advanced",
            'm' => 'config',
            'p' => 'config_settings',
            'force_current_check' => true,
            //'default_page' => 'config_settings',
            'order' => 9999, // at end.
            'menu_include_parent' => 0,
            'allow_nesting' => 1,
        ),
    );
}

if(module_config::can_i('view','Upgrade System')){
    $links[] = array(
        "name"=>"Upgrade",
            'm' => 'config',
            'p' => 'config_upgrade',
            'force_current_check' => true,
            'order' => 9998, // at end.
            'menu_include_parent' => 0,
            'allow_nesting' => 1,
        );
}

?>