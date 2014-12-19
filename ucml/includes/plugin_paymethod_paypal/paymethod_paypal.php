<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:09 
  * IP Address: 127.0.0.1
  */

//306412171 ucm_1306412206_per@gmail.com



class module_paymethod_paypal extends module_base{


    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
        $this->version = 2.17;
		$this->module_name = "paymethod_paypal";
		$this->module_position = 8882;

	}

    public function pre_menu(){

        if(module_config::can_i('view','Settings')){
            $this->links[] = array(
                "name"=>"PayPal",
                "p"=>"paypal_settings",
                'holder_module' => 'config', // which parent module this link will sit under.
                'holder_module_page' => 'config_payment',  // which page this link will be automatically added to.
                'menu_include_parent' => 1,
            );
        }
    }

    public static function is_sandbox(){
        return module_config::c('payment_method_paypal_sandbox',0);
    }

    public function handle_hook($hook){
        switch($hook){
            case 'get_payment_methods':
                return $this;
                break;
        }
    }

    public function is_method($method){
        return $method=='online';
    }
    public static function is_enabled(){
        return module_config::c('payment_method_paypal_enabled',1);
    }

    public static function get_payment_method_name(){
        return module_config::s('payment_method_paypal_label','PayPal');
    }


    public function get_invoice_payment_description($invoice_id,$method=''){



    }
    
    public static function start_payment($invoice_id,$payment_amount,$invoice_payment_id){
        if($invoice_id && $payment_amount && $invoice_payment_id){
            // we are starting a payment via paypal!
            // setup a pending payment and redirect to paypal.
            $invoice_data = module_invoice::get_invoice($invoice_id);
            $invoice_payment_data = module_invoice::get_invoice_payment($invoice_payment_id);
            $description = _l('Payment for invoice %s',$invoice_data['name']);
            self::paypal_redirect($description,$payment_amount,module_security::get_loggedin_id(),$invoice_payment_id,$invoice_id,$invoice_payment_data['currency_id']);
            return true;
        }
        return false;
    }

    public function external_hook($hook){
         switch($hook){
            case 'ipn':
                // handle IPN response from paypal.
                $this->handle_paypal_ipn();
                break;
        }
    }

    public static function paypal_redirect($description,$amount,$user_id,$payment_id,$invoice_id,$currency_id){

        $currency = module_config::get_currency($currency_id);
        
        $url = 'https://www.'. (self::is_sandbox()? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?';

        $fields = array(
            'cmd' => '_xclick',
            'business' => module_config::c('payment_method_paypal_email',_ERROR_EMAIL),
            'currency_code' => $currency['code'],
            'item_name' => $description,
            'amount' => $amount,
            'return' => module_invoice::link_open($invoice_id),
            'notify_url' => full_link(_EXTERNAL_TUNNEL.'?m=paymethod_paypal&h=ipn&method=paypal'),
            'custom' => self::paypal_custom($user_id,$payment_id,$invoice_id),
        );

        foreach($fields as $key=>$val){
            $url .= $key.'='.urlencode($val).'&';
        }

        //echo '<a href="'.$url.'">'.$url.'</a>';exit;

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
        // todo - this can generate an "unknown ssl" error.
        $fp=@fsockopen($ssl . $web['host'],$web['port'],$errnum,$errstr,30);

        //Error checking
        if(!$fp) {
            send_error("There was a problem with PayPal IPN and fsockopen: $errnum: $errstr");
            return false;
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

            $sql = "SELECT * FROM `"._DB_PREFIX."user` WHERE user_id = '$user_id' LIMIT 1";
            $res = qa($sql);
            if($res){

                $user = array_shift($res);
                if($user && $user['user_id'] == $user_id){

                    // check for payment exists
                    $payment = module_invoice::get_invoice_payment($payment_id);
                    $invoice = module_invoice::get_invoice($invoice_id);
                    if($payment && $invoice){

                        $invoice_currency = module_config::get_currency($invoice['currency_id']);
                        $invoice_currency_code = $invoice_currency['code'];

                        // check correct business
                        if(!$_REQUEST['business']&&$_REQUEST['receiver_email']){
                            $_REQUEST['business'] = $_REQUEST['receiver_email'];
                        }
                        if($_REQUEST['business'] != module_config::c('payment_method_paypal_email',_ERROR_EMAIL)){
                            send_error('PayPal error! Paid the wrong business name. '.$_REQUEST['business'] .' instead of '.module_config::c('payment_method_paypal_email',_ERROR_EMAIL));
                            exit;
                        }
                        // check correct currency
                        if($invoice_currency_code && $_REQUEST['mc_currency'] != $invoice_currency_code){
                            send_error('PayPal error! Paid the wrong currency code. '.$_REQUEST['mc_currency'] .' instead of '.$invoice_currency_code);
                            exit;
                        }

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
                                    //$sandbox = (self::is_sandbox())?"sandbox.":'';
                                    // quick check we're not getting a fake payment request.
                                    $url = 'https://www.'. (self::is_sandbox()? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr';
                                    $result= self::fsockPost($url,$_POST);
                                    //send_error('paypal sock post: '.$url."\n\n".var_export($result,true));
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
                                        send_error("PayPal IPN Error (paypal rejected the payment!) ".var_export($result,true));
                                    }
                                    break;
                                case "subscr_signup":
                                default:
                                    // TODO: support different payment methods later? like a monthly hosting fee..
                                    send_error("PayPal IPN Error (we dont currently support this payment method: ".$_REQUEST['txn_type'].")");
                                    break;
                            }
                        }else{
                            send_error("PayPal info: This payment is not yet completed, this usually means it's an e-cheque, follow it up in a few days if you dont hear anything. This also means you may have to login to paypal and 'Accept' the payment. So check there first.");
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