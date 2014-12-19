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

$import_options = json_decode(base64_decode($_REQUEST['import_options']),true);
if(!$import_options || !is_array($import_options)){
    echo 'Sorry import failed. Please try again';
    exit;
}
$demo_csv_url = $_SERVER['REQUEST_URI'].'&download';

if(isset($_REQUEST['download'])){
    ob_end_clean();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    header("Content-Type: text/csv");
    //todo: correct file name
    header("Content-Disposition: attachment; filename=\"SampleImportFile.csv\";");
    header("Content-Transfer-Encoding: binary");
    foreach($import_options['fields'] as $key=>$val){
        echo '"'.str_replace('"','""',$key).'",';
    }
    echo "\n";
    exit;
}

print_heading(_l('Import Data: %s',$import_options['name']));

if(isset($_REQUEST['run_import']) && $_REQUEST['run_import'] == 'true'){

    $add_to_group = json_decode(base64_decode($_REQUEST['add_to_group']),true);
    // we get all the import data from the posted form, then we hand it back to the callback function specified in the import options.
    $columns = $_REQUEST['column'];
    $rows = $_REQUEST['row'];
    // sort them into a big matrix of matching data.
    $data = array();
    foreach($rows as $rowid => $rowdata){
        $newrow = array();
        foreach($rowdata as $column => $value){
            if(isset($columns[$column])&&$columns[$column]){
                // if we selected this as a column:
                $newrow[$columns[$column]] = $value;
            }
        }
        $data[$rowid] = $newrow;
        unset($rows[$rowid]);
    }

    if($import_options['callback']){
        call_user_func($import_options['callback'],$data,$add_to_group);
        _e('Successfully imported %s records. Thanks!',count($data));
    }else{
        echo 'failed..';
    }
    if($import_options['return_url']){ ?>
        <input type="button" name="cancel" value="<?php _e('Continue');?>" class="submit_button" onclick="window.location.href='<?php echo htmlspecialchars($import_options['return_url']);?>';">
    <?php }

}else if(isset($_REQUEST['upload_import']) && $_REQUEST['upload_import'] == 'true'){

    $csv_file = $_FILES['csv']['tmp_name'];
    if(!$csv_file){
        echo 'Upload failed. Please try again.';
        exit;
    }
    $fd = fopen($csv_file,'r');
    $rows = array();
    while($row = fgetcsv($fd)){
        $rows[]=$row;
    }
    if(count($rows)<=1){
        echo 'There are less than 1 rows in this import file. Please try again with more rows.';
        exit;
    }
    $provided_header = array_shift($rows);
    foreach($provided_header as $key=>$val){
        if(!trim($val)){
            unset($provided_header[$key]);
        }
    }
    $column_count = count($provided_header);
    ?>

    <form action="" method="post">
        <input type="hidden" name="run_import" value="true">
        <input type="hidden" name="add_to_group" value="<?php echo base64_encode(json_encode(isset($_REQUEST['add_to_group']) ? $_REQUEST['add_to_group'] : array()));?>">

        <p><?php _e('We have detected %s records to import. Please confirm your import data below, once you are happy with the columns please press the process button. If the import format does not look correct, please go back and try again.',count($rows));?></p>

        <div style="overflow-x: auto;">
        <table class="tableclass tableclass_rows">
            <thead>
            <tr>
                <?php
                for($column = 0;$column < $column_count;$column++){
                    $display_key = isset($provided_header[$column]) ? $provided_header[$column] : '';
                    ?>
                <th><select name="column[<?php echo $column;?>]">
                    <option value=""> - ignore -</option>
                    <?php foreach($import_options['fields'] as $key2=>$val2){
                        if(!is_array($val2)){
                            $val2 = array($val2);
                        }
                        $this_key = $val2[0];
                        ?>
                        <option value="<?php echo $this_key;?>"<?php echo $display_key==$key2 ? ' selected':'';?>><?php echo htmlspecialchars($key2);?></option>
                    <?php } ?>
                </select>
                </th>
                <?php
                } ?>
            </tr>
            </thead>
            <tbody>
            <?php
                $rowid=0;
                foreach($rows as $row){ ?>
                <tr>
                    <?php
                    for($column = 0;$column < $column_count;$column++){ ?>
                        <td>
                            <?php // is it a multiline field?
                            if(preg_match('/[\n\r][^\s]/',$row[$column])){ ?>
                                <textarea rows="3" cols="3" name="row[<?php echo $rowid;?>][<?php echo $column;?>]" class="i"><?php echo htmlspecialchars($row[$column]);?></textarea>
                            <?php }else{ ?>
                                <input type="text" name="row[<?php echo $rowid;?>][<?php echo $column;?>]" value="<?php echo htmlspecialchars($row[$column]);?>" class="i">
                            <?php } ?>
                        </td>
                    <?php
                    }
                ?>
                </tr>
            <?php
            $rowid++;
            } ?>
            </tbody>
        </table>
        </div>
        <input type="submit" name="save" value="<?php _e('Process Import');?>" class="submit_button save_button">
        <?php if($import_options['return_url']){ ?>
            <input type="button" name="cancel" value="<?php _e('Cancel');?>" class="submit_button" onclick="window.location.href='<?php echo htmlspecialchars($import_options['return_url']);?>';">
        <?php } ?>

        <p><?php _e('For your reference, the fields are:');?></p>
        <ul>
            <?php
            foreach($import_options['fields'] as $key=>$val){
                if(!is_array($val)){
                    $val = array($val);
                }
                echo '<li>';
                echo htmlspecialchars($key);
                if(isset($val[1])&&$val[1]){
                    echo ' <span class="required">(required field)</span>';
                }
                if(isset($val[2])&&$val[2]){
                    _h($val[2]);
                }
                echo '</li>';
            }
            ?>
        </ul>
    </form>
    <?php
}else{
    ?>

    <p><?php _e('Please make sure your data is in the below format. The <strong>first line</strong> of your CSV file should be the column headers as below. You can <a href="%s">click here</a> to download a sample CSV template ready for import. Please use UTF8 file format. %s',$demo_csv_url,_hr('Please try to save your import CSV file in UTF8 format for best results (search google for a howto). Once your import CSV file is ready to upload please use the form below. (Please ensure this is a CSV file, not an excel file.)<br> We recommend OpenOffice for best CSV file generation.'));?></p>


    <div style="overflow-x: auto; overflow-y: hidden;">
    <table class="tableclass tableclass_rows">
        <thead>
        <tr>
            <?php foreach($import_options['fields'] as $key=>$val){
                if(!is_array($val)){
                    $val = array($val);
                }
                $display_name = $val[0];
                ?>
            <th><?php echo htmlspecialchars($key);
                if(isset($val[1])&&$val[1]){
                    echo ' <span class="required">*</span>';
                }
                if(isset($val[2])&&$val[2]){
                    _h($val[2]);
                }
                ?></th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php for($x=1;$x<3;$x++){ ?>
        <tr>
            <?php foreach($import_options['fields'] as $key=>$val){ ?>
                <td><?php _e('Record %s',$x);?></td>
            <?php } ?>
        </tr>
        <?php } ?>
        <tr>
            <td colspan="<?php echo count($import_options['fields']);?>">
                <?php _e('etc...');?>
            </td>
        </tr>
        </tbody>
    </table>
    </div>



    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="upload_import" value="true">
        <h3><?php _e('Import');?></h3>
        <table class="tableclass tableclass_form tableclass_full">
            <tbody>
            <tr>
                <th class="width1"><?php _e('Your CSV file (formatted to the above specification):');?></th>
                <td>
                    <input type="file" name="csv">
                </td>
            </tr>
            <?php if(class_exists('module_group',false) && isset($import_options['group']) && $import_options['group']){
                // hack to support multiple groups (for members)
                if(!is_array($import_options['group'])){
                    $import_options['group'] = array($import_options['group']);
                }
                foreach($import_options['group'] as $group_option){
                ?>
                <tr>
                    <th>
                        <?php _e('Add imported records to group:');?>
                    </th>
                    <td>
                        <?php $groups = module_group::get_groups($group_option);
                        if(!count($groups)){
                            _e('Sorry, no groups exist. Please create a %s group first.',$group_option);
                        }
                        foreach($groups as $group){
                            $group_id = $group['group_id'];
                            ?>
                            <input type="checkbox" class="add_to_group" name="add_to_group[<?php echo $group['group_id'];?>]" id="groupchk<?php echo $group_id;?>" value="yes">
                            <label for="groupchk<?php echo $group_id;?>"><?php echo htmlspecialchars($group['name']);?></label> <br/>
                            <?php
                        } ?>
                    </td>
                </tr>
                <?php
                }
             } ?>
            <tr>
                <th></th>
                <td>
                    <input type="submit" name="go" value="<?php _e('Upload');?>" class="submit_button save_button">
                    <?php if($import_options['return_url']){ ?>
                        <input type="button" name="cancel" value="<?php _e('Cancel');?>" class="submit_button" onclick="window.location.href='<?php echo htmlspecialchars($import_options['return_url']);?>';">
                    <?php } ?>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
<?php } ?>