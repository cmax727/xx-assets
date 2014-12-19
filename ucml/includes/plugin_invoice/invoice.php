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



class module_invoice extends module_base{
	
	public $links;
	public $invoice_types;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->links = array();
		$this->invoice_types = array();
		$this->module_name = "invoice";
		$this->module_position = 18;

        $this->version = 2.33;

//        module_config::register_css('invoice','invoice_items.css');


		
	}

    public function pre_menu(){

        if($this->can_i('view','Invoices')){
            $this->ajax_search_keys = array(
                _DB_PREFIX.'invoice' => array(
                    'plugin' => 'invoice',
                    'search_fields' => array(
                        'name',
                    ),
                    'key' => 'invoice_id',
                    'title' => _l('Invoice: '),
                ),
                _DB_PREFIX.'invoice_payment' => array(
                    'plugin' => 'invoice',
                    'search_fields' => array(
                        'amount',
                        'method',
                    ),
                    'key' => 'invoice_id',
                    'title' => _l('Invoice Payment: '),
                ),
            );
            // only display if a customer has been created.
            if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] && $_REQUEST['customer_id']!='new'){
                // how many invoices?
                $invoices = $this->get_invoices(array('customer_id'=>$_REQUEST['customer_id']));
                $name = _l('Invoices');
                if(count($invoices)){
                    $name .= " <span class='menu_label'>".count($invoices)."</span> ";
                }
                $this->links[] = array(
                    "name"=>$name,
                    "p"=>"invoice_admin",
                    'args'=>array('invoice_id'=>false),
                    'holder_module' => 'customer', // which parent module this link will sit under.
                    'holder_module_page' => 'customer_admin_open',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }
            $this->links[] = array(
                "name"=>"Invoices",
                "p"=>"invoice_admin",
                'args'=>array('invoice_id'=>false),
            );
            if($this->can_i('edit','Invoices')){
                $this->links[] = array(
                    "name"=>"Currency",
                    "p"=>"currency",
                    'args'=>array('currency_id'=>false),
                    'holder_module' => 'config', // which parent module this link will sit under.
                    'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }
        }
        /*else{
            if(module_security::is_contact()){
                // find out how many for this contact.
                $customer_ids = module_security::get_customer_restrictions();
                if($customer_ids){
                    $invoices = array();
                    foreach($customer_ids as $customer_id){
                        $invoices = $invoices + $this->get_invoices(array('customer_id'=>$customer_id));
                    }
                    $name = _l('Invoices');
                    if(count($invoices)){
                        $name .= " <span class='menu_label'>".count($invoices)."</span> ";
                    }
                    $this->links[] = array(
                        "name"=>$name,
                        "p"=>"invoice_admin",
                        'args'=>array('invoice_id'=>false),
                    );
                }
            }
        }*/
    }
	public function handle_hook($hook,&$calling_module=false){
		switch($hook){
			case "home_alerts":
				$alerts = array();
                if(module_config::c('invoice_alerts',1)){
                    // find any invoices that are past the due date and dont have a paid date.
                    $sql = "SELECT * FROM `"._DB_PREFIX."invoice` p ";
                    $sql .= " WHERE p.date_due != '0000-00-00' AND p.date_due <= '".date('Y-m-d',strtotime('+'.module_config::c('alert_days_in_future',5).' days'))."' AND p.date_paid = '0000-00-00'";
                    $invoice_items = qa($sql);
                    
                    foreach($invoice_items as $invoice_item){
                        $alert_res = process_alert($invoice_item['date_due'], _l('Invoice Payment Due'));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($invoice_item['invoice_id']);
                            $alert_res['name'] = $invoice_item['name'];
                            $alerts[] = $alert_res;
                        }
                    }
                    if($this->can_i('edit','Invoices')){
                        // find any invoices that haven't been sent
                        $sql = "SELECT * FROM `"._DB_PREFIX."invoice` p ";
                        $sql .= " WHERE p.date_sent = '0000-00-00' AND p.date_paid = '0000-00-00'";
                        $invoice_items = qa($sql);
                        foreach($invoice_items as $invoice_item){
                            $alert_res = process_alert(date('Y-m-d'), _l('Invoice Not Sent'));
                            if($alert_res){
                                $alert_res['link'] = $this->link_open($invoice_item['invoice_id']);
                                $alert_res['name'] = $invoice_item['name'];
                                $alerts[] = $alert_res;
                            }
                        }
                    }
				}
				return $alerts;
				break;
        }
        return false;
    }


    public static function link_generate($invoice_id=false,$options=array(),$link_options=array()){

        $key = 'invoice_id';
        if($invoice_id === false && $link_options){
            foreach($link_options as $link_option){
                if(isset($link_option['data']) && isset($link_option['data'][$key])){
                    ${$key} = $link_option['data'][$key];
                    break;
                }
            }
            if(!${$key} && isset($_REQUEST[$key])){
                ${$key} = $_REQUEST[$key];
            }
        }
        $bubble_to_module = false;
        if(!isset($options['type']))$options['type']='invoice';
        if(!isset($options['page']))$options['page'] = 'invoice_admin';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['invoice_id'] = $invoice_id;
        $options['module'] = 'invoice';
        if((int)$invoice_id > 0){
            $data = self::get_invoice($invoice_id,true);
        }else{
            $data = array(
                
            );
        }
        $options['data'] = $data;
        if(!isset($data['total_amount_due'])){

        }else if($data['total_amount_due'] <= 0){
            $link_options['class'] = 'success_text';
        }else{
            $link_options['class'] = 'error_text';
        }
        // what text should we display in this link?
        $options['text'] = (!isset($data['name'])||!trim($data['name'])) ? 'N/A' : $data['name'];
        if(
            // only bubble for admins:
            self::can_i('edit','Invoices') &&
            isset($data['customer_id']) && $data['customer_id']>0
        ){
            $bubble_to_module = array(
                'module' => 'customer',
                'argument' => 'customer_id',
            );
        }
        array_unshift($link_options,$options);


        if(!module_security::has_feature_access(array(
            'name' => 'Customers',
            'module' => 'customer',
            'category' => 'Customer',
            'view' => 1,
            'description' => 'view',
        ))
           // only apply this restriction to administrators, not contacts.
           && self::can_i('edit','Invoices')

        ){
            if(!isset($options['full']) || !$options['full']){
                return '#';
            }else{
                return isset($options['text']) ? $options['text'] : 'N/A';
            }

        }
        if($bubble_to_module){
            global $plugins;
            return $plugins[$bubble_to_module['module']]->link_generate(false,array(),$link_options);
        }else{
            // return the link as-is, no more bubbling or anything.
            // pass this off to the global link_generate() function
            //print_r($link_options);
            return link_generate($link_options);

        }
    }

	public static function link_open($invoice_id,$full=false){
        return self::link_generate($invoice_id,array('full'=>$full));
    }


    public static function link_receipt($invoice_payment_id,$h=false){
        if($h){
            return md5('s3cret7hash '._UCM_FOLDER.' '.$invoice_payment_id);
        }
        return full_link(_EXTERNAL_TUNNEL.'?m=invoice&h=receipt&i='.$invoice_payment_id.'&hash='.self::link_receipt($invoice_payment_id,true));
    }
    

    public static function link_public($invoice_id,$h=false){
        if($h){
            return md5('s3cret7hash for invoice '._UCM_FOLDER.' '.$invoice_id);
        }
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.invoice/h.public/i.'.$invoice_id.'/hash.'.self::link_public($invoice_id,true));
    }
    public static function link_public_print($invoice_id,$h=false){
        if($h){
            return md5('s3cret7hash for invoice '._UCM_FOLDER.' '.$invoice_id);
        }
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.invoice/h.public_print/i.'.$invoice_id.'/hash.'.self::link_public($invoice_id,true));
    }


    public function external_hook($hook){
        
        switch($hook){
            case 'public_print':
                ob_start();

                $invoice_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($invoice_id && $hash){
                    $correct_hash = $this->link_public_print($invoice_id,true);
                    if($correct_hash == $hash){
                        ini_set('display_errors',false);
                        $pdf_file = $this->generate_pdf($invoice_id);

                        if($pdf_file && is_file($pdf_file)){
                            ob_end_clean();
                            ob_end_clean();

                            // send pdf headers and prompt the user to download the PDF

                            header("Pragma: public");
                            header("Expires: 0");
                            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                            header("Cache-Control: private",false);
                            header("Content-Type: application/pdf");
                            header("Content-Disposition: attachment; filename=\"".basename($pdf_file)."\";");
                            header("Content-Transfer-Encoding: binary");
                            header("Content-Length: ".filesize($pdf_file));
                            readfile($pdf_file);

                        }else{
                            echo _l('Sorry PDF is not currently available.');
                        }
                    }
                }

                exit;

                break;
            case 'public':
                $invoice_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($invoice_id && $hash){
                    $correct_hash = $this->link_public($invoice_id,true);
                    if($correct_hash == $hash){

                        // are we processing this payment?
                        if(isset($_REQUEST['payment'])&&$_REQUEST['payment']=='go'){
                            $this->handle_payment();
                        }

                        // all good to print a receipt for this payment.
                        $invoice = $invoice_data = $this->get_invoice($invoice_id);
                        
                        module_template::init_template('external_invoice','<h2>Invoice</h2>
Invoice Number: <strong>{INVOICE_NUMBER}</strong> <br/>
Due Date: <strong>{DUE_DATE}</strong> <br/>
{PROJECT_TYPE} Name: <strong>{PROJECT_NAME}</strong> <br/>
Job: <strong>{JOB_NAME}</strong> <br/>
<a href="{PRINT_LINK}">Print PDF Invoice</a> <br/>
<br/>
{TASK_LIST}
{PAYMENT_METHODS}
{PAYMENT_HISTORY}
','Used when displaying the external view of an invoice.','code');
                        // correct!
                        // load up the receipt template.
                        $template = module_template::get_template_by_key('external_invoice');


                        ob_start();
                        include('template/invoice_task_list.php');
                        $task_list_html = ob_get_clean();
                        ob_start();
                        include('template/invoice_payment_history.php');
                        $invoice_payment_history = ob_get_clean();
                        ob_start();
                        include('template/invoice_payment_methods.php');
                        $invoice_payment_methods = ob_get_clean();

                        $job_data = module_job::get_job(current($invoice_data['job_ids']));
                        $website_data = module_website::get_website($job_data['website_id']);

                        $data = array(
                            'invoice_number' => htmlspecialchars($invoice_data['name']),
                            'due_date' => print_date($invoice_data['date_due']),
                            'project_type' => _l(module_config::c('project_name_single','Website')),
                            'project_name' => htmlspecialchars($website_data['name']),
                            'job_name' => htmlspecialchars($job_data['name']),
                            'task_list' => $task_list_html,
                            'print_link' => $this->link_public_print($invoice_id),
                            'payment_methods' => $invoice_payment_methods,
                            'payment_history' => $invoice_payment_history,
                        );

                        $template->page_title = htmlspecialchars($invoice_data['name']);

                        $template->assign_values($data);
                        echo $template->render('pretty_html');
                        exit;
                    }
                }
                break;
            case 'receipt':
                $invoice_payment_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($invoice_payment_id && $hash){
                    $correct_hash = $this->link_receipt($invoice_payment_id,true);
                    if($correct_hash == $hash){
                        // all good to print a receipt for this payment.
                        $invoice_payment_data = $this->get_invoice_payment($invoice_payment_id);
                        if($invoice_payment_data){
                            $invoice_data = $this->get_invoice($invoice_payment_data['invoice_id']);
                            if($invoice_payment_data && $invoice_data){
                                // correct!
                                 module_template::init_template('invoice_payment_receipt','Payment Receipt for Invoice # {NAME}

    Receipt Number: <strong>{RECEIPT_NUMBER}</strong>
    Payment status: <strong>{PAY_STATUS}</strong>
    Payment made on: <strong>{PAYMENT_DATE}</strong>
    Payment amount: <strong>{AMOUNT}</strong>
    Payment method: <strong>{METHOD}</strong>
    ','Receipts for invoice payments.',array(
                                       'NAME' => 'Invoice Number',
                                       'DATE_SENT' => 'Date invoice was sent',
                                       'DATE_DUE' => 'Date invoice was due',
                                       'DATE_PAID' => 'Date invoice was paid',
                                       'HOURLY_RATE' => 'Hourly rate of the invoice',
                                       'TOTAL_AMOUNT' => 'Total amount of invoice',
                                       'TOTAL_AMOUNT_DUE' => 'Total due on invoice',
                                       'TOTAL_AMOUNT_PAID' => 'Total paid on invoice',
                                       'RECEIPT_NUMBER' => 'Our Receipt Number',
                                       'PAY_STATUS' => 'Paid or not',
                                       'PAYMENT_DATE' => 'Date payment was made',
                                       'AMOUNT' => 'Amount that was paid',
                                       'METHOD' => 'What payment method was used',
                                       ));
                                // load up the receipt template.
                                if($invoice_payment_data['date_paid']=='0000-00-00'){
                                    $custom_data = array(
                                        'receipt_number' => 'N/A',
                                        'pay_status' => _l('Not Paid Yet'),
                                        'payment_date' => 'Not Yet',
                                    );
                                }else{
                                    $custom_data = array(
                                        'receipt_number' => $invoice_payment_data['invoice_payment_id'],
                                        'pay_status' => _l('Payment Completed'),
                                        'payment_date' => print_date($invoice_payment_data['date_paid']),
                                    );
                                }
                                $invoice_payment_data['amount'] = dollar($invoice_payment_data['amount'],true,$invoice_payment_data['currency_id']);
                                $template = module_template::get_template_by_key('invoice_payment_receipt');
                                $template->assign_values($invoice_payment_data+$invoice_data+$custom_data);
                                echo $template->render('pretty_html');
                            }
                        }
                    }
                }
                break;
        }
    }

	
	public function process(){
		$errors=array();
        if($_REQUEST['_process'] == 'make_payment'){
            $this->handle_payment();
        }else if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['invoice_id']){
            $data = self::get_invoice($_REQUEST['invoice_id']);
            if(module_form::confirm_delete('invoice_id',"Really delete invoice: ".$data['name'],self::link_open($_REQUEST['invoice_id']))){
                $invoice_data = self::get_invoice($_REQUEST['invoice_id'],true);
                $this->delete_invoice($_REQUEST['invoice_id']);
                set_message("Invoice deleted successfully");
                if(isset($invoice_data['job_ids']) && $invoice_data['job_ids']){
                    redirect_browser(module_job::link_open(current($invoice_data['job_ids'])));
                }else{
                    redirect_browser(self::link_open(false));
                }
            }
		}else if("assign_credit_to_customer" == $_REQUEST['_process']){
            $invoice_id = (int)$_REQUEST['invoice_id'];
            if($invoice_id>0){
                $invoice_data = $this->get_invoice($invoice_id,true);
                $credit = $invoice_data['total_amount_credit'];
                if($credit > 0){
                    if($invoice_data['customer_id']){
                        // assign to customer.
                        module_customer::add_credit($invoice_data['customer_id'],$credit);
                        // assign this as a negative payment, and also give it to the customer account.
                        $this->add_history($invoice_id,'Added '.dollar($credit).' credit to customers account from this invoice overpayment');
                        update_insert('invoice_payment_id','new','invoice_payment',array(
                                                              'invoice_id'=>$invoice_id,
                                                              'amount' => -$credit,
                                                              'method' => 'Assigning Credit',
                                                              'date_paid' => date('Y-m-d'),
                                               ));
                    }
                }
                redirect_browser($this->link_open($invoice_id));
            }
		}else if("save_invoice" == $_REQUEST['_process']){
			$invoice_id = $this->save_invoice($_REQUEST['invoice_id'],$_POST);

            // check for credit assessment.
            if(isset($_POST['apply_credit_from_customer']) && $_POST['apply_credit_from_customer'] == 'do'){
                $invoice_data = $this->get_invoice($invoice_id);
                $customer_data = module_customer::get_customer($invoice_data['customer_id']);
                if($customer_data['credit'] > 0){
                    $invoice_data['discount_amount'] += $customer_data['credit'];
                    $this->add_history($invoice_id,'Adding '.dollar($customer_data['credit']).' customer credit to this invoice.');
                    $this->save_invoice($invoice_id,array('discount_amount'=>$invoice_data['discount_amount'],'discount_description'=>_l('Credit:')));
                    module_customer::remove_credit($customer_data['customer_id'],$customer_data['credit']);
                }
            }

            if(isset($_REQUEST['butt_makepayment']) && $_REQUEST['butt_makepayment'] == 'yes'){
                self::handle_payment();
            }else if(isset($_REQUEST['butt_print']) && $_REQUEST['butt_print']){
                $_REQUEST['_redirect'] = self::link_generate($invoice_id,array('arguments'=>array('print'=>1)));;
            }else if(isset($_REQUEST['butt_merge']) && $_REQUEST['butt_merge'] && is_array($_REQUEST['merge_invoice'])){
                $merge_invoice_ids = self::check_invoice_merge($invoice_id);
                foreach($merge_invoice_ids as $merge_invoice){
                    if(isset($_REQUEST['merge_invoice'][$merge_invoice['invoice_id']])){
                        // copy all the tasks from that invoice over to this invoice.
                        $sql = "UPDATE `"._DB_PREFIX."invoice_item` SET invoice_id = ".(int)$invoice_id." WHERE invoice_id = ".(int)$merge_invoice['invoice_id']." ";
                        query($sql);
                        $this->delete_invoice($merge_invoice['invoice_id']);
                    }
                }
                $_REQUEST['_redirect'] = $this->link_open($invoice_id);
                set_message('Invoices merged successfully');
            }else if(isset($_REQUEST['butt_email']) && $_REQUEST['butt_email']){
                $_REQUEST['_redirect'] = self::link_generate($invoice_id,array('arguments'=>array('email'=>1)));;
            }else{
                $_REQUEST['_redirect'] = $this->link_open($invoice_id);
                set_message("Invoice saved successfully");
            }
		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		print_error($errors,true);
	}


	public static function get_invoices($search=array()){
		// limit based on customer id
		/*if(!isset($_REQUEST['customer_id']) || !(int)$_REQUEST['customer_id']){
			return array();
		}*/
		// build up a custom search sql query based on the provided search fields
		$sql = "SELECT u.*,u.invoice_id AS id ";
        $sql .= ", u.name AS name ";
        $sql .= ", c.customer_name ";
        $from = " FROM `"._DB_PREFIX."invoice` u ";
        $from .= " LEFT JOIN `"._DB_PREFIX."customer` c USING (customer_id)";
        $from .= " LEFT JOIN `"._DB_PREFIX."invoice_item` ii ON u.invoice_id = ii.invoice_id ";
        $from .= " LEFT JOIN `"._DB_PREFIX."task` t ON ii.task_id = t.task_id";
		$where = " WHERE 1 ";
		if(isset($search['generic']) && $search['generic']){
			$str = mysql_real_escape_string($search['generic']);
			$where .= " AND ( ";
			$where .= " u.name LIKE '%$str%' ";
			//$where .= "OR  u.url LIKE '%$str%'  ";
			$where .= ' ) ';
		}
        foreach(array('customer_id','status','name') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND u.`$key` = '$str'";
            }
        }
        if(isset($search['job_id']) && (int)$search['job_id']>0){
            $where .= " AND t.`job_id` = ".(int)$search['job_id'];
        }

        // permissions from job module.
        switch(module_job::get_job_access_permissions()){
            case _JOB_ACCESS_ALL:

                break;
            case _JOB_ACCESS_ASSIGNED:
                // only assigned jobs!
                //$from .= " LEFT JOIN `"._DB_PREFIX."task` t ON u.job_id = t.job_id ";
                //u.user_id = ".(int)module_security::get_loggedin_id()." OR
                $where .= " AND (t.user_id = ".(int)module_security::get_loggedin_id().")";
                break;
            case _JOB_ACCESS_CUSTOMER:
                break;
        }

        // permissions from customer module.
        // tie in with customer permissions to only get jobs from customers we can access.
        switch(module_customer::get_customer_data_access()){
            case _CUSTOMER_ACCESS_ALL:
                // all customers! so this means all jobs!
                break;
            case _CUSTOMER_ACCESS_CONTACTS:
                // we only want customers that are directly linked with the currently logged in user contact.
                if(isset($_SESSION['_restrict_customer_id']) && (int)$_SESSION['_restrict_customer_id']> 0){
                    // this session variable is set upon login, it holds their customer id.
                    // todo - share a user account between multiple customers!
                    //$where .= " AND c.customer_id IN (SELECT customer_id FROM )";
                    $where .= " AND u.customer_id = '".(int)$_SESSION['_restrict_customer_id']."'";
                }
                break;
            case _CUSTOMER_ACCESS_TASKS:
                // only customers who have a job that I have a task under.
                // this is different to "assigned jobs" Above
                // this will return all jobs for a customer even if we're only assigned a single job for that customer
                // tricky!
                // copied from customer.php
                $where .= " AND u.customer_id IN ";
                $where .= " ( SELECT cc.customer_id FROM `"._DB_PREFIX."customer` cc ";
                $where .= " LEFT JOIN `"._DB_PREFIX."job` jj ON cc.customer_id = jj.customer_id ";
                $where .= " LEFT JOIN `"._DB_PREFIX."task` tt ON jj.job_id = tt.job_id ";
                $where .= " WHERE (jj.user_id = ".(int)module_security::get_loggedin_id()." OR tt.user_id = ".(int)module_security::get_loggedin_id().")";
                $where .= " )";

                break;
        }


        $group_order = ' GROUP BY u.invoice_id ORDER BY u.name'; // stop when multiple company sites have same region
		$sql = $sql . $from . $where . $group_order;
		$result = qa($sql);
		//module_security::filter_data_set("invoice",$result);
		return $result;
