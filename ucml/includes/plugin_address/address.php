<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:18:39 
  * IP Address: 127.0.0.1
  */



class module_address extends module_base{
	
	public $links;
	public $address_types;

    public $version = 2.11;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->links = array();
		$this->address_types = array();
		$this->module_name = "address";
		$this->module_position = 101;

		$this->link_include_parents = true; // set defaullt

	}

	function handle_hook($hook,&$calling_module=false,$address_type=false,$owner_table=false,$key_name=false,$key_value=false){

		if(!$address_type){
			$address_type = "main";
		}
		// find the key we are saving this address against.
		$owner_id = $key_value;
		if(!$owner_id || $owner_id == 'new'){
			// find one in the post data.
			if(isset($_REQUEST[$key_name])){
				$owner_id = $_REQUEST[$key_name];
			}
		}
		$address_hash = md5($owner_table.'|'.$address_type); // just for posting unique arrays.
		switch($hook){
			case "address_block_save":
				// we have to use this 3 key to find an address because
				// we could be saving a new record with no existing address_id
		        $address = $this->get_address( $owner_id, $owner_table, $address_type);
				$address_id = $address['address_id'];
		        $address_post_data = isset($_POST['address'][$address_hash]) ? $_POST['address'][$address_hash] : array();
		        if($address_post_data){
			        $address_post_data['owner_id'] = $owner_id; // incase on new save.
			        // we have post data to save, write it to the table!!
			        $this->save_address($address_id,$address_post_data);
		        }
		        break;
			case "address_block_delete":
				// we have to use this 3 key to find an address because
				// we could be saving a new record with no existing address_id
		        $address = $this->get_address( $owner_id, $owner_table, $address_type);
				$address_id = (int)$address['address_id'];
		        if($address_id){
			        $sql = "DELETE FROM `"._DB_PREFIX."address` WHERE address_id = '$address_id' LIMIT 1";
			        query($sql);
		        }
		        break;
			case "address_block":
				$address = $this->get_address( $owner_id, $owner_table, $address_type);
				$address_id = $address['address_id'];
				//module_address::print_address_form($address_id,$address);
				include("pages/address_block.php");
				break;
			case "address_delete":

				if($owner_table && $owner_id){
					$sql = "DELETE FROM `"._DB_PREFIX."address` WHERE owner_table = '".mysql_real_escape_string($owner_table)."'
					AND owner_id = '".mysql_real_escape_string($owner_id)."'";
					$res = query($sql);
				}
				break;

		}
	}

	public function process(){
		$errors=array();
		if("save_from_popup" == $_REQUEST['_process']){
			// dont use the normal hook to save, its gay way of saving.
			// look at post data.
			if(isset($_POST['address']) && is_array($_POST['address'])){
				foreach($_POST['address'] as $address_hash=>$address_data){
					if(isset($address_data['address_id']) && (int)$address_data['address_id']){
						$this->save_address($address_data['address_id'],$address_data);
					}
				}
			}
		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		print_error($errors,true);
	}

	public static function save_address($address_id,$data){
		return update_insert('address_id',$address_id,'address',$data);
	}

	/*public static function print_address_form($address_id,$address=array()){
		if(!$address){
			global $plugins;
			$address = $plugins['address']->get_address_by_id($address_id);
		}
		include("pages/address_block.php");
	}*/

	public static function print_address($owner_id,$owner_table,$address_type,$output='html',$restrict=array()){
		global $plugins;
		$address = $plugins['address']->get_address($owner_id,$owner_table,$address_type);
		if(!$restrict){
			$restrict = array('line_1','suburb','state','post_code');
		}
		$hash = md5(implode(',',$restrict).$owner_id);
		switch($output){
			case 'html':
				$address_output = '';
		        ?>
				<span class="address">
					<?php
					foreach($restrict as $key){
						if(isset($address[$key]) && $address[$key]){
							$address_output .= $address[$key].', ';
						}
					}
					$address_output = rtrim($address_output,', ');
					if($address_output != ''){
						echo htmlspecialchars($address_output);
						?>
						<a href="#" id="address_popup_<?php echo $hash;?>_go">&raquo;</a>
						<div id="address_popup_<?php echo $hash;?>" title="<?php _e('Edit Address');?>">
							<div class="modal_inner"></div>
						</div>
						<?php
					}
					?>
				</span>

				<?php if($address_output != ''){ ?>
				<script type="text/javascript">
					$(function(){
						$("#address_popup_<?php echo $hash;?>").dialog({
							autoOpen: false,
							width: 400,
							height: 350,
							modal: true,
							buttons: {
								'<?php _e('Save Address');?>': function() {
									$('form',this)[0].submit();
								},
								'<?php _e('Cancel');?>': function() {
									$(this).dialog('close');
								}
							},
							open: function(){
								var t = this;
								$.ajax({
									type: "GET",
									url: '<?php echo $plugins['address']->link(
										'address_popup',
										array('address_id'=>$address['address_id']),
										'address',
										false
									);?>',
									dataType: "html",
									success: function(d){
										$('.modal_inner',t).html(d);
										$('.redirect',t).val(window.location.href);
										load_calendars();
									}
								});
							},
							close: function() {
								$('.modal_inner',this).html('');
							}
						});
						$('#address_popup_<?php echo $hash;?>_go').click(function(){
							$("#address_popup_<?php echo $hash;?>").dialog('open');
							return false;
						});
					});
				</script>
		        <?php
				}
		        break;
		}
	}

	public static function get_address($owner_id,$owner_table,$address_type){
		if(!$owner_id)return array();
        $sql = "SELECT a.*, address_id AS id ";
        //$sql .= " ,s.`state`, r.`region`, c.`country` ";
        $sql .= " FROM `"._DB_PREFIX."address` a ";
//        $sql .= " LEFT JOIN `"._DB_PREFIX."address_state` s ON a.state_id = s.state_id ";
//        $sql .= " LEFT JOIN `"._DB_PREFIX."address_region` r ON a.region_id = r.region_id ";
//        $sql .= " LEFT JOIN `"._DB_PREFIX."address_country` c ON a.country_id = c.country_id ";
        $sql .= "WHERE";
        $sql .= " a.`owner_id` = ".(int)$owner_id."";
        $sql .= " AND a.`owner_table` = '".mysql_real_escape_string($owner_table)."'";
        $sql .= " AND a.`address_type` = '".mysql_real_escape_string($address_type)."'";
        return array_shift(qa($sql));
//		return array_shift(get_multiple("address",array('owner_id'=>$owner_id,'owner_table'=>$owner_table,'address_type'=>$address_type),"owner_id"));
	}
	public static function get_address_by_id($address_id){

		return get_single("address",'address_id',$address_id);
	}

    public function get_upgrade_sql(){
        $sql = '';
        $fields = get_fields('address');
        if(isset($fields['post_code']) && $fields['post_code']['maxlength'] < 10){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'address`  CHANGE  `post_code` `post_code` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT \'\';';
        }
        return $sql;
    }
    public function get_install_sql(){
        ob_start();
        ?>

CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>address` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `owner_table` varchar(30) NOT NULL,
  `address_type` varchar(255) NOT NULL,
  `line_1` varchar(50) NOT NULL DEFAULT '',
  `line_2` varchar(50) NOT NULL DEFAULT '',
  `suburb` varchar(40) NOT NULL DEFAULT '',
  `state` varchar(40) NOT NULL DEFAULT '',
  `region` varchar(40) NOT NULL DEFAULT '',
  `country` varchar(40) NOT NULL DEFAULT '',
  `post_code` varchar(10) NOT NULL DEFAULT '',
  `date_created` date NOT NULL,
  `date_updated` date DEFAULT NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NOT NULL DEFAULT '0',
  `create_ip_address` varchar(15) NOT NULL,
  `update_ip_address` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`address_id`),
  UNIQUE KEY `owner_id` (`owner_id`,`owner_table`,`address_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            
<?php
        return ob_get_clean();
    }
	
}