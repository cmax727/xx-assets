<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:36 
  * IP Address: 127.0.0.1
  */


function get_yes_no(){
	$data = array(
		1=>"Yes",
		0=>"No",
	);
	return $data;
}
function friendly_key($data,$key){
	return isset($data[$key]) ? $data[$key] : false;
}

function print_select_box($data,$id,$cur='',$class='',$blank=true,$array_id=false,$allow_new=false){
	$sel = '<select name="'.$id.'" id="'.$id.'" class="'.$class.'"';
	if($allow_new){
		$sel .= ' onchange="dynamic_select_box(this);"';

	}
	$sel .= '>';
	if($blank){
		$foo = ucwords(str_replace("_"," ",$id));
		$sel .= '<option value="">' . ($blank===true ? ' - Select - ' : $blank) . '</option>';
	}
	$found_selected = false;
	$current_val = 'Enter new value here';
	foreach($data as $key => $val){
		$sel .= '<option value="'.$key.'"';
		if(is_array($val)){
			if(!$array_id){
				if(isset($val[$id]))$array_id = $id;
				else $array_id = key($val);
			}
			$printval = $val[$array_id];
		}else{
			$printval = $val;
		}
        // to handle 0 elements:
        if($cur !== false && ($cur != '') && $key == $cur){
			$current_val = $printval;
			$sel .= ' selected';
			$found_selected = true;
		}
		$sel .= '>'.htmlspecialchars($printval).'</option>';
	}
	if($cur && !$found_selected){
		$sel .= '<option value="'.$cur.'" selected>'.htmlspecialchars($cur).'</option>';
	}
	if($allow_new && get_display_mode() != 'mobile'){
		$sel .= '<option value="create_new_item">'._l(' - Create New - ') .'</option>';
	}
	$sel .= '</select>';
	if($allow_new){
		//$sel .= '<input type="text" name="new_'.$id.'" style="display:none;" value="'.$current_val.'">';
	}
	return $sel;
}
/*print_db_select_box(array(
		'db_table'=>'inventory_category',
		'db_key'=>'category_id',
		'db_val'=>'category_name',
		'db_order'=>'category_name',
		'name'=>'category_id',
		'val'=>$inventory['category_id'],
		'allow_new' => true,
	));*/

