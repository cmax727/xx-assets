<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:03 
  * IP Address: 127.0.0.1
  */


if(isset($_REQUEST['note_id'])){

    // re-check permissions sent in from ajax.
    // there could be a way to trick this, if someone can find it my congratulations to them!
    $options = unserialize(base64_decode($_REQUEST['options']));
    if(!$options)return;
    $can_view = $can_edit = $can_create = $can_delete = false;
    if(isset($options) && isset($options['owner_table']) && $options['owner_table'] && isset($options['title']) && $options['title']){
        global $plugins;
        $can_view = $plugins[$options['owner_table']]->can_i('view',$options['title']);
        $can_edit = $plugins[$options['owner_table']]->can_i('edit',$options['title']);
        $can_create = $plugins[$options['owner_table']]->can_i('create',$options['title']);
        $can_delete = $plugins[$options['owner_table']]->can_i('delete',$options['title']);
    }else{
        return;
    }

    if(!$can_view && !$can_edit)return;

    $note_id = (int)$_REQUEST['note_id'];
    if($note_id>0){
        $note = module_note::get_note($note_id);
        $owner_table = $note['owner_table'];
        $owner_id = $note['owner_id'];
        
    }else{
        $owner_table = isset($options['owner_table']) ? $options['owner_table'] : (isset($_REQUEST['owner_table']) ? htmlspecialchars($_REQUEST['owner_table']) : '');
        $owner_id = isset($options['owner_id']) ? $options['owner_id'] : (isset($_REQUEST['owner_id']) ? htmlspecialchars($_REQUEST['owner_id']) : '');
        $note = array(
            "note_time" => time(),
            "note" => '',
            "reminder" => '',
            "user_id" => '',
            'rel_data' => (isset($rel_data)) ? serialize($rel_data) : '',
        );
    }
    $note_edit_safe = true;
    if(get_display_mode() != 'ajax'){
        print_heading('Note');
        //print_r($options);
        if(isset($options['view_link'])){
            $note['rel_data'] = $options['view_link'];
        }
        ?>
        <form action="<?php
        // copied from the ajax version
           echo $plugins['note']->link('note_edit',array(
                                '_process' => 'save_note',
                                'owner_table' => $owner_table,
                                'owner_id' => $owner_id,
                                  'note_id'=>$note_id,
                            ));
    ?>" method="post">
            <input type="hidden" name="options" value="<?php echo base64_encode(serialize($options));?>">
            <input type="hidden" name="from_normal" value="true">

        <?php include('note_edit.php'); ?>

            <input type="submit" name="save" value="<?php _e('Save Note');?>" class="submit_button save_button">
            <?php if($note['rel_data']){
            ?>
            <input type="button" name="cancel" value="<?php _e('Cancel');?>" onclick="window.location.href='<?php echo htmlspecialchars($note['rel_data']);?>';" class="submit_button">
            <?php
        } ?>
        </form>
        <?php
    }else{
        include('note_edit.php');
    }
}