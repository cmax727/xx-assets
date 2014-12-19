<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:39 
  * IP Address: 127.0.0.1
  */


/**
 * FORM CLASS
 * Version: 2
 */
class module_form extends module_base{

    static $form_options = array();
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
    public $version = 2.1;
	public static function get_class() {
        return __CLASS__;
    }
    public function init(){
		$this->module_name = "form";
		$this->module_position = 0;
    }
    public static function init_form(){
        static $init_complete = false;
        if($init_complete)return;
        // we load any form settings from the session into
        // our local static variable so we can process things like required fields.
        if(isset($_SESSION['_plugin_form'])){
            self::$form_options = $_SESSION['_plugin_form'];
        }else{
            $_SESSION['_plugin_form'] = array();
        }
        $init_complete = true;
    }
    public static function save($form_name,$form){
        self::$form_options[$form_name] = $form;
        $_SESSION['_plugin_form'] = self::$form_options; // save for later.
    }
    public static function clear($form_name){
        if(isset(self::$form_options[$form_name])){
            unset(self::$form_options[$form_name]);
        }
        $_SESSION['_plugin_form'] = self::$form_options; // save for later.
    }

    private static function _get_error_msg($error_fields=array()){
        ob_start();
        ?>
        <div class="ui-widget">
                <div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
                    <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
                    <strong>Alert:</strong> Required fields missing.</p>
                    <?php if($error_fields){ ?>
                    <ul>
                        <?php foreach($error_fields as $field=>$value){ ?>
                        <li><?php echo htmlspecialchars($value);?></li>
                        <?php } ?>
                    </ul>
                    <?php } ?>
                </div>
            </div>
        <?php
        return preg_replace('/\s+/',' ',ob_get_clean());
    }
    private static function _serialize_post(&$data,$post_data,$prefix='') {
		foreach($post_data as $key=>$val){
			if(preg_match('/^form_saver_/',$key)){
				continue;
			}else if(is_array($val)){
				self::_serialize_post($data,$val,$key);
			}else{
				// normal string, just add it to the array.
				$data[$prefix.(strlen($prefix)?'[':'').$key.(strlen($prefix)?']':'')] = $val;
			}
		}
	}
    public static function set_required($options){
        self::init_form();
        $form_name = (isset($options['form_name'])) ? $options['form_name'] : md5($_SERVER['REQUEST_URI']);
        $required_fields = (isset($options['fields']) && is_array($options['fields'])) ? $options['fields'] : array();
        $required_email_fields = (isset($options['emails']) && is_array($options['emails'])) ? $options['emails'] : array();
        $form = isset(self::$form_options[$form_name]) ? self::$form_options[$form_name] : array();
        $form['required_fields'] = $required_fields;
        $form['return_url'] = $_SERVER['REQUEST_URI']; // wont work for post data.
        $error_fields = isset($form['error_fields']) ? $form['error_fields'] : array();
        $data_to_load = isset($form['data_to_load']) ? $form['data_to_load'] : array();
        ?>
        <span id="plugin_form_header_<?php echo htmlspecialchars($form_name);?>">
            <?php
            if(isset($form['show_error']) && $form['show_error']){
                echo self::_get_error_msg($error_fields);
            } ?>
        </span>
        <input type="hidden" name="_plugin_form_name" id="_plugin_form_<?php echo htmlspecialchars($form_name);?>" value="<?php echo htmlspecialchars($form_name);?>">
        <?php
        // now some javascript to apply 'required' fields to all specified
        ?>
        <script type="text/javascript">
            <?php if($data_to_load){ ?>
            if(typeof Base64 == 'undefined'){
                var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(a){var b="";var c,chr2,chr3,enc1,enc2,enc3,enc4;var i=0;a=Base64._utf8_encode(a);while(i<a.length){c=a.charCodeAt(i++);chr2=a.charCodeAt(i++);chr3=a.charCodeAt(i++);enc1=c>>2;enc2=((c&3)<<4)|(chr2>>4);enc3=((chr2&15)<<2)|(chr3>>6);enc4=chr3&63;if(isNaN(chr2)){enc3=enc4=64}else if(isNaN(chr3)){enc4=64}b=b+this._keyStr.charAt(enc1)+this._keyStr.charAt(enc2)+this._keyStr.charAt(enc3)+this._keyStr.charAt(enc4)}return b},decode:function(a){var b="";var c,chr2,chr3;var d,enc2,enc3,enc4;var i=0;a=a.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(i<a.length){d=this._keyStr.indexOf(a.charAt(i++));enc2=this._keyStr.indexOf(a.charAt(i++));enc3=this._keyStr.indexOf(a.charAt(i++));enc4=this._keyStr.indexOf(a.charAt(i++));c=(d<<2)|(enc2>>4);chr2=((enc2&15)<<4)|(enc3>>2);chr3=((enc3&3)<<6)|enc4;b=b+String.fromCharCode(c);if(enc3!=64){b=b+String.fromCharCode(chr2)}if(enc4!=64){b=b+String.fromCharCode(chr3)}}b=Base64._utf8_decode(b);return b},_utf8_encode:function(a){a=a.replace(/\r\n/g,"\n");var b="";for(var n=0;n<a.length;n++){var c=a.charCodeAt(n);if(c<128){b+=String.fromCharCode(c)}else if((c>127)&&(c<2048)){b+=String.fromCharCode((c>>6)|192);b+=String.fromCharCode((c&63)|128)}else{b+=String.fromCharCode((c>>12)|224);b+=String.fromCharCode(((c>>6)&63)|128);b+=String.fromCharCode((c&63)|128)}}return b},_utf8_decode:function(a){var b="";var i=0;var c=c1=c2=0;while(i<a.length){c=a.charCodeAt(i);if(c<128){b+=String.fromCharCode(c);i++}else if((c>191)&&(c<224)){c2=a.charCodeAt(i+1);b+=String.fromCharCode(((c&31)<<6)|(c2&63));i+=2}else{c2=a.charCodeAt(i+1);c3=a.charCodeAt(i+2);b+=String.fromCharCode(((c&15)<<12)|((c2&63)<<6)|(c3&63));i+=3}}return b}}
            }
            <?php } ?>

