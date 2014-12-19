<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:34 
  * IP Address: 127.0.0.1
  */


class module_finance extends module_base{

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
    public $version = 2.12;
	function init(){
		$this->links = array();
		$this->module_name = "finance";
		$this->module_position = 28;

        module_config::register_css('finance','finance.css');

	}

    public function pre_menu(){

		// the link within Admin > Settings > finances.
		if($this->can_i('view','Finance') && is_file('includes/plugin_finance/pages/finance.php')){
			$this->links[] = array(
				"name"=>"Finance",
				"p"=>"finance",
				"icon"=>"icon.png",
				"args"=>array('finance_id'=>false),
			);
		}


        if(module_security::has_feature_access(array(
				'name' => 'Settings',
				'module' => 'config',
				'category' => 'Config',
				'view' => 1,
				'description' => 'view',
		)) && is_file('includes/plugin_finance/pages/finance.php')){
			$this->links[] = array(
				"name"=>"Finance",
				"p"=>"finance_settings",
				"icon"=>"icon.png",
				"args"=>array('finance_id'=>false),
				'holder_module' => 'config', // which parent module this link will sit under.
				'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
				'menu_include_parent' => 0,
			);
		}
                
    }


    public static function link_generate($finance_id=false,$options=array(),$link_options=array()){

        $key = 'finance_id';
        if($finance_id === false && $link_options){
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
        if(!isset($options['type']))$options['type']='finance';
        if(!isset($options['page'])){
            if($finance_id && !isset($link_options['stop_bubble'])){
                $options['page'] = 'finance_edit';
            }else{
                $options['page'] = 'finance';
            }
        }

        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['finance_id'] = $finance_id;
        $options['module'] = 'finance';
        if(isset($options['data'])){
            $data = $options['data'];
        }else{
            $data = self::get_finance($finance_id,false);
        }
        $options['data'] = $data;
        // what text should we display in this link?
        $options['text'] = (!isset($data['name'])||!trim($data['name'])) ? 'N/A' : $data['name'];
        if(($options['page']=='recurring' || $options['page']=='finance_edit') && !isset($link_options['stop_bubble'])){
            $link_options['stop_bubble']=true;
            $bubble_to_module = array(
                'module' => 'finance',
                'argument' => 'finance_id',
            );
        }
        array_unshift($link_options,$options);

        if($bubble_to_module){
            global $plugins;
            return $plugins[$bubble_to_module['module']]->link_generate(false,array(),$link_options);
        }else{
            // return the link as-is, no more bubbling or anything.
            // pass this off to the global link_generate() function
            return link_generate($link_options);

        }
    }

	public static function link_open($finance_id,$full=false){
        return self::link_generate($finance_id,array('full'=>$full));
    }
	public static function link_open_recurring($finance_recurring_id,$full=false,$data=array()){
        return self::link_generate(false,array('full'=>$full,'page'=>'recurring','arguments'=>array(
                                                            'finance_recurring_id' => $finance_recurring_id,
                                                        ),'data'=>$data));
    }
	public static function link_open_record_recurring($finance_recurring_id,$full=false,$data=array()){
        return self::link_generate('new',array('full'=>$full,'page'=>'recurring','arguments'=>array(
                                                            'record_new' => 1,
                                                            'finance_recurring_id' => $finance_recurring_id,
                                                        ),'data'=>$data));
    }


    public function process(){
        switch($_REQUEST['_process']){
            case 'quick_save_finance':

                // check for date / name at least.
                $date = trim($_REQUEST['transaction_date']);
                $name = trim($_REQUEST['name']);
                if(!$date || !$name){
                    redirect_browser(module_finance::link_open(false));
                }
                $credit = trim($_REQUEST['credit']);
                $debit = trim($_REQUEST['debit']);
                if($credit > 0){
                    $_POST['type'] = 'i';
                    $_POST['amount'] = $credit;
                }else{
                    $_POST['type'] = 'e';
                    $_POST['amount'] = $debit;
                }
                
            case 'save_finance':
                if(isset($_REQUEST['butt_del'])){
                    $this->delete($_REQUEST['finance_id']);
                    redirect_browser(self::link_open(false));
                }
                if(isset($_REQUEST['butt_unlink'])){
                    // unlink this finance_id from other finance_ids.
                    $sql = "UPDATE `"._DB_PREFIX."finance` SET parent_finance_id = 0 WHERE parent_finance_id = '".(int)$_REQUEST['finance_id']."'";
                    query($sql);
                    $sql = "UPDATE `"._DB_PREFIX."invoice_payment` SET parent_finance_id = 0 WHERE parent_finance_id = '".(int)$_REQUEST['finance_id']."'";
                    query($sql);
                    redirect_browser(self::link_open(false));
                }
                $temp_data = $this->get_finance($_REQUEST['finance_id']);
                $data = $_POST + $temp_data;
                // save the finance categories and account.
                $account_id = $_REQUEST['finance_account_id'];
                if((string)(int)$account_id != (string)$account_id && strlen($account_id) > 2){
                    // we have a new account to create.
                    $account_id = update_insert('finance_account_id','new','finance_account',array('name'=>$account_id));
                }
                $data['finance_account_id'] = $account_id;
                $finance_id = update_insert('finance_id',isset($_REQUEST['finance_id']) ? $_REQUEST['finance_id'] : 'new','finance',$data);


                $category_ids = isset($_REQUEST['finance_category_id']) && is_array($_REQUEST['finance_category_id']) ? $_REQUEST['finance_category_id'] : array();
                $sql = "DELETE FROM `"._DB_PREFIX."finance_category_rel` WHERE finance_id = $finance_id";
                query($sql);
                foreach($category_ids as $category_id){
                    $category_id = (int)$category_id;
                    if($category_id <= 0)continue;
                    $sql = "REPLACE INTO `"._DB_PREFIX."finance_category_rel` SET finance_id = $finance_id, finance_category_id = $category_id";
                    query($sql);
                }
                if(isset($_REQUEST['finance_category_new']) && strlen(trim($_REQUEST['finance_category_new'])) > 0){
                    $category_name = trim($_REQUEST['finance_category_new']);
                    $category_id = update_insert('finance_category_id','new','finance_category',array('name'=>$category_name));
                    if(isset($_REQUEST['finance_category_new_checked'])){
                        $sql = "REPLACE INTO `"._DB_PREFIX."finance_category_rel` SET finance_id = $finance_id, finance_category_id = $category_id";
                        query($sql);
                    }
                }

                if(isset($_REQUEST['invoice_payment_id']) && (int)$_REQUEST['invoice_payment_id']>0){
                    // link this as a child invoice payment to this one.
                    update_insert('invoice_payment_id',$_REQUEST['invoice_payment_id'],'invoice_payment',array('parent_finance_id'=>$finance_id));
                }
                if(isset($_REQUEST['finance_recurring_id']) && (int)$_REQUEST['finance_recurring_id']>0){
                    // if we have set a custom "next recurring date" then we don't recalculate this date unless we are saving a new finance id.
                    $recurring = self::get_recurring($_REQUEST['finance_recurring_id']);
                    if(!(int)$_REQUEST['finance_id'] || !$recurring['next_due_date_custom']){
                        self::calculate_recurring_date((int)$_REQUEST['finance_recurring_id'],true);
                    }
                    // we also have to adjust the starting balance of our recurring amount by this amount.
                    // just a little helpful feature.
                    if(!(int)$_REQUEST['finance_id']){
                        $balance=module_config::c('finance_recurring_start_balance',0);
                        if($balance!=0){
                            if($data['type']=='e')$balance-=$data['amount'];
                            else if($data['type']=='i')$balance+=$data['amount'];
                            module_config::save_config('finance_recurring_start_balance',$balance);
                        }
                    }

                    // redirect back to recurring listing.
                    set_message('Recurring transaction saved successfully');
                    redirect_browser(self::link_open_recurring(false));
                }

                set_message('Transaction saved successfully');
                //redirect_browser(self::link_open($finance_id,false));
                redirect_browser(self::link_open(false,false));
                break;
            case 'save_recurring':
                if(isset($_REQUEST['butt_del'])){
                    $this->delete_recurring($_REQUEST['finance_recurring_id']);
                    redirect_browser(self::link_open_recurring(false));
                }
                $data = $_POST;
                // save the finance categories and account.
                $account_id = $_REQUEST['finance_account_id'];
                if((string)(int)$account_id != (string)$account_id && strlen($account_id) > 2){
                    // we have a new account to create.
                    $account_id = update_insert('finance_account_id','new','finance_account',array('name'=>$account_id));
                }
                if(isset($_REQUEST['finance_recurring_id']) && (int)$_REQUEST['finance_recurring_id']){
                    $original_finance_recurring = self::get_recurring($_REQUEST['finance_recurring_id']);
                }else{
                    $original_finance_recurring = array();
                }
                    
                $data['finance_account_id'] = $account_id;
                $finance_recurring_id = update_insert('finance_recurring_id',isset($_REQUEST['finance_recurring_id']) ? $_REQUEST['finance_recurring_id'] : 'new','finance_recurring',$data);

                if((int)$finance_recurring_id>0){
                    $category_ids = isset($_REQUEST['finance_category_id']) && is_array($_REQUEST['finance_category_id']) ? $_REQUEST['finance_category_id'] : array();
                    $sql = "DELETE FROM `"._DB_PREFIX."finance_recurring_catrel` WHERE finance_recurring_id = $finance_recurring_id";
                    query($sql);
                    foreach($category_ids as $category_id){
                        $category_id = (int)$category_id;
                        if($category_id <= 0)continue;
                        $sql = "REPLACE INTO `"._DB_PREFIX."finance_recurring_catrel` SET finance_recurring_id = $finance_recurring_id, finance_category_id = $category_id";
                        query($sql);
                    }
                    if(isset($_REQUEST['finance_category_new']) && strlen(trim($_REQUEST['finance_category_new'])) > 0){
                        $category_name = trim($_REQUEST['finance_category_new']);
                        $category_id = update_insert('finance_category_id','new','finance_category',array('name'=>$category_name));
                        if(isset($_REQUEST['finance_category_new_checked'])){
                            $sql = "REPLACE INTO `"._DB_PREFIX."finance_recurring_catrel` SET finance_recurring_id = $finance_recurring_id, finance_category_id = $category_id";
                            query($sql);
                        }
                    }
                    $calculated_next_date = self::calculate_recurring_date($finance_recurring_id);

                    if(isset($data['set_next_due_date']) && $data['set_next_due_date']){
                        $next_date = input_date($data['set_next_due_date']);
                        $next_due_date_real = module_finance::calculate_recurring_date($finance_recurring_id,true,false);
                        if($next_date != $next_due_date_real){
                            // we have accustom date.
                            update_insert('finance_recurring_id',$finance_recurring_id,'finance_recurring',array(
                                    'next_due_date'=>$next_date,
                                    'next_due_date_custom'=>1,
                               )
                            );
                        }else{
                            // date is the same. not doing a custom date any more
                            update_insert('finance_recurring_id',$finance_recurring_id,'finance_recurring',array(
                                    'next_due_date'=>$next_due_date_real,
                                    'next_due_date_custom'=>0,
                               )
                            );
                        }
                    }
/*
                    $finance_recurring = self::get_recurring($finance_recurring_id);
                    if($finance_recurring['next_due_date_custom']){
                        $next_due_date_real = module_finance::calculate_recurring_date($finance_recurring_id,true,false);
                        // unset the "custom" flag if we've picked the same date as what it should be.
                        if($next_due_date_real == $finance_recurring['next_due_date']){
                            module_finance::calculate_recurring_date($finance_recurring_id,true,true);
                        }
                    }*/
                }


                set_message('Recurring transaction saved successfully');
                //redirect_browser(self::link_open($finance_id,false));
                redirect_browser(self::link_open_recurring(false,false));
                break;
        }

	}
		
	function delete($finance_id){
		$finance_id=(int)$finance_id;
        $finance = $this->get_finance($finance_id);
		$sql = "DELETE FROM "._DB_PREFIX."finance WHERE finance_id = '".$finance_id."' LIMIT 1";
		query($sql);
		$sql = "UPDATE "._DB_PREFIX."finance SET parent_finance_id = 0 WHERE parent_finance_id = '".$finance_id."'";
		query($sql);
		$sql = "UPDATE "._DB_PREFIX."invoice_payment SET parent_finance_id = 0 WHERE parent_finance_id = '".$finance_id."'";
		query($sql);
        if(isset($finance['finance_recurring_id']) && $finance['finance_recurring_id']){
            $this->calculate_recurring_date($finance['finance_recurring_id'],true);
        }
	}


    public static function get_finance($finance_id,$full=true){
        $finance_id = (int)$finance_id;
        if($finance_id>0){
            if(!$full)return get_single("finance","finance_id",$finance_id);

            $sql = "SELECT f.* ";
            $sql .= " , fa.name AS account_name ";
            $sql .= " , GROUP_CONCAT(fc.`name` ORDER BY fc.`name` ASC SEPARATOR ', ') AS categories ";
            $sql .= " FROM `"._DB_PREFIX."finance` f ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."finance_account` fa USING (finance_account_id) ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."finance_category_rel` fcr ON f.finance_id = fcr.finance_id ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."finance_category` fc ON fcr.finance_category_id = fc.finance_category_id ";
            $sql .= " WHERE f.finance_id = $finance_id ";
            $sql .= " GROUP BY f.finance_id ";
            $sql .= " ORDER BY f.transaction_date DESC ";
            $finance = array_shift(qa($sql));

            // get the categories.
            $finance['category_ids'] = get_multiple('finance_category_rel',array('finance_id'=>$finance_id),'finance_category_id');

            // get any linked items.

            $linked_finances = $linked_invoice_payments = array();
            // find any child / linked transactions to this one.
            if((int)$finance_id > 0 && $finance['parent_finance_id']){
                // todo - this could cause problems! 
                $foo = module_finance::get_finance($finance['parent_finance_id'],false);
                if($foo['finance_id'] != $finance_id){
                    // copied from get_finances() method
                    $foo['url'] = module_finance::link_open($foo['finance_id'],false);
                    $foo['credit'] = $foo['type'] == 'i' ? $foo['amount'] : 0;
                    $foo['debit'] = $foo['type'] == 'e' ? $foo['amount'] : 0;
                    if(!isset($foo['categories'])){
                        $foo['categories'] = '';
                    }
                    if(!isset($foo['account_name'])){
                        $foo['account_name'] = '';
                    }
                    $linked_finances[] = $foo;
                }
                // find any child finances that are also linked to this parent finance.
                foreach(module_finance::get_finances_simple(array('parent_finance_id'=>$finance['parent_finance_id'])) as $foo){
                    if($foo['finance_id'] != $finance_id){
                        // copied from get_finances() method
                        $foo['url'] = module_finance::link_open($foo['finance_id'],false);
                        $foo['credit'] = $foo['type'] == 'i' ? $foo['amount'] : 0;
                        $foo['debit'] = $foo['type'] == 'e' ? $foo['amount'] : 0;
                        if(!isset($foo['categories'])){
                            $foo['categories'] = '';
                        }
                        if(!isset($foo['account_name'])){
                            $foo['account_name'] = '';
                        }
                        $linked_finances[] = $foo;
                    }
                }
                // find any child invoice payments that are also linked to this parent finance
                foreach(get_multiple('invoice_payment',array('parent_finance_id'=>$finance['parent_finance_id'])) as $invoice_payments){

                    // copied from get_finances() method
                    $invoice_data = module_invoice::get_invoice($invoice_payments['invoice_id'],true);
                    $invoice_payments['url'] = module_finance::link_open('new',false).'&invoice_payment_id='.$invoice_payments['invoice_payment_id'];
                    $invoice_payments['name'] = _l('Invoice Payment');
                    $invoice_payments['description'] = _l('Payment against invoice <a href="%s">#%s</a> via "%s" method',module_invoice::link_open($invoice_payments['invoice_id'],false),$invoice_data['name'],$invoice_payments['method']);
                    $invoice_payments['credit'] = $invoice_payments['amount'];
                    $invoice_payments['debit'] = 0;
                    $invoice_payments['account_name'] = '';
                    $invoice_payments['categories'] = '';
                    $invoice_payments['transaction_date'] = $invoice_payments['date_paid'];
                    $linked_invoice_payments [] = $invoice_payments;
                }
            }
            if((int)$finance_id > 0){
                // find any child finances that are linked to this finance.
                foreach(module_finance::get_finances_simple(array('parent_finance_id'=>$finance_id)) as $foo){
                    if($foo['finance_id'] != $finance_id){
                        // copied from get_finances() method
                        $foo['url'] = module_finance::link_open($foo['finance_id'],false);
                        $foo['credit'] = $foo['type'] == 'i' ? $foo['amount'] : 0;
                        $foo['debit'] = $foo['type'] == 'e' ? $foo['amount'] : 0;
                        if(!isset($foo['categories'])){
                            $foo['categories'] = '';
                        }
                        if(!isset($foo['account_name'])){
                            $foo['account_name'] = '';
                        }
                        $linked_finances[] = $foo;
                    }
                }
                // find any child invoice payments that are also linked to this parent finance
                foreach(get_multiple('invoice_payment',array('parent_finance_id'=>$finance_id)) as $invoice_payments){

                    // copied from get_finances() method
                    $invoice_data = module_invoice::get_invoice($invoice_payments['invoice_id'],true);
                    $invoice_payments['url'] = module_finance::link_open('new',false).'&invoice_payment_id='.$invoice_payments['invoice_payment_id'];
                    $invoice_payments['name'] = _l('Invoice Payment');
                    $invoice_payments['description'] = _l('Payment against invoice <a href="%s">#%s</a> via "%s" method',module_invoice::link_open($invoice_payments['invoice_id'],false),$invoice_data['name'],$invoice_payments['method']);
                    $invoice_payments['credit'] = $invoice_payments['amount'];
                    $invoice_payments['debit'] = 0;
                    $invoice_payments['account_name'] = '';
                    $invoice_payments['categories'] = '';
                    $invoice_payments['transaction_date'] = $invoice_payments['date_paid'];
                    $linked_invoice_payments [] = $invoice_payments;
                }
            }

            $finance['linked_invoice_payments'] = $linked_invoice_payments;
            $finance['linked_finances'] = $linked_finances;


            return $finance;
        }else{

            $finance = array(
                'finance_id' => 0,
                'parent_finance_id' => 0,
                'transaction_date' => print_date(time()),
                'name' => '',
                'description' => '',
                'type' => 'e',
                'amount' => 0,
                'category_ids' => array(),
            );
            if(isset($_REQUEST['invoice_payment_id']) && (int)$_REQUEST['invoice_payment_id'] > 0){
                $invoice_payment_data = module_invoice::get_invoice_payment((int)$_REQUEST['invoice_payment_id']);
                if($invoice_payment_data && $invoice_payment_data['invoice_id']){
                    $invoice_data = module_invoice::get_invoice($invoice_payment_data['invoice_id'],true);
                    $finance['transaction_date'] = $invoice_payment_data['date_paid'];
                    $finance['name'] = _l('Invoice Payment');
                    $finance['description'] = _l('Payment against invoice #%s via "%s" method',$invoice_data['name'],$invoice_payment_data['method']);
                    $finance['type'] = 'i';
                    $finance['amount'] = $invoice_payment_data['amount'];
                }
            }
            return $finance;
        }
	}

    public static function get_recurrings($search){
        $sql = "SELECT r.*  ";
        $sql .= ", f.amount AS last_amount ";
        $sql .= ", f.transaction_date AS last_transaction_date ";
        $sql .= ", f.finance_id AS last_transaction_finance_id ";
        $sql .= " , fa.name AS account_name ";
        $sql .= " , (SELECT GROUP_CONCAT(fc.`name` ORDER BY fc.`name` ASC SEPARATOR ', ') FROM `"._DB_PREFIX."finance_recurring_catrel` fcr LEFT JOIN `"._DB_PREFIX."finance_category` fc ON fcr.finance_category_id = fc.finance_category_id WHERE fcr.finance_recurring_id = r.finance_recurring_id) AS categories";
        $sql .= " FROM `"._DB_PREFIX."finance_recurring` r ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."finance` f ON r.finance_recurring_id = f.finance_recurring_id ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."finance_account` fa ON r.finance_account_id = fa.finance_account_id ";
        $sql .= " WHERE 1";
        $sql .= " AND ( f.finance_id IS NULL or f.finance_id = (SELECT ff.finance_id FROM `"._DB_PREFIX."finance` ff WHERE ff.finance_recurring_id = r.finance_recurring_id ORDER BY transaction_date DESC LIMIT 1) )";
        if(isset($search['show_finished']) && $search['show_finished']){
            $sql .= " ";
        }else{
            $sql .= " AND r.next_due_date IS NOT NULL AND r.next_due_date != '0000-00-00' ";
        }
        $sql .= " ORDER BY next_due_date ASC ";
        return qa($sql);
        //return get_multiple('finance_recurring',$search,'finance_recurring_id');
    }
    public static function get_recurring($finance_recurring_id){
        // show last transaction etc..
        $finance_recurring_id = (int)$finance_recurring_id;
        if($finance_recurring_id > 0){
            //return get_single('finance_recurring','finance_recurring_id',$finance_recurring_id);

            $sql = "SELECT r.*  ";
            $sql .= ", f.amount AS last_amount ";
            $sql .= ", f.transaction_date AS last_transaction_date ";
            $sql .= ", f.finance_id AS last_transaction_finance_id ";
            $sql .= " , fa.name AS account_name ";
            $sql .= " , (SELECT GROUP_CONCAT(fc.`name` ORDER BY fc.`name` ASC SEPARATOR ', ') FROM `"._DB_PREFIX."finance_recurring_catrel` fcr LEFT JOIN `"._DB_PREFIX."finance_category` fc ON fcr.finance_category_id = fc.finance_category_id WHERE fcr.finance_recurring_id = r.finance_recurring_id) AS categories";
            $sql .= " FROM `"._DB_PREFIX."finance_recurring` r ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."finance` f ON r.finance_recurring_id = f.finance_recurring_id ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."finance_account` fa ON r.finance_account_id = fa.finance_account_id ";
            $sql .= " WHERE 1";
            $sql .= " AND ( f.finance_id IS NULL or f.finance_id = (SELECT ff.finance_id FROM `"._DB_PREFIX."finance` ff WHERE ff.finance_recurring_id = r.finance_recurring_id ORDER BY transaction_date DESC LIMIT 1) )";
            $sql .= " AND r.finance_recurring_id = $finance_recurring_id";
            $recurring = qa1($sql);
            $recurring['category_ids'] = get_multiple('finance_recurring_catrel',array('finance_recurring_id'=>$finance_recurring_id),'finance_category_id');
            return $recurring;
        }else{
            return array(
                'name' => '',
                'description' => '',
                'finance_account_id' => '',
                'start_date' => '',
                'end_date' => '',
                'amount' => '',
                'days' => '0',
                'months' => '0',
                'years' => '0',
                'type' => 'e',
                'category_ids' => array(),
            );
        }
        
    }
    public static function get_finances_simple($search){
        return get_multiple('finance',$search,'finance_id');
    }

	public static function get_finances($search=array()){
		// we have to search for recent transactions. this involves combining the "finance" table with the "invoice_payment" table
        // then sort the results by date
        $hide_invoice_payments = false;
        $sql = "SELECT f.* ";
        $sql .= " , fa.name AS account_name ";
        $sql .= " , GROUP_CONCAT(fc.`name` ORDER BY fc.`name` ASC SEPARATOR ', ') AS categories ";
        $sql .= " FROM `"._DB_PREFIX."finance` f ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."finance_account` fa USING (finance_account_id) ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."finance_category_rel` fcr ON f.finance_id = fcr.finance_id ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."finance_category` fc ON fcr.finance_category_id = fc.finance_category_id ";
        $sql .= " WHERE 1 ";
        if(isset($search['generic']) && strlen(trim($search['generic']))){
            $name = mysql_real_escape_string(trim($search['generic']));
            $sql .= " AND (f.`name` LIKE '%$name%' OR f.description LIKE '%$name%' )";
        }
        if(isset($search['finance_account_id']) && $search['finance_account_id']){
            $sql .= " AND f.finance_account_id = '".(int)$search['finance_account_id']."'";
            $hide_invoice_payments = true;
        }
        if(isset($search['finance_recurring_id']) && $search['finance_recurring_id']){
            $sql .= " AND f.finance_recurring_id = '".(int)$search['finance_recurring_id']."'";
            $hide_invoice_payments = true;
        }
        if(isset($search['finance_category_id']) && $search['finance_category_id']){
            $sql .= " AND fcr.finance_category_id = '".(int)$search['finance_category_id']."'";
            $hide_invoice_payments = true;
        }

        if(isset($search['date_from']) && $search['date_from'] != ''){
            $sql .= " AND f.transaction_date >= '".input_date($search['date_from'])."'";
        }
        if(isset($search['date_to']) && $search['date_to'] != ''){
            $sql .= " AND f.transaction_date <= '".input_date($search['date_to'])."'";
        }

        $sql .= " GROUP BY f.finance_id ";
        $sql .= " ORDER BY f.transaction_date DESC ";
        $finances1 = qa($sql);
        // invoice payments:
        $finances2 = array();
        if(!$hide_invoice_payments){
            $sql = "SELECT p.* ";
            $sql .= " , p.date_paid AS transaction_date";
            $sql .= " FROM `"._DB_PREFIX."invoice_payment` p ";
            $sql .= " WHERE p.date_paid != '0000-00-00' ";
            $sql .= " AND p.`amount` > 0 ";
            if(isset($search['date_from']) && $search['date_from'] != ''){
                $sql .= " AND p.date_paid >= '".input_date($search['date_from'])."'";
            }
            if(isset($search['date_to']) && $search['date_to'] != ''){
                $sql .= " AND p.date_paid <= '".input_date($search['date_to'])."'";
            }
            $sql .= " ORDER BY p.date_paid DESC ";
            $finances2 = qa($sql);
        }
        $finances = array_merge($finances1,$finances2);
        unset($finances1);
        unset($finances2);
        // sort this
        if(!function_exists('sort_finance')){
            function sort_finance($a,$b){
                $t1 = strtotime($a['transaction_date']);
                $t2 = strtotime($b['transaction_date']);
                if($t1==$t2){
                    // sort by amount
                    return $a['amount'] > $b['amount'];
                }else{
                    return $t1<$t2;
                }
            }
        }
        uasort($finances,'sort_finance');

        foreach($finances as $finance_id => $finance){
            // we load each of these transactions
            // transaction can be a "transaction" or an "invoice_payment"

            // find out if this transaction is a child transaction to another transaction.
            // if it is a child transaction and we haven't already dispayed it in this listing
            // then we find the parent transaction and display it along with all it's children in this place.
            // this wont be perfect all the time but will be awesome in 99% of cases.

            if(isset($finance['invoice_payment_id']) && $finance['invoice_payment_id'] && isset($finance['invoice_id']) && $finance['invoice_id']){
                // this is an invoice payment (incoming payment)
                // displayed before already?
                if(isset($displayed_invoice_payment_ids[$finance['invoice_payment_id']])){
                    $finances[$displayed_invoice_payment_ids[$finance['invoice_payment_id']]]['link_count']++;
                    unset($finances[$finance_id]);
                    continue;
                }
                $displayed_invoice_payment_ids[$finance['invoice_payment_id']] = $finance_id; // so we dont display again.
            }else if(isset($finance['finance_id']) && $finance['finance_id']){
                // displayed before already?
                if(isset($displayed_finance_ids[$finance['finance_id']])){
                    $finances[$displayed_finance_ids[$finance['finance_id']]]['link_count']++;
                    unset($finances[$finance_id]);
                    continue;
                }
                $displayed_finance_ids[$finance['finance_id']] = $finance_id;
            }else{
                // nfi?
                unset($finances[$finance_id]);
                continue;
            }


            if(isset($finance['parent_finance_id']) && $finance['parent_finance_id']){
                // check if it's parent finance id has been displayed already somewhere.
                if(isset($displayed_finance_ids[$finance['parent_finance_id']])){

                    $finances[$displayed_finance_ids[$finance['parent_finance_id']]]['link_count']++;
                    unset($finances[$finance_id]);
                    continue; // already done it on this page.
                }
                $displayed_finance_ids[$finance['parent_finance_id']] = $finance_id;
                // we haven't displayed the parent one yet.
                // display the parent one in this listing.
                $finance = self::get_finance($finance['parent_finance_id']);
            }

            if(isset($finance['invoice_payment_id']) && $finance['invoice_payment_id'] && isset($finance['invoice_id']) && $finance['invoice_id']){
                // doesn't have an finance / account reference just yet.
                // but they can create one and this will become a child entry to it.
                $invoice_data = module_invoice::get_invoice($finance['invoice_id'],true);
                $finance['url'] = self::link_open('new',false).'&invoice_payment_id='.$finance['invoice_payment_id'];
                $finance['name'] = _l('Invoice Payment');
                $finance['description'] = _l('Payment against invoice <a href="%s">#%s</a> via "%s" method',module_invoice::link_open($finance['invoice_id'],false),$invoice_data['name'],$finance['method']);
                $finance['credit'] = $finance['amount'];
                $finance['debit'] = 0;
                $finance['account_name'] = '';
                $finance['categories'] = '';
            }else if(isset($finance['finance_id']) && $finance['finance_id']){
                $finance['url'] = self::link_open($finance['finance_id'],false);
                $finance['credit'] = $finance['type'] == 'i' ? $finance['amount'] : 0;
                $finance['debit'] = $finance['type'] == 'e' ? $finance['amount'] : 0;
                if(!isset($finance['categories'])){
                    $finance['categories'] = '';
                }
                if(!isset($finance['account_name'])){
                    $finance['account_name'] = '';
                }
            }

            $finance['link_count'] = 0;

            $finances[$finance_id] = $finance;
        }
        return $finances;
	}



    public static function get_accounts()
    {
        return get_multiple('finance_account',false,'finance_account_id','exact','name');
    }

    public static function get_categories()
    {
        return get_multiple('finance_category',false,'finance_category_id','exact','name');

    }

    public static function handle_link_transactions()
    {
        $link_invoice_payment_ids = (isset($_REQUEST['link_invoice_payment_ids']) && is_array($_REQUEST['link_invoice_payment_ids'])) ? $_REQUEST['link_invoice_payment_ids'] : array();
        $link_finance_ids = (isset($_REQUEST['link_finance_ids']) && is_array($_REQUEST['link_finance_ids'])) ? $_REQUEST['link_finance_ids'] : array();
        if(count($link_invoice_payment_ids) || count($link_finance_ids)){
            // success we can link!
            if(!count($link_finance_ids)){
                set_error('Please select at least one transaction that is not an invoice payment.');
                redirect_browser(self::link_open(false));
            }
            $parent_finance_id = (int)key($link_finance_ids);
            
            if($parent_finance_id > 0){
                // we have a parent! woo!
                unset($link_finance_ids[$parent_finance_id]);
                foreach($link_finance_ids as $link_finance_id => $tf){
                    $link_finance_id = (int)$link_finance_id;
                    if(strlen($tf) && $link_finance_id > 0){
                        // create this link.
                        $sql = "UPDATE `"._DB_PREFIX."finance` SET parent_finance_id = $parent_finance_id WHERE finance_id = $link_finance_id LIMIT 1";
                        query($sql);
                    }
                }
                foreach($link_invoice_payment_ids as $link_invoice_payment_id => $tf){
                    $link_invoice_payment_id = (int)$link_invoice_payment_id;
                    if(strlen($tf) && $link_invoice_payment_id > 0){
                        // create this link.
                        $sql = "UPDATE `"._DB_PREFIX."invoice_payment` SET parent_finance_id = $parent_finance_id WHERE invoice_payment_id = $link_invoice_payment_id LIMIT 1";
                        query($sql);
                    }
                }
            }
        }
        set_message('Linking success');
        redirect_browser(self::link_open(false));
    }

    private function delete_recurring($finance_recurring_id)
    {
        $finance_recurring_id=(int)$finance_recurring_id;
        $sql = "DELETE FROM `"._DB_PREFIX."finance_recurring` WHERE finance_recurring_id = '".$finance_recurring_id."' LIMIT 1";
        query($sql);
        $sql = "UPDATE `"._DB_PREFIX."finance` SET finance_recurring_id = 0 WHERE finance_recurring_id = '$finance_recurring_id'";
        query($sql);

    }

    public static function calculate_recurring_date($finance_recurring_id,$force=false,$update_db=true) {

        $recurring = self::get_recurring($finance_recurring_id);
        if($recurring['next_due_date_custom'] && !$force){
            return $recurring['next_due_date'];
        }

            $data=array();
            $data['next_due_date'] = '';
            $data['next_due_date_custom'] = '0';
             // work out next due date from the start date or from last transaction date.
            $last_transaction = $recurring['last_transaction_date'];
            if(!$last_transaction || $last_transaction == '0000-00-00' || $last_transaction== '0000-00-00 00:00:00'){
                // no last transaction date!
                // use the start date?
                $last_transaction = $recurring['start_date'];
                if(!$last_transaction || $last_transaction == '0000-00-00'){
                    // default to todays date.
                    $last_transaction = date('Y-m-d');
                }
                $next_time = strtotime($last_transaction);
            }else{
                // check if the start date has increased past the last transaction date.
                $start_time = strtotime($recurring['start_date']);
                $last_transaction_time = strtotime($last_transaction);
                if(isset($_REQUEST['reset_start']) && $start_time > $last_transaction_time){
                    // todo - set this as a flag - a button they click to reset the counter from "this date" onwards
                    // without doing this then recording a paymetn early will not set the correct recurring date from that time.
                    $next_time = $start_time;
                }else{
                    // there was a previous one - base our time off that.
                    // only if it's not a once off..
                    if(!$recurring['days']&&!$recurring['months']&&!$recurring['years']){
                        // it's a once off..
                        $next_time = 9999999999;
                        $recurring['end_date'] = '1970-01-02';
                    }else{
                        // work out when the next one will be.
                        $next_time = strtotime($last_transaction);
                        $next_time = strtotime('+'.abs((int)$recurring['days']).' days',$next_time);
                        $next_time = strtotime('+'.abs((int)$recurring['months']).' months',$next_time);
                        $next_time = strtotime('+'.abs((int)$recurring['years']).' years',$next_time);
                    }
                }
            }
            $end_time = ($recurring['end_date'] && $recurring['end_date'] != '0000-00-00') ? strtotime($recurring['end_date']) : 0;
            if($end_time > 0 && $next_time > $end_time){
                $data['next_due_date'] = '0000-00-00';
            }else{
                $data['next_due_date'] = date('Y-m-d',$next_time);
            }
            if($update_db){
                update_insert('finance_recurring_id',$finance_recurring_id,'finance_recurring',$data);
            }
        return $data['next_due_date'];
    }

    public function handle_hook($hook,$mod=false){
        switch($hook){
            case 'dashboard':
                include('pages/dashboard_summary.php');
                // not in lite edition:
                if(is_file(dirname(__FILE__).'/pages/finance_quick.php')){
                    include('pages/finance_quick.php');
                }
                return false;
                break;
			case "home_alerts":
				$alerts = array();
                if($mod!='calendar' && module_config::c('finance_alerts',1)){
                    // find any jobs that are past the due date and dont have a finished date.
                    $sql = "SELECT * FROM `"._DB_PREFIX."finance_recurring` r ";
                    $sql .= " WHERE r.next_due_date != '0000-00-00' AND r.next_due_date <= '".
                        date('Y-m-d',strtotime('+'.module_config::c('finance_alert_days_in_future',14).' days'))."'";
                    $upcoming_finances = qa($sql);
                    foreach($upcoming_finances as $finance){
                        $alert_res = process_alert($finance['next_due_date'], _l('Recurring Transaction Due'), module_config::c('finance_alert_days_in_future',14));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open_recurring($finance['finance_recurring_id']);
                            $alert_res['name'] = ($finance['type']=='i' ? '+'.dollar($finance['amount']) : '') . ($finance['type']=='e' ? '-'.dollar($finance['amount']) : '') . ' ('.$finance['name'].')';
                            $alerts[] = $alert_res;
                        }
                    }
				}
				return $alerts;
				break;
        }
    }