function currency($data,$show_currency=true,$currency_id=false){

    // find the default currency.
    if(!defined('_DEFAULT_CURRENCY_ID')){
        $default_currency_id = module_config::c('default_currency_id',1);
        foreach(get_multiple('currency','','currency_id') as $currency){
            if($currency['currency_id']==$default_currency_id){
                define('_DEFAULT_CURRENCY_ID',$default_currency_id);
                define('_DEFAULT_CURRENCY_SYMBOL',$currency['symbol']);
                define('_DEFAULT_CURRENCY_LOCATION',$currency['location']);
                define('_DEFAULT_CURRENCY_CODE',$currency['code']);
            }
        }
    }
    $currency_symbol = _DEFAULT_CURRENCY_SYMBOL;
    $currency_location = _DEFAULT_CURRENCY_LOCATION;
    $currency_code = _DEFAULT_CURRENCY_CODE;
    $show_name = false;

    if($currency_id && $currency_id != _DEFAULT_CURRENCY_ID){
        if($show_currency){
            $show_name = true;
        }
        foreach(get_multiple('currency','','currency_id') as $currency){
            if($currency['currency_id']==$currency_id){
                $currency_symbol = $currency['symbol'];
                $currency_location = $currency['location'];
                $currency_code = $currency['code'];
            }
        }
    }
	/*$currency_location = module_config::c('currency_location','before');
	$currency_code = module_config::c('currency','$');
	$currency_name = module_config::c('currency_name','USD');*/

	switch(strtolower($currency_symbol)){
		case "yen":
			$currency_symbol = '&yen;';
			break;
		case "eur":
			$currency_symbol = '&euro;';
			break;
		case "gbp":
			$currency_symbol = '&pound;';
			break;
		default:
			break;
	}

    if(!$show_currency){
        $currency_symbol = '';
    }
    if(module_config::c('currency_show_code_always',0)){
        $data .= ' '.$currency_code;
    }else if($show_name && module_config::c('currency_show_non_default',1)){
        $data .= ' '.$currency_code;
    }

	switch($currency_location){
		case 'after':
		case 0:
			return $data.$currency_symbol;
			break;
        case 1:
		default:
			return $currency_symbol.$data;
	}
}
function dollar($number,$show_currency=true,$currency_id=false){
    // todo - this number format needs to be in the database currency table as well.
	return currency(number_format($number,2,".",","),$show_currency,$currency_id);
}
function input_date($date,$include_time=false){

    $time = false;
    if(preg_match('/^\d+$/',$date)){
        $time = $date;
    }

	if(
		!$date ||
		(preg_match('/[a-z]/i',$date) && !preg_match('/^[\+-]\d/',$date))
	)return '';

	// takes a user input date and returns the mysql YYYY-MM-DD valid format.
	// 1 = DD/MM/YYYY
	// 2 = YYYY/MM/DD
	// 3 = MM/DD/YYYY

	// could use sscanf below, but still wanted to run preg_match
	// so used implode(explode( instead... meh

    if(!$time){
	switch(_DATE_INPUT){
		case 1:
			if(preg_match('#^\d?\d([-/])\d?\d\1\d{2,4}(.*)$#',$date,$matches)){
				$time_bits = $matches[2];
				$date = str_replace($time_bits,'',$date);
				$date = implode("-",array_reverse(explode($matches[1],$date)));
				$date .= $time_bits;
				if(strtotime($date)){
					$date = date('Y-m-d'.(($include_time)?' H:i:s':''),strtotime($date));
					break;
				}
			}
		case 2:
			if(preg_match('#^\d{2,4}([-/])\d?\d\1\d?\d(.*)$#',$date,$matches)){
				$time_bits = $matches[2];
				$date = str_replace($time_bits,'',$date);
				$date = implode("-",explode($matches[1],$date));
				$date .= $time_bits;
				if(strtotime($date)){
					$date = date('Y-m-d'.(($include_time)?' H:i:s':''),strtotime($date));
					break;
				}
			}
		case 3:
			if(preg_match('#^\d?\d([-/])\d?\d\1\d{2,4}(.*)$#',$date,$matches)){
				$time_bits = $matches[2];
				$date = str_replace($time_bits,'',$date);
				$date_bits = explode($matches[1],$date);
				$date = $date_bits[2] .'-'. $date_bits[0] .'-'. $date_bits[1];
				$date .= $time_bits;
				if(strtotime($date)){
					$date = date('Y-m-d'.(($include_time)?' H:i:s':''),strtotime($date));
					break;
				}
			}
		default:
			$date = date('Y-m-d'.(($include_time)?' H:i:s':''),strtotime($date));
	}
	}

    if($include_time){
        // if we're on todays date, and there is no time set, use nows time.
        if(date('Y-m-d',strtotime($date)) == date('Y-m-d')){
            if($date == date('Y-m-d H:i:s',strtotime(date('Y-m-d')))){
                $date = date('Y-m-d H:i:s');
            }
        }
    }

	return $date;
}
function print_date($date,$include_time=false,$input_format=false){
	if(!$date || (preg_match('/[a-z]/i',$date) && !preg_match('/^[\+-]\d/',$date)))return '';
	if(strpos($date,'0000-00-00')!==false)return '';
	if(strpos($date,'1970-01-01')!==false)return '';
	if(is_numeric($date)){
		// we have a timestamp, simply spit this out
		$time = $date;
	}else{
		$time = strtotime(input_date($date,$include_time));
	}
	if($input_format){
		switch(_DATE_INPUT){
			case 1:
				$date = date("d/m/Y",$time);
				break;
			case 2:
				$date = date("Y/m/d",$time);
				break;
			case 3:
				$date = date("m/d/Y",$time);
				break;
		}
	}else{
		$date = date(_DATE_FORMAT,$time);
	}
	if($include_time){
		$date.= ' '.date("g:ia",$time);
	}
	return $date;
}

