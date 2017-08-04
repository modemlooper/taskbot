<?php

if ( function_exists( 'facetwp_display' ) && BP_DOCS_SLUG === bp_current_component() && bp_is_directory() ) { ?>

	<?php echo facetwp_display( 'facet', 'categories' ); ?>

	<?php echo facetwp_display( 'facet', 'license' ); ?>

	<?php echo facetwp_display( 'facet', 'date' ); ?>

	<?php echo facetwp_display( 'facet', 'item_type' ); ?>

<?php
}