    public static function get_finance_summary($week_start,$week_end,$multiplyer=1,$row_limit=7){

        $base_href = module_finance::link_generate(false,array('full'=>false,'page'=>'dashboard_popup','arguments'=>array(
                                                                'display_mode' => 'ajax',
                                                            )),array('foo'));
        $base_href .= '&';
        /*$base_href .= (strpos($base_href,'?')!==false) ? '&' : '?';
        $base_href .= 'display_mode=ajax&';
        $base_href .= 'home_page_stats=true&';*/

        // init structure:
        if($multiplyer>1)$row_limit++;
        for($x=0;$x<$row_limit;$x++){
            //$time = strtotime("+$x days",strtotime($week_start));
            $time = strtotime("+" . ($x*$multiplyer)." days",strtotime($week_start));
            $data[date("Ymd",$time)] = array(
                "day" => $time,
                "hours" => 0,
                "amount" => 0,
                "amount_invoiced" => 0,
                "amount_paid" => 0,
                "amount_spent" => 0,
            );
            if(class_exists('module_envato',false)){
                $data[date("Ymd",$time)]['envato_earnings'] = 0;
            }
        }
        $data['total']=array(
            'day'=>_l('Totals:'),
            'week'=>_l('Totals:'),
            'hours'=>0,
            'amount'=>0,
            'amount_invoiced'=>0,
            'amount_paid'=>0,
            'amount_spent'=>0,
        );
        if(class_exists('module_envato',false)){
            $data['total']['envato_earnings'] = 0;
        }
        // find all task LOGS completed within these dayes
        $sql = "SELECT tl.date_created, t.hours AS task_hours, t.amount, tl.hours AS hours_logged, p.job_id, p.hourly_rate ";
        $sql .= " FROM `"._DB_PREFIX."task_log` tl ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON tl.task_id = t.task_id ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."job` p ON tl.job_id = p.job_id";
        $sql .= " WHERE tl.date_created >= '$week_start' AND tl.date_created < '$week_end'";
        //echo $sql;
        $tasks = query($sql);
        $logged_tasks = array();
        while($task = mysql_fetch_assoc($tasks)){
            if($multiplyer > 1){
                $week_day = date('w',strtotime($task['date_created'])) - 1;
                $task['date_created'] = date('Y-m-d',strtotime('-'.$week_day.' days',strtotime($task['date_created'])));
            }
            $key = date("Ymd",strtotime($task['date_created']));
            $hours_logged = ($task['task_hours'] > 0 ? $task['hours_logged'] : 0);
            $data[$key]['hours'] += $hours_logged;
            $data['total']['hours'] += $hours_logged;
            $hourly_rate = $task['hourly_rate'];
            if($hours_logged > 0 && $task['amount'] > 0 && $hourly_rate > 0){
                // there is a custom amount assigned to thsi task.
                // only calculate this amount if the full hours is complete.
                $hourly_rate = $task['amount'] / $task['task_hours'];
            }
            if($hours_logged > 0 && $hourly_rate > 0){
                $data[$key]['amount'] += ($hours_logged * $hourly_rate);
                $data['total']['amount'] += ($hours_logged * $hourly_rate);
            }
        }

        // find invoices sent this week.
        $sql = "SELECT i.* ";
        $sql .= " FROM `"._DB_PREFIX."invoice` i ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."invoice_item` ii ON i.invoice_id = ii.invoice_id ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON ii.task_id = t.task_id ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."job` p ON t.job_id = p.job_id ";
        $sql .= " WHERE (i.date_sent >= '$week_start' AND i.date_sent <= '$week_end')";
        $sql .= " GROUP BY i.invoice_id";
        // todo - sql in here to limit what they can see.
        $invoices = query($sql);
        // group invoices into days of the week.
        while($i = mysql_fetch_assoc($invoices)){
            $invoice_data = module_invoice::get_invoice($i['invoice_id'],1);
            if($multiplyer > 1){
                $week_day = date('w',strtotime($i['date_sent'])) - 1;
                $i['date_sent'] = date('Y-m-d',strtotime('-'.$week_day.' days',strtotime($i['date_sent'])));
            }
            $key = date("Ymd",strtotime($i['date_sent']));
            if(isset($data[$key])){
                $data[$key]['amount_invoiced'] += $invoice_data['total_amount'];
                $data['total']['amount_invoiced'] += $invoice_data['total_amount'];
            }
        }
        // find all payments made this week.
        // we also have to search for entries in the new "finance" table and make sure we dont double up here.
        $finance_records = module_finance::get_finances(array('date_from'=>$week_start,'date_to'=>$week_end));
        foreach($finance_records as $finance_record){
            if($finance_record['credit'] > 0){
                if($multiplyer > 1){
                    $week_day = date('w',strtotime($finance_record['transaction_date'])) - 1;
                    $finance_record['transaction_date'] = date('Y-m-d',strtotime('-'.$week_day.' days',strtotime($finance_record['transaction_date'])));
                }
                $key = date("Ymd",strtotime($finance_record['transaction_date']));
                if(isset($data[$key])){
                    $data[$key]['amount_paid'] += $finance_record['amount'];
                    $data['total']['amount_paid'] += $finance_record['amount'];
                }
            }
            if($finance_record['debit'] > 0){
                if($multiplyer > 1){
                    $week_day = date('w',strtotime($finance_record['transaction_date'])) - 1;
                    $finance_record['transaction_date'] = date('Y-m-d',strtotime('-'.$week_day.' days',strtotime($finance_record['transaction_date'])));
                }
                $key = date("Ymd",strtotime($finance_record['transaction_date']));
                if(isset($data[$key])){
                    $data[$key]['amount_spent'] += $finance_record['amount'];
                    $data['total']['amount_spent'] += $finance_record['amount'];
                }
            }
        }
        /*$sql = "SELECT p.* ";
        $sql .= " FROM `"._DB_PREFIX."invoice_payment` p ";
        $sql .= " WHERE (p.date_paid >= '$week_start' AND p.date_paid <= '$week_end')";
        // todo - sql in here to limit what they can see.
        $payments = query($sql);
        // group invoices into days of the week.
        while($payment = mysql_fetch_assoc($payments)){
            //$invoice_data = module_invoice::get_invoice($i['invoice_id']);
            if($multiplyer > 1){
                $week_day = date('w',strtotime($payment['date_paid'])) - 1;
                $payment['date_paid'] = date('Y-m-d',strtotime('-'.$week_day.' days',strtotime($payment['date_paid'])));
            }
            $key = date("Ymd",strtotime($payment['date_paid']));
            if(isset($data[$key])){
                $data[$key]['amount_paid'] += $payment['amount'];
                $data['total']['amount_paid'] += $payment['amount'];
            }
        }*/


        if(class_exists('module_envato',false)){

            $envato_currency = "USD";
            $envato = new envato_api();
            $local_currency = $envato->read_setting("local_currency","AUD");
            $currency_convert_multiplier = $envato->currency_convert($envato_currency,$local_currency);

            // find summary of earnings between these dates in the envato statement.
            $week_start_time = strtotime($week_start);
            $week_end_time = strtotime($week_end);
            $sql = "SELECT * FROM `"._DB_PREFIX."envato_statement` s WHERE `time` >= '$week_start_time' AND `time` <= $week_end_time";
            $sql .= " AND ( `type` = 'sale' OR `type` = 'referral_cut' )";
            foreach(qa($sql) as $sale){
                $sale_time = $sale['time'];
                if($multiplyer > 1){
                    $week_day = date('w',$sale_time) - 1;
                    $sale_time = strtotime('-'.$week_day.' days',$sale_time);
                }
                $key = date("Ymd",$sale_time);
                $data[$key]['envato_earnings'] += round($currency_convert_multiplier * $sale['earnt'],2);
                $data['total']['envato_earnings'] += round($currency_convert_multiplier * $sale['earnt'],2);
                /*if($sale['type']=='sale'){
                    $sales_count++;
                }
                $sales_amount+= $sale['earnt'];*/
            }

        }

        if($multiplyer>1){
            // dont want totals on previous weeks listing
            unset($data['total']);
        }

        foreach($data as $data_id => $row){
            //$row['amount'] = dollar($row['amount']);
            $row['amount'] = currency((int)$row['amount']);
            $row['amount_invoiced'] = currency((int)$row['amount_invoiced']);
            $row['amount_paid'] = currency((int)$row['amount_paid']);
            $row['amount_spent'] = currency((int)$row['amount_spent']);
            if(class_exists('module_envato',false)){
                $row['envato_earnings'] = currency((int)$row['envato_earnings']);
            }
            // combine together
            $row['hours'] = sprintf('%s (%s)',$row['hours'],$row['amount']);
            if(is_numeric($row['day'])){
                $time = $row['day'];
                $date = date('Y-m-d',$time);
                $row['date'] = $date;
                if($multiplyer > 1){
                    $date .= '|' . date('Y-m-d',strtotime('+'.$multiplyer.' days',$time));
                }
                //$row['hours'] = '<a href="'.$base_href.'w=hours&date='.$date.'" class="summary_popup">'. _l('%s hours',$row['hours']) . '</a>';
                $row['hours_link'] = '<a href="'.$base_href.'w=hours&date='.$date.'" class="summary_popup">'. $row['hours'] . '</a>';
                $row['amount_link'] = '<a href="'.$base_href.'w=hours&date='.$date.'" class="summary_popup">'. $row['amount'] . '</a>';
                $row['amount_invoiced_link'] = '<a href="'.$base_href.'w=amount_invoiced&date='.$date.'" class="summary_popup">'. $row['amount_invoiced'] . '</a>';
                $row['amount_paid_link'] = '<a href="'.$base_href.'w=amount_paid&date='.$date.'" class="summary_popup">'. $row['amount_paid'] . '</a>';
                $row['amount_spent_link'] = '<a href="'.$base_href.'w=amount_spent&date='.$date.'" class="summary_popup">'. $row['amount_spent'] . '</a>';
                $row['day'] = date("D jS",$time);
                $row['week'] = date("M jS",$time);
                // if it's today.
                if($time == strtotime(date("Y-m-d"))){
                    $row['highlight'] = true;
                }
            }else{

            }

            $data[$data_id] = $row;
        }
        return $data;
    }

