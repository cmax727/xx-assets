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
if(!$note_list_safe)die('fail');
//print_r($note_items);exit;
if(!isset($popup_links)){
    $popup_links= true;
}
?>

<?php
if(isset($options['title']) && $options['title']){
	?>
	<h3>
        <?php if($can_create){ ?>
		<span class="button">
			<a href="<?php echo module_note::link_open('new',false,$options);?>" class="note_add"><?php _e('Add New Note');?></a>
		</span>
        <?php } ?>
		<?php echo _l($options['title']);?>
	</h3>
	<?php
}else if($can_create){
	?>
	<a href="<?php echo module_note::link_open('new',false,$options);?>" class="note_add"><?php _e('Add New Note');?></a>
	<?php
}
?>
<table class="tableclass tableclass_rows notes" width="100%" id="note_<?php echo $owner_table;?>_<?php echo $owner_id;?>" style="<?php if(!count($note_items))echo ' display:none; '; ?>">
	<thead>
		<tr>
			<th width="60"><?php _e('Date');?></th>
			<th><?php _e('Description');?></th>
			<th width="40"><?php _e('Info');?></th>
		</tr>
	</thead>
	<tbody>
		<?php
        //foreach($pagination['rows'] as $n){
        foreach($note_items as $n){
			echo $n['html'];
		}
		?>
	</tbody>
</table>
<div style="min-height: 10px;">
</div>
 <?php
//echo $pagination['links'];
?>


<div id="new_note_popup" title="<?php _e('Add New Note');?>">
	<div id="new_note_inner"></div>
</div>
<?php if($popup_links){ ?>
<script type="text/javascript">
	var edit_note_id = 'new';
	var edit_note_changed = false;
	function run_note_edit(){
		$('.note_edit')
		.addClass('note_edit_done')
		.removeClass('note_edit')
		.click(function(){
			edit_note_id = $(this).attr('rel');
			$('#new_note_popup').dialog('open');
			return false;
		});
	}

	$(function(){
		$("#new_note_popup").dialog({
			autoOpen: false,
			height: 350,
			width: 400,
			modal: true,
			buttons: {
				'<?php _e('Save note');?>': function() {
					$.ajax({
						type: 'POST',
                        url: '<?php echo $plugins['note']->link('note_admin',array(
                            '_process' => 'save_note',
                            'options' => base64_encode(serialize($options)),
                            //'owner_id' => $owner_id,
                        ));?>&note_id='+edit_note_id+'',
						data: {
							note_time: $('#form_note_time').val(),
							note: $('#form_note_data').val(),
							rel_data: $('#form_rel_data').val(),
							user_id: $('.form_user_id').val(),
							reminder: ($('#form_reminder')[0].checked ? 1 : 0)
						},
						success: function(h){
							$('#note_<?php echo $owner_table;?>_<?php echo $owner_id;?>').show();
							if(edit_note_id == 'new'){
								$('#note_<?php echo $owner_table;?>_<?php echo $owner_id;?> tbody').append(h);
							}else{
								$('#note_'+edit_note_id+'').replaceWith(h);
							}
							edit_note_changed = false;
							$('#new_note_popup').dialog('close');
							run_note_edit();
						}
					});
				},
                '<?php _e('Cancel');?>': function() {
					$(this).dialog('close');
				}
			},
			open: function(){
				$.ajax({
					type: "GET",
                    url: '<?php echo $plugins['note']->link('note_admin',array(
                        'options' => base64_encode(serialize($options)),
                        //'owner_table' => $owner_table,
                        //'owner_id' => $owner_id,
                    ));?>&note_id='+edit_note_id+'&display_mode=ajax',
					dataType: "html",
					success: function(d){
						if($('#form_note_data',d).length < 1){
							alert('Failed to load note, please login again.');
							$(this).dialog('close');
							return false;
						}
						$('#new_note_inner').html(d);
						<?php if($rel_data){ ?>
						if(edit_note_id=='new'){
							$('#form_rel_data').val('<?php echo $rel_data;?>');
						}
						<?php } ?>
						load_calendars();
						edit_note_changed = false;
						$('#form_note_data')[0].focus();
						$('#form_note_data').change(function(){
							edit_note_changed = true;
						})
					}
				});
			},
			beforeclose: function(){
				if(edit_note_changed && $('#form_note_data').val() != ''){
					return(confirm('Close without saving?'));
				}
				return true;
			},
			close: function() {
				$('#new_note_inner').html('');
			}
		});
		$('.note_add')
		.button()
		.click(function(){
			edit_note_id = 'new';
			$('#new_note_popup').dialog('open');
			return false;
		});
		run_note_edit();
	});
</script>

<?php } ?>

<a name="t_note_<?php echo $owner_table;?>_<?php echo $owner_id;?>"></a>
