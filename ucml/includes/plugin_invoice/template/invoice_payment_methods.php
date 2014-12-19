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

if($invoice_data['total_amount_due']>0){


// find all payment methods that are available for invoice payment.
$payment_methods = handle_hook('get_payment_methods');
$methods_count = count($payment_methods);
?>

<?php if(!isset($mode) || $mode=='html'){ ?>
    <h3>Payment Methods:</h3>
<?php }else{ ?>
    <strong>Payment Methods:</strong><br/>
<?php } ?>
<table width="100%" class="tableclass">
    <tbody>
    <tr>
        <td valign="top" width="50%">

            <strong>Option #1: Pay Online</strong>
            <br/>
            We support the following secure payment methods:
            <br/>

            <?php if(!isset($mode) || $mode=='html'){ ?>

                    <form action="" method="post">
                    <input type="hidden" name="payment" value="go">
                    <input type="hidden" name="invoice_id" value="<?php echo $invoice_id;?>">
                    <table class="" cellpadding="0" cellspacing="0">
                        <tbody>
                        <tr>
                            <th class="width1">
                                <?php _e('Payment Method'); ?>
                            </th>
                            <td>
                                <?php
                                // find out all the payment methods.
                                $x=1;
                                foreach($payment_methods as $payment_method_id => $payment_method){
                                    if($payment_methods[$payment_method_id]->is_enabled() && $payment_methods[$payment_method_id]->is_method('online')){ ?>
                                        <input type="radio" name="payment_method" value="<?php echo $payment_methods[$payment_method_id]->module_name;?>" id="paymethod<?php echo $x;?>">
                                        <label for="paymethod<?php echo $x;?>"><?php echo $payment_methods[$payment_method_id]->get_payment_method_name(); ?></label> <br/>
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
                                <input type="submit" name="pay" value="<?php _e('Make Payment');?>" class="submit_button save_button">
                            </td>
                        </tr>
                        </tbody>
                    </table>
            </form>

                

            <?php }else{ ?>

                <ul>
                <?php
                foreach($payment_methods as $payment_method_id => $payment_method){
                    if($payment_methods[$payment_method_id]->is_enabled() && $payment_methods[$payment_method_id]->is_method('online')){ ?>
                            <li>
                            <strong><?php echo $payment_methods[$payment_method_id]->get_payment_method_name(); ?></strong><br/>
                            <?php echo $payment_methods[$payment_method_id]->get_invoice_payment_description($invoice_id); ?>
                            </li>
                        <?php
                    }
                }
                ?>
                </ul>
                <br/>
                Please <a href="<?php echo module_invoice::link_public($invoice_id);?>">click here</a> to pay online.
            <?php } ?>

        </td>
        <td valign="top" width="50%">

            <strong>Option #2: Pay Offline</strong>
            <br/>
            We support the following offline payment methods:
            <br/>
            <ul>
            <?php
            foreach($payment_methods as $payment_method_id => $payment_method){
                if($payment_methods[$payment_method_id]->is_enabled() && $payment_methods[$payment_method_id]->is_method('offline')){ ?>
                        <li>
                        <strong><?php echo $payment_methods[$payment_method_id]->get_payment_method_name(); ?></strong><br/>
                        <?php echo $payment_methods[$payment_method_id]->get_invoice_payment_description($invoice_id); ?>
                        </li>
                    <?php
                }
            }
            ?>
            </ul>

        </td>
    </tr>
    </tbody>
</table>

    <?php }else{ ?>

        <p align="center">
        Invoice has been paid in full. <br/><br/>
    Thank you for your business.
</p>
    
<?php } ?>