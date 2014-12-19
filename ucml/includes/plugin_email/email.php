<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:12 
  * IP Address: 127.0.0.1
  */


define('_MAIL_STATUS_OVER_QUOTA',5);
define('_MAIL_STATUS_SENT',2);
define('_MAIL_STATUS_FAILED',4);

class module_email extends module_base{

	public $replace_values;
	public $db_fields;

	public $to = array();
	public $cc = array();
	public $bcc = array();
    public $message_html;
    public $from;
    public $attachments;
    public $message_text;
    public $subject;
    public $sent_time;
    public $status;
    public $reply_to;
    public $bounce_address = '';
    public $error_text;
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
    function init(){
		$this->module_name = "email";
		$this->module_position = 1666;

        $this->version = 2.2;

		// the link within Admin > Settings > Emails.
		if($this->can_i('view','Email Settings','Config')){
			$this->links[] = array(
				"name"=>"Email",
				"p"=>"email_settings",
				"icon"=>"icon.png",
				"args"=>array('email_template_id'=>false),
				'holder_module' => 'config', // which parent module this link will sit under.
				'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
				'menu_include_parent' => 0,
			);
		}

		$this->reset();
	}

	public function reset(){
		// clear all local variables.
		$this->replace_values = array();
		$this->to = array();
		$this->cc = array();
		$this->bcc = array();
		$this->error_text = '';
		$this->from = array();
		$this->attachments = array();
		$this->bounce_address = '';
		$this->reply_to= '';
		$this->subject= '';
		$this->message_html= '';
		$this->message_text= '';
		$this->sent_time= 0;
	}
	

     public function process(){
		if('send_email' == $_REQUEST['_process']){
			$this->_handle_send_email();
		}

	}

    public function get_summary($field_type,$field_id,$field_key) {
        global $plugins;
        switch($field_type){
            case 'customer':
                if($field_key=='name')$field_key = 'customer_name';
                $data = $plugins['customer']->get_customer($field_id);
                $primary_user_id = $data['primary_user_id'];
                $data = $plugins['user']->get_user($primary_user_id);
                return isset($data[$field_key]) ? $data[$field_key] : '';
            case 'user':
                $data = $plugins['user']->get_user($field_id);
                return isset($data[$field_key]) ? $data[$field_key] : '';
        }
        return false;
    }

    


    /**
     * Create a new email ready to send.
     * @return module_email
     */
    public static function &new_email(){
		$email = new self();
        $email -> reset();
        return $email;
    }
    public function replace($key,$val){
        $this->replace_values[$key] = $val;
    }
	/**
	 * Adds the sender of this email.
	 * @param  $type
	 * @param  $id
	 * @return void
	 */
    public function set_from($type,$id){
        $this->from = array(
            'type' => $type,
            'id' => $id,
            'name' => $this->get_summary($type,$id,'name'),
            'email' => $this->get_summary($type,$id,'email'),
        );
    }
	/**
	 * Adds the sender of this email manually.
	 * @param  $email
	 * @param  $name
	 * @return void
	 */
    public function set_from_manual($email,$name=''){
        $this->from = array(
            'type' => 'manual',
            'id' => false,
            'name' => $name,
            'email' => $email,
        );
    }
	/**
	 * Adds the reply to of this email.
	 * @param  $type
	 * @param  $id
	 * @return void
	 */
    public function set_reply_to($email,$name){
        $this->reply_to = array($email,$name);
    }
	/**
	 * Adds a recipient to this email.
	 * @param  $type
	 * @param  $id
	 * @return void
	 */
    public function set_to($type,$id,$email='',$name=''){
        // grab the details of the recipient.
		// add it as a recipient to this email
        if(!$email){
            $email = $this->get_summary($type,$id,'email');
        }
        if(!$name){
            $name = $this->get_summary($type,$id,'name');
        }
        $this->to[] = array(
            'type' => $type,
            'id' => $id,
            'name' => $name,
            'email' => $email,
        );

    }
	/**
	 * Adds the to of this email manually.
	 * @param  $email
	 * @param  $name
	 * @return void
	 */
    public function set_to_manual($email,$name=''){
        $this->to[] = array(
            'type' => 'manual',
            'id' => false,
            'name' => $name,
            'email' => $email,
        );
    }
    public function set_bcc_manual($email,$name){
        $this->bcc[] = array(
            'name' => $name,
            'email' => $email,
        );
    }
    public function set_bounce_address($email){
        $this->bounce_address = $email;
    }
	/**
	 * Adds an attachment to the email
	 * the attachment name will be worked out from the path.
	 * @param  $path
	 * @return void
	 */
	public function add_attachment($path){
		$this->attachments[] = $path;
	}

