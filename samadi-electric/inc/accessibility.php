<?php
/**
 * Accessibility helpers.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Skip link to main content (rendered first inside <body>).
 */
function samadi_skip_link() {
	printf(
		'<a class="se-skip-link screen-reader-text" href="#se-main">%s</a>',
		esc_html__( 'رفتن به محتوای اصلی', 'samadi-electric' )
	);
}
add_action( 'wp_body_open', 'samadi_skip_link', 5 );

/**
 * Polite live region used by scripts to announce cart and filter updates.
 */
function samadi_live_region() {
	echo '<div id="se-live" class="screen-reader-text" aria-live="polite" aria-atomic="true"></div>';
}
add_action( 'wp_footer', 'samadi_live_region', 5 );

/**
 * Give the search form input a proper label and a unique ID.
 *
 * @param string $form Form HTML.
 * @return string
 */
function samadi_search_form( $form ) {
	static $instance = 0;
	++$instance;

	$id        = 'se-search-' . $instance;
	$post_type = samadi_is_wc() ? 'product' : '';

	ob_start();
	?>
	<form role="search" method="get" class="se-search-form search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'جستجو برای:', 'samadi-electric' ); ?></label>
		<input type="search" id="<?php echo esc_attr( $id ); ?>" class="se-search-form__input search-field" placeholder="<?php echo esc_attr( samadi_is_wc() ? __( 'جستجوی محصول، برند یا کد کالا…', 'samadi-electric' ) : __( 'جستجو…', 'samadi-electric' ) ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" autocomplete="off">
		<?php if ( $post_type ) : ?>
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>">
		<?php endif; ?>
		<button type="submit" class="se-search-form__submit">
			<?php samadi_icon( 'search', array( 'size' => 20 ) ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'جستجو', 'samadi-electric' ); ?></span>
		</button>
	</form>
	<?php
	return (string) ob_get_clean();
}
add_filter( 'get_search_form', 'samadi_search_form' );