    public static function get_dashboard_data() {

        $show_previous_weeks = module_config::c('dashboard_income_previous_weeks',7);
        $home_summary = array(
            array(
                "week_start" => date('Y-m-d', mktime(1, 0, 0, date('m'), date('d')-date('N')-(($show_previous_weeks+2)*7)+1, date('Y'))), // 7 weeks ago
                "week_end" => date('Y-m-d', strtotime('-1 day',mktime(1, 0, 0, date('m'), date('d')+(6-date('N'))-(2*7)+2, date('Y')))), // 2 weeks ago
                'table_name' => 'Previous Weeks',
                'array_name' => 'previous_weeks_data',
                'multiplyer' => 7,
                'col1' => 'week',
                'row_limit' => $show_previous_weeks,
            ),
            array(
                "week_start" => date('Y-m-d', mktime(1, 0, 0, date('m'), date('d')-date('N')-6, date('Y'))), // sunday midnight
                "week_end" => date('Y-m-d', mktime(1, 0, 0, date('m'), date('d')+(6-date('N'))-5, date('Y'))),
                'table_name' => 'Last Week',
                'array_name' => 'last_week_data',
                'multiplyer' => 1,
                'col1' => 'day',
                'row_limit' => 7,
            ),
            array(
                "week_start" => date('Y-m-d', mktime(1, 0, 0, date('m'), date('d')-date('N')+1, date('Y'))), // sunday midnight
                "week_end" => date('Y-m-d', mktime(1, 0, 0, date('m'), date('d')+(6-date('N'))+2, date('Y'))),
                'table_name' => 'This Week',
                'array_name' => 'this_week_data',
                'multiplyer' => 1,
                'col1' => 'day',
                'row_limit' => 7,
            ),
        );

        $return = array();



        foreach($home_summary as $home_sum){
            extract($home_sum); // hacky, better than old code tho.
            $data = self::get_finance_summary($week_start,$week_end,$multiplyer,$row_limit);


            // return the bits that will be used in the output of the HTML table (and now in the calendar module output)
            $return [] = array(
                'data' => $data,
                'table_name' => $table_name,
                'col1' => $col1,
            );

        }

        return $return;
    }