function print_error($errors,$fatal=false){
	if(!is_array($errors)){
		$errors = array($errors);
	}
	echo "Error!";
	print_r($errors);
	if($fatal)exit;
}


function handle_hook($hook){
	global $plugins;
	// check each plugin if they want to handle this hook.

	$argv = array();
	$tmp = func_get_args();
	foreach($tmp as $key => $value) $argv[$key] = &$tmp[$key]; // hack for php5.3.2
	$return = array();
	if(is_array($plugins)){
		foreach($plugins as $plugin_name => &$plugin){
			$this_return = call_user_func_array(array(&$plugin,'handle_hook'),$argv);
			if($this_return !== false && $this_return !== null){
				$return[] = $this_return;
			}
			//$return[] = $plugin->handle_hook($hook,$calling_module);
		}
	}
	if(count($return) == 0){
		$return = false;
	}
	return $return;
}


function process_alert($check_date,$item,$alert_days_in_future=false){
    if($alert_days_in_future===false){
        $alert_days_in_future = module_config::c('alert_days_in_future',5);
    }
	$date = input_date($check_date,true);
	if($check_date != 's2009-07-12'){
		//echo $date;
	}
	if(!strtotime($date)){
		$date = false;
	}
	/*if(preg_match('#^\d?\d/\d?\d/\d{2,4}$#',$check_date)){
		$date = implode("-",array_reverse(explode("/",$check_date)));
	}else if(preg_match('#^\d{2,4}-\d?\d-\d?\d$#',$check_date)){
		$date = $check_date;
	}*/

	$alert_res = false;

	if($date){
		// we have a date
		$secs = date("U") - date("U",strtotime($date));
		$days = $secs/86400;
		$alert_field = false;
		$warning = false;
		if($secs > 0){
			$days = floor($days);
            if($days == 0){
				$alert_field = " "._l('today!');
                $warning=true;
			}else{
				$alert_field = " " . _l('%s days ago',$days);
				$warning=true;
			}
		}else{
            $days = abs($days);
			$days = ceil($days);
			if($days == 0){
				$alert_field = " "._l('today!');
                $warning=true;
			}else if($days < $alert_days_in_future){
				$alert_field = " " . _l('in %s days',$days);
			}
		}

		if($alert_field){
			$alert_res = array(
				"warning"=>$warning,
				"alert" => _l($item) . $alert_field,
				"item" => _l($item),
				"days" => $alert_field,
				"date" => $date,
			);
		}
	}
	return $alert_res;
}

function set_message($message){
	if(!isset($_SESSION['_message']))$_SESSION['_message']=array();
	$_SESSION['_message'][] = ($message);
}
function set_error($message){
	if(!isset($_SESSION['_errors']))$_SESSION['_errors']=array();
    foreach($_SESSION['_errors'] as $existing_error){
        if($existing_error == ($message)){
            return false;
        }
    }
	$_SESSION['_errors'][] = ($message);
    return true;
}

function print_header_message(){
    $return = false;
	if(isset($_SESSION['_message']) && count($_SESSION['_message'])){
		?>
		<div class="ui-widget" style="padding-top:10px;">
			<div class="ui-state-highlight ui-corner-all" style="padding: 0 .7em;">
				<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
				<?php
				$x=1;
				foreach($_SESSION['_message'] as $msg){
					if(count($_SESSION['_message'])>1){
						echo "<strong>#$x</strong> ";
						$x++;
					}
					echo nl2br(($msg))."<br>";
				}
				?>
				</p>
			</div>
		</div>
		<?php 
        $return = true;
	}
	if(isset($_SESSION['_errors']) && count($_SESSION['_errors'])){
        $x=1;
        foreach($_SESSION['_errors'] as $msg){
		?>
		<div class="ui-widget" style="padding-top:10px;">
			<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
				<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
				<?php echo nl2br($msg)."<br>"; ?>
                </p>
			</div>
		</div>
		<?php
        }
        $return = true;
	}
	$_SESSION['_message'] = array();
	$_SESSION['_errors'] = array();
    return $return;
}


