<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:28 
  * IP Address: 127.0.0.1
  */


class module_file extends module_base{
	
	var $links;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
		$this->links = array();
		$this->module_name = "file";
		$this->module_position = 8882;

        $this->version = 2.35;

		
	}

    public function pre_menu(){

        if($this->can_i('edit','Files') || $this->can_i('view','Files')){
            $this->ajax_search_keys = array(
                _DB_PREFIX.'file' => array(
                    'plugin' => 'file',
                    'search_fields' => array(
                        'file_name',
                        'description',
                    ),
                    'key' => 'file_id',
                    'title' => _l('File: '),
                ),
            );

            // only display if a customer has been created.
            if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] && $_REQUEST['customer_id']!='new'){
                // how many files?
                $files = $this->get_files(array('customer_id'=>$_REQUEST['customer_id']));
                $name = _l('Files');
                if(count($files)){
                    $name .= " <span class='menu_label'>".count($files)."</span> ";
                }
                $this->links[] = array(
                    "name"=>$name,
                    "p"=>"file_admin",
                    'args'=>array('file_id'=>false),
                    'holder_module' => 'customer', // which parent module this link will sit under.
                    'holder_module_page' => 'customer_admin_open',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }
            /*$this->links[] = array(
                "name"=>"Files",
                "p"=>"file_admin",
                'args'=>array('file_id'=>false),
            );*/

        }

        if(!$this->can_i('edit','Files')){
            // find out how many for this contact.
            $customer_ids = module_security::get_customer_restrictions();
            if($customer_ids){
                $files = array();
                foreach($customer_ids as $customer_id){
                    $files = $files + $this->get_files(array('customer_id'=>$customer_id));
                }
                $this->links[] = array(
                    "name"=>_l('Files')." <span class='menu_label'>".count($files)."</span> ",
                    "p"=>"file_admin",
                    'args'=>array('file_id'=>false),
                );
            }
        }
    }

    
    public static function link_generate($file_id=false,$options=array(),$link_options=array()){

        $key = 'file_id';
        if($file_id === false && $link_options){
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
        if(!isset($options['type']))$options['type']='file';
        $options['page'] = 'file_admin';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['file_id'] = $file_id;
        $options['module'] = 'file';
        $data = self::get_file($file_id);
        $options['data'] = $data;
        // what text should we display in this link?
        $options['text'] = (!isset($data['file_name'])||!trim($data['file_name'])) ? 'N/A' : $data['file_name'];
        if(isset($data['customer_id']) && $data['customer_id']>0){
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
            return link_generate($link_options);

        }
    }

	public static function link_open($file_id,$full=false){
        return self::link_generate($file_id,array('full'=>$full));
    }
	
	
	function handle_hook($hook,&$calling_module=false,$owner_table=false,$key_name=false,$key_value=false){
		switch($hook){
            case 'file_list':
            case 'file_delete':
                // find the key we are saving this address against.
                $owner_id = (int)$key_value;
                if(!$owner_id || $owner_id == 'new'){
                    // find one in the post data.
                    if(isset($_REQUEST[$key_name])){
                        $owner_id = $_REQUEST[$key_name];
                    }
                }
                $file_hash = md5($owner_id.'|'.$owner_table); // just for posting unique arrays.
                break;
        }
		switch($hook){
			case "file_list":
				if($owner_id && $owner_id != 'new'){

					$file_items = $this->get_files(array("owner_table"=>$owner_table,"owner_id"=>$owner_id));
					foreach($file_items as &$file_item){
						// do it in loop here because of $this issues in static method below.
						// instead of include file below.
						$file_item['html'] = $this->print_file($file_item['file_id']);
					}
					include("pages/file_list.php");
				}else{
					echo 'Please save first before creating files.';
				}
				break;
			case "file_delete":

				if($owner_table && $owner_id){
                    $this->delete_files($owner_table,$owner_id);
				}
				break;
			
		}
	}

	public static function display_files($options){
        
		$owner_id = (isset($options['owner_id']) && $options['owner_id']) ? (int)$options['owner_id'] : false;
		$owner_table = (isset($options['owner_table']) && $options['owner_table']) ? $options['owner_table'] : false;
		if($owner_id && $owner_table){
			// we have all that we need to display some files!! yey!!
			// do we display a summary or not?
			global $plugins;
			$file_items = $plugins['file']->get_files(array('owner_table'=>$owner_table,'owner_id'=>$owner_id));
			if(isset($options['summary_owners']) && is_array($options['summary_owners'])){
				// generate a list of other files we have to display int eh list.
				foreach($options['summary_owners'] as $summary_owner_table => $summary_owner_ids){
					if(is_array($summary_owner_ids)){
						foreach($summary_owner_ids as $summary_owner_id){
							$file_items = array_merge($file_items,$plugins['file']->get_files(array('owner_table'=>$summary_owner_table,'owner_id'=>$summary_owner_id)));
						}
					}
				}
			}
			$layout_type = (isset($options['layout']) && $options['layout']) ?$options['layout'] : 'gallery';
			$editable = (!isset($options['editable']) || $options['editable']);
			foreach($file_items as &$file_item){
				$file_item['html'] = $plugins['file']->print_file($file_item['file_id'],$layout_type,$editable,$options);
			}
            
			$title = (isset($options['title']) && $options['title']) ?$options['title'] :false;
            if(!@include('pages/file_list_'.basename($layout_type).'.php')){
                include("pages/file_list.php");
            }
		}
	}

	public function print_file($file_id,$layout_type='gallery',$editable=true,$options=array()){
		$file_item = $this->get_file($file_id);
		ob_start();
		switch($layout_type){
			case 'gallery':
			?>

			<div class="file_<?php echo $file_item['file_id'];?>" style="float:left; width:110px; margin:3px; border:1px solid #CCC; text-align:center;">
				<div style="width:110px; height:90px; overflow:hidden; ">
                    <?php
                    $link = $this->link('',array('_process'=>'download','file_id'=>$file_id),'file',false);
                    if(isset($options['click_callback'])){
                        $link = 'javascript:'.$options['click_callback'].'('.$file_id.',\''.htmlspecialchars($this->link_public_view($file_id)).'\',\''.htmlspecialchars(addcslashes($file_item['file_name'],"'")).'\')';
                    }
                    ?>
					<a href="<?php echo $link;?>">
					<?php
					// /display a thumb if its supported.
					if(preg_match('/\.(\w\w\w\w?)$/',$file_item['file_name'],$matches)){
						switch(strtolower($matches[1])){
							case 'jpg':
							case 'jpeg':
							case 'gif':
							case 'png':
								?>
                                    <img src="<?php
                                    // echo _BASE_HREF . nl2br(htmlspecialchars($file_item['file_path']));
                                    echo $this->link_public_view($file_id);
                                    ?>" width="100" alt="download" border="0">
								<?php
								break;
							default:
								echo 'Download';
						}
					}
					?>
					</a>
				</div>
				<?php if($editable){ ?>
				<a href="#" class="file_edit<?php echo $file_item['owner_table'];?>_<?php echo $file_item['owner_id'];?>" rel="<?php echo $file_item['file_id'];?>"><?php echo nl2br(htmlspecialchars($file_item['file_name']));?></a>
				<?php }else{ ?>
				<a href="<?php echo $this->link('',array('_process'=>'download','file_id'=>$file_item['file_id']),'file',false);?>"><?php echo nl2br(htmlspecialchars($file_item['file_name']));?></a>
				<?php } ?>
			</div>
			<?php
			break;
			case 'list':
			?>
			<span class="file_<?php echo $file_item['file_id'];?>">
				<?php if($editable){ ?>
					<a href="#" class="file_edit<?php echo $file_item['owner_table'];?>_<?php echo $file_item['owner_id'];?>" rel="<?php echo $file_item['file_id'];?>"><?php echo nl2br(htmlspecialchars($file_item['file_name']));?></a>
				<?php }else{ ?>
					<a href="<?php echo $this->link('',array('_process'=>'download','file_id'=>$file_item['file_id']),'file',false);?>"><?php echo nl2br(htmlspecialchars($file_item['file_name']));?></a>
				<?php } ?>
			</span>
			<?php
			break;
		}
		return ob_get_clean();
	}
	function process(){
		if('download' == $_REQUEST['_process']){
			$file_id = (int)$_REQUEST['file_id'];
			$file_data = $this->get_file($file_id);
			if(is_file($file_data['file_path'])){
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false);
				//header("Content-Type: application/pdf");
                header("Content-type: ".mime_content_type($file_data['file_name']));
				header("Content-Disposition: attachment; filename=\"".$file_data['file_name']."\";");
				header("Content-Transfer-Encoding: binary");
				header("Content-Length: ".filesize($file_data['file_path']));
				readfile($file_data['file_path']);
			}else{
				echo 'Not found';
			}
			exit;
		}else if('save_file_popup' == $_REQUEST['_process']){
			$file_id = $_REQUEST['file_id'];

			$file_path = false;
			$file_name = false;

            $options = unserialize(base64_decode($_REQUEST['options']));

			// have we uploaded anything
			if(isset($_FILES['file_upload']) && is_uploaded_file($_FILES['file_upload']['tmp_name'])){
				// copy to file area.
				$file_name = basename($_FILES['file_upload']['name']);
				if($file_name){
					$file_path = 'includes/plugin_file/upload/'.md5(time().$file_name);
					if(move_uploaded_file($_FILES['file_upload']['tmp_name'],$file_path)){
						// it worked. umm.. do something.
					}else{
                        ?>
                    <script type="text/javascript">
                        alert('Unable to save file. Please check permissions.');
                    </script>
                    <?php
						// it didnt work. todo: display error.
                        $file_path = false;
                        $file_name = false;
                        //set_error('Unable to save file');
					}
				}
			}

			if(isset($_REQUEST['file_name']) && $_REQUEST['file_name']){
				$file_name = $_REQUEST['file_name'];
			}

            if(!$file_path || !$file_name){
                return false;
            }

			if(!$file_id || $file_id == 'new'){
				$file_data = array(
					'file_id' => $file_id,
					'owner_id' => (int)$_REQUEST['owner_id'],
					'owner_table' => $_REQUEST['owner_table'],
					'file_time' => time(), // allow UI to set a file time? nah.
					'file_name' => $file_name,
					'file_path' => $file_path,
				);
			}else{
				// some fields we dont want to overwrite on existing files:
				$file_data = array(
					'file_id' => $file_id,
					'file_path' => $file_path,
					'file_name' => $file_name,
				);
			}
			// make sure we're saving a file we have access too.
			module_security::sanatise_data('file',$file_data);
			$file_id = update_insert('file_id',$file_id,'file',$file_data);
			$file_data = $this->get_file($file_id);
			// we've updated from a popup.
			// this means we have to replace an existing file id with the updated output.
			// or if none exists on the page, we add a new one to the holder.
			$layout_type = (isset($_REQUEST['layout']) && $_REQUEST['layout']) ?$_REQUEST['layout'] : 'gallery';
			?>
			<script type="text/javascript">
				// check if it exists in parent window
				var new_html = '<?php echo addcslashes(preg_replace('/\s+/',' ',$this->print_file($file_id,$layout_type,true,$options)),"'");?>';
				parent.new_file_added<?php echo $file_data['owner_table'];?>_<?php echo $file_data['owner_id'];?>(<?php echo $file_id;?>,'<?php echo $file_data['owner_table'];?>',<?php echo $file_data['owner_id'];?>,new_html);
			</script>
			<?php
			exit;
		}else if('save_file' == $_REQUEST['_process']){
			$file_id = (int)$_REQUEST['file_id'];

			$file_path = false;
			$file_name = false;

            if(isset($_REQUEST['butt_del'])){
                if(module_form::confirm_delete('file_id','Really delete this file?')){
                    $file_data = $this->get_file($file_id);
                    if(true){ //module_security::can_access_data('file',$file_data,$file_id)){
                        // delete the physical file.
                        if(is_file($file_data['file_path'])){
                            unlink($file_data['file_path']);
                        }
                        // delete the db entry.
                        delete_from_db('file','file_id',$file_id);
                        set_message('File removed successfully');
                    }
                }
                redirect_browser(module_file::link_open(false));
            }else{

                // have we uploaded anything
                if(isset($_FILES['file_upload']) && is_uploaded_file($_FILES['file_upload']['tmp_name'])){
                    // copy to file area.
                    $file_name = basename($_FILES['file_upload']['name']);
                    if($file_name){
                        $file_path = 'includes/plugin_file/upload/'.md5(time().$file_name);
                        if(move_uploaded_file($_FILES['file_upload']['tmp_name'],$file_path)){
                            // it worked. umm.. do something.

                        }else{
                            // it didnt work. todo: display error.
                            $file_path = false;
                            $file_name = false;
                            set_error('Unable to save file');
                        }
                    }
                }

                if(!$file_id || $file_id == 'new'){
                    $file_data = array(
                        'file_id' => $file_id,
                        'customer_id' => isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : 0,
                        'job_id' => isset($_REQUEST['job_id']) ? (int)$_REQUEST['job_id'] : 0,
                        'website_id' => isset($_REQUEST['website_id']) ? (int)$_REQUEST['website_id'] : 0,
                        'status' => $_REQUEST['status'],
                        'description' => $_REQUEST['description'],
                        'file_time' => time(), // allow UI to set a file time? nah.
                        'file_name' => $file_name,
                        'file_path' => $file_path,
                    );
                }else{
                    // some fields we dont want to overwrite on existing files:
                    $file_data = array(
                        'file_id' => $file_id,
                        'file_path' => $file_path,
                        'file_name' => $file_name,
                        'customer_id' => isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : 0,
                        'job_id' => isset($_REQUEST['job_id']) ? (int)$_REQUEST['job_id'] : 0,
                        'website_id' => isset($_REQUEST['website_id']) ? (int)$_REQUEST['website_id'] : 0,
                        'status' => $_REQUEST['status'],
                        'description' => $_REQUEST['description'],
                    );
                }
                // make sure we're saving a file we have access too.
                module_security::sanatise_data('file',$file_data);
                $file_id = update_insert('file_id',$file_id,'file',$file_data);

                module_extra::save_extras('file','file_id',$file_id);

                set_message('File saved successfully');
                redirect_browser($this->link_open($file_id));
            }
		}else if('delete_file_popup' == $_REQUEST['_process']){
			$file_id = (int)$_REQUEST['file_id'];

			if(!$file_id || $file_id == 'new'){
				// cant delete a new file.. do nothing.
			}else{
				$file_data = $this->get_file($file_id);
				if(true){ //module_security::can_access_data('file',$file_data,$file_id)){
					// delete the physical file.
					if(is_file($file_data['file_path'])){
						unlink($file_data['file_path']);
					}
					// delete the db entry.
                    delete_from_db('file','file_id',$file_id);
					// update ui with changes.
					?>
					<script type="text/javascript">
						var new_html = '';
						parent.new_file_added<?php echo $file_data['owner_table'];?>_<?php echo $file_data['owner_id'];?>(<?php echo $file_id;?>,'<?php echo $file_data['owner_table'];?>',<?php echo $file_data['owner_id'];?>,new_html);
					</script>
					<?php
				}
			}
			exit;
		}

	}
	
	function save(){
		
	}

	function delete($file_id){
		$file_id=(int)$file_id;
		$sql = "DELETE FROM "._DB_PREFIX."file WHERE file_id = '".$file_id."' LIMIT 1";
		$res = query($sql);
	}

	public static function get_file($file_id){
		$file = get_single("file","file_id",$file_id);
        if(!$file){
            $file = array(
                'file_id' => 'new',
                'customer_id' => isset($_REQUEST['customer_id']) ? $_REQUEST['customer_id'] : 0,
                'job_id' => isset($_REQUEST['job_id']) ? $_REQUEST['job_id'] : 0,
                'description' => '',
                'status' => '',
                'file_name' => '',
            );
        }
		if($file){
			// optional processing here later on.
			
		}
		return $file;
	}

	public static function get_files($search=false){

        // build up a custom search sql query based on the provided search fields
        $sql = "SELECT f.* ";
        $from = " FROM `"._DB_PREFIX."file` f ";
        if(class_exists('module_customer',false)){
            $from .= " LEFT JOIN `"._DB_PREFIX."customer` c USING (customer_id)";
        }
        $where = " WHERE 1 ";
        if(isset($search['generic']) && $search['generic']){
            $str = mysql_real_escape_string($search['generic']);
            $where .= " AND ( ";
            $where .= " f.file_name LIKE '%$str%' ";
            //$where .= "OR  u.url LIKE '%$str%'  ";
            $where .= ' ) ';
        }
        if(isset($search['job']) && $search['job']){
            $str = mysql_real_escape_string($search['job']);
            $from .= " LEFT JOIN `"._DB_PREFIX."job` j USING (job_id)";
            $where .= " AND ( ";
            $where .= " j.name LIKE '%$str%' ";
            $where .= ' ) ';
        }
        foreach(array('customer_id','job_id','file_id','owner_id','owner_table','status') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND f.`$key` = '$str'";
            }
        }

        // permissions from customer module.
        // tie in with customer permissions to only get jobs from customers we can access.
        if(class_exists('module_customer',false)){ //added for compat in newsletter system that doesn't have customer module
            switch(module_customer::get_customer_data_access()){
                case _CUSTOMER_ACCESS_ALL:
                    // all customers! so this means all files!
                    break;
                case _CUSTOMER_ACCESS_CONTACTS:
                    // we only want customers that are directly linked with the currently logged in user contact.
                    if(isset($_SESSION['_restrict_customer_id']) && (int)$_SESSION['_restrict_customer_id']> 0){
                        // this session variable is set upon login, it holds their customer id.
                        // todo - share a user account between multiple customers!
                        //$where .= " AND c.customer_id IN (SELECT customer_id FROM )";
                        // we are searching for files that match this customer_id
                        // we are also search for files that have an owner_table of customer and an owner_id of $customer_id

                        $where .= " AND ( ";
                        if(isset($search['owner_table'])){
                            $where .= " (f.owner_table = 'customer' AND f.owner_id = '".(int)$_SESSION['_restrict_customer_id']."')";
                        }else{
                            //$where .= " OR ";
                            $where .= " (f.customer_id = '".(int)$_SESSION['_restrict_customer_id']."')";
                        }
                        $where .= " ) ";
                    }
                    break;
                case _CUSTOMER_ACCESS_TASKS:
                    // only customers who have a job that I have a task under.
                    // this is different to "assigned jobs" Above
                    // this will return all jobs for a customer even if we're only assigned a single job for that customer
                    // tricky!
                    // copied from customer.php
                    //$where .= " AND ( f.customer_id IS NULL OR f.customer_id = 0 OR f.customer_id IN ";
                    $where .= " AND ( f.customer_id IN ";
                    $where .= " ( SELECT cc.customer_id FROM `"._DB_PREFIX."customer` cc ";
                    $where .= " LEFT JOIN `"._DB_PREFIX."job` jj ON cc.customer_id = jj.customer_id ";
                    $where .= " LEFT JOIN `"._DB_PREFIX."task` tt ON jj.job_id = tt.job_id ";
                    $where .= " WHERE (jj.user_id = ".(int)module_security::get_loggedin_id()." OR tt.user_id = ".(int)module_security::get_loggedin_id().")";
                    $where .= " )";
                    $where .= " )";

                    break;
            }
        }


        $group_order = ' GROUP BY f.file_id ORDER BY f.file_name'; // stop when multiple company sites have same region
        $sql = $sql . $from . $where . $group_order;
        $result = qa($sql);
        //module_security::filter_data_set("invoice",$result);
        return $result;
		//return get_multiple("file",$search,"file_id","exact","file_id");
	}


    public static function format_bytes($size) {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
        return round($size, 2).$units[$i];
    }

    public function get_install_sql(){
        ob_start();
        ?>

    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX;?>file` (
      `file_id` int(11) NOT NULL AUTO_INCREMENT,
      `customer_id` int(11) NULL,
      `job_id` int(11) NULL,
      `owner_id` int(11) NULL,
      `owner_table` varchar(80) NULL,
      `file_path` varchar(100) NULL,
      `file_name` varchar(100) NULL,
      `file_time` int(11) NULL,
      `status` varchar(100) NULL,
      `description` TEXT NOT NULL,
      `date_created` datetime NOT NULL,
      `date_updated` datetime NULL,
      `create_user_id` int(11) NOT NULL,
      `update_user_id` int(11) NULL,
      `create_ip_address` varchar(15) NOT NULL,
      `update_ip_address` varchar(15) NULL,
      PRIMARY KEY (`file_id`),
      KEY `group_id` (`owner_id`),
      KEY `group_key` (`owner_table`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    <?php
    return ob_get_clean();
    }

    public static function get_statuses() {

        $sql = "SELECT `status` FROM `"._DB_PREFIX."file` GROUP BY `status` ORDER BY `status`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['status']] = $r['status'];
        }
        return $statuses;
    }



    public static function link_public_view($file_id,$h=false){
        if($h){
            return md5('s3cret7hash '._UCM_FOLDER.' '.$file_id);
        }
        if(_REWRITE_LINKS){
            return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.file/h.download/i.'.$file_id.'/hash.'.self::link_public_view($file_id,true));
        }else{
            return full_link(_EXTERNAL_TUNNEL.'?m=file&h=download&i='.$file_id.'&hash='.self::link_public_view($file_id,true));
        }
    }

    public function external_hook($hook){
        switch($hook){
            case 'download':
                $file_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($file_id && $hash){
                    $correct_hash = $this->link_public_view($file_id,true);
                    if($correct_hash == $hash){
                        // all good to print a receipt for this payment.
                        $file_data = $this->get_file($file_id);
                        if(is_file($file_data['file_path'])){
                            header("Pragma: public");
                            header("Expires: 0");
                            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                            header("Cache-Control: private",false);
                            header("Content-Type: application/pdf");
                            header("Content-Disposition: attachment; filename=\"".$file_data['file_name']."\";");
                            header("Content-Transfer-Encoding: binary");
                            header("Content-Length: ".filesize($file_data['file_path']));
                            readfile($file_data['file_path']);
                        }else{
                            echo 'Not found';
                        }
                    }
                }
                exit;
                break;
        }
    }

    public static function customer_id_changed($old_customer_id, $new_customer_id) {
        $old_customer_id = (int)$old_customer_id;
        $new_customer_id = (int)$new_customer_id;
        if($old_customer_id>0 && $new_customer_id>0){
            $sql = "UPDATE `"._DB_PREFIX."file` SET customer_id = ".$new_customer_id." WHERE customer_id = ".$old_customer_id;
            query($sql);
        }
    }

    public static function delete_files($owner_table, $owner_id) {
        $sql = "DELETE FROM `"._DB_PREFIX."file` WHERE owner_table = '".mysql_real_escape_string($owner_table)."' AND owner_id = '".mysql_real_escape_string($owner_id)."'";
        query($sql);
    }

}

