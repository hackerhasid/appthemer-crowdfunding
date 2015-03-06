<?php 

function atcf_purchase_variable_pricing( $download_id ) {
	$variable_pricing = edd_has_variable_prices( $download_id );

	if ( ! $variable_pricing )
		return;

	$prices = edd_get_variable_prices( $download_id );
	$type   = edd_single_price_option_mode( $download_id ) ? 'checkbox' : 'radio';

	do_action( 'edd_before_price_options', $download_id ); 
	do_action( 'atcf_campaign_contribute_options', $prices, $type, $download_id );
	do_action( 'edd_after_price_options', $download_id );
}