function send_error($message){
	//echo $message;exit;
	mail(_ERROR_EMAIL,"Admin System Notification (".date("Y-m-d H:i:s").")",$message . "\n\n".var_export($_REQUEST,true). "\n\n".var_export($_SERVER,true));
}


function send_email($email_to, $subject, $content, $data = false){

	if(is_array($data)){
		extract($data);
	}


	if(!preg_match('/\n/',$content) && is_file($content)){
		ob_start();
		include($content);
		$email_content = ob_get_clean();
	}else{
		$email_content = $content;
	}

	$from_name = ($FROM_NAME)?$FROM_NAME:$_SESSION['_user_name'];
	$from_email = ($FROM)?$FROM:$_SESSION['_user_email'];
	$headers = "From: $from_name <$from_email>\n";
	if($BCC)$headers .= "BCC: $BCC\n";

	if(preg_match('/<(br|p|h1|h2|table)/',$email_content)){
		$headers .= 'MIME-Version: 1.0' . "\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
	}

	$headers .= "\n";

	if(_DEBUG_MODE){
		$email_to = _ERROR_EMAIL;
	}
	if(mail($email_to, $subject, $email_content, $headers)){
		return true;
	}else{
		set_message("ERROR: Failed to send email with mail() command??? Weird..");
	}
	return false;
}
function _hr($text){
    ob_start();
    _h($text);
    return ob_get_clean();
}
function _h($text){
    // is help enabled?
    if(!module_config::c('show_help',1))return '';
    $help_id = md5($text);
    $argv = func_get_args();
    ?> <a href="#" onclick="open_help('<?php echo $help_id;?>');return false;" class="ui-corner-all ui-icon ui-icon-help" style="display:inline-block;">[?]</a>
    <div id="help_<?php echo $help_id;?>" style="display:none;" title="<?php _e('Help');?>">
        <?php print call_user_func_array('_l',$argv); ?>
    </div>
    <?php
}
function _e($text){
    $argv = func_get_args();
    print call_user_func_array('_l',$argv);;
}
// this is the makings of a labelling system.
function _l($text){
	// read in from the global label array
    //return 'L';
	global $labels;
	$argv = func_get_args();
	// see if the first one is a lang label
	if(isset($labels[$text]) && trim($labels[$text])){
		$argv[0] = $labels[$text];
	}
	// use this for building up the language array.
	// visit index.php?dump_lang=true to get a csv file of language vars.
	if(_DEBUG_MODE){
        $foo = debug_backtrace();
        $last_file = false;
        while($last = array_shift($foo)){
            if($last && isset($last['file'])){
                $last_file = $last['file'];
                break;
            }
        }
        $last_file = str_replace(_UCM_FOLDER,'',$last_file);
        if(!$last_file){
            print_r($foo);exit;
        }
		$_SESSION['ll'][$last_file][$text] = true;
	}
	$result = call_user_func_array('sprintf',$argv);
	if(isset($_SESSION['_edit_labels'])){
		// this idea didn't really work because we use
		// labels in hidden fields and in javascript.
		// this worked fine when the labels were just on the page, but not otherwise.
		return '{[{'.$result.'}]}';
		$edit_result = '<span style="" onclick="return false;">';
		$edit_result .= '<input type="text" style="font-size:10px; border:1px solid #FF0000;" size="'.strlen($result).'" value="'.$result.'" onclick="return false;">';
		$edit_result .= '</span>';
		return $edit_result;
	}else{
		return $result;
	}

}

function get_languages(){
	$files = @glob("includes/lang/*.php");
	if(!is_array($files))$files = array();
	$languages=array();
	foreach($files as $file){
		$languages[] = basename(str_replace('.php','',$file));
	}
	return $languages;
}

