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

$options = isset($_REQUEST['options']) ? unserialize(base64_decode($_REQUEST['options'])) : array();

$file_id = (int)$_REQUEST['file_id'];
$file = module_file::get_file($file_id);
if($file_id>0 && $file && $file['file_id']==$file_id){
	if(class_exists('module_security',false)){
        // make sure current customer can access this invoice
        if(!module_security::can_access_data('file',$file,$file_id)){
            echo 'Data access denied. Sorry.';
            exit;
        }

        module_security::check_page(array(
			'module' => $module->module_name,
            'feature' => 'Edit',
		));

	}
}else{
	if(class_exists('module_security',false)){
		module_security::check_page(array(
			'module' => $module->module_name,
            'feature' => 'Create',
		));
	}
	module_security::sanatise_data('file',$file);
}




?>


	
<form action="" method="post" enctype="multipart/form-data">
	<input type="hidden" name="_process" value="save_file" />
    <input type="hidden" name="file_id" value="<?php echo $file_id; ?>" />
    <input type="hidden" name="options" value="<?php echo base64_encode(serialize($options)); ?>" />


    <?php

    $fields = array(
    'fields' => array(
        'url' => 'Name',
    ));
    module_form::set_required(
        $fields
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
        ))
    );
    

    ?>

	<table cellpadding="10" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="50%">
					<h3><?php echo _l('File Details'); ?></h3>



					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>

							<tr>
								<th class="width1">
									<?php echo _l('File'); ?>
								</th>
								<td>
									<input type="file" name="file_upload">
                                    <a href="<?php echo $module->link('file_edit',array('_process'=>'download','file_id'=>$file['file_id']),'file',false);?>"><?php echo nl2br(htmlspecialchars($file['file_name']));?></a>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Status'); ?>
								</th>
								<td>
									<?php echo print_select_box(module_file::get_statuses(),'status',$file['status'],'',true,false,true); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Customer'); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    $res = module_customer::get_customers();
                                    foreach($res as $row){
                                        $c[$row['customer_id']] = $row['customer_name'];
                                    }
                                    echo print_select_box($c,'customer_id',$file['customer_id']);
                                    ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Job'); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    $res = module_job::get_jobs(array('customer_id'=>$file['customer_id']));
                                    foreach($res as $row){
                                        $c[$row['job_id']] = $row['name'];
                                    }
                                    echo print_select_box($c,'job_id',$file['job_id']);
                                    ?>
								</td>
							</tr>
						</tbody>
                        <?php
                         module_extra::display_extras(array(
                            'owner_table' => 'file',
                            'owner_key' => 'file_id',
                            'owner_id' => $file['file_id'],
                            'layout' => 'table_row',
                            )
                        );
                        ?>
					</table>
				</td>
				<td valign="top" width="50%">

					<h3><?php echo _l('File Description'); ?></h3>

					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
								<td>
									<textarea name="description" rows="4" cols="50" style="width:100%;"><?php echo htmlspecialchars($file['description']);?></textarea>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save file'); ?>" class="submit_button save_button" />
					<?php if((int)$file_id){ ?>
					<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
					<?php } ?>
					<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_file::link_open(false); ?>';" class="submit_button" />
				</td>
			</tr>
		</tbody>
	</table>


</form>
