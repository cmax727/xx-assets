<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:33 
  * IP Address: 127.0.0.1
  */



class module_website extends module_base{
	
	public $links;
	public $website_types;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->links = array();
		$this->website_types = array();
		$this->module_name = "website";
		$this->module_position = 16;
        $this->version = 2.1;

        if($this->can_i('view',module_config::c('project_name_plural','Websites'))){
            $this->ajax_search_keys = array(
                _DB_PREFIX.'website' => array(
                    'plugin' => 'website',
                    'search_fields' => array(
                        'url',
                        'name',
                    ),
                    'key' => 'website_id',
                    'title' => _l(module_config::c('project_name_single','Website').': '),
                ),
            );

            // only display if a customer has been created.
            if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] && $_REQUEST['customer_id']!='new'){
                // how many websites?
                $websites = $this->get_websites(array('customer_id'=>$_REQUEST['customer_id']));
                $name = module_config::c('project_name_plural','Websites');
                if(count($websites)){
                    $name .= " <span class='menu_label'>".count($websites)."</span> ";
                }
                $this->links[] = array(
                    "name"=>$name,
                    "p"=>"website_admin",
                    'args'=>array('website_id'=>false),
                    'holder_module' => 'customer', // which parent module this link will sit under.
                    'holder_module_page' => 'customer_admin_open',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }
            $this->links[] = array(
                "name"=>module_config::c('project_name_plural','Websites'),
                "p"=>"website_admin",
                'args'=>array('website_id'=>false),
            );

        }
		
	}

    public static function link_generate($website_id=false,$options=array(),$link_options=array()){

        $key = 'website_id';
        if($website_id === false && $link_options){
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
        if(!isset($options['type']))$options['type']='website';
        $options['page'] = 'website_admin';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['website_id'] = $website_id;
        $options['module'] = 'website';
        $data = self::get_website($website_id);
        $options['data'] = $data;
        // what text should we display in this link?
        $options['text'] = (!isset($data['name'])||!trim($data['name'])) ? 'N/A' : $data['name'];
        if($data['customer_id']>0){
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
        ))){
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
            return link_generate($link_options);

        }
    }

	public static function link_open($website_id,$full=false){
        return self::link_generate($website_id,array('full'=>$full));
    }

	
	public function process(){
		$errors=array();
		if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['website_id']){
            $data = self::get_website($_REQUEST['website_id']);
            if(module_form::confirm_delete('website_id',"Really delete ".module_config::c('project_name_single','Website').": ".$data['name'],self::link_open($_REQUEST['website_id']))){
                $this->delete_website($_REQUEST['website_id']);
                set_message(module_config::c('project_name_single','Website')." deleted successfully");
                redirect_browser(self::link_open(false));
            }
		}else if("save_website" == $_REQUEST['_process']){
			$website_id = $this->save_website($_REQUEST['website_id'],$_POST);
			$_REQUEST['_redirect'] = $this->link_open($website_id);
			set_message(module_config::c('project_name_single','Website')." saved successfully");
		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		print_error($errors,true);
	}


	public static function get_websites($search=array()){
		// limit based on customer id
		/*if(!isset($_REQUEST['customer_id']) || !(int)$_REQUEST['customer_id']){
			return array();
		}*/
		// build up a custom search sql query based on the provided search fields
		$sql = "SELECT u.*,u.website_id AS id ";
        $sql .= ", u.name AS name ";
        $sql .= ", c.customer_name ";
        // add in our extra fields for the csv export
        //if(isset($_REQUEST['import_export_go']) && $_REQUEST['import_export_go'] == 'yes'){
        if(class_exists('module_extra',false)){
            $sql .= " , (SELECT GROUP_CONCAT(ex.`extra_key` ORDER BY ex.`extra_id` ASC SEPARATOR '"._EXTRA_FIELD_DELIM."') FROM `"._DB_PREFIX."extra` ex WHERE owner_id = u.website_id AND owner_table = 'website') AS extra_keys";
            $sql .= " , (SELECT GROUP_CONCAT(ex.`extra` ORDER BY ex.`extra_id` ASC SEPARATOR '"._EXTRA_FIELD_DELIM."') FROM `"._DB_PREFIX."extra` ex WHERE owner_id = u.website_id AND owner_table = 'website') AS extra_vals";
        }
        $from = " FROM `"._DB_PREFIX."website` u ";
        $from .= " LEFT JOIN `"._DB_PREFIX."customer` c USING (customer_id)";
		$where = " WHERE 1 ";
		if(isset($search['generic']) && $search['generic']){
			$str = mysql_real_escape_string($search['generic']);
			$where .= " AND ( ";
			$where .= " u.name LIKE '%$str%' OR ";
			$where .= " u.url LIKE '%$str%'  ";
			$where .= ' ) ';
		}
        foreach(array('customer_id','status') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND u.`$key` = '$str'";
            }
        }
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

		$group_order = ' GROUP BY u.website_id ORDER BY u.name'; // stop when multiple company sites have same region
		$sql = $sql . $from . $where . $group_order;
		$result = qa($sql);
		//module_security::filter_data_set("website",$result);
		return $result;
