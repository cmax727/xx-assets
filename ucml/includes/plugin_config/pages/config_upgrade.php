<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:18:53 
  * IP Address: 127.0.0.1
  */

set_time_limit(0);
if(!module_config::can_i('view','Upgrade System')){
    echo 'upgrade denied. ssorry';
    exit;
}
?>

<?php if(!isset($setup_upgrade_hack))print_heading('Upgrade System'); ?>

<?php

if(isset($_REQUEST['save_license_codes']) && isset($_REQUEST['license_codes']) && is_array($_REQUEST['license_codes'])){
    $license_codes = '';
    foreach($_REQUEST['license_codes'] as $license_code){
        $license_code= trim($license_code);
        if(strlen($license_code)>5)$license_codes .= $license_code.'|';
    }
    $license_codes = rtrim($license_codes,'|');
    module_config::save_config('_installation_code',$license_codes);
}
if(isset($_REQUEST['install_upgrade'])){

    if(!module_config::c('_installation_code')){
        ?>
        Please enter your license code before doing an upgrade.
        <?php
        exit;
    }

    if(!isset($setup_upgrade_hack) && (!isset($_REQUEST['doupdate']) || !is_array($_REQUEST['doupdate']))){
        echo 'please select at least one upgrade to perform';
        exit;
    }
    $available_updates = module_config::check_for_upgrades();

    if(isset($setup_upgrade_hack) && $available_updates && isset($available_updates['message']) && strlen(trim($available_updates['message']))>2){
        echo $available_updates['message'];
        ?>
        <input type="button" name="go" value="<?php _e('Try Installation Again');?>" class="submit_button save_button" onclick="window.location.href='?m=setup&step=3'">
        <?php
        return;
    }

    if($available_updates && isset($available_updates['plugins'])){ ?>
        <p>When this is complete please click the "Continue Installation" button at the very bottom of the page.</p>
    <?php }

    $errors = array();

    // grab the requested plugins and process each one at a time outputting the result to the page

    if(isset($setup_upgrade_hack)){
        $upgrade_plugins = array();
        if($available_updates && isset($available_updates['plugins'])){
            foreach($available_updates['plugins'] as $available_update){
                $upgrade_plugins[$available_update['key']] = true;
            }
        }
    }else{
        $upgrade_plugins = $_REQUEST['doupdate'];
    }

    $completed_updates = array();

    foreach($upgrade_plugins as $plugin_name => $tf){

        $this_update = false;
        foreach($available_updates['plugins'] as $available_update){
            if($available_update['key'] == $plugin_name){
                $this_update = $available_update;
                break;
            }
        }
        if($this_update){
            echo "Downloading update: <span style='text-decoration:underline;'>".htmlspecialchars($this_update['description'])."</span>... ";

            if($update = module_config::download_update($this_update['key'])){
                ?>
                <span class="success_text">complete!</span>
                <a href="#" onclick="$('#update_<?php echo $this_update['key'];?>').toggle();">[view details]</a>
                <div style="font-weight: 0.9em; padding:9px; display: none;" id="update_<?php echo $this_update['key'];?>">
                <?php
                //echo '<pre>';print_r($update);exit;
                foreach($update['plugins'] as $available_update){

                    if(
                        $available_update['key'] != $this_update['key']
                        //(!isset($available_update['linked_key']) && $available_update['key'] != $this_update['key']) ||
                        //(isset($available_update['linked_key']) && $available_update['linked_key'] != $this_update['key'])
                    ){
                        // core update bug fix.
                        continue;
                    }
                    // have we done this yet?
                    if(isset($completed_updates[$available_update['key']]))continue;
                    $completed_updates[$available_update['key']] = true;

                    foreach($available_update['folders'] as $file){ ?>
                        Folder: <?php echo $file;?>
                        <span class="small">
                            <?php
                            if(is_dir(_UCM_FOLDER.$file)){
                                echo 'this folder exists, nothing will change.';
                            }else{
                                // check if writable
                                if(mkdir(_UCM_FOLDER.$file,0777,true)){
                                    echo 'this new folder has been <strong>created</strong>';
                                }else{
                                    $error = '<strong>WARNING:</strong> failed to create new folder: '.$file;
                                    $errors[] = $error;
                                    echo $error;
                                }
                            }
                            ?>
                        </span><br>
                    <?php } ?>
                    <?php foreach($available_update['files'] as $file){ ?>
                        File: <?php echo $file;?>
                        <span class="small">
                            <?php
                            if(!isset($available_update['file_contents'][$file])){
                                $error = '<strong>WARNING:</strong> failed to get file contents from server: '.$file;
                                $errors[] = $error;
                                echo $error;
                            }else if(!file_put_contents(_UCM_FOLDER.$file,base64_decode($available_update['file_contents'][$file]))){
                                $error = '<strong>WARNING:</strong> failed to install the file: '.$file;
                                $errors[] = $error;
                                echo $error;
                            }else{
                                echo 'this file has been <strong>installed</strong> successfully';
                            }
                        ?>
                        </span><br>
                    <?php }
                }
                ?>
                </div>
                <?php
                //exit;
            }else{
                echo '<span class="error_text">failed to download update :( </span> ';
            }
            echo '<br>';
            //$_REQUEST['run_upgrade'] = true; // so we do the DB update again down the bottom.
        }else{
            $error = "Failed to start update ($plugin_name):";
            $errors[] = $error;
            echo $error;
        }
    }

    if($errors){ ?>
    <div class="warning">
        <ul>
            <?php foreach($errors as $error){ ?>
            <li><?php echo $error;?></li>
            <?php } ?>
        </ul>
    </div>

    <form action="" method="post">
        <input type="hidden" name="install_upgrade" value="true">
        <?php if(!isset($setup_upgrade_hack)){
        foreach($upgrade_plugins as $key=>$val){
        ?>
            <input type="hidden" name="doupdate[<?php echo htmlspecialchars($key);?>]" value="1">
        <?php } ?>
        <?php } ?>
        <input type="submit" name="go" value="<?php _e('Try Installation Again');?>" class="submit_button save_button">
    </form>

    <?php

    }

    ?>
    Update has completed! Please click the button below to finish installation:

<form action="" method="post">
    <input type="hidden" name="run_upgrade" value="true">
    <input type="submit" name="go" value="<?php _e('Continue Installation');?>" class="submit_button">
</form>
    <?php


}else if(isset($_REQUEST['download_upgrade'])){

    if(_DEMO_MODE){
        echo 'Sorry, downloading updates as a zip file is not allowed in demo mode :)';
        echo ' If you do find a demo mode bug though, please let me know. Cheers!';
        exit;
    }

}else if(isset($_REQUEST['check_upgrade'])){

    if(!module_config::c('_installation_code')){

        ?>
        Please enter your license code before doing an upgrade.
        <?php
        exit;

    }
    // check for any available upgrades on the internet.
    $available_updates = module_config::check_for_upgrades();

    if(isset($available_updates['message'])){
        echo $available_updates['message'];
    }

    if($available_updates && isset($available_updates['plugins']) && count($available_updates['plugins'])){
        $x=0;
        $errors = array();
        ?>

        <form action="" method="post">
        We found <?php count($available_updates['plugins']);?> available updates! <br>
        This will upgrade your system from version <?php echo module_config::current_version(); ?> to <?php echo $available_updates['new_version']; ?> <br>

        Please select which updates you would like to install:

        <table class="tableclass tableclass_rows">
            <thead>
            <tr>
                <th>Update</th>
                <th>Installed Version</th>
                <th>Available Version</th>
                <th>Description</th>
                <th>Files to update</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($available_updates['plugins'] as $available_update){ ?>
            <tr class="<?php echo $x++%2 ? 'odd' : 'even';?>">
                <td>
                    <input type="checkbox" name="doupdate[<?php echo $available_update['key'];?>]" value="1" <?php echo isset($available_update['recommended']) && $available_update['recommended'] ? 'checked="checked"' : '';?>>
                </td>
                <td>
                    <?php echo $available_update['installed_version'];?>
                </td>
                <td>
                    <?php echo $available_update['available_version'];?>
                </td>
                <td>
                    <?php echo htmlspecialchars($available_update['description']);?>
                </td>
                <td>
                    <?php echo count($available_update['files']);?> files,
                    <?php echo count($available_update['folders']);?> folders:
                    <a href="#" onclick="$('#update_<?php echo $available_update['key'];?>').toggle(); return false;">view</a>
                    <div id="update_<?php echo $available_update['key'];?>" style="display:none;">
                    <?php foreach($available_update['folders'] as $file){ ?>
                        Folder: <?php echo $file;?><br>
                            <span class="small">
                                <?php if(is_dir(_UCM_FOLDER.$file)){
                                    echo 'this folder exists, nothing will change.';
                                }else{
                                    // check if writable
                                    $dir = _UCM_FOLDER.dirname($file);
                                    if(mkdir(_UCM_FOLDER.$file)){
                                        echo 'this new folder will be <strong>created</strong>';
                                    }else{
                                        $error = '<strong>WARNING:</strong> no permissions to create folder: '.$file;
                                        $errors[] = $error;
                                        echo $error;
                                    }
                                }
                                ?>
                            </span><br>
                    <?php } ?>
                    <?php foreach($available_update['files'] as $file){ ?>
                        File: <?php echo $file;?><br>
                            <span class="small">
                            <?php if(is_file(_UCM_FOLDER.$file)){
                                if(is_writable(_UCM_FOLDER.$file)){
                                    echo 'this file exists and will be <strong>upgraded</strong>.';
                                }else{
                                    $error = '<strong>WARNING:</strong> no permissions to update file: '.$file;
                                    $errors[] = $error;
                                    echo $error;
                                }
                            }else{
                                // check if holding folder is writable
                                // edit: only if parent folder exists.
                                $dir = _UCM_FOLDER.dirname($file);
                                if(is_dir($dir)){
                                    if(is_writable($dir)){
                                        echo 'this new file will be <strong>created</strong>';
                                    }else{
                                        $error = '<strong>WARNING:</strong> no permissions to create file: '.$file;
                                        $errors[] = $error;
                                        echo $error;
                                    }
                                }
                            }
                            ?>
                            </span><br>
                    <?php } ?>
                    </div>

                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
            <?php if($errors){ ?>
            <div class="warning">
            <ul>
            <?php foreach($errors as $error){ ?>
                <li><?php echo $error;?></li>
            <?php } ?>
            </ul>
            </div>
            <?php } ?>

            Please make sure you have a backup of your system before doing an upgrade. Especially if you have modified any of the PHP code yourself.
            <br>

            <input type="submit" name="install_upgrade" value="Install Selected Updates Automatically">
            <!-- <input type="submit" name="download_upgrade" value="Download Updates as ZIP file for manual install"> -->
                </form>

        <?php

    }else{
        ?>
        No updates available at this time.
        <br>
        <?php
        if(isset($setup_upgrade_hack)){
            ?>
            <input type="button" name="go" value="<?php _e('Continue');?>" class="submit_button" onclick="window.location.href='?m=setup&step=4';">
            <?php
        }
    }
}else if(isset($_REQUEST['run_upgrade'])){

    $new_system_version = module_config::current_version();
    $fail = false;
    $set_versions = array();
    foreach($plugins as $plugin_name => &$p){
        echo "Checking plugin: <span style='text-decoration:underline;'>$plugin_name</span> - Current Version: ".$p->get_plugin_version().".... ";
        if($version = $p->install_upgrade()){
            echo '<span class="success_text">all good</span>';
            $set_versions[$plugin_name] = $version;
            $new_system_version = max($version,$new_system_version);
        }else{
            $fail = true;
            echo '<span class="error_text">failed</span> ';
        }
        echo '<br>';
    }
    // all done?

    //if(isset($set_versions['config'])){
        // config db worked.
        foreach($plugins as $plugin_name => &$p){
            if(isset($set_versions[$plugin_name])){
                $p->init();
                // lol typo - oh well.
                $p->set_insatlled_plugin_version($set_versions[$plugin_name]);
            }
        }
    //}
    if($fail){
        print_header_message();
        echo '<br><br>';
        _e('Some things failed. Please go back and try again');
    }else{
        echo '<br><br><strong>';
        _e('Success! Everything worked.');
        echo '</strong>';
        module_config::set_system_version($new_system_version);
    }

    if(isset($setup_upgrade_hack)){
        ?>
        <input type="button" name="go" value="<?php _e('Continue');?>" class="submit_button" onclick="window.location.href='?m=setup&step=4';">
        <?php
    }

}else{

    $license_codes = explode('|',module_config::c('_installation_code',''));
    foreach($license_codes as $license_code_id => $license_code){
        if(!trim($license_code)){
            unset($license_codes[$license_code_id]);
        }
    }
    if(!count($license_codes)){
        $license_codes[]='';
    }
    ?>

    <?php _e('To check for any available updates please insert your license code(s) for any purchased UCM products below and click the button.'); ?>
    <form action="" method="post">
        <input type="hidden" name="check_upgrade" value="true">
        <input type="hidden" name="save_license_codes" value="true">

        <div id="license_codes_holder">
            <?php foreach($license_codes as $license_code){ ?>
            <div class="dynamic_block">
                <input type="text" name="license_codes[]" value="<?php echo htmlspecialchars($license_code);?>" style="width:400px; padding:5px; border:1px solid #CCC;">
                <a href="#" class="add_addit" onclick="return seladd(this);">+</a>
                <a href="#" class="remove_addit" onclick="return selrem(this);">-</a>
            </div>
            <?php } ?>

        </div>
        <script type="text/javascript">
            set_add_del('license_codes_holder');
        </script>
        <input type="submit" name="go" value="<?php _e('Check for Updates');?>" class="submit_button">
    </form>
<br><br>
    <?php _e('To install any manual updates please click button below (ie: if you installed from a zip file).'); ?>
    <form action="" method="post">
        <input type="hidden" name="run_upgrade" value="true">
        <input type="submit" name="go" value="<?php _e('Run Manual Upgrades');?>" class="submit_button">
    </form>

<?php } ?>