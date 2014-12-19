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

// dynamically generate a css stylesheet based on the users theme preferences.

chdir('../../../');
require_once('init.php');

header('Content-type: text/css');

$styles = module_theme::get_theme_styles();
?>

/** css stylesheet */

<?php foreach($styles as $style){

    echo $style['r'].'{';
    foreach($style['v'] as $s=>$v){
        echo $s.':'.$v[0].'; ';
    }
    echo "}\n";

} ?>