//		return get_multiple("website",$search,"website_id","fuzzy","name");

	}
	public static function get_website($website_id){
		$website = get_single("website","website_id",$website_id);
        if(!$website){
            $website = array(
                'website_id' => 'new',
                'customer_id' => isset($_REQUEST['customer_id']) ? $_REQUEST['customer_id'] : 0,
                'name' => '',
                'status'  => module_config::s('website_status_default','New'),
                'url' => '',
            );
        }
		return $website;
	}
	public function save_website($website_id,$data){
        $original_website_data = $this->get_website($website_id);
		$website_id = update_insert("website_id",$website_id,"website",$data);
        if(isset($original_website_data['customer_id']) && $original_website_data['customer_id'] && isset($data['customer_id']) && $data['customer_id'] && $original_website_data['customer_id'] != $data['customer_id']){
            module_cache::clear_cache();
            // the customer id has changed. update jobs and invoices.
            module_job::customer_id_changed($original_website_data['customer_id'],$data['customer_id']);
        }
        module_extra::save_extras('website','website_id',$website_id);
		return $website_id;
	}

	public static function delete_website($website_id){
		$website_id=(int)$website_id;
		if(_DEMO_MODE && $website_id == 1){
			return;
		}
		$sql = "DELETE FROM "._DB_PREFIX."website WHERE website_id = '".$website_id."' LIMIT 1";
		$res = query($sql);
        foreach(module_job::get_jobs(array('website_id'=>$website_id)) as $val){
            module_job::delete_job($val['website_id']);
        }
		module_note::note_delete("website",$website_id);
        module_extra::delete_extras('website','website_id',$website_id);
	}
    public function login_link($website_id){
        return module_security::generate_auto_login_link($website_id);
    }

    public static function get_statuses(){
        $sql = "SELECT `status` FROM `"._DB_PREFIX."website` GROUP BY `status` ORDER BY `status`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['status']] = $r['status'];
        }
        return $statuses;
    }


    public function get_install_sql(){
        ob_start();
        ?>

CREATE TABLE `<?php echo _DB_PREFIX; ?>website` (
  `website_id` int(11) NOT NULL auto_increment,
  `customer_id` INT(11) NULL,
  `url` varchar(255) NOT NULL DEFAULT  '',
  `name` varchar(255) NOT NULL DEFAULT  '',
  `status` varchar(255) NOT NULL DEFAULT  '',
  `date_created` date NULL,
  `date_updated` date NULL,
  PRIMARY KEY  (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    <?php
// todo: add default admin permissions.
        
        return ob_get_clean();
    }

    public static function handle_import($data,$add_to_group){

        // woo! we're doing an import.

        // our first loop we go through and find matching customers by their "customer_name" (required field)
        // and then we assign that customer_id to the import data.
        // our second loop through if there is a customer_id we overwrite that existing customer with the import data (ignoring blanks).
        // if there is no customer id we create a new customer record :) awesome.

        /*
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

        }*/


    }

}