<?php
/**
 * Full demo importer: store settings, media, taxonomies, products, blog, pages (Elementor),
 * menus, widgets and theme options. Every step is idempotent and every created object is
 * recorded so the demo can be removed again.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Demo importer.
 */
class Samadi_Core_Demo_Importer {

	const MAP_OPTION = 'samadi_core_demo_map';
	const DONE       = 'samadi_core_demo_imported';

	/**
	 * Demo definition.
	 *
	 * @var array|null
	 */
	private static $data = null;

	/**
	 * Ordered import steps (key => label).
	 *
	 * @return array<string, string>
	 */
	public static function steps() {
		return array(
			'settings'   => __( 'تنظیمات فروشگاه و سایت', 'samadi-core' ),
			'media'      => __( 'تصاویر', 'samadi-core' ),
			'taxonomies' => __( 'دسته‌بندی‌ها، برندها و ویژگی‌ها', 'samadi-core' ),
			'products'   => __( 'محصولات', 'samadi-core' ),
			'posts'      => __( 'مقالات وبلاگ', 'samadi-core' ),
			'pages'      => __( 'برگه‌ها و صفحه اصلی', 'samadi-core' ),
			'menus'      => __( 'منوها و ابزارک‌ها', 'samadi-core' ),
			'theme'      => __( 'تنظیمات قالب', 'samadi-core' ),
			'finalize'   => __( 'نهایی‌سازی', 'samadi-core' ),
		);
	}

	/**
	 * Requirements that must be active before importing.
	 *
	 * @return string[] Missing requirement labels.
	 */
	public static function missing_requirements() {
		$missing = array();
		if ( ! class_exists( 'WooCommerce' ) ) {
			$missing[] = 'WooCommerce';
		}
		if ( ! did_action( 'elementor/loaded' ) ) {
			$missing[] = 'Elementor';
		}
		if ( ! defined( 'SAMADI_EW_VERSION' ) ) {
			$missing[] = 'Samadi Elementor Widgets';
		}
		return $missing;
	}

	/**
	 * Run one step.
	 *
	 * @param string $step Step key.
	 * @return string Human readable result.
	 * @throws RuntimeException When the step fails.
	 */
	public static function run_step( $step ) {
		if ( ! array_key_exists( $step, self::steps() ) ) {
			throw new RuntimeException( 'Unknown step.' );
		}
		$missing = self::missing_requirements();
		if ( $missing ) {
			/* translators: %s: plugin names. */
			throw new RuntimeException( sprintf( __( 'ابتدا این افزونه‌ها را فعال کنید: %s', 'samadi-core' ), implode( '، ', $missing ) ) );
		}

		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
		wp_raise_memory_limit( 'admin' );
		wp_defer_term_counting( true );

		$method = 'step_' . $step;
		$result = self::$method();

		wp_defer_term_counting( false );

		return $result;
	}

	/**
	 * Run every step.
	 *
	 * @param callable|null $log Progress callback( label, result ).
	 */
	public static function run_all( $log = null ) {
		foreach ( self::steps() as $step => $label ) {
			$result = self::run_step( $step );
			if ( $log ) {
				call_user_func( $log, $label, $result );
			}
		}
	}

	/* ===================================================================== data & map */

	/**
	 * Demo definition.
	 *
	 * @return array
	 */
	private static function data() {
		if ( null === self::$data ) {
			self::$data = require SAMADI_CORE_DIR . 'demo/content.php';
		}
		return self::$data;
	}

	/**
	 * Created object map.
	 *
	 * @return array
	 */
	public static function map() {
		$map = get_option( self::MAP_OPTION, array() );
		$map = is_array( $map ) ? $map : array();

		return array_merge(
			array(
				'attachments' => array(),
				'terms'       => array(),
				'posts'       => array(),
				'attributes'  => array(),
				'menus'       => array(),
			),
			$map
		);
	}

	/**
	 * Remember a created object.
	 *
	 * @param string     $group Group.
	 * @param string     $key   Key.
	 * @param int|string $id    ID.
	 */
	private static function remember( $group, $key, $id ) {
		$map                   = self::map();
		$map[ $group ][ $key ] = $id;
		update_option( self::MAP_OPTION, $map, false );
	}

	/**
	 * Mapped ID, verifying it still exists.
	 *
	 * @param string $group Group.
	 * @param string $key   Key.
	 * @return int
	 */
	private static function mapped( $group, $key ) {
		$map = self::map();
		$id  = isset( $map[ $group ][ $key ] ) ? (int) $map[ $group ][ $key ] : 0;

		if ( ! $id ) {
			return 0;
		}
		if ( in_array( $group, array( 'attachments', 'posts' ), true ) && ! get_post( $id ) ) {
			return 0;
		}
		if ( 'terms' === $group && ! get_term( $id ) ) {
			return 0;
		}
		if ( 'menus' === $group && ! wp_get_nav_menu_object( $id ) ) {
			return 0;
		}
		return $id;
	}

	/**
	 * Attachment ID of a demo image.
	 *
	 * @param string $key Image key.
	 * @return int
	 */
	private static function image( $key ) {
		return self::mapped( 'attachments', $key );
	}

	/* ===================================================================== steps */

