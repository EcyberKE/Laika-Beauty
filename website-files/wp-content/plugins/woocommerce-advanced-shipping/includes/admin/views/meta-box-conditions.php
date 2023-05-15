<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?><div class='wpc-conditions wpc-conditions-meta-box'>
	<div class='wpc-condition-groups'>

		<p>
			<strong><?php _e( 'Match one of the condition groups to allow this shipping rate:', 'woocommerce-advanced-shipping' ); ?></strong>
		</p><?php

		if ( ! empty( $condition_groups ) ) :

			foreach ( $condition_groups as $condition_group => $conditions ) :
				include 'html-condition-group.php';
			endforeach;

		else :

			$condition_group = '0';
			include 'html-condition-group.php';

		endif;

	?></div>

	<div class='wpc-condition-group-template hidden' style='display: none'><?php
		$condition_group = '9999';
		$conditions      = array();
		include 'html-condition-group.php';
	?></div>
	<a class='button wpc-condition-group-add' href='javascript:void(0);'><?php _e( 'Add \'Or\' group', 'woocommerce-advanced-shipping' ); ?></a>
</div>
