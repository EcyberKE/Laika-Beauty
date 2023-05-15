<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package WordPress
 * @subpackage greenmart
 * @since greenmart 2.1.6
 */
/*

*Template Name: 404 Page
*/
get_header();

greenmart_tbay_render_breadcrumbs();

?>
<section id="main-container" class=" container inner">
	<div class="clearfix">
		<div id="main-content" class="main-page page-404">

			<section class="error-404 not-found text-center clearfix">
			<div class="notfound-top">
				<h1><?php esc_html_e( 'Page Not Found', 'greenmart' ); ?></h1>
				<p class="sub"><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try a search?', 'greenmart' ); ?></p>
			</div>
				<div class="page-content notfound-bottom">
					<p class="sub-title"><?php esc_html_e( 'If you want go back to my store. Please in put the BOX BELOW', 'greenmart' ); ?></p>

					<?php get_search_form(); ?>
					<a class="backtohome" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e('back to home', 'greenmart'); ?><i class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>
				</div><!-- .page-content -->
			</section><!-- .error-404 -->

		</div><!-- .content-area -->
		
	</div>
</section>
<?php get_footer(); ?>