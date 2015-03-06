<?php
/**
 * Theme Stuff
 *
 * Some stuff themes can use, and theme compatability. 
 *
 * @since Astoundify Crowdfunding 0.1-alpha
 */

/**
 * Does the current theme support certain functionality?
 *
 * @since Astoundify Crowdfunding 1.3
 *
 * @param string $feature The name of the feature to check.
 * @return boolean If the feature is supported or not.
 */
function atcf_theme_supports( $feature ) {
	$supports = get_theme_support( 'appthemer-crowdfunding' );
	$supports = $supports[0];

	return isset ( $supports[ $feature ] );
}

/**
 * Extend WP_Query with some predefined defaults to query
 * only campaign items.
 *
 * @since Astoundify Crowdfunding 0.1-alpha
 */
class ATCF_Campaign_Query extends WP_Query {
	/**
	 * Extend WP_Query with some predefined defaults to query
	 * only campaign items.
	 *
	 * @since Astoundify Crowdfunding 0.1-alpha
	 *
	 * @param array $args
	 * @return void
	 */
	function __construct( $args = array() ) {
		$defaults = array(
			'post_type'      => array( 'download' ),
			'posts_per_page' => get_option( 'posts_per_page' )
		);

		$args = wp_parse_args( $args, $defaults );

		parent::__construct( $args );
	}
}

/**
 * If the option to disable custom pledging has been checked,
 * then remove a bunch of stuff we do to move the fields around,
 * add fields, etc.
 *
 * @since Fundify 1.0
 *
 * @return void
 */
function atcf_disable_custom_pledging() {
	global $edd_options;

	if ( isset ( $edd_options[ 'atcf_settings_custom_pledge' ] ) )
		return;

	remove_action( 'edd_purchase_link_top', 'atcf_campaign_contribute_custom_price', 5 );
	remove_action( 'init', 'atcf_theme_variable_pricing' );
	//add_action( 'edd_purchase_link_top', 'atcf_purchase_variable_pricing' );
	
	remove_filter( 'edd_add_to_cart_item', 'atcf_edd_add_to_cart_item' );
	remove_filter( 'edd_ajax_pre_cart_item_template', 'atcf_edd_add_to_cart_item' );
	remove_filter( 'edd_cart_item_price', 'atcf_edd_cart_item_price', 10, 3 );
}
add_action( 'init', 'atcf_disable_custom_pledging' );

/**
 * When a campaign is over, show a message.
 *
 * @since Astoundify Crowdfunding 1.3
 *
 * @return void
 */
function atcf_campaign_notes( $campaign ) {
	$end_date = date( get_option( 'date_format' ), strtotime( $campaign->end_date() ) );

	if ( 'fixed' == $campaign->type() ) {
?>
	<?php if ( ! $campaign->is_active() && ! $campaign->is_funded() ) : ?>
		<div class="edd_errors">
			<p class="edd_error"><?php printf( __( '<strong>Funding Unsuccessful</strong>. This project reached the deadline without achieving its funding goal on %s.', 'atcf' ), $end_date ); ?></p>
		</div>
	<?php elseif ( $campaign->is_funded() && ! $campaign->is_active() ) : ?>
		<div class="edd_errors">
			<p class="edd_error"><?php printf( __( '<strong>Funding Successful</strong>. This project reached its goal before %s.', 'atcf' ), $end_date ); ?></p>
		</div>
	<?php endif; ?>
<?php
	} elseif ( 'flexible' == $campaign->type() ) {
?>
	<?php if ( ! $campaign->is_active() ) : ?>
		<div class="edd_errors">
			<p class="edd_error"><?php printf( __( '<strong>Campaign Complete</strong>. This project has ended on %s. No more contributions can be made.', 'atcf' ), $end_date ); ?></p>
		</div>
	<?php endif; ?>
<?php
	} else {
		do_action( 'atcf_campaign_notes_before_' . $campaign->type(), $campaign );
	}
}
add_action( 'atcf_campaign_before', 'atcf_campaign_notes' );

function atcf_campaign_preview_note() {
	global $post;

	if ( ! is_preview() )
		return;
?>
	<div class="edd_errors">
		<p class="edd_error"><?php printf( __( 'This is a preview of your %1$s. <a href="%2$s">Edit</a>', 'atcf' ), strtolower( edd_get_label_singular() ), add_query_arg( array( 'edit' => true ), get_permalink( $post->ID ) ) ); ?></p>
	</div>
<?php
}
add_action( 'atcf_campaign_before', 'atcf_campaign_preview_note' );

add_action( 'atcf_campaign_before', 'edd_print_errors' );
add_action( 'atcf_shortcode_submit_hidden', 'edd_print_errors' );