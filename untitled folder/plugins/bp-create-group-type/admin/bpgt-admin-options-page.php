<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
$group_types = get_option('bpgt_group_types');
$flag = 0;
if (!empty($group_types)) {
    $flag = 1;
    $group_types = unserialize($group_types);
}

$all_group_types = bp_groups_get_group_types();
if (!empty($all_group_types)) {
    $all_group_types = json_encode(array_keys($all_group_types));
}
if( empty( $all_group_types ) ) {
	$all_group_types = '';
}
?>
<div class="wrap nosubsub">
	<h1><?php _e('Group Types', 'bp-grp-types'); ?></h1>

	<p class="search-box">
		<label class="screen-reader-text" for="group-type-search-input">Search Group Types:</label>
		<input id="group-type-search-input" placeholder="<?php _e('Write here..', 'bp-grp-types'); ?>" type="text">
		<input id="search-bpgt" class="button" value="<?php _e('Search Group Types', 'bp-grp-types'); ?>" type="button">
	</p>

	<div id="col-container" class="wp-clearfix">
		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h2><?php _e('Add New Group Type', 'bp-grp-types'); ?></h2>
					<div class="notice notice-success group-type-add-success">
						<p><?php _e('Group Type Added!', 'bp-grp-types'); ?></p>
					</div>
					<div class="notice notice-error group-type-add-error">
						<p><?php _e('Group Type With This Slug Already Exists!', 'bp-grp-types'); ?></p>
					</div>
					<div class="notice notice-error group-type-add-error-name">
						<p><?php _e('Group Type "Name" Is Required!', 'bp-grp-types'); ?></p>
					</div>
					<div class="form-field form-required term-name-wrap">
						<label for="group-type-name"><?php _e('Name', 'bp-grp-types'); ?></label>
						<input id="group-type-name" value="" size="40" aria-required="true" type="text">
						<p><?php _e('The name is how it appears on your site.', 'bp-grp-types'); ?></p>
					</div>
					<div class="form-field term-slug-wrap">
						<label for="group-type-slug"><?php _e('Slug', 'bp-grp-types'); ?></label>
						<input name="slug" id="group-type-slug" value="" size="40" type="text">
						<p><?php _e('The slug is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'bp-grp-types'); ?></p>
					</div>
					<div class="form-field term-description-wrap">
						<label for="group-type-description"><?php _e('Description', 'bp-grp-types'); ?></label>
						<textarea id="group-type-desc" rows="5" cols="40"></textarea>
						<p><?php _e('The description is not prominent by default; however, some themes may show it.', 'bp-grp-types'); ?></p>
					</div>
					<p class="submit">
						<input id="add-grp-type" class="button button-primary" value="<?php _e('Add New Group Type', 'bp-grp-types'); ?>" type="button">
						<input type="hidden" id="all_bp_group_types" value='<?php echo $all_group_types; ?>'>
						<span class="ajax-loader">
							<i class="fa fa-spinner fa-spin"></i>
						</span>
					</p>
				</div>
			</div>
		</div><!-- /col-left -->

		<div id="col-right">
			<div class="col-wrap">
				<div class="tablenav top">
					<div class="tablenav-pages one-page">
						<span class="displaying-num">
							<?php echo count($group_types) > 1 ? count($group_types)." items" : "1 item"; ?>
						</span>
					</div>
					<br class="clear">
				</div>
				<table class="wp-list-table widefat fixed striped group-types">
					<thead>
						<tr>

							<th scope="col" id="name" class="manage-column column-name column-primary sortable desc">
								<a href="">
									<span><?php _e('Name', 'bp-grp-types'); ?></span>
								</a>
							</th>
							<th scope="col" id="description" class="manage-column column-description sortable desc">

									<span><?php _e('Description', 'bp-grp-types'); ?></span>

							</th>
							<th scope="col" id="slug" class="manage-column column-slug sortable desc">
								<a href="javascript:void(0);">
									<span><?php _e('Slug', 'bp-grp-types'); ?></span>
								</a>
							</th>
						</tr>
					</thead>

					<tbody id="the-list" class="bpgt-list">
						<?php if ($flag == 0) {?>
							<tr class="bpgt-not-found">
								<td colspan="3">
									<?php _e('Group Types Not Found!', 'bp-grp-types'); ?>
								</td>
							</tr>
						<?php } else {?>
							<?php foreach ($group_types as $group_type) {?>
								<tr class="bpgt-<?php echo $group_type['slug']; ?>">
									<td class="name column-name has-row-actions column-primary">
										<strong>
											<a class="row-title" href="javascript:void(0);" id="name-<?php echo $group_type['slug']; ?>">
												<?php echo $group_type['name']; ?>
											</a>
										</strong>
										<br>
										<div class="row-actions">
											<span class="edit">
												<a class="edit-bpgt" href="javascript:void(0);" id="<?php echo $group_type['slug']; ?>">
													<?php _e('Edit', 'bp-grp-types'); ?>
												</a> |
											</span>
											<span class="delete">
												<a class="dlt-bpgt" href="javascript:void(0);" id="<?php echo $group_type['slug']; ?>">
													<?php _e('Delete', 'bp-grp-types'); ?>
												</a>
											</span>
										</div>
									</td>
									<td class="column-description" id="desc-<?php echo $group_type['slug']; ?>"><?php echo $group_type['desc']; ?></td>
									<td class="column-slug" id="slug-<?php echo $group_type['slug']; ?>"><?php echo $group_type['slug']; ?></td>
									<!--<td class="column-posts">2</td>-->
								</tr>

								<!-- Row Editor -->

								<tr class="inline-edit-row bpgt-editor" id="edit-bpgt-<?php echo $group_type['slug']; ?>">
									<td colspan="3" class="colspanchange">
										<fieldset>
											<legend class="inline-edit-legend">
												<?php _e('Edit', 'bp-grp-types'); ?> <?php echo $group_type['name']; ?>
											</legend>
											<div class="inline-edit-col">
												<label>
													<span class="title"><?php _e('Name', 'bp-grp-types'); ?></span>
													<span class="input-text-wrap">
														<input id="<?php echo $group_type['slug']; ?>-name" class="ptitle" value="<?php echo $group_type['name']; ?>" type="text">
													</span>
												</label>
												<label>
													<span class="title"><?php _e('Slug', 'bp-grp-types'); ?></span>
													<span class="input-text-wrap">
														<input id="<?php echo $group_type['slug']; ?>-slug" class="ptitle" value="<?php echo $group_type['slug']; ?>" type="text">
													</span>
												</label>
												<label>
													<span class="title">
														<?php _e('Description', 'bp-grp-types'); ?>
													</span>
													<span class="input-text-wrap">
														<textarea id="<?php echo $group_type['slug']; ?>-desc"><?php echo $group_type['desc']; ?></textarea>
													</span>
												</label>
											</div>
										</fieldset>
										<p class="inline-edit-save submit">
											<button type="button" class="close button-secondary alignleft">
												<?php _e('Cancel', 'bp-grp-types'); ?>
											</button>
											<button class="bpgt-update button-primary alignright" id="<?php echo $group_type['slug']; ?>">
												<?php _e('Update Group Type', 'bp-grp-types'); ?>
											</button>
											<span class="ajax-loader alignright" id="ajax-loader-for-<?php echo $group_type['slug']; ?>">
												<i class="fa fa-spinner fa-spin"></i>
											</span>
											<br class="clear">
										</p>
									</td>
								</tr>
							<?php }?>
						<?php }?>
					</tbody>
				</table>
			</div>
		</div><!-- /col-right -->
	</div><!-- /col-container -->
</div>
