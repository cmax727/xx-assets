<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:20 
  * IP Address: 127.0.0.1
  */

define('_EXTRA_FIELD_DELIM','$#%|');

function sort_extras($a,$b){
    return $a['extra_time'] < $b['extra_time'];
}

class module_extra extends module_base{
	
	var $links;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
        $this->version = 2.11;
		$this->links = array();
		$this->module_name = "extra";
		$this->module_position = 8882;
        module_config::register_css('extra','extra.css');
	}
	
	public static function display_extras($options){
		$owner_id = (isset($options['owner_id']) && $options['owner_id']) ? (int)$options['owner_id'] : false;
		$owner_table = (isset($options['owner_table']) && $options['owner_table']) ? $options['owner_table'] : false;
		$layout = (isset($options['layout']) && $options['layout']) ? $options['layout'] : false;
        $html = '';
		if($owner_id && $owner_table){
			// we have all that we need to display some extras!! yey!!
			$extra_items = self::get_extras(array('owner_table'=>$owner_table,'owner_id'=>$owner_id));
			foreach($extra_items as $extra_item){
                $extra_id=$extra_item['extra_id'];
				ob_start();
                ?>
                <tr id="extra_<?php echo $extra_id;?>">
                    <th>
                        <span class="extra_field_key" onclick="$(this).hide(); $(this).parent().find('input').show();"><?php echo htmlspecialchars($extra_item['extra_key']);?></span>
                        <input type="text" name="extra_<?php echo $owner_table;?>_field[<?php echo $extra_id;?>][key]" value="<?php echo htmlspecialchars($extra_item['extra_key']);?>" class="extra_field" style="display:none;">
                    </th>
                    <td>
                        <input type="text" name="extra_<?php echo $owner_table;?>_field[<?php echo $extra_id;?>][val]" value="<?php echo htmlspecialchars($extra_item['extra']);?>">
                    </td>
                </tr>
                <?php
                $html .= ob_get_clean();
			}
            if(module_security::is_page_editable()){
            $extra_id = 'new';
            ob_start();
            ?>
            <tr id="extra_<?php echo $owner_table;?>_options_<?php echo $extra_id;?>" <?php if(!module_config::c('hide_extra',1)){ ?>style="display:none;"<?php } ?>>
                <th>

                </th>
                <td>
                    <a href="#" onclick="$('#extra_<?php echo $owner_table;?>_options_<?php echo $extra_id;?>').hide();$('#extra_<?php echo $owner_table;?>_holder_<?php echo $extra_id;?>').show(); return false;"><?php _e('more fields &raquo;');?></a>
                </td>
            </tr>
            <tbody id="extra_<?php echo $owner_table;?>_holder_<?php echo $extra_id;?>" <?php if(module_config::c('hide_extra',1)){ ?>style="display:none;"<?php } ?>>
            <!-- show all other options here from this $owner_table -->
            <?php
            $defaultid = 0;
            foreach(self::get_defaults($owner_table) as $default){
                $defaultid ++;
                // check this key islany already existing.
                foreach($extra_items as $extra_item){
                    if($extra_item['extra_key'] == $default['key'])continue 2;
                }
                ?>
                <tr>
                    <th>
                        <span class="extra_field_key" onclick="$(this).hide(); $(this).parent().find('input').show();"><?php echo htmlspecialchars($default['key']);?></span>
                        
                        <input type="text" name="extra_<?php echo $owner_table;?>_field[new<?php echo $defaultid;?>][key]" value="<?php echo $default['key']; ?>" class="extra_field" style="display:none;">
                    </th>
                    <td>
                        <input type="text" name="extra_<?php echo $owner_table;?>_field[new<?php echo $defaultid;?>][val]" value="<?php ?>">
                    </td>
                </tr>
            <?php } ?>
            <tr id="extra_<?php echo $extra_id;?>">
                <th>
                    <input type="text" name="extra_<?php echo $owner_table;?>_field[<?php echo $extra_id;?>][key]" value="<?php ?>" class="extra_field">
                </th>
                <td>
                    <input type="text" name="extra_<?php echo $owner_table;?>_field[<?php echo $extra_id;?>][val]" value="<?php ?>">
                    <?php _h('Enter anything you like in this blank field. eg: Passwords, Links, Notes, etc..'); ?>
                </td>
            </tr>
            </tbody>
            <?php
            $html .= ob_get_clean();
            }
		}
        print $html;
	}

    public static function save_extras($owner_table,$owner_key,$owner_id){
        if(isset($_REQUEST['extra_'.$owner_table.'_field']) && is_array($_REQUEST['extra_'.$owner_table.'_field'])){
            $owner_id = (int)$owner_id;
            if($owner_id<=0){
                if(isset($_REQUEST[$owner_key])){
                    $owner_id = (int)$_REQUEST[$owner_key];
                }
            }
            if($owner_id<=0)return; // failed for some reason?
            $existing_extras = self::get_extras(array('owner_table'=>$owner_table,'owner_id'=>$owner_id));
            foreach($_REQUEST['extra_'.$owner_table.'_field'] as $extra_id => $extra_data){
                $key = trim($extra_data['key']);
                $val = trim($extra_data['val']);
                if(!$key || $val==''){
                    unset($_REQUEST['extra_'.$owner_table.'_field'][$extra_id]);
                    continue;
                }
                $extra_id = (int)$extra_id;
                $extra_db = array(
                    'extra_key' => $key,
                    'extra' => $val,
                    'owner_table' => $owner_table,
                    'owner_id' => $owner_id,
                );
                $extra_id = update_insert('extra_id',$extra_id,'extra',$extra_db);
            }
            // work out which ones were not saved.
            foreach($existing_extras as $existing_extra){
                if(!isset($_REQUEST['extra_'.$owner_table.'_field'][$existing_extra['extra_id']])){
                    // remove it.
                    $sql = "DELETE FROM "._DB_PREFIX."extra WHERE extra_id = '".(int)$existing_extra['extra_id']."' LIMIT 1";
                    query($sql);
                }
            }
		}
    }

	public static function delete_extras($owner_table,$owner_key,$owner_id){
		$extra_items = self::get_extras(array('owner_table'=>$owner_table,'owner_id'=>$owner_id));
        foreach($extra_items as $extra_item){
            $sql = "DELETE FROM "._DB_PREFIX."extra WHERE extra_id = '".(int)$extra_item['extra_id']."' LIMIT 1";
            query($sql);
        }

    }
	public static function get_extra($extra_id){
		$extra = get_single("extra","extra_id",$extra_id);
		if($extra){
			// optional processing here later on.
		}
		return $extra;
	}

	public static function get_extras($search=false){
		return get_multiple("extra",$search,"extra_id","exact","extra_id");
	}

    public function get_install_sql(){
        return 'CREATE TABLE `'._DB_PREFIX.'extra` (
  `extra_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `owner_table` varchar(80) NOT NULL,
  `extra_key` varchar(100) NOT NULL,
  `extra` longtext NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `create_ip_address` varchar(15) NOT NULL,
  `update_ip_address` varchar(15) NULL,
  PRIMARY KEY (`extra_id`),
  KEY `owner_id` (`owner_id`),
  KEY `owner_table` (`owner_table`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;';
    }

    /**
     * @static
     * @param $owner_table
     * @return array
     *
     * search the extra fields for default keys
     * (ie: keys that have been used on this owner_table before)
     * 
     */
    public static function get_defaults($owner_table) {

        // todo - search database for keys.
        $sql = "SELECT `extra_key` FROM `"._DB_PREFIX."extra` e WHERE e.owner_table = '".mysql_real_escape_string($owner_table)."' GROUP BY e.extra_key";
        $defaults = array();
        foreach(qa($sql) as $r){
            $defaults[] = array(
                'key' => $r['extra_key'],
            );
        }

/*        switch($owner_table){
            case 'website':
                $defaults = array(
                    array('key' => 'FTP Username',),
                    array('key' => 'FTP Password',),
                    array('key' => 'FTP Provider',),
                    array('key' => 'Host Username',),
                    array('key' => 'Host Password',),
                    array('key' => 'Host Provider',),
                    array('key' => 'WordPress User',),
                    array('key' => 'WordPress Pass',),
                    array('key' => 'Analytics Account',),
                    array('key' => 'Webmaster Account',),
                );
                break;
        }*/
        return $defaults;
    }
}