<?php

$footer 		= apply_filters( 'greenmart_tbay_get_footer_layout', 'default' );
$copyright 		= greenmart_tbay_get_config('copyright_text', '');

?>

	</div><!-- .site-content -->
	<?php 
		do_action( 'greenmart_before_do_footer' );
	?>
	<footer id="tbay-footer" class="tbay-footer" role="contentinfo">
		<?php if ( !empty($footer) ): ?>
			<?php greenmart_tbay_display_footer_builder(); ?>
		<?php else: ?>
			<?php if ( is_active_sidebar( 'footer' ) ) : ?>
				<div class="footer">
					<div class="container">
						<div class="row">
							<?php dynamic_sidebar( 'footer' ); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
			<?php if( !empty($copyright) ) : ?>
				<div class="tbay-copyright">
					<div class="container">
						<div class="copyright-content">
							<div class="text-copyright text-center">
							
								<?php echo trim($copyright); ?>

							</div> 
						</div>
					</div>
				</div>

			<?php else: ?>
				<div class="tbay-copyright">
					<div class="container">
						<div class="copyright-content">
							<div class="text-copyright text-center">
							<?php
								$allowed_html_array = array( 'a' => array('href' => array() ) );
								echo wp_kses(__('Copyright &copy; 2022 - greenmart. All Rights Reserved. <br/> Powered by <a href="//thembay.com/">ThemBay</a>', 'greenmart'), $allowed_html_array);
							
							?>

							</div> 
						</div>
					</div>
				</div>

			<?php endif; ?>	
			
		<?php endif; ?>			
	</footer><!-- .site-footer -->

	<?php 
		do_action( 'greenmart_after_do_footer' );
	?>

</div><!-- .site -->

<?php wp_footer(); ?>

</body>
</html>