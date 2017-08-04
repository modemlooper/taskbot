<div id="buddypress">
	<?php
	/** This action is documented in bp-templates/bp-legacy/buddypress/activity/index.php */
	do_action( 'template_notices' ); ?>

	<header class="activity-header page-header">
		<h1 class="entry-title main-title"><?php buddyboss_page_title(); ?></h1>
		<?php if ( bp_docs_can_edit() ) : ?>
			<a class="line-button document-upload">Edit Document</a>
		<?php endif ; ?>
	</header><!-- .page-header -->

	<?php if ( bp_docs_can_edit() ) : ?>
		<div id="progress">
			<div class="bar" style="width: 0%;"></div>
		</div>

		<div class="upload-form hidden">

				<form action="" method="post" id="document-form" class="edit" name="document-form">
					<label>Title *</label>
						<input type="text" class="document-title" name="doc-title" value="<?php bp_docs_edit_form_value( 'title' ); ?>"/>
					<label>Description</label>
						<textarea class="document-description" name="doc-description"><?php bp_docs_edit_form_value( 'description' ); ?></textarea>
					<label>Tags <span class="tool-tip" data-help="To add a tag press enter.">?</span></label>
						<ul id="doc-tags" class="input"><?php bp_docs_edit_form_value( 'tags' ); ?></ul>

						<div class="select left">
							<label>Subjects</label>
							<div class="scroll-wrapper">
								<div class="input-check scrollbar-inner">
									<div class="checkbox-wrap"><?php bp_docs_subject_select_options('edit'); ?></div>
								</div>
							</div>
						</div>

						<div class="select left">
							<label>Item Type</label>
							<div class="scroll-wrapper">
								<div class="input-check scrollbar-inner">
									<div class="checkbox-wrap"><?php bp_docs_type_select_options('edit'); ?></div>
								</div>
							</div>
						</div>


					<div class="select">
						<?php if ( bp_is_active( 'groups' ) && ! bp_is_my_profile() && ! bp_is_group() ) : ?>

							<?php if ( bp_has_groups( 'user_id=' . bp_loggedin_user_id() . '&type=alphabetical&max=100&per_page=100&populate_extras=0&update_meta_cache=0' ) ) : ?>
								<label><?php _e( 'Post in sections(s) *', 'schoolpresser-docs' ); ?></label>

								<div id="document-post-in" class="document-post-in input-check scrollbar-inner">
									<div class="checkbox-wrap">
										<?php while ( bp_groups() ) : bp_the_group(); ?>
											<div class="group-check"><input type="checkbox" name="document-post-in[]" value="<?php bp_group_id(); ?>" <?php echo bp_docs_is_group_checked( 'edit', bp_get_group_id() ); ?> /><span class="group-name"><?php bp_group_name(); ?></span></div>
										<?php endwhile; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<div class="select radio">
						<label>Permissions</label>
						<div class="inputwrap">
							<?php bp_docs_permission_select_options( 'edit' ); ?>
						</div>
					</div>

					<!-- <div class="select">
						<label>License</label>
							<select id="doc-license" name="doc-license">
								<option value="0" selected="selected">None</option>
								<?php bp_docs_license_select_options( 'edit' ); ?>
							</select>
					</div> -->

				    <div class="clearfix"></div>

					<input id="fileupload" type="file" class="document-file" name="files" data-url="<?php echo get_site_url(); ?>/wp-json/schoolpresser/v1/documents">
					<input type="hidden" id="edit-doc-id" name="edit-doc-id" value="<?php echo bp_docs_id(); ?>" />

					<div id="submit-wrap"><button id="edit-doc">Save</button></div>
				</form>
		</div>

	<?php endif ; ?>

	<div id="members-dir-list" class="members dir-list single-doc-loop">
		<?php bp_docs_get_template_part( 'document-loop' ); ?>
	</div>

</div>
