<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:15 
  * IP Address: 127.0.0.1
  */

// todo: _DEMO_MODE - dont allow access to setup wizard.

if(_UCM_INSTALLED && !module_security::is_logged_in()){
    ob_end_clean();
    echo 'Sorry the system is already installed. You need to be logged in to run the setup again.';
    exit;
}

print_heading('Step #5: Complete');?>

      <p>Congratulations. The Ultimate Client Manager is now installed! You can find more settings under the "settings" menu above.</p>

    <p>Have fun exploring the system and configuring it to suit your needs. Be sure to follow me on twitter (@dtbaker) or check http://codecanyon.net/user/dtbaker for any new versions, updates and bug fixes. </p>

    <p>If you have any support requests or find a bug please send it to this website: http://support.dtbaker.com.au - you can submit a support ticket or search the community forum. (please be aware that support is an optional extra and I provide it in my free time, so there may be a delay in replying to your questions.) </p>

    <p>I've spent a long time building this system to suit my individual needs, so I hope it will fit into your business needs as well. If you like this little package please give it a rating and a positive comment on CodeCanyon (this will improve sales and help me pay my bills! haha). Enjoy! <br><br>
        Cheers,<br>
        dtbaker

    </p>

    
    <p align="center">
        <a href="<?php echo _BASE_HREF;?>?m[0]=config&p[0]=config_admin&m[1]=config&p[1]=config_basic_settings" class="uibutton">Show me to more settings!</a>
    </p>
