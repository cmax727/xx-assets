<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:03 
  * IP Address: 127.0.0.1
  */


define('_CUSTOMER_ACCESS_ALL','All customers in system');
define('_CUSTOMER_ACCESS_CONTACTS','Only customer I am assigned to as a contact');
define('_CUSTOMER_ACCESS_TASKS','Only customers I am assigned to in a job');

class module_customer extends module_base{

	public $links;
	public $customer_types;
    public $customer_id;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
    public function init(){
		$this->links = array();
		$this->customer_types = array();
		$this->module_name = "customer";
		$this->module_position = 5.1;
        $this->version = 2.12;

	}

    public function pre_menu(){

		if($this->can_i('view','Customers')){

			$this->links['customers'] = array(
				"name"=>"Customers",
				"p"=>"customer_admin_list",
				"args"=>array('customer_id'=>false),
				/*'holder_module' => 'people', // which parent module this link will sit under.
				'holder_module_page' => 'people_admin',  // which page this link will be automatically added to.
				'menu_include_parent' => 0,*/
                //'current' => (isset($_REQUEST['m'][0]) && $_REQUEST['m'][0]=='customer'), // hack to get nested menu working correctly.
			);
            if(file_exists(dirname(__FILE__).'/pages/customer_signup.php')){
                $this->links['customer_settings'] = array(
                    "name"=>"Signup",
                    "p"=>"customer_signup",
                    'holder_module' => 'config', // which parent module this link will sit under.
                    'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }
		}
    }

    public function ajax_search($search_key){
        // return results based on an ajax search.
        $ajax_results = array();
        $search_key = trim($search_key);
        if(strlen($search_key) > 2){
            //$sql = "SELECT * FROM `"._DB_PREFIX."customer` c WHERE ";
            //$sql .= " c.`customer_name` LIKE %$search_key%";
            //$results = qa($sql);
            $results = $this->get_customers(array('generic'=>$search_key));
            if(count($results)){
                foreach($results as $result){
                    // what part of this matched?
                    if(
                        preg_match('#'.preg_quote($search_key,'#').'#i',$result['name']) ||
                        preg_match('#'.preg_quote($search_key,'#').'#i',$result['phone'])
                    ){
                        // we matched the customer contact details.
                        $match_string = _l('Customer Contact: ');
                        $match_string .= _shl($result['customer_name'],$search_key);
                        $match_string .= ' - ';
                        $match_string .= _shl($result['name'],$search_key);
                        // hack
                        $_REQUEST['customer_id'] = $result['customer_id'];
                        $ajax_results [] = '<a href="'.module_user::link_open_contact($result['user_id']) . '">' . $match_string . '</a>';
                    }else{
                        $match_string = _l('Customer: ');
                        $match_string .= _shl($result['customer_name'],$search_key);
                        $ajax_results [] = '<a href="'.$this->link_open($result['customer_id']) . '">' . $match_string . '</a>';
                        //$ajax_results [] = $this->link_open($result['customer_id'],true);
                    }
                }
            }
        }
        return $ajax_results;
    }

    /** static stuff */

    
     public static function link_generate($customer_id=false,$options=array(),$link_options=array()){
        // we accept link options from a bubbled link call.
        // so we have to prepent our options to the start of the link_options array incase
        // anything bubbled up to this method.
        // build our options into the $options variable and array_unshift this onto the link_options at the end.
        $key = 'customer_id'; // the key we look for in data arrays, on in _REQUEST variables. for sub link building.

        // we check if we're bubbling from a sub link, and find the item id from a sub link
        if(${$key} === false && $link_options){
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
        // grab the data for this particular link, so that any parent bubbled link_generate() methods
        // can access data from a sub item (eg: an id)

        if(isset($options['full']) && $options['full']){
            // only hit database if we need to print a full link with the name in it.
            if(!isset($options['data']) || !$options['data']){
                $data = self::get_customer($customer_id);
                $options['data'] = $data;
            }else{
                $data = $options['data'];
            }
            // what text should we display in this link?
            $options['text'] = (!isset($data['customer_name'])||!trim($data['customer_name'])) ? 'N/A' : $data['customer_name'];
        }
        $options['text'] = isset($options['text']) ? htmlspecialchars($options['text']) : '';
        // generate the arguments for this link
        $options['arguments'] = array(
            'customer_id' => $customer_id,
        );
        // generate the path (module & page) for this link
        $options['page'] = 'customer_admin_' . (($customer_id||$customer_id=='new') ? 'open' : 'list');
        $options['module'] = 'customer';

        // append this to our link options array, which is eventually passed to the
        // global link generate function which takes all these arguments and builds a link out of them.

         if(!self::can_i('view','Customers')){
            if(!isset($options['full']) || !$options['full']){
                return '#';
            }else{
                return isset($options['text']) ? $options['text'] : 'N/A';
            }
        }

        // optionally bubble this link up to a parent link_generate() method, so we can nest modules easily
        // change this variable to the one we are going to bubble up to:
     $bubble_to_module = false;
        /*$bubble_to_module = array(
            'module' => 'people',
            'argument' => 'people_id',
        );*/
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


	public static function link_open($customer_id,$full=false,$data=array()){
		return self::link_generate($customer_id,array('full'=>$full,'data'=>$data));
	}



	public static function get_customers($search=array()){

        // work out what customers this user can access?
        $customer_access = self::get_customer_data_access();

		// build up a custom search sql query based on the provided search fields
		$sql = "SELECT c.*, c.customer_id AS id, u.user_id, u.name, u.phone ";
		$sql .= " , pu.user_id, pu.name AS primary_user_name, pu.phone AS primary_user_phone, pu.email AS primary_user_email";
        $sql .= " , a.line_1, a.line_2, a.suburb, a.state, a.region, a.country, a.post_code ";
        $sql .= " FROM `"._DB_PREFIX."customer` c ";
		$where = " WHERE 1";
        if(defined('_SYSTEM_ID')) $sql .= " AND c.system_id = '"._SYSTEM_ID."' ";
		$group_order = '';
        $sql .= ' LEFT JOIN `'._DB_PREFIX."user` u ON c.customer_id = u.customer_id"; //c.primary_user_id = u.user_id AND 
        $sql .= ' LEFT JOIN `'._DB_PREFIX."user` pu ON c.primary_user_id = pu.user_id";
        $sql .= ' LEFT JOIN `'._DB_PREFIX."address` a ON c.customer_id = a.owner_id AND a.owner_table = 'customer' AND a.address_type = 'physical'";
		if(isset($search['generic']) && trim($search['generic'])){
			$str = mysql_real_escape_string(trim($search['generic']));
			// search the customer name, contact name, cusomter phone, contact phone, contact email.
			//$where .= 'AND u.customer_id IS NOT NULL AND ( ';
			$where .= " AND ( ";
			$where .= "c.customer_name LIKE '%$str%' OR ";
			// $where .= "c.phone LIKE '%$str%' OR "; // search company phone number too.
			$where .= "u.name LIKE '%$str%' OR u.email LIKE '%$str%' OR ";
			$where .= "u.phone LIKE '%$str%' OR u.fax LIKE '%$str%' ";
			$where .= ') ';
		}
		if(isset($search['state_id']) && trim($search['state_id'])){
			$str = (int)$search['state_id'];
			// search all the customer site addresses.
			$sql .= " LEFT JOIN `"._DB_PREFIX."address` a ON (a.owner_id = c.customer_id)";
			$where .= " AND (a.state_id = '$str' AND a.owner_table = 'customer')";
		}
        switch($customer_access){
            case _CUSTOMER_ACCESS_ALL:

                break;
            case _CUSTOMER_ACCESS_CONTACTS:
                // we only want customers that are directly linked with the currently logged in user contact.
//                if(isset($_SESSION['_restrict_customer_id']) && (int)$_SESSION['_restrict_customer_id']> 0){
                    // this session variable is set upon login, it holds their customer id.
                    // todo - share a user account between multiple customers!
                    //$where .= " AND c.customer_id IN (SELECT customer_id FROM )";
                    $where .= " AND c.customer_id = '".(int)$_SESSION['_restrict_customer_id']."'";
//                }
                break;
            case _CUSTOMER_ACCESS_TASKS:
                // only customers who have linked jobs that I am assigned to.
                $sql .= " LEFT JOIN `"._DB_PREFIX."job` j ON c.customer_id = j.customer_id ";
                $sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON j.job_id = t.job_id ";
                $where .= " AND (j.user_id = ".(int)module_security::get_loggedin_id()." OR t.user_id = ".(int)module_security::get_loggedin_id().")";
                break;
        }
		
		$group_order = ' GROUP BY c.customer_id ORDER BY c.customer_name ASC'; // stop when multiple company sites have same region
		$sql = $sql . $where . $group_order;
		$result = qa($sql);
        /*if(!function_exists('sort_customers')){
            function sort_customers($a,$b){
                return strnatcasecmp($a['customer_name'],$b['customer_name']);
            }
        }
        uasort($result,'sort_customers');*/

        // we are filtering in the SQL code now..
		//module_security::filter_data_set("customer",$result);
        
		return $result;
		//return get_multiple("customer",$search,"customer_id","fuzzy","name");
	}

	public static function get_customer($customer_id){
        $customer_id = (int)$customer_id;
        $customer = false;
        if($customer_id>0){
            $customer = get_single("customer","customer_id",$customer_id);

            switch(self::get_customer_data_access()){
                case _CUSTOMER_ACCESS_ALL:

                    break;
                case _CUSTOMER_ACCESS_CONTACTS:
                    // we only want customers that are directly linked with the currently logged in user contact.
                    if(isset($_SESSION['_restrict_customer_id']) && (int)$_SESSION['_restrict_customer_id']> 0){
                        // this session variable is set upon login, it holds their customer id.
                        //$where .= " AND c.customer_id = '".(int)$_SESSION['_restrict_customer_id']."'";
                        if($customer['customer_id'] != $_SESSION['_restrict_customer_id']){
                            $customer = false;
                        }
                    }
                    break;
                case _CUSTOMER_ACCESS_TASKS:
                    // only customers who have linked jobs that I am assigned to.
                    //$sql .= " LEFT JOIN `"._DB_PREFIX."job` j ON c.customer_id = j.customer_id ";
                    //$sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON j.job_id = t.job_id ";
                    //$where .= " AND (j.user_id = ".(int)module_security::get_loggedin_id()." OR t.user_id = ".(int)module_security::get_loggedin_id().")";
                    $has_job_access = false;
                    $jobs = module_job::get_jobs(array('customer_id'=>$customer_id));
                    foreach($jobs as $job){
                        if($job['user_id']==module_security::get_loggedin_id()){
                            $has_job_access=true;
                            break;
                        }
                        $tasks = module_job::get_tasks($job['job_id']);
                        foreach($tasks as $task){
                            if($task['user_id']==module_security::get_loggedin_id()){
                                $has_job_access=true;
                                break;
                            }
                        }
                    }
                    if(!$has_job_access){
                        $customer = false;
                    }
                    break;
            }
        }
        if(!$customer){
            $customer = array(
                'customer_id' => 'new',
                'customer_name' => '',
                'primary_user_id' => '',
                'credit' => '0',
            );
        }
		//$customer['customer_industry_id'] = get_multiple('customer_industry_rel',array('customer_id'=>$customer_id),'customer_industry_id');
		//echo $customer_id;print_r($customer);exit;
		return $customer;
	}


    public static function print_customer_summary($customer_id,$output='html',$fields=array()) {
		global $plugins;
		$customer_data = $plugins['customer']->get_customer($customer_id);
		if(!$fields){
			$fields = array('customer_name');
		}
		$customer_output = '';
		foreach($fields as $key){
			if(isset($customer_data[$key]) && $customer_data[$key]){
				$customer_output .= $customer_data[$key].', ';
			}
		}
		$customer_output = rtrim($customer_output,', ');
		if($customer_data){
			switch($output){
				case 'text':
			        echo $customer_output;
			        break;
				case 'html':
					?>
					<span class="customer">
						<a href="<?php echo $plugins['customer']->link_open($customer_id);?>">
							<?php echo $customer_output;?>
						</a>
					</span>
					<?php
					break;
				case 'full':
					include('pages/customer_summary.php');
					break;
			}
		}
	}


    /** methods  */

    
	public function process(){
		if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['customer_id']){
			$data = self::get_customer($_REQUEST['customer_id']);
            if(module_form::confirm_delete('customer_id',"Really delete customer: ".$data['customer_name'],self::link_open($_REQUEST['customer_id']))){
                $this->delete_customer($_REQUEST['customer_id']);
                set_message("Customer deleted successfully");
                redirect_browser(self::link_open(false));
            }
		}else if("save_customer" == $_REQUEST['_process']){
			$customer_id = $this->save_customer($_REQUEST['customer_id'],$_POST);
			set_message("Customer saved successfully");
			redirect_browser(self::link_open($customer_id));
		}
	}

    public function load($customer_id){
        $data = self::get_customer($customer_id);
        foreach($data as $key=>$val){
            $this->$key = $val;
        }
        return $data;
    }
	public function save_customer($customer_id,$data){
		$customer_id = update_insert("customer_id",$customer_id,"customer",$data);
        if(isset($_REQUEST['user_id'])){
            $user_id = (int)$_REQUEST['user_id'];
            // assign specified user_id to this customer.
            // could this be a problem?
            // maybe?
            // todo: think about security precautions here, maybe only allow admins to set primary contacts.
            if(!$user_id){
                $data['customer_id']=$customer_id;
                $user_id = update_insert("user_id",$user_id,"user",$data);
                $this->set_primary_user_id($customer_id,$user_id);
            }else{
                // make sure this user is part of this customer.
                $users = module_user::get_users(array('customer_id'=>$customer_id));
                $saved_user_id = false;
                foreach($users as $user){
                    if($user['user_id']==$user_id){
                        $saved_user_id = $user_id = update_insert("user_id",$user_id,"user",$data);
                        $this->set_primary_user_id($customer_id,$user_id);
                        break;
                    }
                }
                if(!$saved_user_id){
                    $this->set_primary_user_id($customer_id,0);
                }
            }
            // todo: move this functionality back into the user class.
            // maybe with a static save_user method ?
            if($user_id>0){
                module_extra::save_extras('user','user_id',$user_id);
            }
        }
		
		handle_hook("address_block_save",$this,"physical","customer","customer_id",$customer_id);
		//handle_hook("address_block_save",$this,"postal","customer","customer_id",$customer_id);
        module_extra::save_extras('customer','customer_id',$customer_id);

		return $customer_id;
	}

	public static function set_primary_user_id($customer_id,$user_id){
		update_insert('customer_id',$customer_id,'customer',array('primary_user_id'=>$user_id));
	}
	public function delete_customer($customer_id){
		$customer_id=(int)$customer_id;
        $customer = self::get_customer($customer_id);
        if($customer && $customer['customer_id'] == $customer_id){
            $sql = "DELETE FROM "._DB_PREFIX."customer WHERE customer_id = '".$customer_id."' LIMIT 1";
            query($sql);
            foreach(module_user::get_users(array('customer_id'=>$customer_id)) as $val){
                module_user::delete_user($val['user_id']);
            }
            foreach(module_website::get_websites(array('customer_id'=>$customer_id)) as $val){
                module_website::delete_website($val['website_id']);
            }
            foreach(module_job::get_jobs(array('customer_id'=>$customer_id)) as $val){
                module_job::delete_job($val['job_id']);
            }
            module_note::note_delete("customer",'customer_id',$customer_id);
            handle_hook("address_delete",$this,'all',"customer",'customer_id',$customer_id);
            handle_hook("file_delete",$this,"customer",'customer_id',$customer_id);
            module_extra::delete_extras('customer','customer_id',$customer_id);
        }
	}

    public static function handle_import($data,$add_to_group){

        // woo! we're doing an import.

        // our first loop we go through and find matching customers by their "customer_name" (required field)
        // and then we assign that customer_id to the import data.
        // our second loop through if there is a customer_id we overwrite that existing customer with the import data (ignoring blanks).
        // if there is no customer id we create a new customer record :) awesome.

        foreach($data as $rowid => $row){
            if(!isset($row['customer_name']) || !trim($row['customer_name'])){
                unset($data[$rowid]);
                continue;
            }
            if(!isset($row['customer_id']) || !$row['customer_id']){
                $data[$rowid]['customer_id'] = 0;
            }
            // search for a custoemr based on name.
            $customer = get_single('customer','customer_name',$row['customer_name']);
            if($customer && $customer['customer_id'] > 0){
                $data[$rowid]['customer_id'] = $customer['customer_id'];
            }
        }

        // now save the data.
        foreach($data as $rowid => $row){
            $customer_id = (int)$row['customer_id'];
            // check if this ID exists.
            $customer = self::get_customer($customer_id);
            if(!$customer || $customer['customer_id'] != $customer_id){
                $customer_id = 0;
            }
            $customer_id = update_insert("customer_id",$customer_id,"customer",$row);
            // see if we're updating an old contact, or adding a new primary contact.
            // match on name since that's a required field.
            $users = module_user::get_users(array('customer_id'=>$customer_id));
            $user_match = 0;
            foreach($users as $user){
                if($user['name']==$row['primary_user_name']){
                    $user_match = $user['user_id'];
                    break;
                }
            }
            $user_match = update_insert("user_id",$user_match,"user",array(
                                                     'customer_id'=>$customer_id,
                                                     'name' => $row['primary_user_name'],
                                                     'email' => $row['primary_user_email'],
                                                     'phone' => $row['primary_user_phone'],
                                                 ));
            self::set_primary_user_id($customer_id,$user_match);

            // do a hack to save address.
            $existing_address = module_address::get_address($customer_id,'customer','physical');
            $address_id = ($existing_address&&isset($existing_address['address_id'])) ? (int)$existing_address['address_id'] : 'new';
            $address = array_merge($row,array(
                                           'owner_id'=>$customer_id,
                                           'owner_table'=>'customer',
                                           'address_type'=>'physical',
                                        ));
            module_address::save_address($address_id,$address);

            foreach($add_to_group as $group_id => $tf){
                module_group::add_to_group($group_id,$customer_id,'customer');
            }

        }


    }


    public function get_install_sql(){
        ob_start();
        ?>

CREATE TABLE `<?php echo _DB_PREFIX; ?>customer` (
  `customer_id` int(11) NOT NULL auto_increment,
  `primary_user_id` int(11) NOT NULL DEFAULT '0',
  `customer_name` varchar(255) NOT NULL DEFAULT '',
  `credit` double(10,2) NOT NULL DEFAULT '0',
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY  (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `<?php echo _DB_PREFIX; ?>customer` VALUES (1, 3, 'Bobs Printing Service', 0, NOW(), NOW());
INSERT INTO `<?php echo _DB_PREFIX; ?>customer` VALUES (2, 4, 'Richards Roof Repairs', 0, NOW(), NOW());

<?php
        return ob_get_clean();
    }

    public static function add_credit($customer_id, $credit) {
        $customer_data = self::get_customer($customer_id);
        $customer_data['credit'] += $credit;
        update_insert('customer_id',$customer_id,'customer',array('credit'=>$customer_data['credit']));
        //self::add_history($customer_id,'Added '.dollar($credit).' credit to customers account.');
    }
    public static function remove_credit($customer_id, $credit) {
        $customer_data = self::get_customer($customer_id);
        $customer_data['credit'] -= $credit;
        update_insert('customer_id',$customer_id,'customer',array('credit'=>$customer_data['credit']));
        //self::add_history($customer_id,'Added '.dollar($credit).' credit to customers account.');
    }

    
    public static function add_history($customer_id,$message){
		module_note::save_note(array(
			'owner_table' => 'customer',
			'owner_id' => $customer_id,
			'note' => $message,
			'rel_data' => self::link_open($customer_id),
			'note_time' => time(),
		));
	}

    public static function get_customer_data_access() {
        if(class_exists('module_security',false)){
            return module_security::can_user_with_options(module_security::get_loggedin_id(),'Customer Data Access',array(
                                                                                                   _CUSTOMER_ACCESS_ALL,
                                                                                                   _CUSTOMER_ACCESS_CONTACTS,
                                                                                                   _CUSTOMER_ACCESS_TASKS,
                                                                                                                       ));
        }else{
            return true;
        }
    }

    public static function link_public_signup(){
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.customer/h.public_signup');
    }

    public function external_hook($hook){

        switch($hook){
            case 'public_signup':
                //todo - recaptcha on signup form.
                print_r($_REQUEST);
                break;
        }
    }
}
