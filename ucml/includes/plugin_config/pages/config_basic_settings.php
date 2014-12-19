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

print_heading('Basic System Settings');


$settings = array(
         /*array(
            'key'=>'_installation_code',
            'default'=>'',
             'type'=>'text',
             'description'=>'Your license code',
             'help' => 'You can find your unique license code in the "license" file from CodeCanyon.net after you purchase this item. It looks like this: 30d91230-a8df-4545-1237-467abcd5b920 ',
         ),*/
         array(
            'key'=>'system_base_dir',
            'default'=>'/',
             'type'=>'text',
             'description'=>'Base URL for your system (eg: / or /admin/)',
         ),
         array(
            'key'=>'system_base_href',
            'default'=>'',
             'type'=>'text',
             'description'=>'URL for your system (eg: http://foo.com)',
         ),
         array(
            'key'=>'admin_system_name',
            'default'=>'Ultimate Client Manager',
             'type'=>'text',
             'description'=>'Name your system',
         ),
         array(
            'key'=>'header_title',
            'default'=>'UCM',
             'type'=>'text',
             'description'=>'Text to appear in header',
         ),
         array(
            'key'=>'date_format',
            'default'=>'d/m/Y',
             'type'=>'text',
             'description'=>'Date format for system',
         ),
         array(
            'key'=>'timezone',
            'default'=>'America/New_York',
             'type'=>'text',
             'description'=>'Your timezone (<a href="http://php.net/manual/en/timezones.php">see all</a>) ',
         ),
         array(
            'key'=>'alert_days_in_future',
            'default'=>'5',
             'type'=>'text',
             'description'=>'Days to alert due tasks in future (for dashboard)',
         ),
         array(
            'key'=>'hide_extra',
            'default'=>'1',
             'type'=>'checkbox',
             'description'=>'Hide "extra" form fields by default',
         ),
         array(
            'key'=>'hourly_rate',
            'default'=>'60',
             'type'=>'text',
             'description'=>'Default hourly rate',
         ),
         array(
            'key'=>'job_type_default',
            'default'=>'Website Design',
             'type'=>'text',
             'description'=>'Default type of job',
         ),
         array(
            'key'=>'tax_name',
            'default'=>'TAX',
             'type'=>'text',
             'description'=>'What is your TAX called? (eg: GST)',
         ),
         array(
            'key'=>'tax_percent',
            'default'=>'10',
             'type'=>'text',
             'description'=>'Percentage tax to calculate by default? (eg: 10)',
         ),
         array(
            'key'=>'todo_list_limit',
            'default'=>'6',
             'type'=>'text',
             'description'=>'Number of TODO items to show',
         ),
         array(
            'key'=>'admin_email_address',
            'default'=>'info@example.com',
             'type'=>'text',
             'description'=>'The admins email address',
         ),
         /*array(
            'key'=>'envato_show_summary_what',
            'default'=>1,
             'type'=>'select',
             'options'=>array(
                 1=>'Show todays sales',
                 2=>'Show total balance (can be slower)',
             ),
             'description'=>'What to display in menu.',
         ),*/
);

if(class_exists('module_security',false)){
    $roles = array();
    foreach(module_security::get_roles() as $r){
        $roles[$r['security_role_id']] = $r['name'];
    }

    $settings[] = array(
        'key'=>'contact_default_role',
        'default'=>'',
         'type'=>'select',
         'options'=>$roles,
         'description'=>'When creating a new contact, assign this role<br>(don\'t give them too many permissions!)',
     );
}

module_config::print_settings_form(
     $settings
);