	/**
	 * Store and site settings.
	 *
	 * @return string
	 */
	private static function step_settings() {
		// WooCommerce creates its tables on activation; finish that now if it has not run yet.
		if ( class_exists( 'WC_Install' ) && ! get_option( 'woocommerce_db_version' ) ) {
			WC_Install::install();
		}

		$options = array(
			'blogname'                                => 'صمدی الکتریک',
			'blogdescription'                         => 'فروشگاه تخصصی تجهیزات برق ساختمان و صنعت',
			'timezone_string'                         => 'Asia/Tehran',
			'date_format'                             => 'j F Y',
			'start_of_week'                           => 6,
			'posts_per_page'                          => 9,
			'woocommerce_currency'                    => 'IRT',
			'woocommerce_currency_pos'                => 'right_space',
			'woocommerce_price_thousand_sep'          => '٬',
			'woocommerce_price_decimal_sep'           => '.',
			'woocommerce_price_num_decimals'          => 0,
			'woocommerce_default_country'             => 'IR:THR',
			'woocommerce_allowed_countries'           => 'specific',
			'woocommerce_specific_allowed_countries'  => array( 'IR' ),
			'woocommerce_ship_to_countries'           => '',
			'woocommerce_weight_unit'                 => 'kg',
			'woocommerce_dimension_unit'              => 'cm',
			'woocommerce_enable_myaccount_registration' => 'yes',
			'woocommerce_registration_generate_password' => 'no',
			'woocommerce_enable_guest_checkout'       => 'yes',
			'woocommerce_enable_reviews'              => 'yes',
			'woocommerce_manage_stock'                => 'yes',
			'woocommerce_cod_settings'                => array( 'enabled' => 'yes', 'title' => 'پرداخت در محل', 'description' => 'پرداخت نقدی یا کارت‌خوان هنگام تحویل سفارش.', 'instructions' => '', 'enable_for_methods' => array(), 'enable_for_virtual' => 'yes' ),
			'woocommerce_bacs_settings'               => array( 'enabled' => 'yes', 'title' => 'کارت به کارت / واریز بانکی', 'description' => 'پس از ثبت سفارش، مبلغ را به حساب فروشگاه واریز و رسید را ارسال کنید.', 'instructions' => '', 'account_details' => '' ),
			'woocommerce_coming_soon'                 => 'no',
			'woocommerce_task_list_hidden'            => 'yes',
			'woocommerce_task_list_complete'          => 'yes',
			'woocommerce_show_marketplace_suggestions' => 'no',
			'woocommerce_onboarding_profile'          => array( 'skipped' => true ),
			'elementor_onboarded'                     => true,
			'elementor_cpt_support'                   => array( 'page', 'post' ),
			'elementor_disable_color_schemes'         => 'yes',
			'elementor_disable_typography_schemes'    => 'yes',
			'elementor_load_fa4_shim'                 => '',
			'elementor_unfiltered_files_upload'       => '',
			'thumbnail_size_w'                        => 300,
			'thumbnail_size_h'                        => 300,
		);
		foreach ( $options as $name => $value ) {
			update_option( $name, $value );
		}

		update_option( 'permalink_structure', '/%postname%/' );
		update_option( 'woocommerce_permalinks', array( 'product_base' => '/product', 'category_base' => 'product-category', 'tag_base' => 'product-tag', 'attribute_base' => '' ) );

		// Free shipping zone for Iran with a flat rate fallback.
		self::shipping_zone();

		// WooCommerce core pages (shop, cart, checkout, my account, terms).
		if ( class_exists( 'WC_Install' ) ) {
			WC_Install::create_pages();
		}

		// Persian language pack (only when the host can reach wordpress.org; failures are ignored).
		self::install_language();

		return __( 'تنظیمات واحد پول (تومان)، کشور، پیوندهای یکتا و روش‌های پرداخت اعمال شد.', 'samadi-core' );
	}

