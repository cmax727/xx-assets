<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:39 
  * IP Address: 127.0.0.1
  */
$hash = $_REQUEST['hash'];
$form_data = $_SESSION['_delete_data'][$hash];
if(!$form_data){
    echo 'Error, please go back and try again';
    exit;
}

//$data = array($message,$post_data,$post_uri,$cancel_url);
print_heading(htmlspecialchars($form_data[0]));
?>

<form action="<?php echo $form_data[2];?>" method="post">
    <input type="hidden" name="_confirm_delete" value="<?php echo htmlspecialchars($hash);?>">
<?php foreach($form_data[1] as $key=>$val){
    if(is_array($val))continue;
    ?>
    <input type="hidden" name="<?php echo htmlspecialchars($key);?>" value="<?php echo htmlspecialchars($val);?>">
<?php } ?>
    <input type="submit" value="<?php _e('Confirm Delete');?>" class="submit_button delete_button">
    <input type="button" onclick="window.location.href='<?php echo $form_data[3];?>'" class="submit_button" value="<?php _e('Cancel');?>">
</form>

