<?php

/**
 * The bp_is_active( 'groups' ) check is recommended, to prevent problems
 * during upgrade or when the Groups component is disabled
 */
if ( bp_is_active( 'groups' ) && class_exists( 'BP_Group_Extension' ) ) :

	class Group_Documents extends BP_Group_Extension {
		/**
		 * Here you can see more customization of the config options
		 */
		function __construct() {
			$args = array(
			'slug' => 'documents',
			'name' => 'Documents',
			'access' => array( 'member', 'mod', 'admin' ),
			'show_tab' => array( 'member', 'mod', 'admin' ),
			'nav_item_position' => 105,
			'screens' => array(),
			);
			parent::init( $args );
		}

		function display( $group_id = null ) {
			$group_id = bp_get_group_id();

			do_action( 'bp_docs_gallery_content' );

			$action_var = bp_action_variables();

			if ( is_user_logged_in()  ) :
			?>
			<header class="activity-header">
				<?php if ( bp_docs_can_upload() ) : ?>
					<a class="line-button document-upload">Upload Document</a>
				<?php endif ; ?>
			</header><!-- .page-header -->


			<?php if ( bp_docs_can_upload() ) : ?>
				<?php bp_docs_get_template_part( 'upload-form' ); ?>
			<?php endif ; ?>

			<?php
			endif;

			bp_docs_get_template_part( 'single/home' );

		}

	}
	bp_register_group_extension( 'Group_Documents' );

endif;
