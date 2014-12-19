

					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
                            <?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:29 
  * IP Address: 127.0.0.1
  */
                            if(isset($use_master_key) && $use_master_key == 'customer_id' && (!isset($output) || $output != 'new')){
                                $primary = false;
                                $customer_data = module_customer::get_customer($user[$use_master_key]);
                                if($customer_data['primary_user_id'] == $user_id){
                                    $primary = true;
                                }
                                ?>
							<tr>
								<th class="width1">
									<?php echo _l('Primary'); ?>
								</th>
								<td>
									<input type="checkbox" name="customer_primary" value="1" <?php echo $primary ? ' checked' : '';?> />
									<?php _h('This users details will be used as a primary point of contact for this customer. These details will display in the main customer listing for this customer. Also if you send an invoice or a newsletter to this "customer" then this email address will be used.'); ?>
								</td>
							</tr>
                            <?php } ?>
							<tr>
								<th class="width1">
									<?php echo _l('Name'); ?>
								</th>
								<td>
									<input type="text" name="name" id="name" style="width: 200px;" value="<?php echo htmlspecialchars($user['name']); ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Email Address'); ?>
								</th>
								<td>
									<input type="text" name="email" style="width: 200px;" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" />
								</td>
							</tr>

							<tr>
								<th>
									<?php echo _l('Phone'); ?>
								</th>
								<td>
									<input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="phone" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Mobile'); ?>
								</th>
								<td>
									<input type="text" name="mobile" id="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" class="phone" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Fax'); ?>
								</th>
								<td>
									<input type="text" name="fax" id="fax" value="<?php echo htmlspecialchars($user['fax']); ?>" class="phone" />
								</td>
							</tr>
                            <?php if(class_exists('module_language',false)){ ?>
							<tr>
								<th>
									<?php echo _l('Language'); ?>
								</th>
								<td>
									<?php echo print_select_box(module_language::get_languages_attributes(),'language',$user['language'],'',false); ?>
								</td>
							</tr>
                            <?php } ?>
						</tbody>
                        <?php
                        if(isset($user['user_id']) && (int)$user['user_id']> 0){
                         module_extra::display_extras(array(
                            'owner_table' => 'user',
                            'owner_key' => 'user_id',
                            'owner_id' => $user['user_id'],
                            'layout' => 'table_row',
                            )
                        );
                    }
                        ?>
					</table>


	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />