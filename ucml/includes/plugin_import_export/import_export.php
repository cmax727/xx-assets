<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:42 
  * IP Address: 127.0.0.1
  */


class module_import_export extends module_base{
	
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
		$this->module_name = "import_export";
		$this->module_position = 8882;

        $this->version = 2.11;


        module_config::register_css('import_export','import_export.css');

	}


    static $pagination_options = array();

    public static function run_pagination_hook(&$rows){

        if(isset($_REQUEST['import_export_go']) && $_REQUEST['import_export_go'] == 'yes'){
            // we are posting back tot his script with a go!
            if(is_resource($rows)){
                $new_rows = array();
                while($row = mysql_fetch_assoc($rows)){
                    $new_rows[]=$row;
                }
                $rows = $new_rows;
            }else{
                // rows stays the same.
            }
            // add these items to the import_export.

            if(is_array($rows) && count($rows)){
                $fields = self::$pagination_options['fields'];
                // export as CSV file:
                ob_end_clean();

                ob_start();

                foreach($fields as $key=>$val){
                    echo '"'.str_replace('"','""',$key).'",';
                }
                // check for extra fields.
                $extra_fields = array();
                if(class_exists('module_extra',false) && isset(self::$pagination_options['extra']) && self::$pagination_options['extra']){
                    $sql = "SELECT `extra_key` FROM `"._DB_PREFIX."extra` WHERE owner_table = '".mysql_real_escape_string(self::$pagination_options['extra']['owner_table'])."' AND `extra_key` != '' GROUP BY `extra_key` ORDER BY `extra_key`";
                    $extra_fields = qa($sql);
                    foreach($extra_fields as $extra_field){
                        echo '"'.str_replace('"','""',$extra_field['extra_key']).'",';
                    }
                }
                echo "\n";
                foreach($rows as $row){
                    foreach($fields as $key=>$val){
                        echo '"'.str_replace('"','""',isset($row[$val]) ? $row[$val] : '').'",';
                    }
                    // check for extra fields.
                    if(class_exists('module_extra',false) && $extra_fields){
                        $extra_vals = array();
                        if(isset($row[self::$pagination_options['extra']['owner_id']]) && $row[self::$pagination_options['extra']['owner_id']] > 0){
                            $sql = "SELECT `extra_key` AS `id`, `extra` FROM `"._DB_PREFIX."extra` WHERE owner_table = '".mysql_real_escape_string(self::$pagination_options['extra']['owner_table'])."' AND `owner_id` = '".(int)$row[self::$pagination_options['extra']['owner_id']]."' ORDER BY `extra_key`";
                            $extra_vals = qa($sql);
                        }
                        foreach($extra_fields as $extra_field){

                            echo '"';
                            echo isset($extra_vals[$extra_field['extra_key']]) ? str_replace('"','""',$extra_vals[$extra_field['extra_key']]['extra']) : '';
                            echo '",';
                        }
                    }
                    echo "\n";
                }

                $csv = ob_get_clean();
                if(module_config::c('export_csv_debug',0)){
                    echo '<pre>'.$csv.'</pre>';
                    exit;
                }
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private",false);
                header("Content-Type: text/csv");
                //todo: correct file name
                header("Content-Disposition: attachment; filename=\"".(isset(self::$pagination_options['name']) ? self::$pagination_options['name'].'.csv' :'Export.csv')."\";");
                header("Content-Transfer-Encoding: binary");
                // todo: calculate file size with ob buffering
                header("Content-Length: ".strlen($csv));
                echo $csv;

                exit;
            }
        }
    }

    public static function display_pagination_hook(){
        
        ?>
        <span>
        <a href="#" onclick="if($('#import_export_popdown').css('display')=='inline' || $('#import_export_popdown').css('display')=='block') $('#import_export_popdown').css('display','none'); else $('#import_export_popdown').css('display','inline'); return false;">(<?php _e('export');?>)</a>
        <span id="import_export_popdown" style="position: absolute; width: 200px; display: none; background: #EFEFEF; margin-left: -210px; margin-top: 30px; border: 1px solid #CCC; text-align: left; padding: 6px;">
            <strong><?php _e('Export all these results:');?></strong><br/>
            <input type="hidden" name="import_export_go" id="import_export_go" value="">
            <input type="button" name="import_export_button" id="import_export_button" value="<?php _e('Export CSV File');?>">
            <script type="text/javascript">
                $(function(){
                    $('#import_export_button').click(function(){
                        $('#import_export_go').val('yes');
                        // todo: if no form, create one them submit.
                        $('#import_export_go').parents('form')[0].submit();
                        $('#import_export_popdown').css('display','none');
                    });
                });
            </script>
        </span>
        </span>
        <?php
    }
    
    public static function enable_pagination_hook($options=array()) {
        $GLOBALS['pagination_import_export_hack'] = true;
        self::$pagination_options=$options;
    }

    public static function import_link($options=array()) {
        $m = get_display_mode();
        if($m=='mobile'||$m=='iframe')return false;
        $url = link_generate(array(
                                 array(
                                     'arguments'=>array(
                                         'import_options'=>base64_encode(json_encode($options)),
                                     ),
                                     'page'=>'import',
                                     'module'=>'import_export'
                                 )
                             ));

        return $url;
    }
}