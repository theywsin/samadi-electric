<?php
/**
 * My Account page frame (sidebar + content).
 *
 * Replaces woocommerce/templates/myaccount/my-account.php; both WooCommerce hooks are kept.
 *
 * @package Samadi_Account
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sad-account">
	<?php
	/**
	 * My Account navigation.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_navigation' );
	?>
	<div class="woocommerce-MyAccount-content sad-content">
		<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_content' );
		?>
	</div>
</div>
