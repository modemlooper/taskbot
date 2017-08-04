<?php
/**
 * Plugin Name:     Bp Rbe Links In Ges Digest
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     bp-rbe-links-in-ges-digest
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Bp_Rbe_Links_In_Ges_Digest
 */

function bp_rbeges_init() {
	if ( ! class_exists( 'BP_Reply_By_Email' ) ) {
		return;
	}

	add_filter( 'ass_digest_format_item', 'bp_rbeges_add_link', 10, 6 );
}
add_action( 'bp_include', 'bp_rbeges_init', 20 );

function bp_rbeges_add_link( $message, $item, $action, $timestamp, $type, $replies ) {
	$querystring = '';

	switch ( $item->type ) {
		case 'bbp_topic_create' :
			$querystring = sprintf( 'bbpg=%s&bbpt=%s',
				$item->item_id,
				$item->secondary_item_id
			);
		break;

		case 'bbp_reply_create' :
			$reply_id = $item->secondary_item_id;
			$topic_id = bbp_get_reply_topic_id( $reply_id );
			$querystring = sprintf( 'bbpg=%s&bbpt=%s&bbpr=%s',
				$item->item_id,
				$topic_id,
				$reply_id
			);

		break;

		default :
			return $message;
	}

	if ( ! $querystring ) {
		return $message;
	}

	$querystring = bp_rbe_encode( array( 'string' => $querystring ) );

	// Inject the querystring into the email address
	$email = bp_rbe_inject_qs_in_email( $querystring );

	$email = add_query_arg( array(
		'subject' => 'Re: ' . strip_tags( $item->action )
	), $email );

	$email .= '&body=%0A%0A' . str_replace( ' ', '%20', '--- Reply ABOVE THIS LINE to add a comment ---' ) . '%0A%0A' . str_replace( ' ', '%20', strip_tags( $item->action ) ) . '%0A%0A' . str_replace( ' ', '%20', bp_create_excerpt( $item->content ) );

	$reply_button = sprintf(
		'<a class="digest-item-reply-link" href="mailto:%s">Reply to Thread</a>',
		$email
	);

	$message = preg_replace( '|(<a class="digest-item-view-link"[^>]+>[^<]+</a>)|', '\1 - ' . $reply_button, $message );

	return $message;
}
