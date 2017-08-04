<div class="connect-widget notice">
    <div class="connect-widget-inner">
        <h4>About APSA Connect</h4>
        <p>APSA connect is a professional collaboration and networking site for the political science discipline.</p>
    </div>
</div>
<div class="connect-widget stats">
    <div class="connect-widget-inner">
        <ul class="item-stats">
			<li class="sections-stat"><span class="stat"><?php echo apsa_get_total_group_type_count( 'section' ); ?></span> Sections</li>
			<li class="members-stat"><span class="stat"><?php echo bp_get_total_site_member_count(); ?></span> Members</li>
			<li class="discussions-stat"><span class="stat"><?php echo apsa_get_total_topic_count(); ?></span> Discussions</li>
			<li class="documents-stat"><span class="stat"><?php echo apsa_get_total_doc_count(); ?></span> Documents</li>
		</ul>
	</div>
</div>
<div class="connect-widget members">
	<div class="connect-widget-inner">
		<h4>Recent Members</h4>

		<?php
		// Setup args for querying members.
		$members_args = array(
			'user_id'         => 0,
			'type'            => 'active',
			'per_page'        => 5,
			'max'             => 5,
			'populate_extras' => true,
			'search_terms'    => false,
		);

		// Query for members.
		if ( bp_has_members( $members_args ) ) : ?>
			<?php while ( bp_members() ) : bp_the_member(); ?>
				<div class="vcard">
					<div class="item-avatar">
						<a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar(); ?></a>
					</div>

					<div class="item">
						<div class="item-title fn"><a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a></div>
						<ul class="item-meta">
							<li class="activity"><?php echo bbp_get_user_topic_count_raw( bp_get_member_user_id() ); ?> Discussions</li>
					        <li class="activity"><?php echo apsa_get_total_user_doc_count( bp_get_member_user_id() ); ?> Documents</li>
						</ul>
					</div>
				</div>
			<?php endwhile; ?>

		<?php else : ?>
			<?php esc_html_e( 'There were no members found', 'boss' ) ?>
		<?php endif;
		?>

	</div>
</div>

<div class="connect-widget members">
	<div class="connect-widget-inner">
		<h4>Active Groups</h4>

		<?php
		// Setup args for querying groups.
		$group_args = array(
			'type'            => 'active',
			'per_page'        => 5,
			'max'             => 5,
			'search_terms'    => false,
		);

		// Query for groups.
		if ( bp_has_groups( $members_args ) ) : ?>
			<?php while ( bp_groups() ) : bp_the_group(); ?>
				<div class="vcard">
					<div class="item-avatar">
						<a href="<?php bp_group_permalink(); ?>"><?php bp_group_avatar( 'type=full&width=50&height=50' ); ?></a>
					</div>

					<div class="item">
						<div class="item-title"><a href="<?php bp_group_permalink(); ?>"><?php bp_group_name(); ?></a></div>
						<div class="item-meta">
							<div class="item-meta"><div class="mobile"><?php bp_group_type(); ?></div><span class="activity"><?php printf( __( 'active %s', 'boss' ), bp_get_group_last_active() ); ?></span></div>

							<?php
							global $groups_template;
							if ( isset( $groups_template->group->total_member_count ) ) {
								 $count = (int) $groups_template->group->total_member_count;
							} else {
								 $count = 0;
							}

							$html = sprintf( _n( '<span class="meta-wrap"><span class="count">%1s</span> <span>member</span></span>', '<span class="meta-wrap"><span class="count">%1s</span> <span>members</span></span>', $count, 'boss' ), $count );

							?>
							<div class="desktop"><?php esc_attr_e( sprintf( '%s %s', apsa_get_group_type( bp_get_group_id() ), apsa_get_group_details_field( 'apsa_group_identifier', bp_get_group_id() ) ), 'boss' ); ?></div><?php  echo $html; ?>
						</div>
					</div>
				</div>
			<?php endwhile; ?>

		<?php else : ?>
			<?php esc_html_e( 'There were no groups found.', 'boss' ) ?>
		<?php endif;
		?>

	</div>
</div>
