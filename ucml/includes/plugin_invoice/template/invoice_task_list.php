
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
// do we show the qty or not?
$show_qty = true;
$show_price = true;
$show_description = true;

$oddRow = 1;
?>

<?php

$colspan = 1;
?>


<table cellpadding="4" cellspacing="0" width="100%" class="table tableclass tableclass_rows">
	<thead>
		<tr style="background-color: #000000; color:#FFFFFF;">
			<th width="20px" align="center">
				#
			</th>
            <?php if($show_description){
                $colspan++;
                ?>
			<th align="left">
				<?php _e('Description');?>
			</th>
			<?php } ?>
            <?php if($show_qty){
                $colspan++; ?>
			<th width="14%" align="center">
				<?php _e('Hours');?>
			</th>
            <?php } ?>
			<th width="10%" align="right">
				<?php _e('Total');?>
			</th>
		</tr>
	</thead>
	<tbody>
    <?php /*if ($allow_edit || $invoice['note_top']){ ?>
    <tr class="<?php echo ($oddRow++%2)?'odd':'even';?>">
        <td> </td>
        <td colspan="<?php echo $colspan;?>">
            <?php if($allow_edit){ ?>
           <textarea rows="2" name="note_top" style="width:500px;"><?php echo htmlspecialchars($invoice['note_top']);?></textarea>
            <?php }else{
                echo nl2br(htmlspecialchars($invoice['note_top']));
            } ?>
        </td>
    </tr>
    <?php }*/ ?>
	<?php
	$item_count = 1;
       foreach(module_invoice::get_invoice_items($invoice_id) as $invoice_item_id => $invoice_item_data){
            ?>

                <tr class="<?php echo $item_count%2 ? 'odd' : 'even';?>">
                    <td align="center">
                        <?php echo (isset($invoice_item_data['task_order']) && $invoice_item_data['task_order']) ? $invoice_item_data['task_order'] : $item_count++;?>
                    </td>
                    <td>
                        <?php
                            echo $invoice_item_data['custom_description'] ? htmlspecialchars($invoice_item_data['custom_description']) : htmlspecialchars($invoice_item_data['description']);
                        ?>
                    </td>
                    <td>
                        <?php echo $invoice_item_data['hours']>0 ? $invoice_item_data['hours'] : '-';?>
                    </td>
                    <td align="right">
                        <?php echo $invoice_item_data['amount']>0 ? dollar($invoice_item_data['amount'],true,$invoice['currency_id']) : dollar($invoice_item_data['hours']*$invoice_data['hourly_rate'],true,$invoice['currency_id']);?>
                    </td>
                </tr>
        <?php } ?>
	</tbody>
<tfoot>

                        <tr>
                            <td colspan="4">&nbsp;</td>
                        </tr>
                    <?php if($invoice['total_tax_rate']>0 || $invoice['discount_amount']>0){ ?>
                        <tr>
                            <td colspan="2">
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Sub Total:');?>
                            </td>
                            <td align="right">
                                <?php echo dollar($invoice['total_sub_amount']+$invoice['discount_amount'],true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if($invoice['discount_amount'] > 0){ ?>
                        <tr>
                            <td colspan="2">
                                &nbsp;
                            </td>
                            <td>
                                <?php echo htmlspecialchars($invoice['discount_description']);?>
                            </td>
                            <td align="right">
                                <?php echo dollar($invoice['discount_amount'],true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Sub Total:');?>
                            </td>
                            <td align="right">
                                <?php echo dollar($invoice['total_sub_amount'],true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if($invoice['total_tax_rate']>0 ){ ?>
                        <tr>
                            <td colspan="2">
                                &nbsp;
                            </td>
                            <td>
                                <?php echo $invoice['total_tax_name'] ;?> 
                                <?php echo $invoice['total_tax_rate'] . '%' ;?>
                            </td>
                            <td align="right">
                                <?php echo dollar($invoice['total_tax'],true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                    <?php } ?>
                        <tr>
                            <td colspan="2">
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Total:');?>
                            </td>
                            <td align="right">
                                <span style="font-weight: bold;">
                                    <?php echo dollar($invoice['total_amount'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2" align="right">

                            </td>
                            <td>
                                <?php _e('Paid:');?>
                            </td>
                            <td align="right">
                                    <?php echo dollar($invoice['total_amount_paid'],true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="right">

                            </td>
                            <td>
                                <?php _e('Due:');?>
                            </td>
                            <td align="right">
                                <span style="text-decoration: underline; font-weight: bold; color:#FF0000;">
                                    <?php echo dollar($invoice['total_amount_due'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                        </tr>
</tfoot>
</table>