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

$noredirect = true;
header( 'Content-Type: text/html; charset=UTF-8' );
require_once('init.php');

if(getcred()){
    $search_text = isset($_REQUEST['ajax_search_text']) ? trim(urldecode($_REQUEST['ajax_search_text'])) : false;
	if($search_text){
		$search_results = array();
		foreach($plugins as $plugin_name => &$plugin){
			$search_results = array_merge( $search_results , $plugin->ajax_search($search_text,$db) );
		}
        if(count($search_results)){
            echo '<ul>';
            foreach($search_results as $r){
                echo '<li>' . $r . '</li>';
            }
            echo '</ul>';
        }
	}else{
		echo '';
	}
	exit;
}