            $(function(){
                var plugin_form_clicked = false;
                var plugin_form_fields = <?php
                    echo json_encode($required_fields);
                ?>;
                var plugin_email_fields = <?php
                    echo json_encode($required_email_fields);
                ?>;
                var plugin_error_fields = <?php
                    echo json_encode($error_fields);
                ?>;
                <?php
                // code copied from my "save form for later" program
                if($data_to_load){ ?>
                    var form_saver_permitted_input_types = {};
                    <?php foreach(explode(',','textarea,text,radio,hidden,checkbox,password,select,select-one') as $type){
                        $type = trim($type);
                        if(!$type)continue;
                        ?>
                        form_saver_permitted_input_types['<?php echo $type;?>'] = 'yes';
                    <?php } ?>
                    // all saved data, base64encoded for basic js syntax error safety.
                    var form_saver_data = {};
                    <?php foreach($data_to_load as $key=>$val){ ?>
                        form_saver_data['<?php echo base64_encode($key);?>'] = '<?php echo base64_encode($val);?>';
                    <?php } ?>
                <?php } ?>

                var plugin_form_frm = $('#_plugin_form_<?php echo htmlspecialchars($form_name);?>').parents('form');
                if(typeof plugin_form_frm == 'undefined' || !plugin_form_frm){
                    alert('Form Plugin initialisation failed. Contact developer.');
                    return;
                }
                $(':submit',plugin_form_frm).mousedown(function() {
                    plugin_form_clicked = this;
                });
                // loop through all applicable input options and apply required javascript/class
                $('input,textarea,select',plugin_form_frm).each(function(){
                    var n = $(this).attr('name');
                    var attr_type = (jQuery(this).attr('type')+'').toLowerCase();
                    if(attr_type == 'hidden')return;
                    if(typeof plugin_form_fields[n] != 'undefined'){
                        $(this).addClass('plugin_form_required');
                        $(this).after(' <span class="required">*</span>');
                    }
                    if(typeof plugin_email_fields[n] != 'undefined'){
                        jQuery(this).addClass('plugin_form_required_email');
                    }
                    if(typeof plugin_error_fields[n] != 'undefined'){
                        $(this).addClass('ui-state-error');
                    }
                    <?php if($data_to_load){ ?>
                    if(typeof form_saver_data[Base64.encode(jQuery(this).attr('name'))] != 'undefined'){
                        // see if this is in one of the permitted form types.
                        if(typeof form_saver_permitted_input_types[attr_type] == 'undefined' || form_saver_permitted_input_types[attr_type] != 'yes'){
                            return; // skip this input. not allowed.
                        }
                        var attr_value = Base64.decode(form_saver_data[Base64.encode(jQuery(this).attr('name'))]);
                        if(jQuery(this)[0].disabled){
                            // don't update disabled elements.
                        }else if(attr_type == 'radio' || attr_type == 'checkbox'){
                            if(jQuery(this).val() == attr_value){
                                jQuery(this)[0].checked=true;
                            }
                        }else{
                            // it's a normal input box that we can update it's value.
                            jQuery(this).val(attr_value);
                        }
                    }
                    <?php } ?>
                });
                $(plugin_form_frm).submit(function(){
                    // check required fields on submit
                    if(plugin_form_clicked && ($(plugin_form_clicked).attr('name').match(/cancel/i) || $(plugin_form_clicked).attr('name').match(/butt_del/i))){
                        $('#_plugin_form_<?php echo htmlspecialchars($form_name);?>').after('<input type="hidden" name="_plugin_form_cancel" value="true">');
                        return true;
                    }
                    var plugin_form_error = false;
                    $('.plugin_form_required',this).each(function(){
                        if(!jQuery(this)[0].disabled && (jQuery(this).hasClass('plugin_form_required_email'))){
                            var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                           var address = jQuery(this).val();
                           if(!reg.test(address)) {
                              jQuery(this).addClass('ui-state-error');
                                if(!plugin_form_error){
                                    // focus first element.
                                    jQuery(this)[0].focus();
                                }
                                plugin_form_error = true;
                                var chge = function(){
                                    if(reg.test(jQuery(this).val())){
                                        jQuery(this).removeClass('ui-state-error');
                                    }else{
                                        jQuery(this).addClass('ui-state-error');
                                    }
                                };
                                jQuery(this).keyup(chge).change(chge);
                           }
                        }
                        if(!$(this)[0].disabled && ($(this).val() == '' || !$(this).val())){
                            $(this).addClass('ui-state-error');
                            if(!plugin_form_error){
                                // focus first element.
                                $(this)[0].focus();
                            }
                            plugin_form_error = true;
                            var chg = function(){
                                if($(this).val() != '' || $(this).val()){
                                    $(this).removeClass('ui-state-error');
                                }else{
                                    $(this).addClass('ui-state-error');
                                }
                            };
                            $(this).keyup(chg).change(chg);
                        }
                    });
                    if(plugin_form_error){
                        // show error message
						<?php if(isset($options['fail_js']) && $options['fail_js']){
							echo $options['fail_js'];
						} ?>
						alert('Required fields missing, please complete required fields.');
                        /*$('#plugin_form_header_<?php echo htmlspecialchars($form_name);?>').html('<?php echo addcslashes(self::_get_error_msg(),"'");?>');*/
                    }
                    return !plugin_form_error;
                });
            });
        </script>
        <?php
        self::save($form_name,$form);
    }

    public static function check_required(){
        self::init_form();
        // check if any of these forms have been submitted right now.
        $form_name = isset($_REQUEST['_plugin_form_name']) ? $_REQUEST['_plugin_form_name'] : false;
        // check if a cancel button was clicked.
        if($form_name){
            if(isset($_REQUEST['_plugin_form_cancel'])){
                self::clear($form_name);
                return true;
            }else{
                // incase their browser doesn't support javascript.
                // we hackishly look to see ifa  cancel button was clicked.
                foreach($_REQUEST as $key=>$val){
                    if(preg_match('/cancel/i',$key) && preg_match('/cancel/i',$val)){
                        self::clear($form_name);
                        return true;
                    }
                }
            }
        }
        if(isset($form_name) && isset(self::$form_options[$form_name])){
            $form = self::$form_options[$form_name];
            $form['error_fields'] = array();
            $form['show_error'] = false;
            $required_fields = isset($form['required_fields']) ? $form['required_fields'] : array();
            // check these required fields are set. if not, we redirect (via POST) user back to where they came from along with message.
            $error_fields = array();
            foreach($required_fields as $field=>$name){
                if(!isset($_REQUEST[$field]) || !trim($_REQUEST[$field])){
                    $error_fields[$field] = $name;
                }
            }
            if($error_fields){
                $form['error_fields'] = $error_fields;
                // we also remember all posted data, so that it can be re-inserted back into
                // the form upon redirect. useful when creating 'new' records etc..
                $form['data_to_load'] = array();
                self::_serialize_post($form['data_to_load'],array_merge($_POST,$_GET),'');
                header("Location: ".$form['return_url']);
                $form['show_error'] = true;
                self::save($form_name,$form);
                exit;
            }
            self::clear($form_name);
        }
    }


    public static function confirm_delete($post_key,$message,$cancel_url=''){
        if(!isset($_SESSION['_delete_data'])){
            $_SESSION['_delete_data'] = array();
        }
        $hash = md5('delete '.$post_key.' '.(isset($_REQUEST[$post_key])?$_REQUEST[$post_key]:''));
        if(isset($_REQUEST['_confirm_delete']) && $_REQUEST['_confirm_delete'] == $hash){
            // the user has clicked on the confirm delete button!
            if(isset($_SESSION['_delete_data'][$hash])){
                unset($_SESSION['_delete_data'][$hash]);
            }
            return true;
        }
        // we take the post data, and check if we're confirming or not.
        if(!$cancel_url)$cancel_url = $_SERVER['REQUEST_URI'];
        $post_data = $_POST;
        $post_uri = $_SERVER['REQUEST_URI'];
        // serialise this data and redirect to the delete confirm page.
        $data = array($message,$post_data,$post_uri,$cancel_url,$post_key);
        $_SESSION['_delete_data'][$hash] = $data;
        //redirect_browser(_BASE_HREF.'form.confirm_delete/?hash='.$hash);
        redirect_browser(_BASE_HREF.'?m[0]=form&p[0]=confirm_delete&hash='.$hash);
        return false;
    }


    public static function prevent_exit($options){
        $valid_exits = $options['valid_exits'];
        $id = md5(mt_rand(0,100));
        ?>
        <input type="hidden" name="prevent_exit_<?php echo $id;?>" id="prevent_exit_<?php echo $id;?>" value="true">
        <script type="text/javascript">
            var change_detected = false;
            $(function(){
                var plugin_form_prevent_exit = $('#prevent_exit_<?php echo $id;?>').parents('form');
                if(typeof plugin_form_prevent_exit == 'undefined' || !plugin_form_prevent_exit){
                    alert('Form Plugin initialisation failed. Contact developer.');
                    return;
                }
                $('input,select,textarea',plugin_form_prevent_exit).change(function(){
                    change_detected = true;
                });
                <?php foreach($valid_exits as $valid_exit){ ?>
                $('<?php echo $valid_exit;?>',plugin_form_prevent_exit).click(function(){
                    change_detected = false;
                });
                <?php } ?>
            });
            window.onbeforeunload = function(){
                // check for changes to the form.
                if(change_detected){
                    return 'Leave page and discard changes?';
                }
            };
        </script>
        <?php
    }

    private static $_default_field = false;
    public static function set_default_field($field) {
        if(self::$_default_field)return false;
        self::$_default_field = $field;
        ?>
            <script type="text/javascript">
                $(function(){
                    if($('#<?php echo $field;?>').length>0){
                        $('#<?php echo $field;?>')[0].focus();
                    }
                });
            </script>
        <?php
    }

}