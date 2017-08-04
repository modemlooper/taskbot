<?php
/**
 * Upload form
 *
 * @package SchoolPresser Docs
 */
	?>

<div id="progress">
	<div class="bar" style="width: 0%;"></div>
</div>

<div class="upload-form hidden">

	<?php if ( bp_docs_does_member_have_group() ) : ?>

		<form action="" method="post" id="document-form" name="document-form">
			<label>Title: *</label>
				<input type="text" class="document-title" name="doc-title" required/>
			<label>Description: *</label>
				<textarea class="document-description" name="doc-description" required></textarea>
			<label>Tags: <span class="tool-tip" data-help="To add a tag press enter.">?</span></label>
				<ul id="doc-tags" class="input"></ul>

			<div class="select left">
				<label>Area of Interest: *</label>
				<div class="scroll-wrapper">
					<div class="input-check scrollbar-inner">
						<div class="checkbox-wrap"><?php bp_docs_subject_select_options(); ?></div>
					</div>
				</div>
			</div>

			<div class="select left">
				<label>Item Type: *</label>
				<div class="scroll-wrapper">
					<div class="input-check scrollbar-inner">
						<div class="checkbox-wrap"><?php bp_docs_type_select_options(); ?></div>
					</div>
				</div>
			</div>

			<div class="select">
				<?php if ( bp_is_active( 'groups' ) && ! bp_is_my_profile() && ! bp_is_group() ) : ?>

					<?php if ( bp_has_groups( 'user_id=' . bp_loggedin_user_id() . '&type=alphabetical&max=100&per_page=100&populate_extras=0&update_meta_cache=0' ) ) : ?>
						<label><?php _e( 'Post in group(s): *', 'schoolpresser-docs' ); ?></label>


						<div id="document-post-in" class="document-post-in input-check scrollbar-inner">
							<div class="checkbox-wrap">
								<?php while ( bp_groups() ) : bp_the_group(); ?>
									<div class="group-check"><input type="checkbox" name="document-post-in[]" value="<?php bp_group_id(); ?>" required/><span class="group-name"><?php bp_group_name(); ?></span></div>
								<?php endwhile; ?>
							</div>
						</div>

					<?php endif; ?>


				<?php elseif ( bp_is_group() ) : ?>

					<input type="hidden" id="document-post-object" name="document-post-object" value="groups" />
					<div class="group-check"><input type="hidden" id="document-post-in" name="document-post-in[]" value="<?php bp_group_id(); ?>" /></div>

				<?php endif; ?>
			</div>

			<div class="select radio">
				<label>Permissions:</label>
				<div class="inputwrap">
					<?php bp_docs_permission_select_options(); ?>
				</div>
			</div>

			<!-- <div class="select">
				<label>License</label>
					<select id="doc-license" name="doc-license">
						<option value="0" selected="selected">None</option>
						<?php bp_docs_license_select_options(); ?>
					</select>
			</div> -->

			<!-- <div class="textarea abstract">
				<label>Abstract</label>
				<textarea id="doc-abstract" name="doc-abstract"></textarea>
			</div> -->

		    <div class="clearfix"><br/></div>

			<input id="fileupload" type="file" class="document-file" name="files" data-url="<?php echo get_site_url(); ?>/wp-json/schoolpresser/v1/documents">

			<div id="submit-wrap"></div>
		</form>

	<?php else : ?>

		<p class="form-error"><?php echo sprintf( __( 'Documents can only be added to sections. Why not <a href="%s">join a section</a>?', 'schoolpresser-docs' ), get_site_url() . '/' . bp_get_groups_root_slug() ); ?></p>

	<?php endif;  ?>
</div>
