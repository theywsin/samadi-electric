<?php
/**
 * One-click setup tools (WooCommerce → تنظیمات صمدی). Each tool is explicit, idempotent and
 * creates real store structure only — never demo products or fake content.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Setup tools.
 */
class Samadi_EW_Setup {

	const ACTION = 'samadi_ew_setup';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
	}

	/**
	 * Recommended global attributes for electrical products (slug => label).
	 *
	 * @return array<string, string>
	 */
	public static function recommended_attributes() {
		return array(
			'color'         => 'رنگ',
			'model'         => 'مدل',
			'install-type'  => 'نوع نصب',
			'body-material' => 'جنس بدنه',
			'rated-current' => 'جریان نامی',
			'voltage'       => 'ولتاژ',
			'power'         => 'توان',
			'series'        => 'سری',
			'ip-rating'     => 'درجه حفاظت (IP)',
		);
	}

	/**
	 * Suggested top-level categories.
	 *
	 * @return string[]
	 */
	public static function recommended_categories() {
		return array( 'کلید و پریز', 'روشنایی', 'سیم و کابل', 'تجهیزات صنعتی', 'فیوز و محافظ', 'لامپ و چراغ', 'ابزار و متعلقات' );
	}

	/**
	 * Tools UI.
	 */
	public static function render_tools() {
		$tools = array(
			'attributes' => array(
				__( 'ساخت ویژگی‌های پیشنهادی محصولات', 'samadi-elementor-widgets' ),
				/* translators: %s: list of attributes. */
				sprintf( __( 'ویژگی‌های سراسری برای فیلتر و مشخصات فنی: %s. موارد موجود دوباره ساخته نمی‌شوند.', 'samadi-elementor-widgets' ), implode( '، ', self::recommended_attributes() ) ),
			),
			'categories' => array(
				__( 'ساخت دسته‌بندی‌های اصلی پیشنهادی', 'samadi-elementor-widgets' ),
				implode( '، ', self::recommended_categories() ),
			),
			'quote_page' => array(
				__( 'ساخت صفحه «خرید عمده و استعلام قیمت»', 'samadi-elementor-widgets' ),
				__( 'یک برگه با فرم استعلام می‌سازد و آن را در تنظیمات افزونه و قالب به‌عنوان صفحه خرید عمده ثبت می‌کند.', 'samadi-elementor-widgets' ),
			),
		);

		if ( did_action( 'elementor/loaded' ) ) {
			$tools['home'] = array(
				__( 'ساخت صفحه اصلی با Elementor', 'samadi-elementor-widgets' ),
				__( 'برگه «صفحه اصلی» را با ویجت‌های صمدی (بنر، مزایا، دسته‌ها، پیشنهادهای ویژه، پرفروش‌ها، جدیدترین‌ها، برندها، مقالات) می‌سازد. همه بخش‌ها در Elementor قابل ویرایش‌اند.', 'samadi-elementor-widgets' ),
			);
		}
		?>
		<h2><?php esc_html_e( 'ابزارهای راه‌اندازی', 'samadi-elementor-widgets' ); ?></h2>
		<table class="widefat striped" style="max-width:960px">
			<tbody>
			<?php foreach ( $tools as $tool => $meta ) : ?>
				<tr>
					<td style="width:65%">
						<strong><?php echo esc_html( $meta[0] ); ?></strong>
						<p class="description"><?php echo esc_html( $meta[1] ); ?></p>
					</td>
					<td>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
							<input type="hidden" name="tool" value="<?php echo esc_attr( $tool ); ?>">
							<?php wp_nonce_field( self::ACTION . '_' . $tool ); ?>
							<?php if ( 'home' === $tool ) : ?>
								<label style="display:block;margin-bottom:6px"><input type="checkbox" name="set_front" value="1"> <?php esc_html_e( 'به‌عنوان صفحه اصلی سایت تنظیم شود', 'samadi-elementor-widgets' ); ?></label>
							<?php endif; ?>
							<?php submit_button( __( 'اجرا', 'samadi-elementor-widgets' ), 'secondary', 'submit', false ); ?>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p class="description"><?php esc_html_e( 'قالب‌های آماده هدر/فوتر Elementor Pro در پوشه templates/elementor افزونه قرار دارند و از «قالب‌ها ← وارد کردن» قابل استفاده‌اند.', 'samadi-elementor-widgets' ); ?></p>
		<?php
	}

	/**
	 * Result notices.
	 */
	public static function render_notices() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Display only.
		if ( empty( $_GET['samadi_setup'] ) ) {
			return;
		}
		$code    = sanitize_key( wp_unslash( $_GET['samadi_setup'] ) );
		$count   = isset( $_GET['count'] ) ? absint( $_GET['count'] ) : 0;
		$page_id = isset( $_GET['page_id'] ) ? absint( $_GET['page_id'] ) : 0;
		// phpcs:enable

		$messages = array(
			/* translators: %d: number created. */
			'attributes' => sprintf( __( '%d ویژگی ساخته شد. برای هر ویژگی از «محصولات ← ویژگی‌ها ← پیکربندی» مقادیر را اضافه کنید.', 'samadi-elementor-widgets' ), $count ),
			/* translators: %d: number created. */
			'categories' => sprintf( __( '%d دسته‌بندی ساخته شد. تصویر و ترتیب دسته‌ها را از «محصولات ← دسته‌بندی‌ها» تنظیم کنید.', 'samadi-elementor-widgets' ), $count ),
			'quote_page' => __( 'صفحه خرید عمده ساخته و تنظیم شد.', 'samadi-elementor-widgets' ),
			'home'       => __( 'صفحه اصلی Elementor ساخته شد.', 'samadi-elementor-widgets' ),
			'error'      => __( 'عملیات انجام نشد. دسترسی یا پیش‌نیازها را بررسی کنید.', 'samadi-elementor-widgets' ),
		);

		if ( ! isset( $messages[ $code ] ) ) {
			return;
		}

		$link = '';
		if ( $page_id && in_array( $code, array( 'quote_page', 'home' ), true ) ) {
			$link = ' <a href="' . esc_url( get_permalink( $page_id ) ) . '">' . esc_html__( 'مشاهده', 'samadi-elementor-widgets' ) . '</a>';
			if ( 'home' === $code && did_action( 'elementor/loaded' ) ) {
				$link .= ' | <a href="' . esc_url( admin_url( 'post.php?post=' . $page_id . '&action=elementor' ) ) . '">' . esc_html__( 'ویرایش با Elementor', 'samadi-elementor-widgets' ) . '</a>';
			}
		}

		printf( '<div class="notice notice-%s is-dismissible"><p>%s%s</p></div>', 'error' === $code ? 'error' : 'success', esc_html( $messages[ $code ] ), $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link escaped above.
	}

	/**
	 * Run a tool.
	 */
	public static function handle() {
		$tool = isset( $_POST['tool'] ) ? sanitize_key( wp_unslash( $_POST['tool'] ) ) : '';
		check_admin_referer( self::ACTION . '_' . $tool );

		$back = admin_url( 'admin.php?page=' . Samadi_EW_Settings::PAGE );
		$args = array( 'samadi_setup' => 'error' );

		switch ( $tool ) {
			case 'attributes':
				if ( current_user_can( 'manage_product_terms' ) ) {
					$args = array( 'samadi_setup' => 'attributes', 'count' => self::create_attributes() );
				}
				break;
			case 'categories':
				if ( current_user_can( 'manage_product_terms' ) ) {
					$args = array( 'samadi_setup' => 'categories', 'count' => self::create_categories() );
				}
				break;
			case 'quote_page':
				if ( current_user_can( 'publish_pages' ) && current_user_can( 'manage_woocommerce' ) ) {
					$page_id = self::create_quote_page();
					if ( $page_id ) {
						$args = array( 'samadi_setup' => 'quote_page', 'page_id' => $page_id );
					}
				}
				break;
			case 'home':
				if ( current_user_can( 'publish_pages' ) && did_action( 'elementor/loaded' ) ) {
					$page_id = self::create_home_page( ! empty( $_POST['set_front'] ) && current_user_can( 'manage_options' ) );
					if ( $page_id ) {
						$args = array( 'samadi_setup' => 'home', 'page_id' => $page_id );
					}
				}
				break;
		}

		wp_safe_redirect( add_query_arg( $args, $back ) );
		exit;
	}

	/**
	 * Create recommended attributes.
	 *
	 * @return int Number created.
	 */
	private static function create_attributes() {
		$existing = wp_list_pluck( wc_get_attribute_taxonomies(), 'attribute_name' );
		$created  = 0;

		foreach ( self::recommended_attributes() as $slug => $label ) {
			if ( in_array( $slug, $existing, true ) ) {
				continue;
			}
			$id = wc_create_attribute(
				array(
					'name'         => $label,
					'slug'         => $slug,
					'type'         => 'select',
					'order_by'     => 'menu_order',
					'has_archives' => false,
				)
			);
			if ( ! is_wp_error( $id ) ) {
				++$created;
			}
		}

		return $created;
	}

	/**
	 * Create suggested categories.
	 *
	 * @return int Number created.
	 */
	private static function create_categories() {
		$created = 0;

		foreach ( self::recommended_categories() as $name ) {
			if ( term_exists( $name, 'product_cat' ) ) {
				continue;
			}
			$result = wp_insert_term( $name, 'product_cat' );
			if ( ! is_wp_error( $result ) ) {
				++$created;
			}
		}

		return $created;
	}

	/**
	 * Create (or reuse) the wholesale/quote page.
	 *
	 * @return int Page ID or 0.
	 */
	private static function create_quote_page() {
		$page_id = absint( samadi_ew_setting( 'quote_page' ) );

		if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
			$page_id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => __( 'خرید عمده و استعلام قیمت', 'samadi-elementor-widgets' ),
					'post_name'    => 'wholesale',
					'post_content' => '<!-- wp:shortcode -->[samadi_quote_form]<!-- /wp:shortcode -->',
				)
			);
		}

		if ( ! $page_id || is_wp_error( $page_id ) ) {
			return 0;
		}

		$settings               = get_option( Samadi_EW_Settings::OPTION, array() );
		$settings               = is_array( $settings ) ? $settings : array();
		$settings['quote_page'] = $page_id;
		update_option( Samadi_EW_Settings::OPTION, Samadi_EW_Settings::sanitize( array_merge( samadi_ew_setting_defaults(), $settings ) ) );

		if ( function_exists( 'samadi_option' ) ) {
			set_theme_mod( 'samadi_wholesale_page', $page_id );
		}

		return (int) $page_id;
	}

	/**
	 * Create the Elementor homepage from the bundled template.
	 *
	 * @param bool $set_front Set as the static front page.
	 * @return int Page ID or 0.
	 */
	private static function create_home_page( $set_front ) {
		$file = SAMADI_EW_DIR . 'templates/elementor/home.json';

		if ( ! file_exists( $file ) ) {
			return 0;
		}

		$template = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local bundled file.

		if ( empty( $template['content'] ) || ! is_array( $template['content'] ) ) {
			return 0;
		}

		$replacements = array(
			'{{shop_url}}'      => wc_get_page_permalink( 'shop' ),
			'{{wholesale_url}}' => preg_replace( '/#.*$/', '', samadi_ew_quote_url() ),
			'{{blog_url}}'      => get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : '',
		);

		$content = self::replace_placeholders( $template['content'], $replacements );

		$page_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => __( 'صفحه اصلی', 'samadi-elementor-widgets' ),
				'post_name'   => 'home',
			)
		);

		if ( ! $page_id || is_wp_error( $page_id ) ) {
			return 0;
		}

		update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );

		$saved = false;
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get( $page_id, false );
			if ( $document ) {
				$saved = $document->save(
					array(
						'elements' => $content,
						'settings' => array( 'hide_title' => 'yes' ),
					)
				);
			}
		}

		// Mark as built with Elementor (also required after Document::save()).
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );

		if ( ! $saved ) {
			update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
			update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
			if ( defined( 'ELEMENTOR_VERSION' ) ) {
				update_post_meta( $page_id, '_elementor_version', ELEMENTOR_VERSION );
			}
		}

		if ( $set_front ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page_id );
		}

		return (int) $page_id;
	}

	/**
	 * Recursively replace URL placeholders in template data.
	 *
	 * @param mixed $data         Data.
	 * @param array $replacements Map.
	 * @return mixed
	 */
	private static function replace_placeholders( $data, $replacements ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data[ $key ] = self::replace_placeholders( $value, $replacements );
			}
			return $data;
		}

		return is_string( $data ) ? strtr( $data, $replacements ) : $data;
	}
}