function forum_text($text){
	$text = htmlspecialchars($text);

	// convert links
    $text = " ".$text;
    $text = preg_replace('#(((f|ht){1}tps?://)[-a-zA-Z0-9@:;%_\+.~\#?&//=\[\]]+)#i', '<a href="\\1" target=_blank>\\1</a>', $text);
    $text = preg_replace('#([[:space:]()[{}])(www.[-a-zA-Z0-9@:;%_\+.~\#?&//=]+)#i', '\\1<a href="http://\\2" target=_blank>\\2</a>', $text);
    $text = preg_replace('#([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})#i', '<a href="mailto:\\1" target=_blank>\\1</a>', $text);

    $text = ltrim($text);

	$print_text = '';
	foreach(explode("\n",$text) as $line){
		$line = rtrim($line);
		$line = preg_replace("/\t/","&nbsp;&nbsp;&nbsp;",$line);
		if(preg_match('/^(\s+)/',$line,$matches)){
			$line = str_repeat("&nbsp;",strlen($matches[1])) . ltrim($line);
		}
		$print_text .= $line . "<br />\n";
	}
	return $print_text;
}


function process_pagination($rows,$per_page = 15,$page_number = false,$table_id='table'){
	$data = array();
	$data['rows']=array();
	$data['links']='';
	if($per_page<=0){
		$per_page = 20;
	}

    if(isset($GLOBALS['pagination_group_hack'])){
        module_group::run_pagination_hook($rows);
    }
    if(isset($GLOBALS['pagination_import_export_hack'])){
        module_import_export::run_pagination_hook($rows);
    }

	$db_resource = false;
	if(is_resource($rows)){
		// have the db handle for the sql query
		$db_resource = $rows;
		unset($rows);
		$rows = array();
		$total = mysql_num_rows($db_resource);
		if($total<=$per_page){
			// pull out all records.
			while($row = mysql_fetch_assoc($db_resource)){
				$rows[] = $row;
			}
		}
	}else if(is_array($rows)){
		// we have the rows in an array.
		$total = count($rows);
	}else{
		echo 'Pagination failed. Please report this bug.';
		exit;
	}
    // pagination hooks
    ob_start();
    if($total>0){
        // group hack addition
        if(isset($GLOBALS['pagination_group_hack']) && module_group::groups_enabled()){
            module_group::display_pagination_hook();
        }
        if(get_display_mode() != 'mobile'){
            // export hack addition
            if(isset($GLOBALS['pagination_import_export_hack'])){
                module_import_export::display_pagination_hook();
            }
        }
    }
    $pagination_hooks = ob_get_clean();

	// default summary/links content
	ob_start();
	echo '<div class="pagination_summary"><p>';
	if($total > 0){
		_e('Showing records %s to %s of %s',(($page_number*$per_page)+1),$total,$total);
        echo $pagination_hooks;
	}else{
		_e('No results found');
	}
	echo '</p></div>';
	$data['summary'] = ob_get_clean();
	ob_start();
	echo '<div class="pagination_links">';
	echo "\n<p>";
	echo _l('Page %s of %s',1,1);
	echo '</p>';
	echo '</div>';
	$data['links']=ob_get_clean();

	if($total<=$per_page){

		// double up on query rows from above. oh well.
		$data['rows']=$rows;
	}else{
		$pg=0;
		if(!$page_number){
			$page_number = isset($_REQUEST['pg'.$table_id]) ? $_REQUEST['pg'.$table_id] : 0;
		}
		$page_number = min(ceil($total/$per_page)-1,$page_number);


		// slice up the result into the number of rows requested.
		if($db_resource){
			// do the the mysql way:
			mysql_data_seek($db_resource, ($page_number*$per_page));
			$x=0;
			while($x < $per_page){
				$row_data = mysql_fetch_assoc($db_resource);
				if($row_data){
					$data['rows'] [] = $row_data;
				}
				$x++;
			}
			unset($row_data);
		}else{
			// the old array way.
			$data['rows']=array_slice($rows, ($page_number*$per_page), $per_page);
		}
		$data['summary']='';
		$data['links']='';
		$request_uri = preg_replace('/[&?]pg'.preg_quote($table_id).'=\d+/','',$_SERVER['REQUEST_URI']);
		$request_uri .= (preg_match('/\?/',$request_uri)) ? '&' : '?';
		$request_uri = htmlspecialchars($request_uri);
		if(count($data['rows'])){

			$page_count = ceil($total/$per_page);
			// group into ranges with cute little .... around the numbers if there's too many.
			$rangestart = max(0,$page_number-5);
			$rangeend = min($page_count-1,$page_number+5);

			ob_start();
			echo '<div class="pagination_summary">';
			echo '<p>';
            _e('Showing records %s to %s of %s',(($page_number*$per_page)+1),(($page_number*$per_page)+count($data['rows'])),$total);
            //echo 'Showing records ' . (($page_number*$per_page)+1) . ' to ' . (($page_number*$per_page)+count($data['rows'])) .' of ' . $total . '</p>';
            echo $pagination_hooks;
            echo '</p>';
			echo '</div>';
			$data['summary'] = ob_get_clean();
			ob_start();
			echo '<div class="pagination_links">';
			echo "\n<p>";
			if($page_number > 0){ ?>
			    <a href="<?php echo $request_uri;?>pg<?php echo $table_id;?>=<?php echo $page_number-1;?>#t_<?php echo $table_id;?>" rel="<?php echo $page_number-1;?>"><?php _e('&laquo; Prev');?></a> |
			<?php  } else{ ?>
			    <?php _e('&laquo; Prev');?> |
			<?php  }
            if($rangestart>0){
				?> <a href="<?=$request_uri;?>pg<?php echo $table_id;?>=0#t_<?php echo $table_id;?>" rel="0" class="">1</a> <?php
				if($rangestart>1)echo ' ... ';
			}
			for($x=$rangestart;$x<=$rangeend;$x++){
				if($x == $page_number){
					?>
					<a href="<?=$request_uri;?>pg<?php echo $table_id;?>=<?=$x;?>#t_<?php echo $table_id;?>" rel="<?=$x;?>" class="current"><?=($x+1);?></a>
					<?php
				}else{
					?>
					<a href="<?=$request_uri;?>pg<?php echo $table_id;?>=<?=$x;?>#t_<?php echo $table_id;?>" rel="<?=$x;?>" class=""><?=($x+1);?></a>
					<?php
				}
			}
			if($rangeend < ($page_count-1)){
				if($rangeend < ($page_count-2))echo ' ... ';
				?> <a href="<?=$request_uri;?>pg<?php echo $table_id;?>=<?=($page_count-1);?>#t_<?php echo $table_id;?>" rel="<?=($page_count-1);?>" class=""><?=($page_count);?></a> <?php
			}

			if($page_number < ($page_count-1)){ ?>
			    | <a href="<?php echo $request_uri;?>pg<?php echo $table_id;?>=<?php echo $page_number+1;?>#t_<?php echo $table_id;?>" rel="<?php echo $page_number+1;?>"><?php _e('Next &raquo;');?></a>
			<?php  } else{ ?>
			    | <?php _e('Next &raquo;');?>
			<?php  }
            echo '</p>';
			echo '</div>';
			?>
			<script type="text/javascript">
				$(function(){
					$('.pagination_links a').each(function(){
						// make the links post the search bar on pagination.
						$(this).click(function(){
							// see if there's a search bar to post.
							var search_form = false;
							search_form = $('.search_form')[0]
							$('.search_bar').each(function(){
								var form = $(this).parents('form');
								if(typeof form != 'undefined'){
									search_form = form;
								}
							});
							if(typeof search_form == 'object'){
								$(search_form).append('<input type="hidden" name="pg<?php echo $table_id;?>" value="'+$(this).attr('rel')+'">');
								search_form = search_form[0];
								if(typeof search_form.submit == 'function'){
									search_form.submit();
								}else{
									$('[name=submit]',search_form).click();
								}
								return false;
							}
						});
					});
				});
			</script>
			<?php
			$data['links']=ob_get_clean();
		}
	}
	return $data;
}

