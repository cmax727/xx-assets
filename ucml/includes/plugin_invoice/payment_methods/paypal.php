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

define('_PAYPAL_SANDBOX_MODE',false);


function start_payment($invoice_id,$payment_amount,$invoice_payment_id){
    if($invoice_id && $payment_amount && $invoice_payment_id){
        // we are starting a payment via paypal!
        // setup a pending payment and redirect to paypal.
        $invoice_data = module_invoice::get_invoice($invoice_id);
        $description = _l('Payment for invoice %s',$invoice_data['name']);
        dtbaker_paypal::paypal_redirect($description,$payment_amount,module_security::get_loggedin_id(),$invoice_payment_id,$invoice_id);
        return true;
    }
    return false;
}


class dtbaker_paypal{


    public static function paypal_redirect($description,$amount,$user_id,$payment_id,$invoice_id){
        
        $url = 'https://'. (_PAYPAL_SANDBOX_MODE ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?';

        $fields = array(
            'cmd' => '_xclick',
            'business' => module_config::c('payment_method_paypal_email',_ERROR_EMAIL),
            'currency_code' => module_config::c('currency_name','USD'),
            'item_name' => $description,
            'amount' => $amount,
            'return' => module_invoice::link_open($invoice_id),
            'notify_url' => full_link(_EXTERNAL_TUNNEL.'?m=invoice&h=payment&method=paypal'),
            'custom' => self::paypal_custom($user_id,$payment_id,$invoice_id),
        );

        foreach($fields as $key=>$val){
            $url .= $key.'='.urlencode($val).'&';
        }

        redirect_browser($url);

    }

    public static function fsockPost($url,$data) {
        $web=parse_url($url);
        $postdata = '';
        $info = array();
        //build post string
        foreach($data as $i=>$v) {
            $postdata.= $i . "=" . urlencode($v) . "&";
        }
        $postdata.="cmd=_notify-validate";
        $ssl = '';
        if($web['scheme'] == "https") { $web['port']="443";  $ssl="ssl://"; } else { $web['port']="80"; }

        //Create paypal connection
        $fp=@fsockopen($ssl . $web['host'],$web['port'],$errnum,$errstr,30);

        //Error checking
        if(!$fp) {
            echo "$errnum: $errstr";
        }else {
            fputs($fp, "POST $web[path] HTTP/1.1\r\n");
            fputs($fp, "Host: $web[host]\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: ".strlen($postdata)."\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $postdata . "\r\n\r\n");
            //loop through the response from the server
            while(!feof($fp)) { $info[]=@fgets($fp, 1024); }
            //close fp - we are done with it
            fclose($fp);
            //break up results into a string
            $info=implode(",",$info);
        }
        return $info;
    }


    public static function paypal_custom($user_id,$payment_id,$invoice_id){
        return $user_id.'|'.$payment_id.'|'.$invoice_id.'|'.md5(_UCM_FOLDER." user: $user_id payment: $payment_id invoice: $invoice_id ");
    }
    function handle_paypal_ipn(){

        ob_end_clean();

        $paypal_bits = explode("|",$_REQUEST['custom']);
        $user_id = (int)$paypal_bits[0];
        $payment_id = (int)$paypal_bits[1];
        $invoice_id = (int)$paypal_bits[2];
        //send_error('bad?');
        if($user_id && $payment_id && $invoice_id){
            $hash = $this->paypal_custom($user_id,$payment_id, $invoice_id);
            if($hash != $_REQUEST['custom']){
                send_error("PayPal IPN Error (incorrect hash)");
                exit;
            }

            $sql = "SELECT * FROM "._DB_PREFIX."users WHERE user_id = '$user_id' LIMIT 1";
            $res = qa($sql);
            if($res){

                $user = array_shift($res);
                if($user && $user['user_id'] == $user_id){

                    // check for payment exists
                    $payment = module_invoice::get_invoice_payment($payment_id);
                    $invoice = module_invoice::get_invoice($invoice_id);
                    if($payment && $invoice){

                        if($_REQUEST['payment_status']=="Canceled_Reversal" || $_REQUEST['payment_status']=="Refunded"){
                            // funky refund!! oh noes!!
                            // TODO: store this in the database as a negative payment... should be easy.
                            // populate $_REQUEST vars then do something like $payment_history_id = update_insert("payment_history_id","new","payment_history");
                            send_error("PayPal Error! The payment $payment_id has been refunded or reversed! BAD BAD! You have to follup up customer for money manually now.");

                        }else if($_REQUEST['payment_status']=="Completed"){

                            // payment is completed! yeye getting closer...

                            switch($_REQUEST['txn_type']){
                                case "web_accept":
                                    // running in paypal sandbox or not?
                                    $sandbox = (_PAYPAL_SANDBOX_MODE)?"sandbox.":'';
                                    // quick check we're not getting a fake payment request.
                                    $result= self::fsockPost("http://www.${sandbox}paypal.com/cgi-bin/webscr",$_POST);
                                    if(eregi("VERIFIED",$result)){
                                        // finally have everything.
                                        // mark the payment as completed.
                                        update_insert("invoice_payment_id",$payment_id,"invoice_payment",array(
                                                                              'date_paid' => date('Y-m-d'),
                                                                              'amount' => $_REQUEST['mc_gross'],
                                                                              'method' => 'PayPal (IPN)',
                                                                     ));
                                        
                                        /*// send customer an email thanking them for their payment.
                                        $sql = "SELECT * FROM "._DB_PREFIX."users WHERE user_id = '"._ADMIN_USER_ID."'";
                                        $res = qa($sql);
                                        $admin = array_shift($res);
                                        $from_email = $admin['email'];
                                        $from_name = $admin['real_name'];
                                        $mail_content = "Dear ".$user['real_name'].", \n\n";
                                        $mail_content .= "Your ".dollar($payment['outstanding'])." payment for '".$payment['description']."' has been processed. \n\n";
                                        $mail_content .= "We have successfully recorded your ".dollar($_REQUEST['mc_gross'])." payment in our system.\n\n";
                                        $mail_content .= "You will receive another email shortly from PayPal with details of the transaction.\n\n";
                                        $mail_content .= "Kind Regards,\n\n";
                                        $mail_content .= $from_name."\n".$from_email;

                                        send_error("PayPal SUCCESS!! User has paid you ".$_REQUEST['mc_gross']." we have recorded this against the payment and sent them an email");
                                        //$this->send_email( $payment_id, $user['email'], $mail_content, "Payment Successful", $from_email, $from_name );
                                        send_email($user['email'], "Payment Successful", $mail_content, array("FROM"=>$from_email,"FROM_NAME"=>$from_name));
                                        */
                                        // check if it's been paid in full..

                                        module_invoice::save_invoice($invoice_id,array());

                                        echo "Successful Payment!";

                                    }else{
                                        send_error("PayPal IPN Error (paypal rejected the payment!)");
                                    }
                                    break;
                                case "subscr_signup":
                                default:
                                    // TODO: support different payment methods later? like a monthly hosting fee..
                                    send_error("PayPal IPN Error (we dont currently support this payment method: ".$_REQUEST['txn_type'].")");
                                    break;
                            }
                        }else{
                            send_error("PayPal info: This payment is not yet completed, this usually means it's an e-cheque, follow it up in a few days if you dont hear anything.");
                        }

                    }else{
                        send_error("PayPal IPN Error (no payment found in database!)");
                    }
                }else{
                    send_error("PayPal IPN Error (error with user that was found in database..)");
                }
            }else{
                send_error("PayPal IPN Error (no user found in database #1)");
            }


        }else{
            send_error("PayPal IPN Error (no user id found)");
        }



        exit;
    }

}


