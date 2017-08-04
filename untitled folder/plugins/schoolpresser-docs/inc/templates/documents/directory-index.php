<div id="buddypress">
	<?php

	/** This action is documented in bp-templates/bp-legacy/buddypress/activity/index.php */
	do_action( 'template_notices' ); ?>

	<header class="activity-header page-header">
		<h1 class="entry-title main-title"><?php if ( function_exists( 'buddyboss_page_title' ) ) { buddyboss_page_title(); } ?></h1>
		<?php if ( bp_docs_can_upload() ) : ?>
			<a class="line-button document-upload">Upload Document</a>
		<?php endif ; ?>
	</header><!-- .page-header -->

	<?php if ( bp_docs_can_upload() ) : ?>
		<?php bp_docs_get_template_part( 'upload-form' ); ?>
	<?php endif ; ?>

	<div class="filters">
        <div class="row">
            <div class="col-12">

				<div class="filter-notice"><?php bp_doc_get_filtered_tax_name(); ?></div>

				<div id="document-dir-search" class="dir-search" role="search">
					<form action="" method="get" id="search-document-form">
						<label for="document_search">
							<input type="text" name="document_search" id="document_search" placeholder="Search Documents...">
						</label>

						<input type="submit" id="document_search_submit" name="document_search_submit" value="Search">
					<a href="#" id="clear-input"> </a></form>
				</div><!-- #document-dir-search -->

            </div>
        </div>
    </div>

	<div class="document-list-tabs" role="navigation">
		<div class="choosen-wrap"><span class="selected-tab"></span></div>
		<ul>
				<li id="documents-all" class="<?php bp_docs_is_selected( 'all' ); ?>"><a href="<?php bp_docs_directory_link(); ?>" role="button">All Documents</a></li>

				<?php if ( is_user_logged_in() ) : ?>

					<li id="documents-personal" class="<?php bp_docs_is_selected( 'personal' ); ?>"><a href="<?php bp_docs_directory_link(); ?>?document_filter=personal" role="button">My Documents</a></li>

					<li id="documents-groups" class="<?php bp_docs_is_selected( 'groups' ); ?>"><a href="<?php bp_docs_directory_link(); ?>?document_filter=groups" role="button">Section Documents</a></li>

				<?php endif ; ?>

		</ul>
	</div>

	<div id="dir-list-docs">

		<div id="docs-dir-list" class="dir-list">
			<?php if ( function_exists( 'facetwp_display' ) ) : ?>
				<?php echo facetwp_display( 'selections' ); ?>
				<?php echo facetwp_display( 'template', 'documents' ); ?>

				<div id="pag-bottom" class="pagination">
					<?php echo facetwp_display( 'pager' ); ?>
				</div>
			<?php endif ;?>
		</div>

		<div id="dir-list-docs-sidebar">
			<label class="docs-filter-label">
				Filter Documents
			</label>
			<div id="dir-facets-inner" class="">
				<?php bp_docs_get_template_part( 'document-facetwp' ); ?>
			</div>
		</div>


	</div>
</div>
