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

if(class_exists('module_security',false)){
    // if they are not allowed to "edit" a page, but the "view" permission exists
    // then we automatically grab the page and regex all the crap out of it that they are not allowed to change
    // eg: form elements, submit buttons, etc..
    module_security::check_page(array(
        'category' => 'Config',
        'page_name' => 'Settings',
        'module' => 'config',
        'feature' => 'Edit',
    ));
}
$module->page_title = 'Settings';

?>


<form action="" method="post">

    <?php
module_form::prevent_exit(array(
    'valid_exits' => array(
        // selectors for the valid ways to exit this form.
        '.submit_button',
    ))
);
?>
    <input type="hidden" name="_process" value="save_config">

       
        <p><?php _e('Advanced Configuration area below. This contains every configurable value in the system. Change at own risk :)');?></p>

    <table class="tableclass tableclass_rows">
        <thead>
        <tr>
            <th>
                <?php echo _l('Configuration Key');?>
            </th>
            <th>
                <?php echo _l('Configuration Value');?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach(get_multiple('config',false,'key','exact','`key`') as $config){
            if($config['key'][0]=='_')continue;
            ?>
        <tr>
            <th>
                <?php echo $config['key']; ?>
            </th>
            <td>
                <input type="text" name="config[<?php echo htmlspecialchars($config['key']);?>]" value="<?php echo htmlspecialchars($config['val']);?>" size="60">
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th></th>
            <td>
                <input type="submit" name="save" value="Save" class="submit_button">
            </td>
        </tr>
        </tbody>
    </table>
</form>