//		return get_multiple("invoice",$search,"invoice_id","fuzzy","name");

	}
    public static function get_invoice_items($invoice_id){
        $invoice_id = (int)$invoice_id;
        if(!$invoice_id && isset($_REQUEST['job_id']) && (int)$_REQUEST['job_id'] > 0){

            // hack for half completed invoices
            if(isset($_REQUEST['amount_due']) && $_REQUEST['amount_due'] > 0){

                $amount = (float)$_REQUEST['amount_due'];
                

                $new_tasks = array(
                    'new0' => array(
                        'description' => _l('Invoice Item'),
                        'amount' => $amount,
                        'hours' => 0,
                    ),
                );
                

            }else{

                // we return the items from the job rather than the items from the invoice.
                // for new invoice creation.
                $tasks = module_job::get_invoicable_tasks($_REQUEST['job_id']);
                $new_tasks = array();
                $x=0;
                foreach($tasks as $task){
                    $task['custom_description'] = '';
                    //$task['task_id'] = 'new'.$x;
                    $new_tasks['new'.$x] = $task;
                    $x++;
                }
            }
            return $new_tasks;
        }
        if($invoice_id){
            $sql = "SELECT ii.invoice_item_id AS id, ii.*, t.job_id, t.description AS description, ii.description as custom_description, t.task_order FROM `"._DB_PREFIX."invoice_item` ii LEFT JOIN `"._DB_PREFIX."task` t ON ii.task_id = t.task_id ";
            $sql .= " WHERE ii.invoice_id = $invoice_id";
            $sql .= " ORDER BY t.task_order ";
            return qa($sql);
        }
		//return get_multiple("invoice_item",array('invoice_id'=>$invoice_id),"invoice_item_id","exact","invoice_item_id");
        return array();
	}
    public static function get_invoice_payments($invoice_id){
        $invoice_id = (int)$invoice_id;
		return get_multiple("invoice_payment",array('invoice_id'=>$invoice_id),"invoice_payment_id","exact","invoice_payment_id",true);
	}
    public static function get_invoice_payment($invoice_payment_id){
        $invoice_payment_id = (int)$invoice_payment_id;
		return get_single('invoice_payment','invoice_payment_id',$invoice_payment_id,true);
	}
	public static function get_invoice($invoice_id,$basic=false){
        $invoice = array();
        if((int)$invoice_id>0){
            $sql = "SELECT i.*";
            $sql .= ", GROUP_CONCAT(DISTINCT j.`website_id` SEPARATOR ',') AS website_ids"; // the website id(s)
            $sql .= ", GROUP_CONCAT(DISTINCT j.`job_id` SEPARATOR ',') AS job_ids"; // the website id(s)
            $sql .= ", j.customer_id AS new_customer_id ";
            $sql .= " FROM `"._DB_PREFIX."invoice` i ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."invoice_item` ii USING (invoice_id) ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON ii.task_id = t.task_id";
            $sql .= " LEFT JOIN `"._DB_PREFIX."job` j ON t.job_id = j.job_id";
            $sql .= " WHERE i.invoice_id = ".(int)$invoice_id;
            $sql .= " GROUP BY i.invoice_id";
            $invoice = qa1($sql);
            if(!$invoice)return array();
            // set the job id of the first job just for kicks
            if($invoice['job_ids']){
                $invoice['job_ids'] = explode(',',$invoice['job_ids']);
            }else{
                $invoice['job_ids'] = array();
            }
            if(isset($invoice['website_ids'])){
                $invoice['website_ids'] = explode(',',$invoice['website_ids']);
            }else{
                $invoice['website_ids'] = array();
            }
            // incase teh customer id on this invoice changes:
            if(isset($invoice['new_customer_id']) && $invoice['new_customer_id'] > 0 && $invoice['new_customer_id'] != $invoice['customer_id']){
                $invoice['customer_id'] = $invoice['new_customer_id'];
                update_insert('invoice_id',$invoice_id,'invoice',array('customer_id'=>$invoice['new_customer_id']));
            }
            if($basic===true){
                return $invoice;
            }
        }
        // not sure why this code was here, commenting it out for now until we need it.
        /*if(isset($invoice['customer_id']) && isset($invoice['job_id']) && $invoice['customer_id'] <= 0 && $invoice['job_id'] > 0){
            $job_data = module_job::get_job($invoice['job_id'],false);
            $invoice['customer_id'] = $job_data['customer_id'];
        }*/
        if(!$invoice){
            $customer_id = (isset($_REQUEST['customer_id'])? $_REQUEST['customer_id'] : 0);
            $job_id = (isset($_REQUEST['job_id'])? $_REQUEST['job_id'] : 0);
            $currency_id = module_config::c('default_currency_id',1);
            if($customer_id > 0){
                // find a default website to use ?
            }else if($job_id > 0){
                // only a job, no customer. set the customer id.
                $job_data = module_job::get_job($job_id,false);
                $customer_id = $job_data['customer_id'];
                $currency_id = $job_data['currency_id'];
            }
            // work out an invoice number
            $invoice_number = '';
            if(function_exists('custom_invoice_number')){
                $invoice_number = custom_invoice_number();
            }
            if(!$invoice_number){
                $invoice_number = date('ymd');
                // check if this invoice number exists for this customer
                // if it does exist we create a suffix a, b, c, d etc..
                // this isn't atomic - if two invoices are created for the same customer at the same time then
                // this probably wont work. but for this system it's fine.
                $this_invoice_number = $invoice_number;
                $suffix_ascii = 65; // 65 is a
                do{
                    $invoices = self::get_invoices(array('customer_id'=>$customer_id,'name'=>$this_invoice_number));
                    if(!count($invoices)){
                        $invoice_number = $this_invoice_number;
                    }else{
                        $this_invoice_number = $invoice_number.chr($suffix_ascii);
                    }
                    $suffix_ascii++;
                }while(count($invoices) && $suffix_ascii < 80);
            }
            $invoice = array(
                'invoice_id' => 'new',
                'customer_id' => $customer_id,
                'job_id' => $job_id, // this is  needed as a once off for creating new invoices.
                'job_ids' => array($job_id),
                'currency_id' => $currency_id,
                'name' => $invoice_number,
                'discount_description' => _l('Discount:'),
                'discount_amount' => 0,
                'date_sent' => '',
                'date_due' => date('Y-m-d',strtotime('+'.module_config::c('invoice_due_days',30).' days')),
                'date_paid' => '',
                'status'  => module_config::s('invoice_status_default','New'),
            );

            
        }
        if($invoice){


            // drag some details from the related job
            if(!(int)$invoice_id){
                if(isset($invoice['job_ids']) && $invoice['job_ids']){
                    $first_job_id = current($invoice['job_ids']);
                }else if(isset($invoice['job_id']) && $invoice['job_id']){
                    $first_job_id = $invoice['job_id']; // abckwards compatibility
                }else{
                    $first_job_id = 0;
                }
                if($first_job_id>0){
                    $job_data = module_job::get_job($first_job_id,false);
                    $invoice['hourly_rate'] = $job_data['hourly_rate'];
                    $invoice['total_tax_rate'] = $job_data['total_tax_rate'];
                    $invoice['total_tax_name'] = $job_data['total_tax_name'];
                }
            }
            // work out total hours etc..
            //$invoice['total_hours'] = 0;
            //$invoice['total_hours_completed'] = 0;
            //$invoice['total_hours_overworked'] = 0;
            $invoice['total_sub_amount'] = 0;
            $invoice_items = self::get_invoice_items((int)$invoice['invoice_id']);
            foreach($invoice_items as $invoice_item){
                if($invoice_item['amount'] != 0){
                    // we have a custom amount for this invoice_item
                    $invoice['total_sub_amount'] += $invoice_item['amount'];
                }
                if($invoice_item['hours'] > 0){
                    /*$invoice['total_hours'] += $invoice_item['hours'];
                    $invoice['total_hours_completed'] += min($invoice_item['hours'],$invoice_item['completed']);
                    if($invoice_item['completed'] > $invoice_item['hours']){
                        $invoice['total_hours_overworked'] = $invoice_item['completed'] - $invoice_item['hours'];
                    }*/
                    if($invoice_item['amount'] <= 0){
                        $invoice['total_sub_amount'] += $invoice_item['hours'] * $invoice['hourly_rate'];
                    }
                }
            }

            // add any discounts.
            if($invoice['discount_amount'] != 0){
                $invoice['total_sub_amount']-=$invoice['discount_amount'];
            }

            //$invoice['total_hours_remain'] = $invoice['total_hours'] - $invoice['total_hours_completed'];
            //$invoice['total_percent_complete'] = $invoice['total_hours'] > 0 ? round($invoice['total_hours_remain'] / $invoice['total_hours'],2) : 0;
            $invoice['total_tax'] = ($invoice['total_sub_amount'] * ($invoice['total_tax_rate'] / 100));
            $invoice['total_amount'] = $invoice['total_sub_amount'] + ($invoice['total_sub_amount'] * ($invoice['total_tax_rate'] / 100));

            if($basic===1){
                // so we don't go clearning cache and working out how much has been paid.
                // used in the finance module while displaying dashboard summary.
                return $invoice;
            }

            $paid = 0;
            //module_cache::clear_cache(); // no longer clearnig cache, it does it in get_invoice_payments.
            foreach(self::get_invoice_payments($invoice_id) as $payment){
                if($payment['date_paid'] && $payment['date_paid']!='0000-00-00'){
                    $paid += $payment['amount'];
                }
            }
            // dont go negative on payments:
            $invoice['total_amount_paid'] = max(0,min($invoice['total_amount'],$paid));
            $invoice['total_amount_credit'] = 0;
            if($invoice['total_amount'] > 0 && $paid > $invoice['total_amount']){
                // raise a credit against this customer for the difference.
                $invoice['total_amount_credit'] = $paid - $invoice['total_amount'];
                //echo $invoice['total_amount_overpaid'];exit;
            }
            $invoice['total_amount_due'] = $invoice['total_amount'] - $invoice['total_amount_paid'];
            $invoice['total_amount_invoiced'] = 0;
        }
		return $invoice;
	}
	public static function save_invoice($invoice_id,$data){
        if(!(int)$invoice_id && isset($data['job_id']) && $data['job_id']){
            $linkedjob = module_job::get_job($data['job_id']);
            $data['currency_id'] = $linkedjob['currency_id'];
            $data['customer_id'] = $linkedjob['customer_id'];
        }
		$invoice_id = update_insert("invoice_id",$invoice_id,"invoice",$data);
        if($invoice_id){
            $invoice_data = self::get_invoice($invoice_id);
            // check for new invoice_items or changed invoice_items.
            $invoice_items = self::get_invoice_items($invoice_id);
            if(isset($data['invoice_invoice_item']) && is_array($data['invoice_invoice_item'])){
                foreach($data['invoice_invoice_item'] as $invoice_item_id => $invoice_item_data){
                    $invoice_item_id = (int)$invoice_item_id;
                    if(!is_array($invoice_item_data))continue;
                    if($invoice_item_id > 0 && !isset($invoice_items[$invoice_item_id]))continue; // wrong invoice_item save - will never happen.
                    if(!isset($invoice_item_data['description']) || $invoice_item_data['description'] == ''){
                        if($invoice_item_id>0){
                            // remove invoice_item.
                            $sql = "DELETE FROM `"._DB_PREFIX."invoice_item` WHERE invoice_item_id = '$invoice_item_id' AND invoice_id = $invoice_id LIMIT 1";
                            query($sql);
                        }
                        continue;
                    }
                    // add / save this invoice_item.
                    $invoice_item_data['invoice_id'] = $invoice_id;
                    // remove the amount of it equals the hourly rate.
                    if($invoice_item_data['amount'] > 0 && $invoice_item_data['hours'] > 0){
                        if($invoice_item_data['amount'] - ($invoice_item_data['hours'] * $data['hourly_rate']) == 0){
                            unset($invoice_item_data['amount']);
                        }
                    }
                    // check if we haven't unticked a non-hourly invoice_item
                    if(isset($invoice_item_data['completed_t']) && $invoice_item_data['completed_t'] && !isset($invoice_item_data['completed'])){
                        $invoice_item_data['completed'] = 0;
                    }
                    update_insert('invoice_item_id',$invoice_item_id,'invoice_item',$invoice_item_data); 
                }
            }
            if(isset($data['invoice_invoice_payment']) && is_array($data['invoice_invoice_payment'])){
                foreach($data['invoice_invoice_payment'] as $invoice_payment_id => $invoice_payment_data){
                    $invoice_payment_id = (int)$invoice_payment_id;
                    if(!is_array($invoice_payment_data))continue;
                    if(!isset($invoice_payment_data['amount']) || $invoice_payment_data['amount'] == '' || $invoice_payment_data['amount'] == 0){ // || $invoice_payment_data['amount'] <= 0
                        if($invoice_payment_id>0){
                            // remove invoice_payment.
                            $sql = "DELETE FROM `"._DB_PREFIX."invoice_payment` WHERE invoice_payment_id = '$invoice_payment_id' AND invoice_id = $invoice_id LIMIT 1";
                            query($sql);
                            // delete any existing transactions from the system as well.
                            // todo: is this right???
                            $sql = "DELETE FROM `"._DB_PREFIX."finance` WHERE invoice_payment_id = '$invoice_payment_id' LIMIT 1";
                            query($sql);

                        }
                        continue;
                    }
                    if(!$invoice_payment_id && (!isset($_REQUEST['add_payment']) || $_REQUEST['add_payment'] != 'go')){
                        continue; // not saving a new one.
                    }
                    // add / save this invoice_payment.
                    $invoice_payment_data['invoice_id'] = $invoice_id;
                   // $invoice_payment_data['currency_id'] = $invoice_data['currency_id'];
                    update_insert('invoice_payment_id',$invoice_payment_id,'invoice_payment',$invoice_payment_data);
                }
            }
            // check if the invoice has been paid
            module_cache::clear_cache(); // this helps fix the bug where part payments are not caulcated a correct paid date.
            $invoice_data = self::get_invoice($invoice_id);
            if(((!$invoice_data['date_paid']||$invoice_data['date_paid']=='0000-00-00')) && $invoice_data['total_amount_due'] <= 0 && $invoice_data['total_amount_paid'] > 0){
                update_insert("invoice_id",$invoice_id,"invoice",array(
                                              'date_paid' => date('Y-m-d'),
                                              'status' => _l('Paid'),
                 ));
            }
            if($invoice_data['total_amount_due']>0){
                // update the status to unpaid.
                update_insert("invoice_id",$invoice_id,"invoice",array(
                                              'date_paid' => '',
                 ));
            }
        }
        module_extra::save_extras('invoice','invoice_id',$invoice_id);
		return $invoice_id;
	}

	public static function delete_invoice($invoice_id){
		$invoice_id=(int)$invoice_id;
		$sql = "DELETE FROM "._DB_PREFIX."invoice WHERE invoice_id = '".$invoice_id."' LIMIT 1";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."invoice_item WHERE invoice_id = '".$invoice_id."'";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."invoice_payment WHERE invoice_id = '".$invoice_id."'";
		$res = query($sql);
		module_note::note_delete("invoice",$invoice_id);
        module_extra::delete_extras('invoice','invoice_id',$invoice_id);
	}
    public function login_link($invoice_id){
        return module_security::generate_auto_login_link($invoice_id);
    }

    public static function get_statuses(){
        $sql = "SELECT `status` FROM `"._DB_PREFIX."invoice` GROUP BY `status` ORDER BY `status`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['status']] = $r['status'];
        }
        return $statuses;
    }
    public static function get_payment_methods(){
        $sql = "SELECT `method` FROM `"._DB_PREFIX."invoice_payment` GROUP BY `method` ORDER BY `method`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['method']] = $r['method'];
        }
        return $statuses;
    }
    public static function get_types(){
        $sql = "SELECT `type` FROM `"._DB_PREFIX."invoice` GROUP BY `type` ORDER BY `type`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['type']] = $r['type'];
        }
        return $statuses;
    }

    public function handle_payment(){
        // handle a payment request via post data from
        $invoice_id = (int)$_REQUEST['invoice_id'];
        $payment_method = $_REQUEST['payment_method'];
        $payment_amount = (float)$_REQUEST['payment_amount'];
        $invoice_data = $this->get_invoice($invoice_id);
         //&& module_security::can_access_data('invoice',$invoice_data,$invoice_id)
        if($invoice_id && $payment_method && $payment_amount > 0 && $invoice_data){
            // pass this off to the payment module for handling.
            global $plugins;
            if(isset($plugins[$payment_method])){

                // delete any previously pending payment methods
                //$sql = "DELETE FROM `"._DB_PREFIX."invoice_payment` WHERE invoice_id = $invoice_id AND method = '".mysql_real_escape_string($plugins[''.$payment_method]->get_payment_method_name())."' AND currency_id = '".$invoice_data['currency_id']."' ";
                // insert a temp payment method here.
                $invoice_payment_id = update_insert('invoice_payment_id','new','invoice_payment',array(
                    'invoice_id' => $invoice_id,
                    'amount' => $payment_amount,
                    'currency_id' => $invoice_data['currency_id'],
                    'method' => $plugins[''.$payment_method]->get_payment_method_name(),
                ));


                $plugins[''.$payment_method]->start_payment($invoice_id,$payment_amount,$invoice_payment_id);

            }
        }
        redirect_browser($_SERVER['REQUEST_URI']);
    }


    /**
     * Generate a PDF for the currently load()'d quote
     * Return the path to the file name for this quote.
     * @return bool
     */

    public static function generate_pdf($invoice_id){

        if(!function_exists('convert_html2pdf'))return false;

        $invoice_id = (int)$invoice_id;
        $invoice_data = self::get_invoice($invoice_id);
        $invoice_html = self::invoice_html($invoice_id,$invoice_data,'pdf');
        if($invoice_html){
            //echo $invoice_html;exit;

            $html_file_name = _UCM_FOLDER . "/temp/".'Invoice_'.$invoice_data['name'].'.html';
            $pdf_file_name = _UCM_FOLDER . "/temp/".'Invoice_'.$invoice_data['name'].'.pdf';

            file_put_contents($html_file_name,$invoice_html);

            return convert_html2pdf($html_file_name,$pdf_file_name);


        }
        return false;
    }

    public function invoice_html($invoice_id,$invoice_data,$mode='html'){

        if($invoice_id && $invoice_data){
            // spit out the invoice html into a file, then pass it to the pdf converter
            // to convert it into a PDF.

            module_template::init_template('invoice_print','<html>
    <head>
        <title>Invoice Print Out</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style type="text/css">
        body{
			font-family:arial;
			font-size:17px;
		}
        .table,
        .table2{
            border-collapse:collapse;
        }
        .table td,
        .table2 td.border{
            border:1px solid #EFEFEF;
            border-collapse:collapse;
            padding:4px;
        }
    </style>
    </head>
    <body>

<table width="100%" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td width="10%">&nbsp;</td>
        <td width="80%">


    <table cellpadding="4" cellspacing="0" width="100%">
        <tbody>
            <tr>
                <td width="450" align="left" valign="top">
                    <p>
                        <font style="font-size: 1.6em;">
                            <strong>Invoice #:</strong> {INVOICE_NUMBER}<br/>
                        </font>
                        <strong>Due Date:</strong>
                        {DUE_DATE} <br/>
                    </p>
                    {INVOICE_PAID}
                </td>
                <td align="right" valign="top">
                    <p>
                        <font style="font-size: 1.6em;"><strong>{TITLE}</strong></font>
                        <br/>
                        <font style="color: #333333;">
                        [our company details]
                        </font>
                    </p>
                </td>
            </tr>
            <tr>
                <td align="left" valign="top">
                    <strong>INVOICE TO:</strong><br/>
                    {CUSTOMER_NAME} <br/>
                    {CONTACT_NAME} {CONTACT_EMAIL} <br/>
                </td>
                <td align="right" valign="top">
                    &nbsp;<br/>
                    {PROJECT_TYPE}: <strong>{PROJECT_NAME}</strong> <br/>
                    Job: <strong>{JOB_NAME}</strong>
                </td>
            </tr>
        </tbody>
    </table>
    <br/>
    {TASK_LIST}
    <br/>
    
    {PAYMENT_METHODS}

    {PAYMENT_HISTORY}

        </td>
        <td width="10%">&nbsp;</td>
    </tr>
    </tbody>
</table>


</body>
</html>','Used for printing out an invoice for the customer.','html');


            $invoice = $invoice_data;

            $customer_data = module_customer::get_customer($invoice_data['customer_id']);
            $contact_data = module_user::get_user($customer_data['primary_user_id']);
            $job_data = module_job::get_job(current($invoice_data['job_ids']));
            $website_data = module_website::get_website($job_data['website_id']);

            ob_start();
            include('template/invoice_task_list.php');
            $task_list_html = ob_get_clean();
            ob_start();
            include('template/invoice_payment_history.php');
            $payment_history = ob_get_clean();
            ob_start();
            include('template/invoice_payment_methods.php');
            $payment_methods = ob_get_clean();


            $project_type = _l(module_config::c('project_name_single','Website'));

            $replace = array(
                'title' => module_config::s('admin_system_name'),
                'invoice_number' => $invoice_data['name'],
                'task_list' => $task_list_html,
                'invoice_paid' => ($invoice_data['total_amount_due'] <= 0) ? '<p> <font style="font-size: 1.6em;"><strong>'._l('INVOICE PAID') .'</strong></font> </p>' : '',
                'due_date' => print_date($invoice_data['date_due']),
                'customer_details' => ' - todo - ',
                'payment_methods' => $payment_methods,
                'customer_name' => htmlspecialchars($customer_data['customer_name']),
                'contact_name' => htmlspecialchars($contact_data['name']),
                'contact_email' => htmlspecialchars($contact_data['email']),
                'project_name' => htmlspecialchars($website_data['name']),
                'job_name' => htmlspecialchars($job_data['name']),
                'project_type' => $project_type,
                'payment_history' => $payment_history,
            );


            ob_start();
            $template = module_template::get_template_by_key('invoice_print');
            $template->assign_values($replace);
            echo $template->render('html');
            $invoice_html = ob_get_clean();
            return $invoice_html;
        }
        return false;
    }

    public function get_install_sql(){
        ob_start();
        //  `job_id` INT(11) NULL, (useto be in invoice table)
        ?>

CREATE TABLE `<?php echo _DB_PREFIX; ?>invoice` (
  `invoice_id` int(11) NOT NULL auto_increment,
  `customer_id` INT(11) NULL,
  `hourly_rate` DECIMAL(10,2) NULL,
  `name` varchar(255) NOT NULL DEFAULT  '',
  `status` varchar(255) NOT NULL DEFAULT  '',
  `total_tax_name` varchar(20) NOT NULL DEFAULT  '',
  `total_tax_rate` DECIMAL(10,2) NULL,
  `date_sent` date NOT NULL,
  `date_due` date NOT NULL,
  `date_paid` date NOT NULL,
  `discount_amount` DECIMAL(10,2) NULL,
  `discount_description` varchar(255) NULL,
  `currency_id` int(11) NOT NULL DEFAULT '0',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY  (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `<?php echo _DB_PREFIX; ?>invoice_item` (
  `invoice_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `task_id` int(11) NULL,
  `hours` decimal(10,2) NULL,
  `amount` decimal(10,2) NULL,
  `completed` decimal(10,2) NULL,
  `description` text NOT NULL,
  `date_due` date NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY (`invoice_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `<?php echo _DB_PREFIX; ?>invoice_payment` (
  `invoice_payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `parent_finance_id` int(11) NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) NOT NULL,
  `currency_id` int(11) NOT NULL DEFAULT '1',
  `date_paid` date NOT NULL,
  `data` LONGBLOB NULL,
  `other_id` VARCHAR( 255 ) NOT NULL DEFAULT '',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY (`invoice_payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `<?php echo _DB_PREFIX; ?>currency` (
  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(4) NOT NULL,
  `symbol` varchar(6) NOT NULL,
  `location` TINYINT( 1 ) NOT NULL DEFAULT  '1',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY (`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `<?php echo _DB_PREFIX; ?>currency` (`currency_id`, `code`, `symbol`, `location`, `create_user_id`, `update_user_id`, `date_created`, `date_updated`) VALUES
(1, 'USD', '$', 1, 0, 1, '2011-11-10', '2011-11-10'),
(2, 'AUD', '$', 1, 1, NULL, '2011-11-10', '2011-11-10');

    <?php
        
        return ob_get_clean();
    }

    public function get_upgrade_sql(){
        $sql = '';

        $fields = get_fields('invoice');
        if(!isset($fields['currency_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `currency_id` int(11) NOT NULL DEFAULT \'1\' AFTER `discount_description`;';
        }
        $fields = get_fields('invoice_payment');
        if(!isset($fields['currency_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice_payment` ADD `currency_id` int(11) NOT NULL DEFAULT \'1\' AFTER `method`;';
        }
        if(!isset($fields['data'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice_payment` ADD  `data` LONGBLOB NULL AFTER  `date_paid`;';
        }
        if(!isset($fields['other_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice_payment` ADD  `other_id` VARCHAR( 255 ) NOT NULL DEFAULT \'\' AFTER  `data`;';
        }
        $res = qa1("SHOW TABLES LIKE '"._DB_PREFIX."currency'");
        if(!count($res)){
            $sql .= 'CREATE TABLE `'._DB_PREFIX.'currency` (
  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(4) NOT NULL,
  `symbol` varchar(6) NOT NULL,
  `location` TINYINT( 1 ) NOT NULL DEFAULT  \'1\',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY (`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            $sql .= "INSERT INTO `"._DB_PREFIX ."currency` (`currency_id`, `code`, `symbol`, `location`, `create_user_id`, `update_user_id`, `date_created`, `date_updated`) VALUES
(1, 'USD', '$', 1, 0, 1, '2011-11-10', '2011-11-10'),
(2, 'AUD', '$', 1, 1, NULL, '2011-11-10', '2011-11-10');";
        }

        return $sql;

    }


    public static function add_history($invoice_id,$message){
		module_note::save_note(array(
			'owner_table' => 'invoice',
			'owner_id' => $invoice_id,
			'note' => $message,
			'rel_data' => self::link_open($invoice_id),
			'note_time' => time(),
		));
	}

    public static function customer_id_changed($old_customer_id, $new_customer_id) {
        $old_customer_id = (int)$old_customer_id;
        $new_customer_id = (int)$new_customer_id;
        if($old_customer_id>0 && $new_customer_id>0){
            $sql = "UPDATE `"._DB_PREFIX."invoice` SET customer_id = ".$new_customer_id." WHERE customer_id = ".$old_customer_id;
            query($sql);
        }
    }

    public static function check_invoice_merge($invoice_id) {
        $invoice_data = self::get_invoice($invoice_id);
        $sql = "SELECT invoice_id FROM `"._DB_PREFIX."invoice` i WHERE";
        $sql .= " invoice_id != ".(int)$invoice_id;
        $sql .= " AND total_tax_rate = '".mysql_real_escape_string($invoice_data['total_tax_rate'])."'";
        $sql .= " AND customer_id = ".(int)$invoice_data['customer_id'];
        $sql .= " AND (date_sent IS NULL OR date_sent = '0000-00-00') ";
        return qa($sql);
    }

    public static function email_sent($invoice_id,$template_name){
        switch($template_name){
            case 'invoice_email_paid':
                self::add_history($invoice_id,_l('Invoice Receipt Emailed Successfully'));
                break;
            default:
                self::add_history($invoice_id,_l('Invoice Emailed Successfully'));


        }
    }

}
