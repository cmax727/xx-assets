<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:18 
  * IP Address: 127.0.0.1
  */


class module_template extends module_base{

    public $values = array();
    public $tags = array();
    public $content = '';
    public $description = '';
    public $wysiwyg = false;

    public $template_id;

    private static $_templates;
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
		$this->links = array();
		$this->module_name = "template";
		$this->module_position = 28;

        $this->version = 2.2;

		// the link within Admin > Settings > templates.
		if(module_security::has_feature_access(array(
				'name' => 'Settings',
				'module' => 'config',
				'category' => 'Config',
				'view' => 1,
				'description' => 'view',
		))){
			$this->links[] = array(
				"name"=>"Templates",
				"p"=>"template",
				"icon"=>"icon.png",
				"args"=>array('template_id'=>false),
				'holder_module' => 'config', // which parent module this link will sit under.
				'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
				'menu_include_parent' => 0,
			);
		}


	}

    private static function _load_all_templates(){
        self::$_templates = array();


        if(self::db_table_exists('template')){
            // load all templates into memory for quicker processing.
            foreach(self::get_templates() as $template){
                self::$_templates[$template['template_key']] = $template;
            }
        }
    }


    public static function link_generate($template_id=false,$options=array(),$link_options=array()){

        $key = 'template_id';
        if($template_id === false && $link_options){
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
        if(!isset($options['type']))$options['type']='template';
        $options['page'] = 'template_edit';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['template_id'] = $template_id;
        $options['module'] = 'template';
        $data = self::get_template($template_id);
        $options['data'] = $data;
        // what text should we display in this link?
        $options['text'] = (!isset($data['template_key'])||!trim($data['template_key'])) ? 'N/A' : $data['template_key'];
        //if(isset($data['template_id']) && $data['template_id']>0){
            $bubble_to_module = array(
                'module' => 'config',
                'argument' => 'template_id',
            );
       // }
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

	public static function link_open($template_id,$full=false){
        return self::link_generate($template_id,array('full'=>$full));
    }


    public function process(){
		if('save_template' == $_REQUEST['_process']){
			$this->_handle_save_template();
		}

	}
		
	function delete($template_id){
		$template_id=(int)$template_id;
		$sql = "DELETE FROM "._DB_PREFIX."template WHERE template_id = '".$template_id."' LIMIT 1";
		$res = query($sql);
	}

    public static function add_tags($template_key,$tags){
        $template = self::get_template_by_key($template_key);
        $template_id = $template->template_id;
        if(!is_array($template->tags)){
            $template->tags = array();
        }
        $new_tags = $template->tags + $tags;
        update_insert('template_id',$template_id,'template',array('tags'=>serialize($new_tags)));
    }

    public static function init_template($template_key,$content,$description,$type='text',$tags=array()){

        if(!self::db_table_exists('template'))return;
        if(!count(self::$_templates)){
            self::_load_all_templates();
        }
        $template = false;

        if(isset(self::$_templates[$template_key])){
            $template = self::$_templates[$template_key];
        }else{
            $template = get_single("template","template_key",$template_key);
        }
        //$template=get_single('template','template_key',$template_key);
        if(!$template || (!$template['content'] && $content)){
            $data = array(
                'template_key' => $template_key,
                'description' => $description,
                'content' => $content,
                'wysiwyg' => 1,
                'tags' => serialize($tags),
            );
            if($type=='text'){
                $data['content'] = nl2br($content);
            }else if($type=='code'){
                $data['wysiwyg'] = 0;
            }
            update_insert('template_id',($template&&$template['template_id'])?$template['template_id']:'new','template',$data);
        }
    }

    public static function &get_template_by_key($template_key){
        if(!count(self::$_templates)){
            self::_load_all_templates();
        }
        $template = new self();
        if(isset(self::$_templates[$template_key])){
            $data = self::$_templates[$template_key];
        }else if(self::db_table_exists('template')){
            $data = get_single("template","template_key",$template_key);
        }else{
            $data = array();
        }
        foreach($data as $key=>$val){
            if($key=='tags'){
                $template->{$key} = unserialize($val);
            }else{
                $template->{$key} = $val;
            }
        }
        return $template;
    }
	public static function get_template($template_id){
        if(self::db_table_exists('template')){
            return get_single("template","template_id",$template_id);
        }else{
            return array();
        }
	}

	function get_templates($search=array()){
        if(self::db_table_exists('template')){
            return get_multiple("template",$search,"template_id","exact","template_id DESC");
        }else{
            return array();
        }
	}



	private function _handle_save_template() {
		// handle post back for save template template.
		$template_id = (int)$_REQUEST['template_id'];
		$data = $_POST;
		// write header/footer html based on uploaded images.
		// pass uploaded images to the file manager plugin.
		$template_id = update_insert('template_id',$template_id,'template',$data);
		// redirect upon save.
		set_message('Template saved successfully!');
		redirect_browser($this->link_open($template_id));
		exit;
	}

    public function assign_values($values){
        if(is_array($values)){
            foreach($values as $key=>$val){
                if(is_array($val))continue;
                $this->values[$key] = $val;
            }
        }
    }
    public function replace_content(){
        $content = $this->content;
        foreach($this->values as $key=>$val){
            if(is_array($val))continue;
            $content = str_replace('{'.strtoupper($key).'}',$val,$content);
			//$val = str_replace(array('\\', '$'), array('\\\\', '\$'), $val);
			//$content = preg_replace('/\{'.strtoupper(preg_quote($key,'/')).'\}/',$val,$content);
        }
        return $content;
    }
    public function replace_description(){
        $content = $this->description;
        foreach($this->values as $key=>$val){
            if(is_array($val))continue;
            $content = str_replace('{'.strtoupper($key).'}',$val,$content);
        }
        return $content;
    }
    public function render($type='html',$options=array()){
        ob_start();
        switch($type){
            case 'pretty_html':
                // header and footer so plain contnet can be rendered nicely.
                ?>
                        <html>
                        <head>
                            <title><?php echo $this->page_title ? $this->page_title : module_config::s('admin_system_name');?></title>
                            <link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/desktop.css" type="text/css">
                            <link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/styles.css" type="text/css">
                            <?php module_config::print_css();?>
                            <style type="text/css">
                                .content{
                                    margin:20px auto;
                                    padding:20px;
                                    background: #FFF;
                                    border:1px solid #CCC;
                                    border-radius: 10px;
                                    width: 855px;
                                }
                            </style>

                            <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jquery-1.6.3.min.js"></script>
                            <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jquery-ui-1.8.6.custom.min.js"></script>
                            <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/timepicker.js"></script>
                            <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/cookie.js"></script>
                            <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/javascript.js?ver=2"></script>
                            <?php module_config::print_js();?>
                        </head>

                        <body>
                        <div style="" class="content">
                            <?php
                            $c = $this->replace_content();
                            if(!$this->wysiwyg){
                                //$c = nl2br($c);
                            }
                            echo $c;
                            ?>
                        </div>
                        </body>
                        </html>
                <?php
                break;
            case 'html':
            default:
                $c = $this->replace_content();
                if($this->wysiwyg){
                    //$c = nl2br($c);
                }
                echo $c;
                break;
        }
        return ob_get_clean();
    }

    public function get_install_sql(){
        ob_start();
        ?>

CREATE TABLE `<?php echo _DB_PREFIX; ?>template` (
  `template_id` int(11) NOT NULL auto_increment,
  `template_key` varchar(255) NOT NULL DEFAULT  '',
  `description` varchar(255) NOT NULL DEFAULT  '',
  `content` LONGTEXT NULL,
  `tags` TEXT NULL,
  `wysiwyg` CHAR( 1 ) NOT NULL DEFAULT  '1',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY  (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    <?php
        
        return ob_get_clean();
    }
    
}