function print_heading($options){
	if(!is_array($options)){
		$options = array(
			'type' => 'h2',
			'title' => $options,
		);
	}
	$buttons = array();
	if(isset($options['button']) && is_array($options['button']) && count($options['button'])){
		$buttons = $options['button'];
		if(isset($buttons['url'])){
			$buttons = array($buttons);
		}
	}
	?>
	<<?php echo $options['type'];?>>
		<?php foreach($buttons as $button){ ?>
		<span class="button">
			<a href="<?php echo $button['url'];?>" class="uibutton"<?php if(isset($button['id'])) echo ' id="'.$button['id'].'"';?><?php if(isset($button['onclick'])) echo ' onclick="'.$button['onclick'].'"';?>>
				<span><?php echo _l($button['title']);?></span>
			</a>
		</span>
		<?php } ?>
        <?php if(isset($options['help'])){ ?>
            <span class="button">
                <?php _h($options['help']);?>
            </span>
        <?php } ?>
		<span class="title">
			<?php echo _l($options['title']);?>
		</span>
	</<?php echo $options['type'];?>>
	<?php
}


function redirect_browser($url,$hard=false){
    hook_finish();
	$original_url = $url;
    if($hard){
		header('HTTP/1.1 301 Moved Permanently');
	}
	if(!preg_match('/^https?:/',$url) && $url[0]!="/" && $url[0]!="?"){
		$url = _BASE_HREF.$url;
	}
    if(false && _DEBUG_MODE){
        module_debug::$show_debug = true;
        module_debug::log(array(
            'title' => 'Redirecting',
            'file' => 'includes/functions.php',
            'data' => "to '$original_url'" . ($url!=$original_url ? " (converted to: $url)" : '') . 
                "<br/><br/>Please <a href='$url'>Click Here</a> to perform the redirect.",
        ));
        module_debug::print_heading();
    }else{
        header("Location: ".$url);
    }
    exit;
}

