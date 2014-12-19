<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:09 
  * IP Address: 127.0.0.1
  */ print_heading('PayPal Settings');?>


<?php module_config::print_settings_form(
    array(
         array(
            'key'=>'payment_method_paypal_enabled',
            'default'=>1,
             'type'=>'checkbox',
             'description'=>'Enable PayPal Checkout',
         ),
         array(
            'key'=>'payment_method_paypal_email',
            'default'=>_ERROR_EMAIL,
             'type'=>'text',
             'description'=>'Your PayPal registered email address',
         ),
         array(
            'key'=>'payment_method_paypal_sandbox',
            'default'=>0,
             'type'=>'checkbox',
             'description'=>'Use PayPal Sandbox Mode (for testing payments)',
         ),
    )
); ?>

<?php print_heading('PayPal setup instructions:');?>

<p>Please signup for a PayPal business account here: http://www.paypal.com - please enter your paypal email address above.</p>
