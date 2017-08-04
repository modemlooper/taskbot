<?php

/**
 * Forums Loop - Single Forum
 *
 * @package bbPress
 * @subpackage Theme
 */

$forum_group_ids = bbp_get_forum_group_ids( bbp_get_forum_id() );
$forum_group_id = reset( $forum_group_ids );
$forum_group = groups_get_group( $forum_group_id );

?>

<li id="bbp-forum-<?php bbp_forum_id(); ?>" class="group-has-avatar">
	<div class="item-avatar">
		<a style="width: 100px;" href="<?php bp_group_permalink( $forum_group ); ?>"><?php echo bp_core_fetch_avatar( array(
			'type' => 'full',
			'width' => 100,
			'height' => 100,
			'item_id' => $forum_group_id,
			'object' => 'group',
		) ); ?></a>
	</div>

	<div class="item">
		<div class="item-title"><a class="bbp-forum-title" href="<?php bbp_forum_permalink(); ?>"><?php bbp_forum_title(); ?></a></div>
		<div class="item-meta"><span class="activity"></span></div><?php /* not sure if you want to put anything here */ ?>

		<div class="item-desc"><?php echo bp_create_excerpt( $forum_group->description ); ?></div>

		<div class="item-meta">
			<?php printf( '<span class="count">%s</span> <span>topics</span>', bbp_get_forum_topic_count() ); ?> / &nbsp;&nbsp; <span class="meta-wrap"><?php printf( '<span class="count">%s</span> <span>replies</span>', bbp_get_forum_reply_count() ); ?></span>
		</div>
	</div>

</li><!-- #bbp-forum-<?php bbp_forum_id(); ?> -->