/**
 * Take a full link <a href="adsf">afsdf</a>
 * and turn it into a popup link.
 * 
 * @param  $link
 * @param array $options
 * @return string
 */
function popup_link($link,$options=array()){
    $hash = substr(md5($link.mt_rand(3,8)),4,17);
    module_debug::log(array(
        'title' => 'PopUp Link',
        'file' => 'includes/functions.php',
        'data' => "Converting $link into a popup link",
    ));
    $width = (isset($options['width'])) ? $options['width'] : 400;
    $height = (isset($options['height'])) ? $options['height'] : 300;
    preg_match('#href="([^"]*)"#',$link,$matches);
    $url = $matches[1];
    if(!preg_match('/display_mode/',$url)){
        $url .= (strpos($url,'?') ? '&' : '?').'display_mode=ajax';
    }
    //
    ob_start();
    if(isset($options['force']) && $options['force']){
        $link = preg_replace('#<a href#','<a onclick="$(\'#popup_link'.$hash.'\').dialog(\'open\'); return false;" href',$link);
        echo $link;
    }else{
        echo $link;
        ?>
        <a href="#" onclick="$('#popup_link<?php echo $hash;?>').dialog('open'); return false;">(popup)</a>
    <?php } ?>
    <div id="popup_link<?php echo $hash;?>" title="">
        <div class="modal_inner"></div>
    </div>
    <script type="text/javascript">
        $(function(){
            $("#popup_link<?php echo $hash;?>").dialog({
                autoOpen: false,
                width: <?php echo $width;?>,
                height: <?php echo $height;?>,
                modal: true,
                buttons: {
					<?php if(!isset($options['hide_close'])){ ?>
                    Close: function() {
                        $(this).dialog('close');
                    }
					<?php } ?>
                },
                open: function(){
                    var t = this;
                    $.ajax({
                        type: "GET",
                        url: '<?php echo $url;?>',
                        dataType: "html",
                        success: function(d){
                            $('.modal_inner',t).html(d);
                            $('input[name=_redirect]',t).val(window.location.href);
                            init_interface();
                        }
                    });
                },
                close: function() {
                    $('.modal_inner',this).html('');
                }
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
function part_number($number){
    return '#'.$number;
}





function ordinal($i){$l=substr($i,-1);return$i.($l>3||$l==0?'th':($l==3?'rd':($l==2?'nd':'st')));}

function idnumber($number){
    return str_pad($number,7,'0',STR_PAD_LEFT);
}


function hook_finish($add=false){
    static $hooks = array();
    // run any hooks that have been registered for when execution has finished.
    if($add !== false){
        $hooks[md5(serialize($add))] = $add;
    }else{
        global $plugins;
        // running them
        foreach($hooks as $hook){
            call_user_func_array(array($plugins[$hook['plugin']],$hook['method']),$hook['args']);
        }
    }
}


function is_installed(){
    if(defined('_DB_NAME') && _DB_NAME != ''){
        // check for config table.
        if(db_connect() === false){
            return false; // db connection failed.
        }
        $sql = "SHOW TABLES LIKE '"._DB_PREFIX."config'";
        $res = qa1($sql);
        if(count($res)){
            return true;
        }else{
            return false;
        }
    }
    return false; //not installed
    /*if(!defined('_UCM_INSTALLED') || !_UCM_INSTALLED){
        return false;
    }else{
        return true;
    }*/
}
function h($e){
    return htmlspecialchars($e);
}


function getcred(){
	return module_security::getcred();
}
/*function getlevel(){
	return module_security::getlevel();
}*/

function _shl($string,$highlight){
    $string = htmlspecialchars($string);
	$highlight= trim($highlight);
	if(!$highlight)return $string;
	return preg_replace('/'.preg_quote($highlight,'/').'/i','<span style="background-color:#FFFF66">$0</span>',$string);
}

function file_exists_ip($filename) {
        if(function_exists("get_include_path")) {
            $include_path = get_include_path();
        } elseif(false !== ($ip = ini_get("include_path"))) {
            $include_path = $ip;
        } else {return false;}

        if(false !== strpos($include_path, PATH_SEPARATOR)) {
            if(false !== ($temp = explode(PATH_SEPARATOR, $include_path)) && count($temp) > 0) {
                for($n = 0; $n < count($temp); $n++) {
                    if(false !== @file_exists($temp[$n] . $filename)) {
                        return true;
                    }
                }
                return false;
            } else {return false;}
        } elseif(!empty($include_path)) {
            if(false !== @file_exists($include_path)) {
                return true;
            } else {return false;}
        } else {return false;}
    }


function frndlyfilesize($filesize){

    if(is_numeric($filesize)){
        $decr = 1024;
        $step = 0;
        $prefix = array('Byte','KB','MB','GB','TB','PB');

        while(($filesize / $decr) > 0.9){
            $filesize = $filesize / $decr;
            $step++;
        }
        return round($filesize,2).' '.$prefix[$step];
    } else {
        return '0';
    }
}

function get_display_mode(){

    if(isset($_REQUEST['display_mode']) && $_REQUEST['display_mode']!='iframe' && $_REQUEST['display_mode']!='ajax'){
        $_SESSION['display_mode'] = $_REQUEST['display_mode'];
    }
    if(isset($_REQUEST['display_mode'])){
        return $_REQUEST['display_mode'];
    }
    if(isset($_SESSION['display_mode']) && $_SESSION['display_mode']){
        return $_SESSION['display_mode'];
    }

    if(class_exists('module_mobile',false)){
        if(module_mobile::is_mobile_browser()){
            return 'mobile';
        }
    }
    return 'normal';

}


@include_once('old.php');
@include_once('pro.php');
@include_once('dev.php');