	/**
	 * Sets the text for the email.
	 * @param  $text
	 * @return void
	 */
	public function set_text($text,$html=false){
		if($html){
			$this->message_html = $text;
			// convert it to text if none exists.
			if (!$this->message_text) {
				$this->message_text = strip_tags(preg_replace('/<br/', "\n<br", preg_replace('#\s+#', ' ', $text)));
			}
		}else{
			$this->message_text = $text;
			// convert it to html if none exists.
			if (!$this->message_html) {
				$this->message_html = nl2br($text);
			}
		}
	}
    public function set_html($html){
        $this->set_text($html,true);
    }
    public function set_subject($subject){
        $this->subject=$subject;
    }
    public function AddAttachment($file_path,$file_name=''){
        $this->attachments[$file_path] = array(
            'path' => $file_path,
            'name' => $file_name,
        );
    }
	/**
	 * Sends the email we created above, startign with the new_email() method.
	 * @return bool
	 */
	public function send(){

        if(_DEBUG_MODE){
            module_debug::log(array('title'=>'Email Module','data'=>'Starting to send email'));
        }

        try{
		require_once("class.phpmailer.php");

		$mail = new PHPMailer();
		//$mail -> Hostname = 'yoursite.com';
        $mail->CharSet = 'UTF-8';

		if(module_config::c('email_smtp',0)){
            if(_DEBUG_MODE){
                module_debug::log(array('title'=>'Email Module','data'=>'Connecting via SMTP to: '.module_config::c('email_smtp_hostname','')));
            }
			$mail->IsSMTP();
			// turn on SMTP authentication
			$mail->SMTPAuth = module_config::c('email_smtp_authentication',0);
			$mail->Host     = module_config::c('email_smtp_hostname','');
			if($mail->SMTPAuth){
				$mail->Username = module_config::c('email_smtp_username','');
				$mail->Password = module_config::c('email_smtp_password','');
			}
		}else{
            $mail->IsMail();
        }


        if($this->bounce_address){
            $mail->Sender = $this->bounce_address;
        }
        if($this->reply_to){
            $mail->AddReplyTo($this->reply_to[0],$this->reply_to[1]);
            if(empty($mail->Sender))$mail->Sender = $this->reply_to[0];
        }
        
        if(!$this->from){
            $this->set_from_manual(module_config::c('admin_email_address'),module_config::c('admin_system_name'));
        }
        if(!$this->to){
            $this->set_to_manual(module_config::c('admin_email_address'),module_config::c('admin_system_name'));
        }

		$mail->From     = $this->from['email'];
		if($this->from['name']){
			$mail->FromName = $this->from['name'];
		}

		// turn on HTML emails
		$mail->isHTML(true);

		if($this->attachments){
			foreach($this->attachments as $file){
                if(is_array($file)){
                    $file_path = $file['path'];
                    $file_name = $file['name'];
                }else{
                    $file_path = $file;
                    $file_name = '';
                }
				if(is_file($file_path)){
					$mail->AddAttachment($file_path,$file_name);
				}
			}
		}


		// process the message replacements etc..
		foreach($this->to as $to){
			$this->replace('TO_NAME',$to['name']);
			$this->replace('TO_EMAIL',$to['email']);
		}
		$this->replace('FROM_NAME',$this->from['name']);
		$this->replace('FROM_EMAIL',$this->from['email']);

		foreach($this->replace_values as $key=>$val){

			//$val = str_replace(array('\\', '$'), array('\\\\', '\$'), $val);
			$key = '{'.strtoupper($key).'}';
            // reply to name
            foreach($this->to as &$to){
                if($to['name']){
                    $to['name'] = str_replace($key,$val,$to['name']);
                }
            }
			// replace subject
			$this->subject = str_replace($key,$val,$this->subject);
			// replace message html
			$this->message_html = str_replace($key,$val,$this->message_html);
			// replace message text.html
			$this->message_text = str_replace($key,$val,$this->message_text);
		}

		$mail->Subject     = $this->subject;




		/*if(_DEMO_MODE){

			$mail->AddAddress(module_config::c('admin_email_address','info@'.$_SERVER['HTTP_HOST']));
		}else{*/
        $test_to_str = '';
			foreach ($this->to as $to) {
				$mail->AddAddress($to['email'], $to['name']);
                $test_to_str .= " TO: ".$to['email'] .' - '.$to['name'];
			}
		/*}*/
        foreach($this->cc as $cc){
            $mail->AddCC($cc['email'],$cc['name']);
        }
        foreach($this->bcc as $bcc){
            $mail->AddBCC($bcc['email'],$bcc['name']);
        }


        // debugging.
//        $html = $this->message_html;
//        $mail->ClearAllRecipients();
//        $mail->AddAddress('davidtest@blueteddy.com.au','David Test');
//        $html = $test_to_str.$html;

		$mail->Body    = $this->message_html;
		$mail->AltBody    = $this->message_text;

        if(_DEBUG_MODE){
            module_debug::log(array('title'=>'Email Module','data'=>'Sending to: '.$test_to_str));
        }
		if(!$mail->Send()){
			$this->error_text = $mail->ErrorInfo;
			$this->status = _MAIL_STATUS_FAILED;
			$this->save();
            if(_DEBUG_MODE){
                module_debug::log(array('title'=>'Email Module','data'=>'Send failed: '.$this->error_text));
            }
            // todo - send error to admin ?
		}else{
            // update sent times and status on success.
            $this->sent_time = time();
            $this->status = _MAIL_STATUS_SENT;
            $this->save();
            if(_DEBUG_MODE){
                module_debug::log(array('title'=>'Email Module','data'=>'Send success'));
            }
        }

        //$this->status=_MAIL_STATUS_OVER_QUOTA;//testing.

        // todo : incrase mail count so that it sits within our specified boundaries.

        // true on succes, false on fail.
		return ($this->status == _MAIL_STATUS_SENT);
        }catch(Exception $e){
            return false;
        }
	}
	/**
	 * Just saves any local variables into the database table.
	 * @return void
	 */
	public function save(){
		/*$data = array();
		foreach($this->db_fields as $field => $tf){
			$data[$field] = $this->$field;
		}
		update_insert('email_id',$this->email_id,'email',$data);*/
	}

