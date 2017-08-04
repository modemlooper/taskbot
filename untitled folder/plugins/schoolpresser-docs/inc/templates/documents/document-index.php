<?php
/**
 * BuddyPress - document Stream (Single Item)
 *
 * This template is used by document-loop.php and AJAX functions to show
 * each document.
 */

/**
 * Fires before the display of an document entry.
 */
do_action( 'bp_docs_before_entry' ); ?>

<li class="document-item">
	<div class="document-info">
		<div class="document-image file-icon file-icon-lg" data-type="<?php echo bp_docs_file_type( get_the_ID() ); ?>"></div>

		<div class="document-body-wrapper">
			<div class="document-body">
			<a href="<?php bp_docs_file_permalink( get_the_ID() ); ?>"><h4 class="document-title"><?php the_title(); ?></h4></a>
			<p><?php bp_docs_file_description( get_the_ID() ); ?></p>
			</div>
			<div class="document-footer">
				<p><?php esc_attr_e( 'Uploaded by ', 'schoolpresser_docs' ); ?>: <a href="<?php bp_docs_userlink(); ?>"><?php echo get_the_author(); ?></a></p>
			</div>
		</div>
	</div>
	<?php if ( bp_docs_can_download( get_the_ID() ) ) : ?>
	<div class="document-download">
		<a class="document-download-link line-button" role="button" href="<?php bp_docs_directory_link(); ?>/?download=<?php bp_docs_download_link( get_the_ID() ); ?>"><?php esc_attr_e( 'Download', 'schoolpresser_docs' ); ?></a>
	</div>
	<?php endif; ?>

	<?php if ( bp_docs_can_view_meta( get_the_ID() ) ) : ?>
		<div class="doc-item-meta">
			<ul>
				<li><span class="docs-meta-label">Date Uploaded:</span> <?php the_date( 'Y' ); ?></li>
				<?php bp_docs_get_groups( get_the_ID() ); ?>
				<?php bp_docs_get_subject( get_the_ID() ); ?>
				<?php bp_docs_get_item_type( get_the_ID() ); ?>
				<?php bp_docs_get_item_tags( get_the_ID() ); ?>
				<li><span class="docs-meta-label">Last modified:</span> <?php the_modified_time( 'F j, Y' ); ?></li>
				<li><span class="docs-meta-label">Permanent URL:</span> <a href="<?php esc_url( bp_docs_file_permalink( get_the_ID() ) ); ?>"><?php esc_url( bp_docs_file_permalink( get_the_ID() ) ); ?></a></li>
				<?php bp_docs_get_license( get_the_ID() ); ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( bp_docs_user_can_delete( bp_docs_author() ) ) : ?>
		<a href="<?php bp_docs_delete_link(); ?>" id="doc-delete-link" class="error"><?php _e( 'delete', 'schoolpresser_docs' ); ?></a>
	<?php endif; ?>
</li>