    public function get_upgrade_sql(){
        $sql = '';
        $fields = get_fields('finance_recurring');
        if(!isset($fields['next_due_date_custom'])){
            $sql .= "ALTER TABLE `"._DB_PREFIX."finance_recurring` ADD  `next_due_date_custom` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `next_due_date`;";
        }
        return $sql;
    }

    public function get_install_sql(){
        ob_start();
        ?>


CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>finance` (
  `finance_id` int(11) NOT NULL AUTO_INCREMENT,
  `finance_account_id` int(11) NOT NULL DEFAULT '0',
  `parent_finance_id` int(11) DEFAULT NULL,
  `invoice_payment_id` int(11) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('e','i') NOT NULL,
  `currency` varchar(5) NOT NULL,
  `finance_recurring_id` int(11) NOT NULL DEFAULT '0',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY (`finance_id`),
  KEY `transaction_date` (`transaction_date`),
  KEY `finance_account_id` (`finance_account_id`),
  KEY `parent_finance_id` (`parent_finance_id`),
  KEY `invoice_payment_id` (`invoice_payment_id`),
  KEY `finance_recurring_id` (`finance_recurring_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>finance_account` (
  `finance_account_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY (`finance_account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX;?>finance_category` (
  `finance_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NOT NULL,
  `date_created` date NOT NULL,
  `date_updated` date NOT NULL,
  PRIMARY KEY (`finance_category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;



CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>finance_category_rel` (
  `finance_id` int(11) NOT NULL,
  `finance_category_id` int(11) NOT NULL,
  UNIQUE KEY `finance_id` (`finance_id`,`finance_category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>finance_recurring` (
  `finance_recurring_id` int(11) NOT NULL AUTO_INCREMENT,
  `days` int(11) NOT NULL DEFAULT '0',
  `months` int(11) NOT NULL DEFAULT '0',
  `years` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `next_due_date` date DEFAULT NULL COMMENT 'calculated in php when a recurring is saved',
    `next_due_date_custom` TINYINT( 1 ) NOT NULL DEFAULT  '0',
  `type` enum('i','e') NOT NULL DEFAULT 'e',
  `finance_account_id` int(11) NOT NULL DEFAULT '0',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NOT NULL,
  `date_created` date NOT NULL,
  `date_updated` date NOT NULL,
  PRIMARY KEY (`finance_recurring_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>finance_recurring_catrel` (
  `finance_recurring_id` int(11) NOT NULL,
  `finance_category_id` int(11) NOT NULL,
  UNIQUE KEY `finance_id` (`finance_recurring_id`,`finance_category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            
    <?php

        return ob_get_clean();
    }

    public static function is_expense_enabled(){
        // we dont have the finance_edit.php file if expenses are disabled (ie: lite version)
        return is_file(dirname(__FILE__).'/pages/finance_edit.php');
    }
}