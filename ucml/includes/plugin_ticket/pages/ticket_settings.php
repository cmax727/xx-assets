<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:23 
  * IP Address: 127.0.0.1
  */
$show_other_settings = true;
@include('ticket_settings_accounts.php');
?>
<?php if($show_other_settings){
    print_heading('Ticket Settings');
    $c = array();
    $customers = module_customer::get_customers();
    foreach($customers as $customer){
        $c[$customer['customer_id']] = $customer['customer_name'];
    }

    module_config::print_settings_form(
        array(
             array(
                'key'=>'ticket_show_summary',
                'default'=>1,
                 'type'=>'checkbox',
                 'description'=>'Show unread ticket count in the menu item.',
             ),
             array(
                'key'=>'ticket_admin_email_alert',
                'default'=>'',
                 'type'=>'text',
                 'description'=>'Send notifications of new tickets to this address.',
             ),
             array(
                'key'=>'ticket_admin_alert_subject',
                'default'=>'Support Ticket Updated: #%s',
                 'type'=>'text',
                 'description'=>'The subject to have in ticket notification emails.',
             ),
             array(
                'key'=>'ticket_public_header',
                'default'=>'Submit a support ticket',
                 'type'=>'text',
                 'description'=>'Message to display at the top of the embed ticket form.',
             ),
             array(
                'key'=>'ticket_public_welcome',
                'default'=>'',
                 'type'=>'textarea',
                 'description'=>'Text to display at the top of the embed ticket form.',
             ),
             array(
                'key'=>'ticket_default_customer_id',
                'default'=>1,
                 'type'=>'select',
                 'options' => $c,
                 'description'=>'Which customer to assign tickets to from the public Ticket Embed Form',
                 'help' => 'Only use this default customer if the customer cannot be found based on the ticket users email address.'
             ),
        )
    );
    print_heading('Ticket Embed Form');
    ?>
    <p>
        <?php _e('Place this in an iframe on your website, or as a link on your website, and people can submit support tickets.'); ?>
    </p>
    <p><a href="<?php echo module_ticket::link_public_new();?>" target="_blank"><?php echo module_ticket::link_public_new();?></a></p>
        <p>
            <em><?php _e('Note: logout of this system before submitting a public support ticket for it to work correctly.');?></em>
        </p>
    


<?php
}
?>