	/**
	 * Shipping zone "ایران".
	 */
	private static function shipping_zone() {
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return;
		}
		foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
			if ( 'ایران' === $zone['zone_name'] ) {
				return;
			}
		}
		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( 'ایران' );
		$zone->add_location( 'IR', 'country' );
		$zone->save();

		$flat_id = $zone->add_shipping_method( 'flat_rate' );
		$free_id = $zone->add_shipping_method( 'free_shipping' );
		update_option( 'woocommerce_flat_rate_' . $flat_id . '_settings', array( 'title' => 'پست پیشتاز', 'tax_status' => 'none', 'cost' => '95000' ) );
		update_option( 'woocommerce_free_shipping_' . $free_id . '_settings', array( 'title' => 'ارسال رایگان (خرید بالای ۵ میلیون تومان)', 'requires' => 'min_amount', 'min_amount' => '5000000', 'ignore_discounts' => 'no' ) );
		self::remember( 'misc', 'shipping_zone', $zone->get_id() );
	}

	/**
	 * Try to install and activate the Persian translation.
	 */
	private static function install_language() {
		if ( 'fa_IR' === get_option( 'WPLANG' ) ) {
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		if ( ! function_exists( 'wp_download_language_pack' ) || ! wp_can_install_language_pack() ) {
			return;
		}
		$installed = wp_download_language_pack( 'fa_IR' );
		if ( $installed ) {
			update_option( 'WPLANG', $installed );
		}
	}

	/**
	 * Import demo images into the media library.
	 *
	 * @return string
	 */
	private static function step_media() {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$created = 0;
		foreach ( self::data()['images'] as $key ) {
			if ( self::image( $key ) ) {
				continue;
			}
			$file = self::image_path( $key );
			if ( ! $file ) {
				continue;
			}
			$upload = wp_upload_bits( basename( $file ), null, (string) file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Bundled file.
			if ( ! empty( $upload['error'] ) ) {
				continue;
			}
			$type = wp_check_filetype( $upload['file'] );
			$id   = wp_insert_attachment(
				array(
					'post_mime_type' => $type['type'],
					'post_title'     => self::image_title( $key ),
					'post_status'    => 'inherit',
				),
				$upload['file']
			);
			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}
			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
			update_post_meta( $id, '_wp_attachment_image_alt', self::image_title( $key ) );
			update_post_meta( $id, '_samadi_demo', 1 );
			self::remember( 'attachments', $key, $id );
			++$created;
		}

		/* translators: %d: number of images. */
		return sprintf( __( '%d تصویر به کتابخانه رسانه افزوده شد.', 'samadi-core' ), $created );
	}

	/**
	 * Bundled image path.
	 *
	 * @param string $key Key.
	 * @return string
	 */
	private static function image_path( $key ) {
		foreach ( array( 'jpg', 'png' ) as $ext ) {
			$file = SAMADI_CORE_DIR . 'demo/images/' . $key . '.' . $ext;
			if ( file_exists( $file ) ) {
				return $file;
			}
		}
		return '';
	}

	/**
	 * Attachment title for an image key.
	 *
	 * @param string $key Key.
	 * @return string
	 */
	private static function image_title( $key ) {
		$data = self::data();
		foreach ( $data['products'] as $product ) {
			if ( $product['image'] === $key ) {
				return $product['name'];
			}
		}
		foreach ( $data['categories'] as $cat ) {
			if ( $cat[1] === $key ) {
				return $cat[0];
			}
		}
		foreach ( $data['brands'] as $brand ) {
			if ( $brand[1] === $key ) {
				return $brand[0];
			}
		}
		return 'صمدی الکتریک';
	}

	/**
	 * Categories, brands and global attributes.
	 *
	 * @return string
	 */
	private static function step_taxonomies() {
		$data = self::data();

		$order = 0;
		foreach ( $data['categories'] as $slug => $cat ) {
			$parent = self::term( 'product_cat', $slug, $cat[0], $cat[2], 0, $cat[1] );
			update_term_meta( $parent, 'order', $order++ );
			$child_order = 0;
			foreach ( $cat[3] as $child_slug => $child_name ) {
				$child = self::term( 'product_cat', $child_slug, $child_name, '', $parent, $cat[1] );
				update_term_meta( $child, 'order', $child_order++ );
			}
		}

		$brand_tax = self::brand_taxonomy();
		if ( $brand_tax ) {
			foreach ( $data['brands'] as $slug => $brand ) {
				self::term( $brand_tax, $slug, $brand[0], $brand[2], 0, $brand[1] );
			}
		}

		foreach ( $data['attributes'] as $slug => $attr ) {
			$taxonomy = self::attribute( $slug, $attr[0] );
			$i        = 0;
			foreach ( $attr[1] as $term_slug => $term ) {
				$term_id = self::term( $taxonomy, $term_slug, $term[0], '', 0, '' );
				update_term_meta( $term_id, 'order', $i++ );
				if ( $term[1] ) {
					update_term_meta( $term_id, 'se_color', $term[1] );
				}
			}
		}

		foreach ( $data['post_cats'] as $slug => $name ) {
			self::term( 'category', $slug, $name, '', 0, '' );
		}

		return __( 'دسته‌بندی‌ها (با تصویر)، برندها، ویژگی‌های فنی و رنگ‌ها ساخته شد.', 'samadi-core' );
	}

	/**
	 * Brand taxonomy (WooCommerce core Brands or a compatible plugin).
	 *
	 * @return string
	 */
	private static function brand_taxonomy() {
		foreach ( array( 'product_brand', 'pwb-brand', 'yith_product_brand' ) as $taxonomy ) {
			if ( taxonomy_exists( $taxonomy ) ) {
				return $taxonomy;
			}
		}
		return '';
	}

	/**
	 * Create (or reuse) a term.
	 *
	 * @param string $taxonomy    Taxonomy.
	 * @param string $slug        Slug.
	 * @param string $name        Name.
	 * @param string $description Description.
	 * @param int    $parent      Parent term ID.
	 * @param string $image       Image key for thumbnail_id.
	 * @return int Term ID.
	 */
	private static function term( $taxonomy, $slug, $name, $description, $parent, $image ) {
		$key  = $taxonomy . ':' . $slug;
		$term = self::mapped( 'terms', $key );

		if ( ! $term ) {
			$existing = get_term_by( 'slug', $slug, $taxonomy );
			if ( $existing ) {
				$term = (int) $existing->term_id;
			} else {
				$result = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug, 'description' => $description, 'parent' => $parent ) );
				if ( is_wp_error( $result ) ) {
					return 0;
				}
				$term = (int) $result['term_id'];
				self::remember( 'terms', $key, $term );
			}
		}

		if ( $image && self::image( $image ) ) {
			update_term_meta( $term, 'thumbnail_id', self::image( $image ) );
		}

		return $term;
	}

	/**
	 * Create (or reuse) a global attribute and make its taxonomy available in this request.
	 *
	 * @param string $slug  Slug without pa_.
	 * @param string $label Label.
	 * @return string Taxonomy name.
	 */
	private static function attribute( $slug, $label ) {
		$taxonomy = wc_attribute_taxonomy_name( $slug );

		if ( ! wc_attribute_taxonomy_id_by_name( $slug ) ) {
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
				self::remember( 'attributes', $slug, $id );
			}
		}

		if ( ! taxonomy_exists( $taxonomy ) ) {
			register_taxonomy( $taxonomy, array( 'product' ), array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false ) );
		}

		return $taxonomy;
	}

	/**
	 * Products.
	 *
	 * @return string
	 */
	private static function step_products() {
		$data      = self::data();
		$brand_tax = self::brand_taxonomy();
		$created   = 0;

		// Make sure attribute taxonomies exist in this request.
		foreach ( $data['attributes'] as $slug => $attr ) {
			self::attribute( $slug, $attr[0] );
		}

		foreach ( $data['products'] as $index => $def ) {
			if ( self::mapped( 'posts', 'product:' . $def['sku'] ) ) {
				continue;
			}
			$existing = wc_get_product_id_by_sku( $def['sku'] );
			if ( $existing ) {
				self::remember( 'posts', 'product:' . $def['sku'], $existing );
				continue;
			}

			$variable = ! empty( $def['variations'] );
			$product  = $variable ? new WC_Product_Variable() : new WC_Product_Simple();

			$product->set_name( $def['name'] );
			$product->set_status( 'publish' );
			$product->set_sku( $def['sku'] );
			$product->set_short_description( $def['short'] );
			$product->set_description( $def['desc'] );
			$product->set_featured( ! empty( $def['featured'] ) );
			$product->set_menu_order( $index );
			$product->set_reviews_allowed( true );

			$cat = self::mapped( 'terms', 'product_cat:' . $def['cat'] );
			if ( $cat ) {
				$product->set_category_ids( array_filter( array( $cat, (int) wp_get_term_taxonomy_parent_id( $cat, 'product_cat' ) ) ) );
			}

			if ( self::image( $def['image'] ) ) {
				$product->set_image_id( self::image( $def['image'] ) );
			}
			if ( ! empty( $def['gallery'] ) ) {
				$product->set_gallery_image_ids( array_filter( array_map( array( __CLASS__, 'image' ), $def['gallery'] ) ) );
			}

			if ( ! $variable ) {
				if ( '' !== $def['price'] ) {
					$product->set_regular_price( (string) $def['price'] );
					if ( ! empty( $def['sale'] ) ) {
						$product->set_sale_price( (string) $def['sale'] );
					}
				}
				$product->set_manage_stock( true );
				$product->set_stock_quantity( isset( $def['stock'] ) && 'outofstock' === $def['stock'] ? 0 : 50 + ( $index * 7 ) % 120 );
				$product->set_stock_status( isset( $def['stock'] ) ? $def['stock'] : 'instock' );
			}

			$product->set_weight( '0.3' );
			$product->set_attributes( self::product_attributes( $def ) );
			$product_id = $product->save();

			if ( ! $product_id ) {
				continue;
			}

			if ( $brand_tax && ! empty( $def['brand'] ) ) {
				$brand = self::mapped( 'terms', $brand_tax . ':' . $def['brand'] );
				if ( $brand ) {
					wp_set_object_terms( $product_id, array( $brand ), $brand_tax );
				}
			}

			if ( $variable ) {
				self::variations( $product_id, $def );
			}

			update_post_meta( $product_id, 'total_sales', isset( $def['sales'] ) ? (int) $def['sales'] : 0 );
			update_post_meta( $product_id, '_samadi_demo', 1 );
			// Stagger publish dates so "newest" sorting looks natural.
			wp_update_post(
				array(
					'ID'            => $product_id,
					'post_date'     => gmdate( 'Y-m-d H:i:s', time() - $index * DAY_IN_SECONDS ),
					'post_date_gmt' => gmdate( 'Y-m-d H:i:s', time() - $index * DAY_IN_SECONDS ),
				)
			);

			self::remember( 'posts', 'product:' . $def['sku'], $product_id );
			++$created;
		}

		self::reviews();

		if ( function_exists( 'wc_update_product_lookup_tables' ) ) {
			wc_update_product_lookup_tables();
		}
		wc_delete_product_transients();

		/* translators: %d: number of products. */
		return sprintf( __( '%d محصول (ساده، متغیر، تخفیف‌دار، ناموجود و استعلامی) ساخته شد.', 'samadi-core' ), $created );
	}

	/**
	 * Product attribute objects.
	 *
	 * @param array $def Product definition.
	 * @return WC_Product_Attribute[]
	 */
	private static function product_attributes( $def ) {
		$attributes = array();
		$position   = 0;
		$all        = $def['attrs'];

		if ( ! empty( $def['variations'] ) ) {
			foreach ( $def['variations'] as $slug => $values ) {
				$all[ $slug ] = array_keys( $values );
			}
		}

		foreach ( $all as $slug => $term_slugs ) {
			$taxonomy = wc_attribute_taxonomy_name( $slug );
			$ids      = array();
			foreach ( $term_slugs as $term_slug ) {
				$term = get_term_by( 'slug', $term_slug, $taxonomy );
				if ( $term ) {
					$ids[] = (int) $term->term_id;
				}
			}
			if ( ! $ids ) {
				continue;
			}
			$attribute = new WC_Product_Attribute();
			$attribute->set_id( wc_attribute_taxonomy_id_by_name( $slug ) );
			$attribute->set_name( $taxonomy );
			$attribute->set_options( $ids );
			$attribute->set_position( $position++ );
			$attribute->set_visible( true );
			$attribute->set_variation( ! empty( $def['variations'][ $slug ] ) );
			$attributes[] = $attribute;
		}

		return $attributes;
	}

	/**
	 * Create variations for a variable product.
	 *
	 * @param int   $product_id Parent ID.
	 * @param array $def        Definition.
	 */
	private static function variations( $product_id, $def ) {
		foreach ( $def['variations'] as $slug => $values ) {
			$taxonomy = wc_attribute_taxonomy_name( $slug );
			$i        = 0;
			foreach ( $values as $term_slug => $row ) {
				$variation = new WC_Product_Variation();
				$variation->set_parent_id( $product_id );
				$variation->set_attributes( array( $taxonomy => $term_slug ) );
				$variation->set_regular_price( (string) $row[0] );
				if ( $row[1] ) {
					$variation->set_sale_price( (string) $row[1] );
				}
				$variation->set_sku( $def['sku'] . '-' . strtoupper( $term_slug ) );
				$variation->set_manage_stock( true );
				$variation->set_stock_quantity( 30 + $i * 11 );
				$variation->set_stock_status( 'instock' );
				$variation->set_menu_order( $i );
				if ( self::image( $row[2] ) ) {
					$variation->set_image_id( self::image( $row[2] ) );
				}
				$variation->save();
				++$i;
			}
			// Default selection: first value, so the product page shows a single price like the design.
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$product->set_default_attributes( array( $taxonomy => array_key_first( $values ) ) );
				$product->save();
			}
			WC_Product_Variable::sync( $product_id );
		}
	}

	/**
	 * A few approved reviews so ratings and the reviews tab have content.
	 */
	private static function reviews() {
		$map = self::map();
		if ( ! empty( $map['misc']['reviews'] ) ) {
			return;
		}
		$reviews = array(
			array( 'ARK-AVA-101', 'مریم احمدی', 5, 'کیفیت ساخت عالی و مکانیزم خیلی نرم. رنگ مشکی‌اش واقعاً شیک است.' ),
			array( 'ARK-AVA-101', 'رضا کریمی', 4, 'برای کل واحد از همین سری گرفتم، بسته‌بندی سالم و ارسال سریع بود.' ),
			array( 'NOR-LED-12', 'سارا محمدی', 5, 'نورش خیلی خوب است و سوسو نمی‌زند.' ),
			array( 'VLT-RCD-30', 'مهندس نوری', 5, 'برای پروژه‌ها از این محافظ جان استفاده می‌کنیم؛ دکمه تست و عملکرد دقیق.' ),
			array( 'VLT-MCB-1P16', 'علی رضایی', 4, 'قیمت مناسب و کیفیت خوب.' ),
		);
		$ids = array();
		foreach ( $reviews as $i => $review ) {
			$product_id = wc_get_product_id_by_sku( $review[0] );
			if ( ! $product_id ) {
				continue;
			}
			$comment_id = wp_insert_comment(
				array(
					'comment_post_ID'      => $product_id,
					'comment_author'       => $review[1],
					'comment_author_email' => 'customer' . ( $i + 1 ) . '@example.com',
					'comment_content'      => $review[3],
					'comment_type'         => 'review',
					'comment_approved'     => 1,
					'comment_date'         => gmdate( 'Y-m-d H:i:s', time() - ( $i + 2 ) * DAY_IN_SECONDS ),
				)
			);
			if ( $comment_id ) {
				update_comment_meta( $comment_id, 'rating', $review[2] );
				update_comment_meta( $comment_id, 'verified', 1 );
				$ids[] = $comment_id;
				if ( class_exists( 'WC_Comments' ) ) {
					WC_Comments::clear_transients( $product_id );
				}
			}
		}
		$map                    = self::map();
		$map['misc']['reviews'] = $ids;
		update_option( self::MAP_OPTION, $map, false );
	}

	/**
	 * Blog posts.
	 *
	 * @return string
	 */
	private static function step_posts() {
		$created = 0;
		foreach ( self::data()['posts'] as $i => $post ) {
			$key = 'post:' . sanitize_title( $post[2] );
			if ( self::mapped( 'posts', $key ) ) {
				continue;
			}
			$content = '<!-- wp:paragraph --><p>' . $post[3] . '</p><!-- /wp:paragraph -->';
			foreach ( $post[4] as $heading => $text ) {
				$content .= '<!-- wp:heading --><h2 class="wp-block-heading">' . $heading . '</h2><!-- /wp:heading -->';
				$content .= '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
			}
			$content .= '<!-- wp:paragraph --><p>برای مشاوره در انتخاب کالا با کارشناسان صمدی الکتریک تماس بگیرید.</p><!-- /wp:paragraph -->';

			$cat = self::mapped( 'terms', 'category:' . $post[1] );
			$id  = wp_insert_post(
				array(
					'post_type'     => 'post',
					'post_status'   => 'publish',
					'post_title'    => $post[0],
					'post_excerpt'  => $post[3],
					'post_content'  => $content,
					'post_category' => $cat ? array( $cat ) : array(),
					'post_date'     => gmdate( 'Y-m-d H:i:s', time() - ( $i * 4 + 1 ) * DAY_IN_SECONDS ),
				)
			);
			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}
			if ( self::image( $post[2] ) ) {
				set_post_thumbnail( $id, self::image( $post[2] ) );
			}
			update_post_meta( $id, '_samadi_demo', 1 );
			self::remember( 'posts', $key, $id );
			++$created;
		}

		// Remove WordPress' sample content.
		$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
		if ( $hello ) {
			wp_delete_post( $hello->ID, true );
		}
		$sample = get_page_by_path( 'sample-page' );
		if ( $sample ) {
			wp_delete_post( $sample->ID, true );
		}

		/* translators: %d: number of posts. */
		return sprintf( __( '%d مقاله با تصویر شاخص منتشر شد.', 'samadi-core' ), $created );
	}

	/**
	 * Pages, including the Elementor home, about and contact pages.
	 *
	 * @return string
	 */
	private static function step_pages() {
		$pages = Samadi_Core_Demo_Pages::definitions();
		foreach ( $pages as $key => $page ) {
			self::page( $key, $page );
		}

		// Static front page and posts page.
		$home = self::mapped( 'posts', 'page:home' );
		$blog = self::mapped( 'posts', 'page:blog' );
		if ( $home ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home );
		}
		if ( $blog ) {
			update_option( 'page_for_posts', $blog );
		}

		// Publish the privacy page WordPress creates as a draft, with real content.
		$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( ! $privacy || ! get_post( $privacy ) ) {
			$privacy = self::mapped( 'posts', 'page:privacy' );
		}
		if ( $privacy ) {
			wp_update_post(
				array(
					'ID'           => $privacy,
					'post_status'  => 'publish',
					'post_title'   => 'حریم خصوصی',
					'post_content' => Samadi_Core_Demo_Pages::privacy_content(),
				)
			);
			update_option( 'wp_page_for_privacy_policy', $privacy );
		}

		// Terms page for the checkout.
		$terms = self::mapped( 'posts', 'page:terms' );
		if ( $terms ) {
			update_option( 'woocommerce_terms_page_id', $terms );
		}

		// Wholesale/quote page for the companion plugin and the theme.
		$wholesale = self::mapped( 'posts', 'page:wholesale' );
		if ( $wholesale ) {
			$settings               = get_option( 'samadi_ew_settings', array() );
			$settings               = is_array( $settings ) ? $settings : array();
			$settings['quote_page'] = $wholesale;
			update_option( 'samadi_ew_settings', $settings );
			set_theme_mod( 'samadi_wholesale_page', $wholesale );
		}

		// Title the WooCommerce pages in Persian.
		foreach ( array( 'shop' => 'فروشگاه', 'cart' => 'سبد خرید', 'checkout' => 'تسویه حساب', 'myaccount' => 'حساب کاربری' ) as $wc_page => $title ) {
			$id = wc_get_page_id( $wc_page );
			if ( $id > 0 ) {
				wp_update_post( array( 'ID' => $id, 'post_title' => $title ) );
			}
		}

		return __( 'صفحه اصلی Elementor، فروشگاه، سبد، تسویه، حساب کاربری، خرید عمده، درباره ما، تماس، سؤالات متداول، قوانین، ارسال و مرجوعی، حریم خصوصی و وبلاگ ساخته شد.', 'samadi-core' );
	}

	/**
	 * Create (or reuse) a page.
	 *
	 * @param string $key  Page key.
	 * @param array  $page { title, slug, content?, elementor? (elements), template? }.
	 * @return int
	 */
	private static function page( $key, $page ) {
		$id = self::mapped( 'posts', 'page:' . $key );
		if ( $id ) {
			return $id;
		}

		$existing = get_page_by_path( $page['slug'] );
		if ( $existing && 'trash' !== $existing->post_status ) {
			self::remember( 'posts', 'page:' . $key, $existing->ID );
			return (int) $existing->ID;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page['title'],
				'post_name'    => $page['slug'],
				'post_content' => isset( $page['content'] ) ? $page['content'] : '',
				'menu_order'   => isset( $page['order'] ) ? $page['order'] : 0,
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			return 0;
		}

		if ( ! empty( $page['elementor'] ) ) {
			self::elementor_save( $id, $page['elementor'], isset( $page['template'] ) ? $page['template'] : 'elementor_header_footer' );
		} elseif ( ! empty( $page['template'] ) ) {
			update_post_meta( $id, '_wp_page_template', $page['template'] );
		}

		update_post_meta( $id, '_samadi_demo', 1 );
		self::remember( 'posts', 'page:' . $key, $id );

		return (int) $id;
	}

	/**
	 * Save Elementor data for a post.
	 *
	 * @param int    $post_id  Post ID.
	 * @param array  $elements Elements.
	 * @param string $template Page template.
	 */
	public static function elementor_save( $post_id, $elements, $template ) {
		update_post_meta( $post_id, '_wp_page_template', $template );

		$saved = false;
		if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->documents ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id, false );
			if ( $document ) {
				$saved = $document->save(
					array(
						'elements' => $elements,
						'settings' => array( 'hide_title' => 'yes', 'template' => $template ),
					)
				);
			}
		}

		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
		}
		if ( ! $saved ) {
			update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
			update_post_meta( $post_id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );
		}
	}

	/**
	 * Public accessor for the importer's mapped IDs (used by page builders).
	 *
	 * @param string $group Group.
	 * @param string $key   Key.
	 * @return int
	 */
	public static function id( $group, $key ) {
		return self::mapped( $group, $key );
	}

	/**
	 * Menus and widgets.
	 *
	 * @return string
	 */
	private static function step_menus() {
		$page = static function ( $key ) {
			return self::mapped( 'posts', 'page:' . $key );
		};
		$shop = wc_get_page_id( 'shop' );

		$primary = array(
			array( 'page', $page( 'home' ), 'خانه' ),
			array( 'page', $shop, 'فروشگاه' ),
			array( 'page', $page( 'wholesale' ), 'خرید عمده' ),
			array( 'page', $page( 'blog' ), 'مجله برق' ),
			array( 'page', $page( 'about' ), 'درباره ما' ),
			array( 'page', $page( 'contact' ), 'تماس با ما' ),
		);

		$categories = array();
		foreach ( array_keys( self::data()['categories'] ) as $slug ) {
			$categories[] = array( 'term', self::mapped( 'terms', 'product_cat:' . $slug ), '' );
		}

		$help = array(
			array( 'page', $page( 'faq' ), 'سؤالات متداول' ),
			array( 'page', $page( 'shipping' ), 'رویه ارسال و مرجوعی' ),
			array( 'page', $page( 'terms' ), 'قوانین و مقررات' ),
			array( 'page', (int) get_option( 'wp_page_for_privacy_policy' ), 'حریم خصوصی' ),
			array( 'page', $page( 'contact' ), 'تماس با ما' ),
		);

		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations = is_array( $locations ) ? $locations : array();

		$locations['primary']     = self::menu( 'primary', 'منوی اصلی', $primary );
		$locations['mobile']      = self::menu( 'mobile', 'منوی موبایل', array_merge( $primary, $categories ) );
		$locations['footer_shop'] = self::menu( 'footer_shop', 'دسته‌بندی‌های اصلی', array_slice( $categories, 0, 6 ) );
		$locations['footer_help'] = self::menu( 'footer_help', 'راهنما و قوانین', $help );

		set_theme_mod( 'nav_menu_locations', array_filter( $locations ) );

		self::widgets();

		return __( 'منوهای هدر، موبایل و فوتر و ابزارک‌های فیلتر فروشگاه و وبلاگ تنظیم شد.', 'samadi-core' );
	}

	/**
	 * Create a menu (replacing items of a previously imported one).
	 *
	 * @param string $key   Key.
	 * @param string $name  Menu name.
	 * @param array  $items [ type, object id, title ].
	 * @return int Menu ID.
	 */
	private static function menu( $key, $name, $items ) {
		$menu_id = self::mapped( 'menus', $key );

		if ( ! $menu_id ) {
			$existing = wp_get_nav_menu_object( $name );
			$menu_id  = $existing ? (int) $existing->term_id : (int) wp_create_nav_menu( $name );
			if ( ! $menu_id || is_wp_error( $menu_id ) ) {
				return 0;
			}
			self::remember( 'menus', $key, $menu_id );
		}

		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
			wp_delete_post( $item->ID, true );
		}

		foreach ( $items as $i => $item ) {
			list( $type, $object_id, $title ) = $item;
			if ( ! $object_id ) {
				continue;
			}
			$args = array(
				'menu-item-status'   => 'publish',
				'menu-item-position' => $i + 1,
				'menu-item-title'    => $title,
			);
			if ( 'term' === $type ) {
				$args += array(
					'menu-item-type'      => 'taxonomy',
					'menu-item-object'    => 'product_cat',
					'menu-item-object-id' => $object_id,
				);
				if ( '' === $title ) {
					$term                    = get_term( $object_id );
					$args['menu-item-title'] = $term ? $term->name : '';
				}
			} else {
				$args += array(
					'menu-item-type'      => 'post_type',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $object_id,
				);
			}
			wp_update_nav_menu_item( $menu_id, 0, $args );
		}

		return $menu_id;
	}

	/**
	 * Sidebar widgets (shop filters, blog).
	 */
	private static function widgets() {
		$sidebars = get_option( 'sidebars_widgets', array() );
		$sidebars = is_array( $sidebars ) ? $sidebars : array();

		// Shop filters.
		if ( class_exists( 'Samadi_EW_Filter_Widget' ) ) {
			$instances      = get_option( 'widget_samadi_product_filters', array() );
			$instances      = is_array( $instances ) ? $instances : array();
			$instances[2]   = array( 'title' => 'فیلتر محصولات' );
			$instances['_multiwidget'] = 1;
			update_option( 'widget_samadi_product_filters', $instances );
			$sidebars['sidebar-shop'] = array( 'samadi_product_filters-2' );
		}

		// Blog sidebar: search, categories, recent posts.
		$search                    = get_option( 'widget_search', array() );
		$search[5]                 = array( 'title' => 'جستجو در مجله' );
		$search['_multiwidget']    = 1;
		$cats                      = get_option( 'widget_categories', array() );
		$cats[5]                   = array( 'title' => 'دسته‌بندی مطالب', 'count' => 1, 'hierarchical' => 0, 'dropdown' => 0 );
		$cats['_multiwidget']      = 1;
		$recent                    = get_option( 'widget_recent-posts', array() );
		$recent[5]                 = array( 'title' => 'تازه‌ترین مطالب', 'number' => 4, 'show_date' => true );
		$recent['_multiwidget']    = 1;
		update_option( 'widget_search', $search );
		update_option( 'widget_categories', $cats );
		update_option( 'widget_recent-posts', $recent );
		$sidebars['sidebar-blog'] = array( 'search-5', 'categories-5', 'recent-posts-5' );

		update_option( 'sidebars_widgets', $sidebars );
	}

	/**
	 * Theme (Customizer) options.
	 *
	 * @return string
	 */
	private static function step_theme() {
		$mods = array(
			'samadi_contact_phone'      => '۰۲۱-۳۳۹۱۲۳۴۵',
			'samadi_contact_mobile'     => '۰۹۱۲۱۲۳۴۵۶۷',
			'samadi_contact_email'      => 'info@samadi-electric.ir',
			'samadi_contact_address'    => 'تهران، خیابان لاله‌زار جنوبی، پاساژ برق، طبقه همکف، پلاک ۱۲',
			'samadi_contact_hours'      => 'شنبه تا چهارشنبه ۹ تا ۱۸، پنجشنبه ۹ تا ۱۴',
			'samadi_social_instagram'   => 'https://instagram.com/',
			'samadi_social_telegram'    => 'https://t.me/',
			'samadi_social_whatsapp'    => 'https://wa.me/',
			'samadi_social_linkedin'    => 'https://www.linkedin.com/',
			'samadi_footer_about'       => 'صمدی الکتریک با سال‌ها تجربه در بازار لاله‌زار، تأمین‌کننده انواع کلید و پریز، روشنایی، سیم و کابل و تجهیزات حفاظتی از برندهای معتبر برای مصرف‌کنندگان، پیمانکاران و پروژه‌هاست.',
			'samadi_footer_style'       => 'dark',
			'samadi_footer_cta_show'    => true,
			'samadi_header_show_account' => true,
			'samadi_mobile_bottom_nav'  => true,
			'samadi_hero_image'         => self::image( 'hero-1' ),
		);
		foreach ( $mods as $name => $value ) {
			set_theme_mod( $name, $value );
		}

		$ids = array();
		foreach ( array_keys( self::data()['categories'] ) as $slug ) {
			$ids[] = self::mapped( 'terms', 'product_cat:' . $slug );
		}
		set_theme_mod( 'samadi_home_categories', implode( ',', array_filter( $ids ) ) );

		// Companion plugin: notification email for wholesale requests.
		$settings                = get_option( 'samadi_ew_settings', array() );
		$settings                = is_array( $settings ) ? $settings : array();
		$settings['quote_email'] = get_option( 'admin_email' );
		update_option( 'samadi_ew_settings', $settings );

		// Push theme colors and font into the Elementor kit.
		if ( function_exists( 'samadi_elementor_sync_kit' ) ) {
			samadi_elementor_sync_kit();
		}

		return __( 'اطلاعات تماس نمونه، شبکه‌های اجتماعی، متن فوتر و رنگ‌های Elementor تنظیم شد.', 'samadi-core' );
	}

	/**
	 * Final touches.
	 *
	 * @return string
	 */
	private static function step_finalize() {
		flush_rewrite_rules( false );

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		wc_delete_product_transients();
		delete_transient( 'wc_term_counts' );
		if ( function_exists( '_wc_term_recount' ) ) {
			$cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
			if ( ! is_wp_error( $cats ) ) {
				_wc_term_recount( $cats, get_taxonomy( 'product_cat' ), true, false );
			}
		}

		update_option( self::DONE, time() );

		return __( 'درون‌نمایی کامل شد.', 'samadi-core' );
	}

	/* ===================================================================== removal */

	/**
	 * Remove everything the importer created.
	 *
	 * @return int Number of removed objects.
	 */
	public static function remove() {
		$map     = self::map();
		$removed = 0;

		foreach ( $map['posts'] as $key => $id ) {
			if ( 0 === strpos( (string) $key, 'product:' ) && function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( $id );
				if ( $product ) {
					foreach ( $product->get_children() as $child ) {
						wp_delete_post( $child, true );
					}
				}
			}
			if ( wp_delete_post( (int) $id, true ) ) {
				++$removed;
			}
		}
		foreach ( $map['attachments'] as $id ) {
			if ( wp_delete_attachment( (int) $id, true ) ) {
				++$removed;
			}
		}
		foreach ( $map['menus'] as $id ) {
			if ( wp_delete_nav_menu( (int) $id ) ) {
				++$removed;
			}
		}
		foreach ( $map['terms'] as $key => $id ) {
			$taxonomy = strstr( (string) $key, ':', true );
			if ( $taxonomy && ! is_wp_error( wp_delete_term( (int) $id, $taxonomy ) ) ) {
				++$removed;
			}
		}
		foreach ( $map['attributes'] as $id ) {
			if ( wc_delete_attribute( (int) $id ) ) {
				++$removed;
			}
		}
		if ( ! empty( $map['misc']['shipping_zone'] ) && class_exists( 'WC_Shipping_Zone' ) ) {
			$zone = new WC_Shipping_Zone( (int) $map['misc']['shipping_zone'] );
			$zone->delete();
		}

		if ( (int) get_option( 'page_on_front' ) && ! get_post( (int) get_option( 'page_on_front' ) ) ) {
			update_option( 'show_on_front', 'posts' );
		}

		delete_option( self::MAP_OPTION );
		delete_option( self::DONE );
		wc_delete_product_transients();

		return $removed;
	}
}
