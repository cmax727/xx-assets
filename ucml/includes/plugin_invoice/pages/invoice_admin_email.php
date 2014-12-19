<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:46 
  * IP Address: 127.0.0.1
  */
if(!$invoice_safe)die('failed');
$invoice_id = (int)$_REQUEST['invoice_id'];
$invoice = module_invoice::get_invoice($invoice_id);

?>

<?php print_heading(_l('Email Invoice: %s',$invoice['name'])); ?>

<?php

module_template::init_template('invoice_email_due','Dear {CUSTOMER_NAME},<br>
<br>
Please find attached your invoice {INVOICE_NUMBER}.<br><br>
The {TOTAL_AMOUNT} is due on {DATE_DUE}.<br><br>
You can also view this invoice online by <a href="{INVOICE_URL}">clicking here</a>.<br><br>
Thank you,<br><br>
{FROM_NAME}
','Invoice Owing: {INVOICE_NUMBER}',array(
                                       'CUSTOMER_NAME' => 'Customers Name',
                                       'INVOICE_NUMBER' => 'Invoice Number',
                                       'TOTAL_AMOUNT' => 'Total amount of invoice',
                                       'DATE_DUE' => 'Due Date',
                                       'FROM_NAME' => 'Your name',
                                       'INVOICE_URL' => 'Link to invoice for customer',
                                       ));


module_template::init_template('invoice_email_paid','Dear {CUSTOMER_NAME},<br>
<br>
Thank you for your {TOTAL_AMOUNT} payment on invoice {INVOICE_NUMBER}.<br><br>
This invoice was paid in full on {DATE_PAID}.<br><br>
Please find attached the receipt for this invoice payment. <br>
You can also view this invoice online by <a href="{INVOICE_URL}">clicking here</a>.<br><br>
Thank you,<br><br>
{FROM_NAME}
','Invoice Paid: {INVOICE_NUMBER}',array(
                                       'CUSTOMER_NAME' => 'Customers Name',
                                       'INVOICE_NUMBER' => 'Invoice Number',
                                       'TOTAL_AMOUNT' => 'Total amount of invoice',
                                       'DATE_PAID' => 'Paid date',
                                       'FROM_NAME' => 'Your name',
                                       'INVOICE_URL' => 'Link to invoice for customer',
                                       ));


// template for sending emails.
// are we sending the paid one? or the dueone.
$template_name = '';
if($invoice['date_paid'] && $invoice['date_paid']!='0000-00-00'){
    $template_name = 'invoice_email_paid';
}else{
    $template_name = 'invoice_email_due';
}
$template = module_template::get_template_by_key($template_name);
$invoice['total_amount'] = dollar($invoice['total_amount']);
$invoice['date_paid'] = print_date($invoice['date_paid']);
$invoice['date_due'] = print_date($invoice['date_due']);
$invoice['invoice_number'] = $invoice['name'];
$invoice['invoice_url'] = module_invoice::link_public($invoice_id);
$customer = module_customer::get_customer($invoice['customer_id']);
$invoice['customer_name'] = $customer['customer_name'];
$invoice['from_name'] = module_security::get_loggedin_name();
$template->assign_values($invoice);

// generate the PDF ready for sending.
$pdf = module_invoice::generate_pdf($invoice_id);

// find available "to" recipients.
// customer contacts.
module_email::print_compose(
    array(
        'to'=>module_user::get_contacts(array('customer_id'=>$invoice['customer_id'])),
        'bcc'=>module_config::c('admin_email_address',''),
        'content' => $template->render('html'),
        'subject' => $template->replace_description(),
        'success_url'=>module_invoice::link_open($invoice_id),
        'success_callback'=>'module_invoice::email_sent('.$invoice_id.',"'.$template_name.'");',
        'cancel_url'=>module_invoice::link_open($invoice_id),
        'attachments' => array(
            array(
                'path'=>$pdf,
                'name'=>basename($pdf),
                'preview'=>module_invoice::link_generate($invoice_id,array('arguments'=>array('go'=>1,'print'=>1),'page'=>'invoice_admin','full'=>false)),
            ),
        ),
    )
);
?>