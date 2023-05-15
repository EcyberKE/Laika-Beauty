<?php
namespace JeroenSormani\WP_Updater;


function wpu_get_template( $name, $args = array() ) {
	extract( $args );

	ob_start();
		require plugin_dir_path( __FILE__ ) . 'Views/' . $name;
	$html = ob_get_clean();

	$html = apply_filters( 'WP_Updater/wpu_get_template', $html, $name, $args );
	$html = apply_filters( 'WP_Updater/wpu_get_template/' . $name, $html, $name, $args );

	return $html;
}
