<?php
/**
 * Administration Template Footer
 *
 * @package WMS
 * @subpackage Administration
 */

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Define the application name.
if ( ! defined( 'APP_NAME' ) ) {
	define( 'APP_NAME', 'WebsiteApp' );
}

/**
 * @global string $hook_suffix
 */
global $hook_suffix;
?>

<div class="clear"></div></div><!-- wpbody-content -->
<div class="clear"></div></div><!-- wpbody -->
<div class="clear"></div></div><!-- wpcontent -->

<div id="wpfooter" role="contentinfo">
	<?php
	/**
	 * Fires after the opening tag for the admin footer.
	 *
	 * @since 2.5.0
	 */
	do_action( 'in_admin_footer' );
	?>
	<p id="footer-left" class="alignleft">
		<?php
		$text = sprintf(
			'%1s %2s',
			__( 'This website is managed by' ),
			APP_NAME
		);
		/**
		 * Filters the "Thank you" text displayed in the admin footer.
		 *
		 * @since 2.8.0
		 *
		 * @param string $text The content that will be printed.
		 */
		echo apply_filters( 'admin_footer_text', '<span id="footer-thankyou">' . $text . '</span>' );
		?>
	</p>
	<p id="footer-right" class="alignright">
		<?php
		// Formerly filtered the version/update text displayed in the admin footer.
		// echo apply_filters( 'update_footer', '' );

		// New filter.
		echo apply_filters( 'admin_footer_text_supplement', '' );
		?>
	</p>
	<div class="clear"></div>
</div>
<?php
/**
 * Prints scripts or data before the default footer scripts.
 *
 * @since 1.2.0
 *
 * @param string $data The data to print.
 */
do_action( 'admin_footer', '' );

/**
 * Prints scripts and data queued for the footer.
 *
 * The dynamic portion of the hook name, `$hook_suffix`,
 * refers to the global hook suffix of the current page.
 *
 * @since 4.6.0
 */
do_action( "admin_print_footer_scripts-{$hook_suffix}" );

/**
 * Prints any scripts and data queued for the footer.
 *
 * @since 2.8.0
 */
do_action( 'admin_print_footer_scripts' );

/**
 * Prints scripts or data after the default footer scripts.
 *
 * The dynamic portion of the hook name, `$hook_suffix`,
 * refers to the global hook suffix of the current page.
 *
 * @since 2.8.0
 */
do_action( "admin_footer-{$hook_suffix}" );

// get_site_option() won't exist when auto upgrading from <= 2.7
if ( function_exists('get_site_option') ) {
	if ( false === get_site_option('can_compress_scripts') )
		compression_test();
}

?>

<div class="clear"></div></div><!-- wpwrap -->
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>
