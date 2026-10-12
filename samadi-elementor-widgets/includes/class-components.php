<?php
/**
 * Shared component renderers used by Elementor widgets and shortcodes.
 * Every component reads real WordPress / WooCommerce data.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Component renderers.
 */
class Samadi_EW_Components {

	/**
	 * Section header markup.
	 *
	 * @param string $title     Title.
	 * @param string $link      URL.
	 * @param string $link_text Link text.
	 * @param string $tag       Heading tag.
	 * @return string
	 */
	public static function section_head( $title, $link = '', $link_text = '', $tag = 'h2' ) {
		if ( '' === trim( (string) $title ) && ! $link ) {
			return '';
		}

		$tag  = in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'p', 'div' ), true ) ? $tag : 'h2';
		$html = '<div class="se-section-head">';
		if ( '' !== trim( (string) $title ) ) {
			$html .= sprintf( '<%1$s class="se-section-head__title">%2$s</%1$s>', $tag, esc_html( $title ) );
		}
		if ( $link ) {
			$html .= sprintf(
				'<a class="se-section-head__link" href="%s">%s%s</a>',
				esc_url( $link ),
				esc_html( $link_text ? $link_text : __( 'مشاهده همه', 'samadi-elementor-widgets' ) ),
				samadi_ew_icon( 'chevron-left', 16 )
			);
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Wrap content with optional carousel controls.
	 *
	 * @param string $inner    Inner HTML (must contain a list that becomes the scroll track).
	 * @param bool   $carousel Carousel mode.
	 * @param string $label    Accessible label for the region.
	 * @return string
	 */
	public static function maybe_carousel( $inner, $carousel, $label = '' ) {
		if ( ! $carousel ) {
			return $inner;
		}

		return sprintf(
			'<div class="se-carousel" data-se-carousel role="region" aria-roledescription="carousel"%1$s><div class="se-carousel__nav"><button type="button" class="se-carousel__btn" data-dir="prev" aria-label="%2$s">%3$s</button><button type="button" class="se-carousel__btn" data-dir="next" aria-label="%4$s">%5$s</button></div><div class="se-carousel__track">%6$s</div></div>',
			$label ? ' aria-label="' . esc_attr( $label ) . '"' : '',
			esc_attr__( 'قبلی', 'samadi-elementor-widgets' ),
			samadi_ew_icon( 'chevron-right', 20 ),
			esc_attr__( 'بعدی', 'samadi-elementor-widgets' ),
			samadi_ew_icon( 'chevron-left', 20 ),
			$inner
		);
	}

	/* ---------------------------------------------------------------------
	 * Hero / promotional banner
	 * ------------------------------------------------------------------ */

	/**
	 * Hero banner with one or more slides.
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function hero( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'slides'    => array(),
				'autoplay'  => true,
				'interval'  => 6,
				'title_tag' => 'h2',
				'eager'     => true,
			)
		);

		$slides = array_values( array_filter( (array) $args['slides'], static function ( $s ) {
			return ! empty( $s['title'] ) || ! empty( $s['image_id'] ) || ! empty( $s['image_url'] );
		} ) );

		if ( ! $slides ) {
			return '';
		}

		$tag   = in_array( $args['title_tag'], array( 'h1', 'h2', 'h3', 'p' ), true ) ? $args['title_tag'] : 'h2';
		$multi = count( $slides ) > 1;
		$html  = sprintf(
			'<div class="se-hero-slider%1$s" data-se-slider data-autoplay="%2$s" data-interval="%3$d"%4$s>',
			$multi ? ' is-multi' : '',
			$args['autoplay'] && $multi ? '1' : '0',
			max( 3, absint( $args['interval'] ) ),
			$multi ? ' role="region" aria-roledescription="carousel" aria-label="' . esc_attr__( 'بنرهای تبلیغاتی', 'samadi-elementor-widgets' ) . '"' : ''
		);
		$html .= '<div class="se-hero-slider__track">';

		foreach ( $slides as $index => $slide ) {
			$slide = wp_parse_args(
				$slide,
				array(
					'image_id'  => 0,
					'image_url' => '',
					'title'     => '',
					'text'      => '',
					'btn1_text' => '',
					'btn1_url'  => '',
					'btn2_text' => '',
					'btn2_url'  => '',
					'align'     => 'start',
				)
			);

			$align = in_array( $slide['align'], array( 'start', 'center', 'end' ), true ) ? $slide['align'] : 'start';
			$image = '';

			if ( $slide['image_id'] ) {
				$image = wp_get_attachment_image(
					absint( $slide['image_id'] ),
					'full',
					false,
					array(
						'class'         => 'se-hero__image',
						'alt'           => '',
						'loading'       => ( 0 === $index && $args['eager'] ) ? 'eager' : 'lazy',
						'fetchpriority' => ( 0 === $index && $args['eager'] ) ? 'high' : 'auto',
						'sizes'         => '(max-width: 1320px) 100vw, 1280px',
					)
				);
			} elseif ( $slide['image_url'] ) {
				$image = sprintf( '<img class="se-hero__image" src="%s" alt="" %s>', esc_url( $slide['image_url'] ), 0 === $index ? 'fetchpriority="high"' : 'loading="lazy"' );
			}

			$html .= sprintf(
				'<div class="se-hero se-hero--%1$s%2$s"%3$s>',
				esc_attr( $align ),
				$image ? ' has-image' : '',
				$multi ? sprintf( ' role="group" aria-roledescription="slide" aria-label="%s"', esc_attr( sprintf( '%d / %d', $index + 1, count( $slides ) ) ) ) : ''
			);
			$html .= $image;
			$html .= '<div class="se-hero__content">';
			if ( $slide['title'] ) {
				$html .= sprintf( '<%1$s class="se-hero__title">%2$s</%1$s>', $tag, wp_kses( $slide['title'], samadi_ew_kses_inline() ) );
			}
			if ( $slide['text'] ) {
				$html .= '<p class="se-hero__text">' . wp_kses( $slide['text'], samadi_ew_kses_inline() ) . '</p>';
			}
			$buttons = '';
			if ( $slide['btn1_text'] && $slide['btn1_url'] ) {
				$buttons .= sprintf( '<a class="se-btn se-btn--primary se-btn--lg" href="%s">%s%s</a>', esc_url( $slide['btn1_url'] ), esc_html( $slide['btn1_text'] ), samadi_ew_icon( 'arrow-left', 18 ) );
			}
			if ( $slide['btn2_text'] && $slide['btn2_url'] ) {
				$buttons .= sprintf( '<a class="se-btn se-btn--light se-btn--lg" href="%s">%s</a>', esc_url( $slide['btn2_url'] ), esc_html( $slide['btn2_text'] ) );
			}
			if ( $buttons ) {
				$html .= '<div class="se-hero__actions">' . $buttons . '</div>';
			}
			$html .= '</div></div>';
		}

		$html .= '</div>';

		if ( $multi ) {
			$html .= '<div class="se-hero-slider__dots">';
			foreach ( $slides as $index => $slide ) {
				$html .= sprintf(
					'<button type="button" class="se-hero-slider__dot%1$s" data-index="%2$d" aria-label="%3$s"%4$s></button>',
					0 === $index ? ' is-active' : '',
					$index,
					/* translators: %d: slide number. */
					esc_attr( sprintf( __( 'نمایش بنر %d', 'samadi-elementor-widgets' ), $index + 1 ) ),
					0 === $index ? ' aria-current="true"' : ''
				);
			}
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/* ---------------------------------------------------------------------
	 * Benefits
	 * ------------------------------------------------------------------ */

	/**
	 * Benefits bar.
	 *
	 * @param array $args { items: [{icon,title,text}] }.
	 * @return string
	 */
	public static function benefits( $args ) {
		$items = array_filter( (array) ( isset( $args['items'] ) ? $args['items'] : array() ), static function ( $i ) {
			return ! empty( $i['title'] );
		} );

		if ( ! $items ) {
			return '';
		}

		$html = sprintf( '<ul class="se-benefits" style="--se-benefits-count:%d">', count( $items ) );
		foreach ( $items as $item ) {
			$html .= '<li class="se-benefits__item">';
			$html .= '<span class="se-benefits__icon">' . samadi_ew_icon( isset( $item['icon'] ) ? $item['icon'] : 'check', 26 ) . '</span>';
			$html .= '<span class="se-benefits__body"><strong class="se-benefits__title">' . esc_html( $item['title'] ) . '</strong>';
			if ( ! empty( $item['text'] ) ) {
				$html .= '<span class="se-benefits__text">' . esc_html( $item['text'] ) . '</span>';
			}
			$html .= '</span></li>';
		}
		$html .= '</ul>';

		return $html;
	}

	/* ---------------------------------------------------------------------
	 * Categories
	 * ------------------------------------------------------------------ */

	/**
	 * Product category cards.
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function categories( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'source'     => 'top',
				'ids'        => array(),
				'limit'      => 8,
				'orderby'    => 'menu_order',
				'hide_empty' => true,
				'show_count' => false,
				'show_image' => true,
				'title'      => '',
				'link'       => '',
				'link_text'  => '',
				'title_tag'  => 'h2',
				'layout'     => 'grid',
				'show_desc'  => true,
				'highlight'  => false,
				'mobile_grid' => true,
				'brands_url' => '',
				'brands_label' => __( 'برندها', 'samadi-elementor-widgets' ),
			)
		);

		$query = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => (bool) $args['hide_empty'],
			'number'     => max( 1, absint( $args['limit'] ) ),
		);

		$ids = array_filter( array_map( 'absint', (array) $args['ids'] ) );

		if ( 'manual' === $args['source'] && $ids ) {
			$query['include'] = $ids;
			$query['orderby'] = 'include';
		} elseif ( 'children' === $args['source'] && is_product_category() ) {
			$query['parent']  = get_queried_object_id();
			$query['orderby'] = $args['orderby'];
		} else {
			$query['parent']  = 0;
			$query['orderby'] = $args['orderby'];
			$default          = (int) get_option( 'default_product_cat' );
			if ( $default ) {
				$query['exclude'] = array( $default );
			}
		}

		if ( 'count' === $query['orderby'] ) {
			$query['order'] = 'DESC';
		}

		$terms = get_terms( $query );

		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}

		$items = '';
		foreach ( $terms as $term ) {
			$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
			$media    = ( $args['show_image'] && $thumb_id )
				? wp_get_attachment_image( $thumb_id, 'woocommerce_thumbnail', false, array( 'alt' => $term->name, 'loading' => 'lazy' ) )
				: samadi_ew_icon( 'zap', 36 );

			$items .= '<li class="se-cat-card"><a class="se-cat-card__link" href="' . esc_url( get_term_link( $term ) ) . '">';
			$items .= '<span class="se-cat-card__media">' . $media . '</span>';
			$items .= '<span class="se-cat-card__text"><span class="se-cat-card__name">' . esc_html( $term->name ) . '</span>';
			if ( $args['show_desc'] && '' !== trim( (string) $term->description ) ) {
				$items .= '<span class="se-cat-card__desc">' . esc_html( wp_trim_words( wp_strip_all_tags( $term->description ), 6 ) ) . '</span>';
			}
			$items .= '</span>';
			if ( $args['show_count'] ) {
				/* translators: %s: number of products. */
				$items .= '<span class="se-cat-card__count">' . esc_html( sprintf( __( '%s کالا', 'samadi-elementor-widgets' ), samadi_ew_number( $term->count ) ) ) . '</span>';
			}
			$items .= '</a></li>';
		}

		// Optional tile linking to a real brands page/archive (only when a URL is configured).
		if ( $args['brands_url'] ) {
			$items .= '<li class="se-cat-card se-cat-card--brands"><a class="se-cat-card__link" href="' . esc_url( $args['brands_url'] ) . '"><span class="se-cat-card__media">' . samadi_ew_icon( 'tag', 34 ) . '</span><span class="se-cat-card__text"><span class="se-cat-card__name">' . esc_html( $args['brands_label'] ) . '</span></span></a></li>';
		}

		$carousel = 'carousel' === $args['layout'];
		$classes  = array( 'se-cat-grid', 'se-cat-grid--featured' );
		if ( $carousel ) {
			$classes[] = 'se-cat-grid--row';
		}
		if ( $carousel && $args['mobile_grid'] ) {
			$classes[] = 'se-cat-grid--mobile-grid';
		}
		if ( $args['highlight'] ) {
			$classes[] = 'se-cat-grid--highlight';
		}
		$list = '<ul class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $items . '</ul>';

		return self::section_head( $args['title'], $args['link'], $args['link_text'], $args['title_tag'] ) . self::maybe_carousel( $list, $carousel, $args['title'] );
	}

	/* ---------------------------------------------------------------------
	 * Products
	 * ------------------------------------------------------------------ */

	/**
	 * Product grid/carousel built on WooCommerce's [products] shortcode (real data, WC caching, theme card).
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function products( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'source'     => 'recent',
				'ids'        => array(),
				'categories' => array(),
				'limit'      => 8,
				'columns'    => 4,
				'orderby'    => '',
				'order'      => 'DESC',
				'layout'     => 'grid',
				'title'      => '',
				'link'       => '',
				'link_text'  => '',
				'title_tag'  => 'h2',
				'paginate'   => false,
			)
		);

		$limit   = max( 1, min( 48, absint( $args['limit'] ) ) );
		$columns = max( 1, min( 6, absint( $args['columns'] ) ) );
		$atts    = array(
			'limit'   => $limit,
			'columns' => $columns,
		);

		switch ( $args['source'] ) {
			case 'featured':
				$atts['visibility'] = 'featured';
				break;
			case 'sale':
				$atts['on_sale'] = 'true';
				break;
			case 'best_selling':
				$atts['best_selling'] = 'true';
				break;
			case 'top_rated':
				$atts['top_rated'] = 'true';
				break;
			case 'manual':
				$ids = array_filter( array_map( 'absint', (array) $args['ids'] ) );
				if ( ! $ids ) {
					return '';
				}
				$atts['ids'] = implode( ',', $ids );
				break;
			case 'category':
				$cats = array_filter( array_map( 'absint', (array) $args['categories'] ) );
				if ( $cats ) {
					$atts['category'] = implode( ',', $cats );
				}
				break;
			case 'current_category':
				if ( is_product_category() ) {
					$atts['category'] = (string) get_queried_object_id();
				}
				break;
			case 'related':
			case 'upsells':
			case 'cross_sells':
				$ids = self::contextual_ids( $args['source'], $limit );
				if ( ! $ids ) {
					return '';
				}
				$atts['ids'] = implode( ',', $ids );
				break;
		}

		if ( $args['orderby'] ) {
			$atts['orderby'] = sanitize_key( $args['orderby'] );
			$atts['order']   = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
		} elseif ( 'recent' === $args['source'] ) {
			$atts['orderby'] = 'date';
			$atts['order']   = 'DESC';
		} elseif ( 'manual' === $args['source'] ) {
			$atts['orderby'] = 'post__in';
		}

		if ( $args['paginate'] ) {
			$atts['paginate'] = 'true';
		}

		$shortcode = '[products';
		foreach ( $atts as $key => $value ) {
			$shortcode .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
		}
		$shortcode .= ']';

		$html = do_shortcode( $shortcode );

		if ( false === strpos( $html, '<li' ) ) {
			return '';
		}

		$carousel = 'carousel' === $args['layout'];

		return self::section_head( $args['title'], $args['link'], $args['link_text'], $args['title_tag'] )
			. self::maybe_carousel( '<div class="se-products' . ( $carousel ? ' se-products--row' : '' ) . '" style="--se-cols:' . $columns . '">' . $html . '</div>', $carousel, $args['title'] );
	}

	/**
	 * Related / upsell / cross-sell product IDs for the current product or cart.
	 *
	 * @param string $source Source.
	 * @param int    $limit  Limit.
	 * @return int[]
	 */
	private static function contextual_ids( $source, $limit ) {
		if ( 'cross_sells' === $source ) {
			return ( function_exists( 'WC' ) && WC()->cart ) ? array_slice( WC()->cart->get_cross_sells(), 0, $limit ) : array();
		}

		$product = wc_get_product( get_the_ID() );

		if ( ! $product ) {
			return array();
		}

		if ( 'upsells' === $source ) {
			return array_slice( $product->get_upsell_ids(), 0, $limit );
		}

		return wc_get_related_products( $product->get_id(), $limit, $product->get_upsell_ids() );
	}

	/* ---------------------------------------------------------------------
	 * Brands
	 * ------------------------------------------------------------------ */

	/**
	 * Brand logos linking to each brand archive.
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function brands( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'ids'       => array(),
				'limit'     => 12,
				'layout'    => 'grid',
				'title'     => '',
				'link'      => '',
				'link_text' => '',
				'title_tag' => 'h2',
				'show_name' => false,
			)
		);

		$taxonomy = samadi_ew_brand_taxonomy();

		if ( ! $taxonomy ) {
			return current_user_can( 'manage_woocommerce' ) && self::is_editor()
				? '<p class="se-notice">' . esc_html__( 'هیچ طبقه‌بندی برندی فعال نیست. برندهای ووکامرس (محصولات ← برندها) را فعال کنید.', 'samadi-elementor-widgets' ) . '</p>'
				: '';
		}

		$query = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'number'     => max( 1, absint( $args['limit'] ) ),
			'orderby'    => 'count',
			'order'      => 'DESC',
		);

		$ids = array_filter( array_map( 'absint', (array) $args['ids'] ) );
		if ( $ids ) {
			$query['include'] = $ids;
			$query['orderby'] = 'include';
			unset( $query['order'] );
		}

		$terms = get_terms( $query );

		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}

		$items = '';
		foreach ( $terms as $term ) {
			$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
			$logo     = $thumb_id ? wp_get_attachment_image( $thumb_id, 'medium', false, array( 'alt' => $term->name, 'loading' => 'lazy' ) ) : '';
			$items   .= '<li class="se-brand-strip__item"><a class="se-brand-strip__link" href="' . esc_url( get_term_link( $term ) ) . '">';
			$items   .= $logo ? $logo : '<span class="se-brand-strip__name">' . esc_html( $term->name ) . '</span>';
			if ( $logo && $args['show_name'] ) {
				$items .= '<span class="se-brand-strip__label">' . esc_html( $term->name ) . '</span>';
			} elseif ( $logo ) {
				$items .= '<span class="screen-reader-text">' . esc_html( $term->name ) . '</span>';
			}
			$items .= '</a></li>';
		}

		$carousel = 'carousel' === $args['layout'];
		$list     = '<ul class="se-brand-strip' . ( $carousel ? ' se-brand-strip--row' : '' ) . '">' . $items . '</ul>';

		return self::section_head( $args['title'], $args['link'], $args['link_text'], $args['title_tag'] ) . self::maybe_carousel( $list, $carousel, $args['title'] );
	}

	/* ---------------------------------------------------------------------
	 * Flash sale
	 * ------------------------------------------------------------------ */

	/**
	 * Flash sale: countdown + products (on-sale by default).
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function flash_sale( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'title'        => '',
				'end'          => '',
				'expired'      => 'hide',
				'products'     => array(),
			)
		);

		$end = $args['end'] ? strtotime( get_gmt_from_date( $args['end'] ) . ' UTC' ) : 0;

		if ( $end && $end < time() && 'hide' === $args['expired'] && ! self::is_editor() ) {
			return '';
		}

		$products = self::products( wp_parse_args( $args['products'], array( 'source' => 'sale', 'layout' => 'carousel' ) ) );

		if ( '' === $products ) {
			return '';
		}

		$html  = '<div class="se-flash">';
		$html .= '<div class="se-flash__head">';
		if ( $args['title'] ) {
			$html .= '<h2 class="se-flash__title">' . samadi_ew_icon( 'zap', 22 ) . esc_html( $args['title'] ) . '</h2>';
		}
		if ( $end ) {
			$html .= sprintf(
				'<div class="se-countdown" data-se-countdown="%1$s" role="timer" aria-label="%2$s"><span class="se-countdown__unit" data-unit="d"><b>–</b><small>%3$s</small></span><span class="se-countdown__unit" data-unit="h"><b>–</b><small>%4$s</small></span><span class="se-countdown__unit" data-unit="m"><b>–</b><small>%5$s</small></span><span class="se-countdown__unit" data-unit="s"><b>–</b><small>%6$s</small></span></div>',
				esc_attr( gmdate( 'c', $end ) ),
				esc_attr__( 'زمان باقی‌مانده تا پایان پیشنهاد', 'samadi-elementor-widgets' ),
				esc_html__( 'روز', 'samadi-elementor-widgets' ),
				esc_html__( 'ساعت', 'samadi-elementor-widgets' ),
				esc_html__( 'دقیقه', 'samadi-elementor-widgets' ),
				esc_html__( 'ثانیه', 'samadi-elementor-widgets' )
			);
		}
		$html .= '</div>' . $products . '</div>';

		return $html;
	}

	/* ---------------------------------------------------------------------
	 * Blog posts
	 * ------------------------------------------------------------------ */

	/**
	 * Blog post cards.
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function posts( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'count'     => 3,
				'category'  => 0,
				'title'     => '',
				'link'      => '',
				'link_text' => '',
				'title_tag' => 'h2',
				'excerpt'   => 20,
			)
		);

		$query = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => max( 1, min( 12, absint( $args['count'] ) ) ),
				'cat'                 => absint( $args['category'] ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		if ( ! $query->have_posts() ) {
			return '';
		}

		$html = '<div class="se-post-grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$html .= '<article class="se-post-card se-card">';
			$html .= '<a class="se-post-card__media" href="' . esc_url( get_permalink() ) . '" tabindex="-1" aria-hidden="true">';
			$html .= has_post_thumbnail() ? get_the_post_thumbnail( null, 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) ) : '<span class="se-post-card__placeholder">' . samadi_ew_icon( 'lightbulb', 40 ) . '</span>';
			$html .= '</a><div class="se-post-card__body">';
			$html .= '<h3 class="se-post-card__title"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
			if ( $args['excerpt'] ) {
				$html .= '<p class="se-post-card__excerpt">' . esc_html( wp_trim_words( get_the_excerpt(), absint( $args['excerpt'] ) ) ) . '</p>';
			}
			$html .= '<time class="se-post-card__date" datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>';
			$html .= '</div></article>';
		}
		wp_reset_postdata();
		$html .= '</div>';

		return self::section_head( $args['title'], $args['link'], $args['link_text'], $args['title_tag'] ) . $html;
	}

	/* ---------------------------------------------------------------------
	 * Header pieces
	 * ------------------------------------------------------------------ */

	/**
	 * Search form with live suggestions.
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function search( $args = array() ) {
		static $instance = 0;
		++$instance;

		$args = wp_parse_args(
			$args,
			array(
				'placeholder' => __( 'جستجوی محصول، برند یا کد کالا…', 'samadi-elementor-widgets' ),
			)
		);

		$id = 'se-ew-search-' . $instance;

		return sprintf(
			'<div class="se-ew-search" data-se-live-search><form role="search" method="get" class="se-search-form" action="%1$s"><label class="screen-reader-text" for="%2$s">%3$s</label><input type="search" id="%2$s" class="se-search-form__input" name="s" value="%4$s" placeholder="%5$s" autocomplete="off"><input type="hidden" name="post_type" value="product"><button type="submit" class="se-search-form__submit">%6$s<span class="screen-reader-text">%7$s</span></button></form></div>',
			esc_url( home_url( '/' ) ),
			esc_attr( $id ),
			esc_html__( 'جستجوی محصولات', 'samadi-elementor-widgets' ),
			esc_attr( get_search_query() ),
			esc_attr( $args['placeholder'] ),
			samadi_ew_icon( 'search', 20 ),
			esc_html__( 'جستجو', 'samadi-elementor-widgets' )
		);
	}

	/**
	 * Header tools: account, wishlist (if a wishlist plugin is active), cart with live count.
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function header_tools( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'account'         => true,
				'wishlist'        => true,
				'cart'            => true,
				'wholesale'       => true,
				'wholesale_label' => __( 'خرید عمده', 'samadi-elementor-widgets' ),
			)
		);

		$html = '<div class="se-header__actions se-ew-header-tools">';

		$quote = samadi_ew_quote_url();
		if ( $args['wholesale'] && $quote && $args['wholesale_label'] ) {
			$html .= '<a class="se-btn se-btn--primary se-header__wholesale" href="' . esc_url( preg_replace( '/#.*$/', '', $quote ) ) . '">' . esc_html( $args['wholesale_label'] ) . '</a>';
		}

		if ( $args['wishlist'] && function_exists( 'samadi_wishlist_url' ) && samadi_wishlist_url() ) {
			$html .= '<a class="se-icon-btn" href="' . esc_url( samadi_wishlist_url() ) . '">' . samadi_ew_icon( 'heart' ) . '<span class="screen-reader-text">' . esc_html__( 'علاقه‌مندی‌ها', 'samadi-elementor-widgets' ) . '</span></a>';
		}

		if ( $args['account'] ) {
			$html .= '<a class="se-icon-btn" href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">' . samadi_ew_icon( 'user' ) . '<span class="screen-reader-text">' . ( is_user_logged_in() ? esc_html__( 'حساب کاربری', 'samadi-elementor-widgets' ) : esc_html__( 'ورود / ثبت‌نام', 'samadi-elementor-widgets' ) ) . '</span></a>';
		}

		if ( $args['cart'] ) {
			$count = function_exists( 'samadi_wc_cart_count_html' ) ? samadi_wc_cart_count_html() : self::cart_count_html();
			$html .= '<a class="se-icon-btn se-header__cart" href="' . esc_url( wc_get_cart_url() ) . '">' . samadi_ew_icon( 'shopping-cart' ) . '<span class="screen-reader-text">' . esc_html__( 'سبد خرید', 'samadi-elementor-widgets' ) . '</span>' . $count . '</a>';
		}

		return $html . '</div>';
	}

	/**
	 * Cart count markup when the Samadi theme is not active (same selector so fragments update it).
	 *
	 * @return string
	 */
	public static function cart_count_html() {
		$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;

		return sprintf( '<span class="se-cart-count%s" data-count="%d">%s</span>', $count ? '' : ' is-empty', absint( $count ), esc_html( samadi_ew_number( $count ) ) );
	}

	/**
	 * Product categories mega menu.
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function mega_menu( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'label'    => __( 'همه دسته‌بندی‌ها', 'samadi-elementor-widgets' ),
				'limit'    => 12,
				'children' => 8,
			)
		);

		$parents = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'number'     => absint( $args['limit'] ),
				'exclude'    => array_filter( array( (int) get_option( 'default_product_cat' ) ) ),
			)
		);

		if ( is_wp_error( $parents ) || ! $parents ) {
			return '';
		}

		static $instance = 0;
		++$instance;
		$panel_id = 'se-ew-mega-' . $instance;

		$html  = '<div class="se-mega" data-se-mega>';
		$html .= '<button type="button" class="se-mega__toggle" aria-expanded="false" aria-controls="' . esc_attr( $panel_id ) . '">' . samadi_ew_icon( 'menu', 20 ) . '<span>' . esc_html( $args['label'] ) . '</span></button>';
		$html .= '<div class="se-mega__panel" id="' . esc_attr( $panel_id ) . '" hidden><ul class="se-mega__list">';

		foreach ( $parents as $parent ) {
			$thumb_id = (int) get_term_meta( $parent->term_id, 'thumbnail_id', true );
			$html    .= '<li class="se-mega__item"><a class="se-mega__parent" href="' . esc_url( get_term_link( $parent ) ) . '">';
			$html    .= '<span class="se-mega__thumb">' . ( $thumb_id ? wp_get_attachment_image( $thumb_id, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ) : '' ) . '</span>';
			$html    .= '<span class="se-mega__name">' . esc_html( $parent->name ) . '</span></a>';

			$children = $args['children'] ? get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'parent'     => $parent->term_id,
					'hide_empty' => true,
					'number'     => absint( $args['children'] ),
				)
			) : array();

			if ( $children && ! is_wp_error( $children ) ) {
				$html .= '<ul class="se-mega__children">';
				foreach ( $children as $child ) {
					$html .= '<li><a href="' . esc_url( get_term_link( $child ) ) . '">' . esc_html( $child->name ) . '</a></li>';
				}
				$html .= '</ul>';
			}
			$html .= '</li>';
		}

		$html .= '</ul></div></div>';

		return $html;
	}

	/* ---------------------------------------------------------------------
	 * Single product pieces (for Elementor Pro single product templates)
	 * ------------------------------------------------------------------ */

	/**
	 * Current product in context.
	 *
	 * @return WC_Product|null
	 */
	public static function current_product() {
		global $product;

		if ( $product instanceof WC_Product ) {
			return $product;
		}

		$maybe = wc_get_product( get_the_ID() );

		return $maybe ? $maybe : null;
	}

	/**
	 * Technical specifications table from real product attributes.
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function specs( $args = array() ) {
		$product = self::current_product();

		if ( ! $product ) {
			return self::is_editor() ? '<p class="se-notice">' . esc_html__( 'این ویجت مشخصات فنی محصول جاری را نمایش می‌دهد (در قالب صفحه محصول استفاده کنید).', 'samadi-elementor-widgets' ) . '</p>' : '';
		}

		$args = wp_parse_args( $args, array( 'title' => '' ) );

		ob_start();
		wc_display_product_attributes( $product );
		$table = ob_get_clean();

		if ( '' === trim( $table ) ) {
			return '';
		}

		return self::section_head( $args['title'], '', '', 'h3' ) . '<div class="se-specs woocommerce">' . $table . '</div>';
	}

	/**
	 * Badges for the current product: sale %, featured, stock.
	 *
	 * @return string
	 */
	public static function badges() {
		$product = self::current_product();

		if ( ! $product ) {
			return '';
		}

		$html = '';
		if ( $product->is_on_sale() ) {
			$html .= function_exists( 'samadi_wc_sale_badge' ) ? samadi_wc_sale_badge( $product ) : '<span class="se-badge se-badge--sale">' . esc_html__( 'فروش ویژه', 'samadi-elementor-widgets' ) . '</span>';
		}
		if ( $product->is_featured() ) {
			$html .= '<span class="se-badge se-badge--featured">' . esc_html__( 'ویژه', 'samadi-elementor-widgets' ) . '</span>';
		}
		if ( ! $product->is_in_stock() ) {
			$html .= '<span class="se-badge se-badge--out">' . esc_html__( 'ناموجود', 'samadi-elementor-widgets' ) . '</span>';
		}

		return $html ? '<div class="se-badges">' . $html . '</div>' : '';
	}

	/**
	 * Quote button for the current product (or general wholesale link).
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function quote_button( $args = array() ) {
		$args    = wp_parse_args( $args, array( 'label' => (string) samadi_ew_setting( 'quote_button_label' ) ) );
		$product = self::current_product();
		$url     = samadi_ew_quote_url( $product ? $product->get_id() : 0 );

		if ( ! $url ) {
			return self::is_editor() ? '<p class="se-notice">' . esc_html__( 'ابتدا صفحه خرید عمده/استعلام قیمت را در تنظیمات افزونه مشخص کنید.', 'samadi-elementor-widgets' ) . '</p>' : '';
		}

		return sprintf(
			'<a class="se-btn se-btn--outline se-btn--block se-quote-btn" href="%1$s">%2$s<span>%3$s</span></a>',
			esc_url( $url ),
			samadi_ew_icon( 'receipt', 20 ),
			esc_html( $args['label'] )
		);
	}

	/**
	 * Mobile bottom navigation (for sites not using the theme's built-in bar).
	 *
	 * @param array $args Args.
	 * @return string
	 */
	public static function mobile_nav( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'items' => array( 'home', 'shop', 'cart', 'wishlist', 'account' ),
			)
		);

		$map = array(
			'home'     => array( home_url( '/' ), __( 'خانه', 'samadi-elementor-widgets' ), 'house', is_front_page() ),
			'shop'     => array( wc_get_page_permalink( 'shop' ), __( 'دسته‌بندی‌ها', 'samadi-elementor-widgets' ), 'layout-grid', is_shop() || is_product_taxonomy() ),
			'cart'     => array( wc_get_cart_url(), __( 'سبد خرید', 'samadi-elementor-widgets' ), 'shopping-cart', is_cart() ),
			'wishlist' => array( function_exists( 'samadi_wishlist_url' ) ? samadi_wishlist_url() : '', __( 'علاقه‌مندی‌ها', 'samadi-elementor-widgets' ), 'heart', false ),
			'account'  => array( wc_get_page_permalink( 'myaccount' ), __( 'حساب کاربری', 'samadi-elementor-widgets' ), 'user', is_account_page() ),
		);

		$html = '<nav class="se-bottom-nav se-bottom-nav--widget" aria-label="' . esc_attr__( 'ناوبری سریع', 'samadi-elementor-widgets' ) . '"><ul class="se-bottom-nav__list">';
		foreach ( (array) $args['items'] as $key ) {
			if ( empty( $map[ $key ] ) || ! $map[ $key ][0] ) {
				continue;
			}
			list( $url, $label, $icon, $active ) = $map[ $key ];
			$count = 'cart' === $key ? ( function_exists( 'samadi_wc_cart_count_html' ) ? samadi_wc_cart_count_html() : self::cart_count_html() ) : '';
			$html .= sprintf(
				'<li class="se-bottom-nav__item"><a class="se-bottom-nav__link%1$s" href="%2$s"%3$s><span class="se-bottom-nav__icon">%4$s%5$s</span><span class="se-bottom-nav__label">%6$s</span></a></li>',
				$active ? ' is-active' : '',
				esc_url( $url ),
				$active ? ' aria-current="page"' : '',
				samadi_ew_icon( $icon ),
				$count,
				esc_html( $label )
			);
		}
		$html .= '</ul></nav>';

		return $html;
	}

	/**
	 * Store contact details from the theme Customizer (only filled fields are shown).
	 *
	 * @return string
	 */
	public static function contact() {
		if ( ! function_exists( 'samadi_option' ) ) {
			return self::is_editor() ? '<p class="se-notice">' . esc_html__( 'اطلاعات تماس از «نمایش ← سفارشی‌سازی ← تنظیمات صمدی الکتریک ← اطلاعات تماس» قالب صمدی خوانده می‌شود.', 'samadi-elementor-widgets' ) . '</p>' : '';
		}

		$rows = array();
		foreach ( array( 'contact_phone', 'contact_mobile' ) as $key ) {
			$value = (string) samadi_option( $key );
			if ( $value ) {
				$rows[] = array( 'phone', '<a href="tel:' . esc_attr( samadi_tel( $value ) ) . '" dir="ltr">' . esc_html( $value ) . '</a>' );
			}
		}
		$email = (string) samadi_option( 'contact_email' );
		if ( $email ) {
			$rows[] = array( 'mail', '<a href="mailto:' . esc_attr( antispambot( $email ) ) . '">' . esc_html( antispambot( $email ) ) . '</a>' );
		}
		$address = (string) samadi_option( 'contact_address' );
		if ( $address ) {
			$rows[] = array( 'map-pin', '<address>' . nl2br( esc_html( $address ) ) . '</address>' );
		}
		$hours = (string) samadi_option( 'contact_hours' );
		if ( $hours ) {
			$rows[] = array( 'clock', '<span>' . esc_html( $hours ) . '</span>' );
		}

		if ( ! $rows ) {
			return self::is_editor() ? '<p class="se-notice">' . esc_html__( 'هنوز اطلاعات تماسی در سفارشی‌سازی قالب وارد نشده است.', 'samadi-elementor-widgets' ) . '</p>' : '';
		}

		$html = '<ul class="se-contact">';
		foreach ( $rows as $row ) {
			$html .= '<li class="se-contact__item">' . samadi_ew_icon( $row[0], 18 ) . $row[1] . '</li>';
		}

		return $html . '</ul>';
	}

	/**
	 * Whether we are rendering inside the Elementor editor/preview.
	 *
	 * @return bool
	 */
	public static function is_editor() {
		if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance ) ) {
			return false;
		}

		$plugin = \Elementor\Plugin::$instance;

		return ( isset( $plugin->editor ) && $plugin->editor->is_edit_mode() ) || ( isset( $plugin->preview ) && $plugin->preview->is_preview_mode() );
	}
}
