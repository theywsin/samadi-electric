<?php
/**
 * Puts the active (or previewed) Elementor header/footer on the site.
 *
 * Order: Elementor Pro Theme Builder (conditions set on install) → Samadi Electric theme
 * (its Elementor header/footer setting) → any classic theme (header.php/footer.php replaced).
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renderer.
 */
class SHF_Renderer {

	/**
	 * Previewed template IDs for this request.
	 *
	 * @var array<string, int>
	 */
	private static $preview = array();

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'wp', array( __CLASS__, 'setup_preview' ), 5 );
		add_action( 'wp', array( __CLASS__, 'setup' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 20 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
	}

	/**
	 * Template to render for a part on this request (preview wins).
	 *
	 * @param string $part header|footer.
	 * @return int
	 */
	public static function template( $part ) {
		if ( ! empty( self::$preview[ $part ] ) ) {
			return self::$preview[ $part ];
		}
		return SHF_Library::active( $part );
	}

	/**
	 * ?shf_preview=header-2 (administrators only): show a design without activating it.
	 */
	public static function setup_preview() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview for administrators.
		$key = isset( $_GET['shf_preview'] ) ? sanitize_key( wp_unslash( $_GET['shf_preview'] ) ) : '';
		if ( ! $key || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$id = SHF_Library::install( $key );
		if ( ! is_wp_error( $id ) ) {
			self::$preview[ SHF_Library::part( $key ) ] = (int) $id;
			add_action( 'wp_footer', array( __CLASS__, 'preview_bar' ), 100 );
			add_filter( 'show_admin_bar', '__return_false' );
		}
	}

	/**
	 * Wire the rendering for the current theme.
	 */
	public static function setup() {
		foreach ( array( 'header', 'footer' ) as $part ) {
			$id = self::template( $part );
			if ( ! $id ) {
				continue;
			}

			// Elementor Pro renders active templates itself through its display conditions.
			if ( empty( self::$preview[ $part ] ) && SHF_Library::has_pro() && $part === get_post_meta( $id, '_elementor_template_type', true ) ) {
				continue;
			}

			if ( function_exists( 'samadi_elementor_template_part' ) ) {
				// Samadi Electric theme: feed its "Elementor header/footer template" setting.
				add_filter(
					'theme_mod_samadi_elementor_' . $part . '_template',
					static function () use ( $id ) {
						return $id;
					}
				);
				continue;
			}

			if ( wp_is_block_theme() ) {
				continue; // Block themes have no header.php/footer.php to replace.
			}

			if ( 'header' === $part ) {
				add_action( 'get_header', array( __CLASS__, 'replace_header' ) );
			} else {
				add_action( 'get_footer', array( __CLASS__, 'replace_footer' ) );
			}
		}
	}

	/**
	 * Print a template's Elementor content.
	 *
	 * @param string $part header|footer.
	 */
	public static function render( $part ) {
		$id = self::template( $part );
		if ( ! $id ) {
			return;
		}
		$tag = 'header' === $part ? 'header' : 'footer';
		printf( '<%1$s class="shf-site-%2$s" data-shf="%2$s">', esc_attr( $tag ), esc_attr( $part ) );
		echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $id, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output.
		printf( '</%s>', esc_attr( $tag ) );
	}

	/**
	 * Classic themes: our header, then load the theme's header.php into a discarded buffer
	 * (require_once keeps WordPress from loading it a second time).
	 */
	public static function replace_header() {
		require SHF_DIR . 'templates/header.php';
		$templates = array( 'header.php' );
		remove_all_actions( 'wp_head' );
		ob_start();
		locate_template( $templates, true );
		ob_get_clean();
	}

	/**
	 * Classic themes: our footer instead of the theme's footer.php.
	 */
	public static function replace_footer() {
		require SHF_DIR . 'templates/footer.php';
		$templates = array( 'footer.php' );
		remove_all_actions( 'wp_footer' );
		ob_start();
		locate_template( $templates, true );
		ob_get_clean();
	}

	/**
	 * Load styles early (no flash of unstyled header) when a design is used.
	 */
	public static function assets() {
		$ids = array_filter( array( self::template( 'header' ), self::template( 'footer' ) ) );
		if ( ! $ids ) {
			return;
		}
		wp_enqueue_style( 'shf' );
		wp_enqueue_script( 'shf' );

		$frontend = \Elementor\Plugin::instance()->frontend;
		$frontend->enqueue_styles();
		foreach ( array( 'elementor-icons-fa-solid', 'elementor-icons-fa-brands' ) as $handle ) {
			if ( wp_style_is( $handle, 'registered' ) ) {
				wp_enqueue_style( $handle );
			}
		}
		foreach ( $ids as $id ) {
			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				\Elementor\Core\Files\CSS\Post::create( $id )->enqueue();
			}
		}
	}

	/**
	 * Body classes.
	 *
	 * @param string[] $classes Classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		if ( self::template( 'header' ) ) {
			$classes[] = 'shf-has-header';
			if ( get_option( 'shf_sticky', '1' ) ) {
				$classes[] = 'shf-sticky';
			}
		}
		if ( self::template( 'footer' ) ) {
			$classes[] = 'shf-has-footer';
		}
		return $classes;
	}

	/**
	 * Preview bar.
	 */
	public static function preview_bar() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
		$key       = isset( $_GET['shf_preview'] ) ? sanitize_key( wp_unslash( $_GET['shf_preview'] ) ) : '';
		$catalogue = SHF_Designs::catalogue();
		$part      = SHF_Library::part( $key );
		$name      = isset( $catalogue[ $part ][ $key ] ) ? $catalogue[ $part ][ $key ]['name'] : $key;
		$activate  = wp_nonce_url( admin_url( 'admin-post.php?action=shf&do=activate&design=' . $key ), 'shf_activate_' . $key );
		?>
		<div class="shf-preview-bar" dir="rtl">
			<span>پیش‌نمایش: <strong><?php echo esc_html( $name ); ?></strong></span>
			<span class="shf-preview-bar__nav">
				<?php foreach ( array_keys( $catalogue[ $part ] ) as $i => $other ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'shf_preview', $other ) ); ?>"<?php echo $other === $key ? ' aria-current="true"' : ''; ?>><?php echo esc_html( ( 'header' === $part ? 'هدر ' : 'فوتر ' ) . SHF_Plugin::fa( $i + 1 ) ); ?></a>
				<?php endforeach; ?>
			</span>
			<a class="shf-preview-bar__btn" href="<?php echo esc_url( $activate ); ?>">نصب و فعال‌سازی همین طرح</a>
			<a class="shf-preview-bar__close" href="<?php echo esc_url( admin_url( 'admin.php?page=' . SHF_Admin::PAGE ) ); ?>">بازگشت</a>
		</div>
		<?php
	}
}
