<?php

/**
 * Forums Loop
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<?php do_action( 'bbp_template_before_forums_loop' ); ?>

<div id="groups-dir-list" class="groups dir-list">
	<ul id="groups-list" class="item-list" aria-live="assertive" aria-atomic="true" aria-relevant="all">
		<?php while ( bbp_forums() ) : bbp_the_forum(); ?>

			<?php bbp_get_template_part( 'loop', 'single-forum' ); ?>

		<?php endwhile; ?>
	</ul><!-- #forums-list -->
</div>

<?php do_action( 'bbp_template_after_forums_loop' ); ?>
