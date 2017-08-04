<?php
/**
 * Template Name: Connect Template
 *
 * Description: Use this page template for a BuddyPress activity overview.
 *
 * @package WordPress
 * @subpackage Boss
 * @since Boss 1.0.0
 */
get_header(); ?>

<div id="buddypress" class="page-right-sidebar">

	<div id="primary" class="site-content">

		<div id="content" role="main">

			<?php if ( is_user_logged_in() ) : ?>

				<?php if ( bp_is_active( 'activity' ) ) : ?>

					<?php bp_get_template_part( 'connect/activity' ); ?>

				<?php endif ; ?>

			<?php else : ?>

				<div id="connect-header">

				    <?php if ( ! function_exists( 'dynamic_sidebar' ) || ! dynamic_sidebar( 'Homepage Notice' ) ) : ?>
				    <?php endif;?>

				</div>

				<div class="logged-out-notice">
					<?php

					$args = array(
					  'name'        => 'front-page',
					  'post_type'   => 'page',
					  'post_status' => 'publish',
					  'numberposts' => 1,
					);
					$the_query = new WP_Query( $args ); ?>

					<?php if ( $the_query->have_posts() ) : ?>

						<!-- pagination here -->

						<!-- the loop -->
						<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
							<?php the_content(); ?>
						<?php endwhile; ?>
						<!-- end of the loop -->

						<!-- pagination here -->

						<?php wp_reset_postdata(); ?>

					<?php endif ; ?>

				</div>

			<?php endif ;?>

		</div><!-- #content -->
	</div><!-- #primary -->

	<div id="secondary" class="widget-area" role="complementary">
		<?php bp_get_template_part( 'connect/sidebar' ); ?>
	</div>
</div>
<?php get_footer(); ?>
