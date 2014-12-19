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
if($invoice_id>0 && $invoice && $invoice['invoice_id']==$invoice_id){
    $module->page_title = _l('Invoice: #%s',htmlspecialchars($invoice['name']));
	if(class_exists('module_security',false)){

        // make sure current customer can access this invoice
        if(!module_security::can_access_data('invoice',$invoice,$invoice_id)){
            echo 'Data access denied. Sorry.';
            exit;
        }

        module_security::check_page(array(
            'category' => 'Invoice',
            'page_name' => 'Invoices',
            'module' => 'invoice',
            'feature' => 'edit',
		));
	}
}else{
    $invoice_id = 0;
	if(class_exists('module_security',false)){
		module_security::check_page(array(
            'category' => 'Invoice',
            'page_name' => 'Invoices',
            'module' => 'invoice',
            'feature' => 'create',
		));
	}
	module_security::sanatise_data('invoice',$invoice);
}

$invoice_locked = ($invoice['date_sent'] && $invoice['date_sent'] != '0000-00-00') || ($invoice['date_paid'] && $invoice['date_paid'] != '0000-00-00');

$customer_data = array();
if($invoice['customer_id']){
    $customer_data = module_customer::get_customer($invoice['customer_id']);
}

?>


	
<form action="" method="post" id="invoice_form">
	<input type="hidden" name="_process" value="save_invoice" />
    <input type="hidden" name="invoice_id" value="<?php echo $invoice_id; ?>" />
    <input type="hidden" name="customer_id" value="<?php echo $invoice['customer_id']; ?>" />
    <input type="hidden" name="job_id" value="<?php echo isset($invoice['job_id']) ? (int)$invoice['job_id'] : 0; ?>" />
    <input type="hidden" name="total_tax_rate" value="<?php echo $invoice['total_tax_rate']; ?>" />
    <input type="hidden" name="total_tax_name" value="<?php echo htmlspecialchars($invoice['total_tax_name']); ?>" />
    <input type="hidden" name="hourly_rate" value="<?php echo htmlspecialchars($invoice['hourly_rate']); ?>" />


    <?php

    $fields = array(
    'fields' => array(
        'name' => 'Name',
    ));
    module_form::set_required(
        $fields
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
            '.save_invoice_item',
            '.save_invoice_payment',
            '.delete',
        ))
    );
    

    ?>

	<table cellpadding="10" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="35%">
					<h3><?php echo _l('%sInvoice Details',(!$invoice_id?_l('New '):'')); ?></h3>



					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
								<th class="width1">
									<?php echo _l('Invoice #'); ?>
								</th>
								<td>
                                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($invoice['name']); ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Status'); ?>
								</th>
								<td>
									<?php echo print_select_box(module_invoice::get_statuses(),'status',$invoice['status'],'',true,false,true); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Due Date'); ?>
								</th>
								<td>
									<input type="text" name="date_due" class="date_field" value="<?php echo print_date($invoice['date_due']);?>">
								</td>
							</tr>
                            <?php if((int)$invoice_id){ ?>
							<tr>
								<th>
									<?php echo _l('Sent Date'); ?>
								</th>
								<td>
									<input type="text" name="date_sent" id="date_sent" class="date_field" value="<?php echo print_date($invoice['date_sent']);?>">
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Paid Date'); ?>
								</th>
								<td>
									<input type="text" name="date_paid" class="date_field" value="<?php echo print_date($invoice['date_paid']);?>">
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Tax'); ?>
								</th>
								<td>
									<input type="text" name="total_tax_name" value="<?php echo htmlspecialchars($invoice['total_tax_name']);?>" style="width:30px;">
									@
                                    <input type="text" name="total_tax_rate" value="<?php echo htmlspecialchars($invoice['total_tax_rate']);?>" style="width:35px;">%

								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Currency'); ?>
								</th>
								<td>
									<?php echo print_select_box(get_multiple('currency','','currency_id'),'currency_id',$invoice['currency_id'],'',false,'code'); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Hourly Rate'); ?>
								</th>
								<td>
									<?php echo currency('<input type="text" name="hourly_rate" class="currency" value="'.$invoice['hourly_rate'].'">',true,$invoice['currency_id']);?>
								</td>
							</tr>
                            <?php } ?>
							<tr>
								<th>
									<?php echo _l('Linked Job'); ?>
								</th>
								<td>
									<?php
                                    foreach($invoice['job_ids'] as $job_id){
                                        if((int)$job_id>0){
                                            echo module_job::link_open($job_id,true);
                                        }
                                    } ?>
								</td>
							</tr>
						</tbody>
                        <?php
                         module_extra::display_extras(array(
                            'owner_table' => 'invoice',
                            'owner_key' => 'invoice_id',
                            'owner_id' => $invoice['invoice_id'],
                            'layout' => 'table_row',
                            )
                        );
                        ?>
					</table>

                    
                    <?php if((int)$invoice_id>0){ ?>
                    <h3><?php echo _l('Public Invoice Link'); ?></h3>
                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
                                <td>
                                    <a href="<?php echo module_invoice::link_public($invoice_id);?>" target="_blank"><?php echo _l('Click to view external link');?></a> <?php _h('You can send this link to your customer and they can preview the invoice, pay for the invoice as well as optionally download the invoice as a PDF'); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php } ?>
                    
                    <?php
                    if($invoice_id && $invoice_id!='new'){
                        $note_summary_owners = array();
                        // generate a list of all possible notes we can display for this invoice.
                        // display all the notes which are owned by all the sites we have access to

                        module_note::display_notes(array(
                            'title' => 'Invoice Notes',
                            'owner_table' => 'invoice',
                            'owner_id' => $invoice_id,
                            'view_link' => module_invoice::link_open($invoice_id),
                            )
                        );
                    }
                    ?>

                    <?php if ((int)$invoice_id > 0 && (!$invoice['date_sent'] || $invoice['date_sent'] == '0000-00-00')){ ?>

                    <h3 class="error_text"><?php _e('Send Invoice');?></h3>
                        <div class="tableclass_form content">

                            <p style="text-align: center;">
                                <a href="#" onclick="$('#date_sent').val('<?php echo print_date(date('Y-m-d'));?>'); $('#invoice_form')[0].submit(); return false;" class="uibutton"><?php _e('Mark invoice as sent');?></a>
                                <?php _h('This invoice has not been sent yet. When this invoice has been sent to the customer please click this button or enter a "sent date" into the form above.'); ?>
                            </p>
                        </div>

                    <?php } ?>

                    <?php if (($invoice['date_due'] && $invoice['date_due']!='0000-00-00') && (!$invoice['date_paid'] || $invoice['date_paid'] == '0000-00-00') && strtotime($invoice['date_due']) < time()){ ?>

                    <h3 class="error_text"><?php _e('Invoice Overdue');?></h3>
                        <div class="tableclass_form content">
                            <?php echo _l('This invoice has not been paid by the due date and %s is now overdue.',dollar($invoice['total_amount_due'],true,$invoice['currency_id'])); ?>
                        </div>

                    <?php } ?>

                    <?php if((int)$invoice_id > 0){
                        ?>
                        <h3><?php _e('Make a Payment');?></h3>
                        <table class="tableclass tableclass_form tableclass_full" cellpadding="0" cellspacing="0">
                            <tbody>
                            <tr>
                                <th class="width1">
                                    <?php _e('Payment Method'); ?>
                                </th>
                                <td>
                                    <?php
                                    // find out all the payment methods.
                                    $payment_methods = handle_hook('get_payment_methods',$module);
                                    $x=1;
                                    foreach($payment_methods as &$payment_method){
                                        if($payment_method->is_enabled() && $payment_method->is_method('online')){ ?>
                                            <input type="radio" name="payment_method" value="<?php echo $payment_method->module_name;?>" id="paymethod<?php echo $x;?>">
                                            <label for="paymethod<?php echo $x;?>"><?php echo $payment_method->get_payment_method_name(); ?></label> <br/>
                                            <?php
                                            $x++;
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Payment Amount'); ?>
                                </th>
                                <td>
                                    <?php echo currency('<input type="text" name="payment_amount" value="'.number_format($invoice['total_amount_due'],2,'.','').'" class="currency">',true,$invoice['currency_id']);?>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>
                                    <input type="hidden" name="butt_makepayment" id="butt_makepayment" value="">
                                    <input type="button" name="buttpay" value="<?php _e('Make Payment');?>" class="submit_button" onclick="$('#butt_makepayment').val('yes'); this.form.submit();">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <?php
                    } ?>


				</td>
                <td valign="top">

                <script type="text/javascript">
                    function setamount(a,invoice_item_id){
                        var ee = parseFloat(a);
                        if(ee>0){
                            $('#'+invoice_item_id+'invoice_itemamount').val(ee * <?php echo $invoice['hourly_rate'];?>);
                        }
                    }
                    function editinvoice_item(invoice_item_id,hours){
                        $('#invoice_item_preview_'+invoice_item_id).hide();
                        $('#invoice_item_edit_'+invoice_item_id).show();
                        if(hours>0){
                            $('#complete_'+invoice_item_id).val(hours);
                            if(typeof $('#complete_t_'+invoice_item_id)[0] != 'undefined'){
                                $('#complete_t_'+invoice_item_id)[0].checked = true;
                            }
                        }else{
                            $('#invoice_item_desc_'+invoice_item_id)[0].focus();
                        }
                    }
                </script>

                <?php
                // here we check if this invoice can be merged with any other invoices.
                if($invoice_id>0&&!$invoice_locked){
                    $merge_invoice_ids = module_invoice::check_invoice_merge($invoice_id);
                    if($merge_invoice_ids){
                        ?>
                        <h3><?php _e('Merge Customer Invoices');?></h3>
                        <p>
                            <?php _e('We found %s other invoices from this customer that can be merged.',count($merge_invoice_ids));?>
                            <?php _h('You can generate invoices from multiple jobs (eg: a Hosting Setup job and a Web Development job) then you can combine them together here and send them as a single invoice to the customer, rather than sending multiple invoices.');?>
                        </p>
                        <ul>
                            <?php foreach($merge_invoice_ids as $merge_invoice){
                                $merge_invoice = module_invoice::get_invoice($merge_invoice['invoice_id']);
                                ?>
                                <li>
                                    <input type="checkbox" name="merge_invoice[<?php echo $merge_invoice['invoice_id'];?>]" value="1" checked="checked">
                                    <?php echo module_invoice::link_open($merge_invoice['invoice_id'],true);?>
                                    <?php echo dollar($merge_invoice['total_amount'],true,$invoice['currency_id']);?>
                                    <?php if($merge_invoice['discount_amount']>0){
                                        _e('(You will have to apply the %s discount to this invoice again manually.)',dollar($merge_invoice['discount_amount'],true,$invoice['currency_id']));
                                    } ?>
                                </li>
                            <?php } ?>
                        </ul>
                        <input type="submit" name="butt_merge" value="<?php _e('Merge selected invoices into this invoice');?>" class="submit_button">
                        <p>&nbsp;</p>
                        <?php
                    }
                }
                ?>

					<h3><?php echo _l('Invoice items'); ?></h3>

                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tableclass_full">
                        <thead>
                        <tr>
                            <th class="invoice_item_column"><?php _e('Description');?></th>
                            <th width="40"><?php _e('Hours');?></th>
                            <th width="90"><?php _e('Amount');?></th>
                            <th width="80"> </th>
                        </tr>
                        </thead>
                        <?php if(!$invoice_locked){ ?>
						<tbody>
                        <tr>
                            <td>
                                <input type="text" name="invoice_invoice_item[new][description]" value="" style="width:100%;">
                            </td>
                            <td>
                                <input type="text" name="invoice_invoice_item[new][hours]" value="" size="3" style="width:25px;" onchange="setamount(this.value,'new');" onkeyup="setamount(this.value,'new');">
                            </td>
                            <td nowrap="">
                                <?php echo currency('<input type="text" name="invoice_invoice_item[new][amount]" value="" id="newinvoice_itemamount" class="currency">');?>
                            </td>
                            <td align="center">
                                <input type="submit" name="save" value="<?php _e('Add Item');?>" class="save_invoice_item">
                            </td>
                        </tr>
						</tbody>
                        <?php } ?>
                        <tbody>
                        <?php
                        $c=0;
                        foreach(module_invoice::get_invoice_items($invoice_id) as $invoice_item_id => $invoice_item_data){
                            ?>
                                <?php if(!$invoice_locked){ ?>
                                <tr id="invoice_item_edit_<?php echo $invoice_item_id;?>" style="display:none;">
                                    <td>
                                         <?php if($invoice_item_data['task_id']){
                                                echo htmlspecialchars($invoice_item_data['description']);
                                                echo ' ';
                                                echo _l('(edit in job: %s)',module_job::link_open($invoice_item_data['job_id'],true));
                                                ?>
                                                    <input type="hidden" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][task_id]" value="<?php echo htmlspecialchars($invoice_item_data['task_id']);?>">
                                                    <input type="hidden" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][description]" value="<?php echo htmlspecialchars($invoice_item_data['description']);?>">
                                                <?php
                                        }else{ ?>
                                            <input type="hidden" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][task_id]" value="<?php echo htmlspecialchars($invoice_item_data['task_id']);?>">
                                            <input type="text" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][description]" value="<?php echo htmlspecialchars($invoice_item_data['custom_description']);?>" style="width:90%;" id="invoice_item_desc_<?php echo $invoice_item_id;?>">
                                    <?php } ?>
                                            <a href="#" onclick="if(confirm('<?php _e('Delete invoice item?');?>')){$(this).parent().find('input').val(''); $('#invoice_form')[0].submit();} return false;" class="delete ui-state-default ui-corner-all ui-icon ui-icon-trash" style="display:inline-block; float:right;">[x]</a>
                                    </td>
                                    <td>
                                        <input type="text" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][hours]" value="<?php echo $invoice_item_data['hours'];?>" size="3" style="width:25px;"  onchange="setamount(this.value,'<?php echo $invoice_item_id;?>');" onkeyup="setamount(this.value,'<?php echo $invoice_item_id;?>');">
                                    </td>
                                    <td nowrap="">
                                        <?php echo currency('<input type="text" name="invoice_invoice_item['.$invoice_item_id.'][amount]" value="'.$invoice_item_data['amount'].'" id="'.$invoice_item_id.'invoice_itemamount" class="currency">');?>
                                    </td>
                                    <td nowrap="nowrap">
                                        <input type="submit" name="ts" class="save_invoice_item" value="<?php _e('Save');?>"
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr id="invoice_item_preview_<?php echo $invoice_item_id;?>" class="<?php echo $c++%2 ? 'odd':'even';?>">
                                    <td>
                                        <?php
                                        $desc = $invoice_item_data['custom_description'] ? htmlspecialchars($invoice_item_data['custom_description']) : htmlspecialchars($invoice_item_data['description']);
                                        if($invoice_locked){
                                            echo $desc;
                                        }else{ ?>
                                            <a href="#" onclick="editinvoice_item('<?php echo $invoice_item_id;?>',0); return false;"><?php echo (!trim($desc)) ? 'N/A' : $desc;?></a>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php echo $invoice_item_data['hours']>0 ? $invoice_item_data['hours'] : '-';?>
                                    </td>
                                    <td>
                                        <span class="currency">
                                        <?php echo $invoice_item_data['hours']==0 ? dollar($invoice_item_data['amount'],true,$invoice['currency_id']) : dollar($invoice_item_data['hours']*$invoice['hourly_rate'],true,$invoice['currency_id']);?>
                                        </span>
                                    </td>
                                    <td align="center">
                                        
                                    </td>
                                </tr>
                        <?php } ?>
                        </tbody>
                        <?php if(true){ //(int)$invoice_id>0 ?>
                        <tfoot style="border-top:1px solid #CCC;">
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Sub:');?>
                            </td>
                            <td>
                                <span class="currency">
                                <?php echo dollar($invoice['total_sub_amount']+$invoice['discount_amount'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <?php
                        if(!$invoice_locked && $customer_data && $customer_data['credit']>0){ ?>
                            <tr>
                                <td>
                                    &nbsp;
                                </td>
                                <td colspan="2" align="center">
                                    <input type="hidden" name="apply_credit_from_customer" id="apply_credit_from_customer" value="0">
                                    <a href="#" onclick="$('#apply_credit_from_customer').val('do'); $('#invoice_form')[0].submit(); return false;"><?php echo _l('This customer has a %s credit. Click here to apply it to this invoice.',dollar($customer_data['credit'],true,$invoice['currency_id']));?></a>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if(!$invoice_locked || $invoice['discount_amount']>0){ ?>
                        <tr>
                            <?php if($invoice_locked){ ?>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($invoice['discount_description']);?>
                                </td>
                            <?php }else{ ?>
                            <td colspan="2" align="right">
                                    <input type="text" name="discount_description" value="<?php echo htmlspecialchars($invoice['discount_description']);?>" style="width:80px;">
                            </td>
                            <?php } ?>
                            <td>
                                <?php if($invoice_locked){ ?>
                                    <span class="currency">
                                        <?php echo dollar($invoice['discount_amount'],true,$invoice['currency_id']);?>
                                    </span>
                                <?php }else{ ?>
                                    <input type="text" name="discount_amount" value="<?php echo $invoice['discount_amount'];?>" class="currency">

                                <?php } ?>
                            </td>
                            <td>
                                <?php _h('Here you can apply a before tax discount to this invoice. You can name this anything, eg: DISCOUNT, CREDIT, REFUND, etc..'); ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if($invoice['discount_amount'] != 0){ ?>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Sub:');?>
                            </td>
                            <td>
                                <span class="currency">
                                <?php echo dollar($invoice['total_sub_amount'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Tax:');?>
                            </td>
                            <td>
                                <span class="currency">
                                <?php echo dollar($invoice['total_tax'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td>
                                <?php echo $invoice['total_tax_name'] ;?> =
                                <?php echo $invoice['total_tax_rate'] . '%' ;?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Total:');?>
                            </td>
                            <td>
                                <span class="currency" style="text-decoration: underline; font-weight: bold;">
                                    <?php echo dollar($invoice['total_amount'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7">&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="right">

                            </td>
                            <td>
                                <?php _e('Paid:');?>
                            </td>
                            <td>
                                <span class="currency success_text">
                                    <?php echo dollar($invoice['total_amount_paid'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td>
                                <?php _h('This is how much the customer has paid against the invoice. When they have paid the due amount the invoice will be marked as paid.');?>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">

                            </td>
                            <td>
                                <?php _e('Due:');?>
                            </td>
                            <td>
                                <span class="currency error_text">
                                    <?php echo dollar($invoice['total_amount_due'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td >
                                &nbsp;
                            </td>
                        </tr>
                        <?php if($invoice['total_amount_credit']>0){ ?>
                        <tr>
                            <td align="center">
                                <a href="?_process=assign_credit_to_customer&invoice_id=<?php echo $invoice_id;?>"><?php _e('This customer has overpaid this invoice. Click here to assign this as credit to their account for a future invoice.');?></a>
                            </td>
                            <td>
                                <?php _e('Credit:');?>
                            </td>
                            <td>
                                <span class="currency success_text">
                                    <?php echo dollar($invoice['total_amount_credit'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td>

                            </td>
                        </tr>
                        <?php } ?>
                        </tfoot>
<?php } ?>
					</table>

                    <?php if($invoice_id){ ?>

                <script type="text/javascript">
                    function editinvoice_payment(invoice_payment_id,hours){
                        $('#invoice_payment_preview_'+invoice_payment_id).hide();
                        $('#invoice_payment_edit_'+invoice_payment_id).show();

                    }
                </script>

					<h3><?php echo _l('Invoice payment history'); ?></h3>

                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tableclass_full">
                        <thead>
                        <tr>
                            <th><?php _e('Payment Date');?></th>
                            <th><?php _e('Payment Method');?></th>
                            <th><?php _e('Amount');?></th>
                            <th><?php _e('Details');?></th>
                            <th width="80"> </th>
                        </tr>
                        </thead>
                        <?php if(module_security::is_page_editable() && module_invoice::can_i('create','Invoice Payments')){ // ?>
						<tbody>
                        <tr>
                            <td>
                                <input type="text" name="invoice_invoice_payment[new][date_paid]" value="<?php echo print_date(time());?>" class="date_field">
                            </td>
                            <td>
                                
                                <?php echo print_select_box(module_invoice::get_payment_methods(),'invoice_invoice_payment[new][method]',module_config::s('invoice_payment_default_method','Bank'),'',true,false,true); ?>
                                <!-- <input type="text" name="invoice_invoice_payment[new][method]" value="<?php echo module_config::s('invoice_payment_default_method','Bank');?>" size="20">-->
                            </td>
                            <td nowrap="">
                                <?php echo '<input type="text" name="invoice_invoice_payment[new][amount]" value="'.number_format($invoice['total_amount_due'],2,'.','').'" id="newinvoice_paymentamount" class="currency">';?>
                                <?php echo print_select_box(get_multiple('currency','','currency_id'),'invoice_invoice_payment[new][currency_id]',$invoice['currency_id'],'',false,'code'); ?>
                            </td>
                            <td>&nbsp;</td>
                            <td align="center">
                                <input type="hidden" name="add_payment" value="0" id="add_payment">
                                <input type="button" name="add_payment_btn" value="<?php _e('Add payment');?>" class="save_invoice_payment" onclick="$('#add_payment').val('go'); $('#invoice_form')[0].submit(); return false;">
                            </td>
                        </tr>
						</tbody>
                        <?php } ?>
                        <tbody>
                        <?php foreach(module_invoice::get_invoice_payments($invoice_id) as $invoice_payment_id => $invoice_payment_data){

                            if(module_invoice::can_i('edit','Invoice Payments') && module_security::is_page_editable()){
                            ?>
                                <tr id="invoice_payment_edit_<?php echo $invoice_payment_id;?>" style="display:none;">
                                    <td>
                                        <input type="text" name="invoice_invoice_payment[<?php echo $invoice_payment_id;?>][date_paid]" value="<?php echo print_date($invoice_payment_data['date_paid']);?>" class="date_field" id="invoice_payment_desc_<?php echo $invoice_payment_id;?>">
                                        <?php if(module_invoice::can_i('delete','Invoice Payments')){ ?>
                                        <a href="#" onclick="if(confirm('<?php _e('Delete invoice payment?');?>')){$('#<?php echo $invoice_payment_id;?>invoice_paymentamount').val(''); $('#invoice_form')[0].submit();} return false;"  class="delete ui-state-default ui-corner-all ui-icon ui-icon-trash" style="display:inline-block;">[x]</a>
                                        <?php } ?>
                                    </td><td>
                                        <input type="text" name="invoice_invoice_payment[<?php echo $invoice_payment_id;?>][method]" value="<?php echo htmlspecialchars($invoice_payment_data['method']);?>" size="20">
                                    </td>
                                    <td nowrap="">
                                        <?php echo '<input type="text" name="invoice_invoice_payment['.$invoice_payment_id.'][amount]" value="'.$invoice_payment_data['amount'].'" id="'.$invoice_payment_id.'invoice_paymentamount" class="currency">';?>
                                        <?php echo print_select_box(get_multiple('currency','','currency_id'),'invoice_invoice_payment['.$invoice_payment_id.'][currency_id]',$invoice_payment_data['currency_id'],'',false,'code'); ?>
                                    </td>
                                    <td>&nbsp;</td>
                                    <td nowrap="nowrap">
                                        <input type="submit" name="ts" class="save_invoice_payment" value="<?php _e('Save');?>"
                                    </td>
                                </tr>
                            <?php } ?>
                                <tr id="invoice_payment_preview_<?php echo $invoice_payment_id;?>">
                                    <td>
                                        <?php if(module_invoice::can_i('edit','Invoice Payments') && module_security::is_page_editable()){ ?>
                                        <a href="#" onclick="editinvoice_payment('<?php echo $invoice_payment_id;?>',0); return false;"><?php echo (!trim($invoice_payment_data['date_paid']) || $invoice_payment_data['date_paid'] == '0000-00-00') ? _l('Pending on %s',print_date($invoice_payment_data['date_created'])) : print_date($invoice_payment_data['date_paid']);?></a>
                                        <?php }else{ ?>
                                            <?php echo print_date($invoice_payment_data['date_paid']);?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($invoice_payment_data['method']);?>
                                    </td>
                                    <td>
                                        <span class="currency">
                                        <?php /* echo $invoice_payment_data['amount']>0 ? dollar($invoice_payment_data['amount'],true,$invoice['currency_id']) : dollar($invoice_payment_data['hours']*$invoice['hourly_rate'],true,$invoice['currency_id']); */?>
                                        <?php echo dollar($invoice_payment_data['amount'],true,$invoice_payment_data['currency_id']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if(isset($invoice_payment_data['data'])&&$invoice_payment_data['data']){
                                            $details = unserialize($invoice_payment_data['data']);
                                            if(isset($details['log'])){
                                                ?>
                                                <a href="#" onclick="$('#details_<?php echo $invoice_payment_data['invoice_payment_id'];?>').show(); $(this).hide(); return false;"><?php _e('Show...');?></a>
                                                <div id="details_<?php echo $invoice_payment_data['invoice_payment_id'];?>" style="display:none;">
                                                    <ul>
                                                        <?php foreach($details['log'] as $log){
                                                            echo '<li>'.$log.'</li>';
                                                        } ?>
                                                    </ul>
                                                </div>
                                                <?php
                                            }
                                        } ?>
                                    </td>
                                    <td align="center">
                                        <a href="<?php echo module_invoice::link_receipt($invoice_payment_data['invoice_payment_id']);?>" target="_blank"><?php _e('View Receipt');?></a>
                                    </td>
                                </tr>
                        <?php } ?>
                        </tbody>
					</table>

    <?php } ?>
                    
                </td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save invoice'); ?>" class="submit_button save_button" />
					<?php if((int)$invoice_id){ ?>
                        <?php if($invoice['date_paid'] && $invoice['date_paid']!='0000-00-00'){ ?>
                            <input type="submit" name="butt_email" id="butt_email" value="<?php echo _l('Email Receipt'); ?>" class="submit_button save_button" />
                        <?php }else{ ?>
					        <input type="submit" name="butt_email" id="butt_email" value="<?php echo _l('Email Invoice'); ?>" class="submit_button" />
                        <?php } ?>
                        <?php if(function_exists('convert_html2pdf')){ ?>
                            <input type="submit" name="butt_print" id="butt_print" value="<?php echo _l('Print PDF'); ?>" class="submit_button" />
                        <?php } ?>
					<?php } ?>
					<?php if((int)$invoice_id){ ?>
					<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
					<?php } ?>
					<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_invoice::link_open(false); ?>';" class="submit_button" />
				</td>
			</tr>
		</tbody>
	</table>


</form>
