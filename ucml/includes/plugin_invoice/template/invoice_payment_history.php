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
$payment_historyies = module_invoice::get_invoice_payments($invoice_id);
foreach($payment_historyies as $invoice_payment_id => $invoice_payment_data){
    if(module_config::c('invoice_hide_pending_payments',1)){
        if(!trim($invoice_payment_data['date_paid']) || $invoice_payment_data['date_paid'] == '0000-00-00'){
            unset($payment_historyies[$invoice_payment_id]);
        }
    }
}
if(count($payment_historyies)){
?>

<?php if(!isset($mode) || $mode=='html'){ ?>
    <h3>Payment History:</h3>
<?php }else{ ?>
    <strong>Payment History:</strong><br/>
<?php } ?>
        
<table cellpadding="4" cellspacing="0" width="100%" class="table tableclass tableclass_rows">
	<thead>
		<tr style="background-color: #000000; color:#FFFFFF;">
            <th><?php _e('Payment Date');?></th>
            <th><?php _e('Payment Method');?></th>
            <th><?php _e('Amount');?></th>
            <th> </th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($payment_historyies as $invoice_payment_id => $invoice_payment_data){
        ?>
            <tr>
                <td>
                    <?php echo (!trim($invoice_payment_data['date_paid']) || $invoice_payment_data['date_paid'] == '0000-00-00') ? _l('Pending on %s',print_date($invoice_payment_data['date_created'])) : print_date($invoice_payment_data['date_paid']);?>
                </td>
                <td>
                    <?php echo htmlspecialchars($invoice_payment_data['method']);?>
                </td>
                <td>
                    <?php echo dollar($invoice_payment_data['amount'],true,$invoice_payment_data['currency_id']); ?>
                </td>
                <td align="center">
                    <a href="<?php echo module_invoice::link_receipt($invoice_payment_data['invoice_payment_id']);?>" target="_blank"><?php _e('View Receipt');?></a>
                </td>
            </tr>
    <?php } ?>
    </tbody>
</table>
<?php } ?>