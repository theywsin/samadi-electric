<?php
/**
 * Demo presets: one theme, several store types.
 *
 * A demo applies design tokens (colors, store type → logo mark and icons), homepage texts, store
 * benefits, recommended product attributes and categories, and builds the Elementor homepage.
 * It never creates products, prices, licences or contact details.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Demo registry.
 */
class Samadi_EW_Demos {

	/**
	 * All demos.
	 *
	 * @return array<string, array>
	 */
	public static function all() {
		$demos = array(
			'electric' => array(
				'label'       => 'دمو ۱ — تجهیزات الکتریکی',
				'description' => 'سبز تیره و لیمویی؛ کلید و پریز، روشنایی، سیم و کابل، تجهیزات صنعتی.',
				'theme_mods'  => array(
					'store_type'              => 'electric',
					'color_primary'           => '#064E3B',
					'color_brand'             => '#075E46',
					'color_accent'            => '#C7F52A',
					'color_bg'                => '#F7F8F6',
					'color_text'              => '#152421',
					'color_muted'             => '#718078',
					'color_border'            => '#E8ECE8',
					'color_sale'              => '#F04438',
					'hero_title'              => 'تجهیزات الکتریکی باکیفیت، برای زندگی بهتر',
					'hero_text'               => 'همراه مطمئن شما در تأمین کلیه لوازم برق ساختمان و صنعت.',
					'hero_btn1_text'          => 'مشاهده محصولات',
					'hero_btn2_text'          => 'خرید عمده',
					'header_wholesale_label'  => 'خرید عمده',
					'benefits'                => array(
						array( 'truck', 'ارسال سریع', 'به سراسر ایران' ),
						array( 'shield-check', 'ضمانت اصالت کالا', 'از برندهای معتبر' ),
						array( 'headset', 'مشاوره تخصصی', 'پیش از خرید' ),
						array( 'badge-percent', 'قیمت مناسب', 'خرید مستقیم' ),
						array( 'message-circle', 'پشتیبانی واقعی', 'همیشه کنار شما' ),
					),
				),
				'attributes'  => Samadi_EW_Setup::recommended_attributes(),
				'categories'  => Samadi_EW_Setup::recommended_categories(),
			),
			'medical'  => array(
				'label'       => 'دمو ۲ — تجهیزات و لوازم پزشکی',
				'description' => 'آبی-سبزآبی و نعنایی؛ تجهیزات تشخیصی، لوازم مصرفی، مراقبت در منزل، توانبخشی، آزمایشگاهی.',
				'theme_mods'  => array(
					'store_type'              => 'medical',
					'color_primary'           => '#0B4F6C',
					'color_brand'             => '#0E7490',
					'color_accent'            => '#7DE2D1',
					'color_bg'                => '#F5F8FA',
					'color_text'              => '#132A35',
					'color_muted'             => '#6B7F89',
					'color_border'            => '#E3EAEE',
					'color_sale'              => '#E5484D',
					'hero_title'              => 'لوازم پزشکی اصل، با اطمینان در خانه و مطب',
					'hero_text'               => 'تجهیزات تشخیصی، لوازم مصرفی و مراقبت در منزل؛ همراه با مشاوره پیش از خرید.',
					'hero_btn1_text'          => 'مشاهده محصولات',
					'hero_btn2_text'          => 'خرید سازمانی',
					'header_wholesale_label'  => 'خرید سازمانی',
					'header_categories_label' => 'همه دسته‌بندی‌ها',
					'benefits'                => array(
						array( 'truck', 'ارسال سریع', 'به سراسر ایران' ),
						array( 'shield-plus', 'ضمانت اصالت کالا', 'از تأمین‌کننده معتبر' ),
						array( 'stethoscope', 'مشاوره تخصصی', 'پیش از خرید' ),
						array( 'package', 'بسته‌بندی بهداشتی', 'پلمب و سالم' ),
						array( 'headset', 'پشتیبانی', 'پاسخ‌گوی سؤالات شما' ),
					),
				),
				'attributes'  => array(
					'brand-model'   => 'مدل',
					'size'          => 'سایز',
					'material'      => 'جنس',
					'pack-qty'      => 'تعداد در بسته',
					'usage'         => 'کاربری',
					'power-source'  => 'منبع تغذیه',
					'sterility'     => 'استریل',
					'country'       => 'کشور سازنده',
					'irc-code'      => 'کد IRC',
				),
				'categories'  => array( 'تجهیزات تشخیصی', 'فشارسنج و تب‌سنج', 'لوازم مصرفی پزشکی', 'دستکش و ماسک', 'مراقبت در منزل', 'ارتوپدی و توانبخشی', 'تجهیزات آزمایشگاهی', 'کمک‌های اولیه' ),
			),
		);

		/**
		 * Filter available demos.
		 *
		 * @param array $demos Demos.
		 */
		return apply_filters( 'samadi_demos', $demos );
	}

	/**
	 * Apply a demo.
	 *
	 * @param string $key        Demo key.
	 * @param bool   $structure  Create attributes + categories.
	 * @param bool   $home       Build the Elementor homepage.
	 * @param bool   $set_front  Set the homepage as front page.
	 * @return array{ok:bool, page_id:int}
	 */
	public static function apply( $key, $structure, $home, $set_front ) {
		$demos = self::all();

		if ( empty( $demos[ $key ] ) ) {
			return array( 'ok' => false, 'page_id' => 0 );
		}

		$demo = $demos[ $key ];
		$mods = $demo['theme_mods'];

		foreach ( $mods as $name => $value ) {
			if ( 'benefits' === $name ) {
				foreach ( array_values( $value ) as $i => $item ) {
					$n = $i + 1;
					set_theme_mod( "samadi_benefit_{$n}_icon", sanitize_key( $item[0] ) );
					set_theme_mod( "samadi_benefit_{$n}_title", sanitize_text_field( $item[1] ) );
					set_theme_mod( "samadi_benefit_{$n}_text", sanitize_text_field( $item[2] ) );
				}
				continue;
			}
			$clean = 0 === strpos( $name, 'color_' ) ? sanitize_hex_color( $value ) : sanitize_text_field( $value );
			if ( $clean ) {
				set_theme_mod( 'samadi_' . $name, $clean );
			}
		}

		set_theme_mod( 'samadi_active_demo', $key );

		if ( $structure ) {
			Samadi_EW_Setup::create_attributes( $demo['attributes'] );
			Samadi_EW_Setup::create_categories( $demo['categories'] );
		}

		$page_id = 0;
		if ( $home && did_action( 'elementor/loaded' ) ) {
			$benefits = isset( $mods['benefits'] ) ? $mods['benefits'] : array();
			$extra    = array(
				'{{hero_title}}' => $mods['hero_title'],
				'{{hero_text}}'  => $mods['hero_text'],
				'{{hero_btn1}}'  => $mods['hero_btn1_text'],
				'{{hero_btn2}}'  => $mods['hero_btn2_text'],
			);
			foreach ( array_values( $benefits ) as $i => $item ) {
				$n                        = $i + 1;
				$extra[ "{{b{$n}_icon}}" ]  = $item[0];
				$extra[ "{{b{$n}_title}}" ] = $item[1];
				$extra[ "{{b{$n}_text}}" ]  = $item[2];
			}
			$page_id = Samadi_EW_Setup::create_home_page( $set_front, $extra );
		}

		if ( function_exists( 'samadi_elementor_sync_kit' ) ) {
			samadi_elementor_sync_kit();
		}

		return array( 'ok' => true, 'page_id' => (int) $page_id );
	}
}
