<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:12 
  * IP Address: 127.0.0.1
  */
$settings = array(
         array(
            'key'=>'email_smtp',
            'default'=>'0',
             'type'=>'checkbox',
             'description'=>'Use SMTP when sending emails from this system',
         ),
         array(
            'key'=>'email_smtp_hostname',
            'default'=>'',
             'type'=>'text',
             'description'=>'SMTP hostname (eg: mail.yoursite.com)',
         ),
         array(
            'key'=>'email_smtp_authentication',
            'default'=>'0',
             'type'=>'checkbox',
             'description'=>'Use SMTP authentication',
         ),
         array(
            'key'=>'email_smtp_username',
            'default'=>'',
             'type'=>'text',
             'description'=>'SMTP Username',
         ),
         array(
            'key'=>'email_smtp_password',
            'default'=>'',
             'type'=>'text',
             'description'=>'SMTP Password',
         ),
);

$demo_email = module_config::c('admin_email_address');
if(isset($_REQUEST['email'])){
    $demo_email = $_REQUEST['email'];
}
if(isset($_REQUEST['_email'])){
    // send a test email and report any errors.
    $email = module_email::new_email();
    $email->set_subject('Test Email from '.module_config::c('admin_system_name'));
    $email->set_to_manual($demo_email);
    $email->set_html('This is a test email from the "'.module_config::c('admin_system_name').'" setup wizard.');
    if(!$email->send()){
        ?>
        <div class="warning">
            Failed to send test email. Error message: <?php echo $email->error_text;?>
        </div>
        <?php
    }else{
        ?>
        <strong>Test email sent successfully.</strong>
        <?php
    }
}


?>

<table class="tableclass tableclass_full">
        <tr>
            <td valign="top">
                <h2>Send a test email:</h2>
                <form action="" method="post">
                    <input type="hidden" name="_email" value="true">
                    <p>Please enter your email address:</p>
                    <p><input type="text" name="email" value="<?php echo htmlspecialchars($demo_email);?>" size="40"></p>
                    <p>If sending an email does not work, please change your SMTP details on the right and try again.</p>
                    <input type="submit" name="send" value="Click here to send a test email" class="uibutton save_button">
                    <p><em>(the subject of this email will be "Test Email from <?php echo module_config::c('admin_system_name');?>")</em></p>
                </form>
            </td>
            <td width="50%" valign="top">
                <?php
                 print_heading('Email Settings (SMTP)');

                module_config::print_settings_form(
                     $settings
                );
                ?>
            </td>
        </tr>
    </table>


