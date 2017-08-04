
<!-- <div class="item-list-tabs activity-type-tabs" role="navigation">
    <div class="choosen-wrap"><span class="selected-tab"></span></div> -->
<h3 class="my-activity-header">Latest Activity in My Groups</h3>
	<!-- <ul>
		<?php do_action( 'bp_before_activity_type_tab_all' ); ?>

		<li class="selected" id="activity-all"><a href="<?php bp_activity_directory_permalink(); ?>" title="<?php esc_attr_e( 'The public activity for everyone on this site.', 'boss' ); ?>"><?php printf( __( 'All APSA <span>%s</span>', 'boss' ), 'Members' ); ?></a></li>

		<?php if ( is_user_logged_in() ) : ?>

			<?php do_action( 'bp_before_activity_type_tab_groups' ); ?>

			<?php if ( bp_is_active( 'groups' ) ) : ?>

				<?php if ( bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>

					<li id="activity-groups"><a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . bp_get_groups_slug() . '/'; ?>" title="<?php esc_attr_e( 'The activity of groups I am a member of.', 'boss' ); ?>"><?php printf( __( 'Within My <span>%s</span>', 'boss' ), 'Sections' ); ?></a></li>

				<?php endif; ?>

			<?php endif; ?>


		<?php endif; ?>

		<?php do_action( 'bp_activity_type_tabs' ); ?>


	</ul> -->

	<!-- <div id="activity-filter-select">
			<label for="activity-filter-by"><?php _e( 'Show:', 'boss' ); ?></label>
            <select id="activity-filter-by">
                <option value="-1"><?php _e( 'Everything', 'boss' ); ?></option>
                <?php bp_activity_show_filters(); ?>
                <?php do_action( 'bp_activity_filter_options' ); ?>
            </select>
    </div>
</div> -->
<!-- .item-list-tabs -->

<div class="activity" role="main">

    <?php bp_get_template_part( 'activity/activity-loop' ); ?>

</div><!-- .activity -->