    public static function print_compose($options) {

        include('pages/email_compose_basic.php');
    }

    public static function get_email_compose_options($options) {
        $options = array(
            'subject' => isset($_REQUEST['subject']) ? $_REQUEST['subject'] : (isset($options['subject']) ? $options['subject'] : ''),
            'content' =>  isset($_REQUEST['content']) ? $_REQUEST['content'] : (isset($options['content']) ? $options['content'] : ''),
            'cancel_url' =>  isset($options['cancel_url']) ? $options['cancel_url'] : false,
            'complete_url' => isset($options['complete_url']) ? $options['complete_url'] : (isset($options['cancel_url']) ? $options['cancel_url'] : false),
            'to' => isset($_REQUEST['to']) ? $_REQUEST['to'] : (isset($options['to']) ? $options['to'] : array()),
            'bcc' => isset($_REQUEST['bcc']) ? $_REQUEST['bcc'] : (isset($options['bcc']) ? $options['bcc'] : ''),
            'attachments' => isset($options['attachments']) ? $options['attachments'] : array(),
            'success_callback' => isset($options['success_callback']) ? $options['success_callback'] : '',
        );
        return $options; 
    }

    private function _handle_send_email(){
        $options = unserialize(base64_decode($_REQUEST['options']));
        $options = $this->get_email_compose_options($options);
        if(isset($_REQUEST['custom_to'])){
            $custom_to = explode('||',$_REQUEST['custom_to']);
            $custom_to['email'] = $custom_to[0];
            $custom_to['name'] = $custom_to[1];
            $to = array($custom_to);
        }else{
            $to = isset($options['to']) && is_array($options['to']) ? $options['to'] : array();;
        }

        $email = $this->new_email();
        $email->subject = $options['subject'];
        foreach($to as $t){
            $email->set_to_manual($t['email'],$t['name']);
        }
        // set from is the default from address.
        if($options['bcc']){
            $email->set_bcc_manual($options['bcc'],'');
        }
        $email->set_html($options['content']);
        foreach($options['attachments'] as $attachment){
            $email->AddAttachment($attachment['path'],$attachment['name']);
        }
        if($email->send()){
            if($options['success_callback']){
                eval($options['success_callback']);
            }
            set_message('Email sent successfully');
            redirect_browser($options['complete_url']);
        }else{
            set_error('Sending email failed: '.$email->error_text);
            redirect_browser($options['cancel_url']);
        }
    }
}

