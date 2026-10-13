<?php
/**
 * Post archive / blog index / search results — magazine layout.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_is_search = ! empty( $args['search'] );
$samadi_blog_id   = (int) get_option( 'page_for_posts' );
$samadi_blog_url  = $samadi_blog_id ? get_permalink( $samadi_blog_id ) : home_url( '/' );

if ( $samadi_is_search ) {
	/* translators: %s: search query. */
	$samadi_title = sprintf( __( 'نتایج جستجو برای «%s»', 'samadi-electric' ), get_search_query() );
} elseif ( is_home() && ! is_front_page() ) {
	$samadi_title = single_post_title( '', false );
} elseif ( is_category() || is_tag() || is_tax() ) {
	$samadi_title = single_term_title( '', false );
} elseif ( is_archive() ) {
	$samadi_title = get_the_archive_title();
} else {
	$samadi_title = __( 'مجله برق و روشنایی', 'samadi-electric' );
}

$samadi_desc = $samadi_is_search ? '' : get_the_archive_description();

if ( $samadi_is_search ) {
	global $wp_query;
	/* translators: %s: number of results. */
	$samadi_desc = '<p>' . esc_html( sprintf( __( '%s نتیجه پیدا شد.', 'samadi-electric' ), samadi_number( (int) $wp_query->found_posts ) ) ) . '</p>';
}

$samadi_cats = $samadi_is_search ? array() : get_categories(
	array(
		'hide_empty' => true,
		'number'     => 8,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
?>
<main id="se-main" class="se-main se-blog">
	<div class="se-container">
		<?php samadi_breadcrumb(); ?>
		<header class="se-blog__header">
			<h1 class="se-blog__title"><?php echo wp_kses_post( $samadi_title ); ?></h1>
			<?php if ( $samadi_desc ) : ?>
				<div class="se-blog__desc"><?php echo wp_kses_post( $samadi_desc ); ?></div>
			<?php endif; ?>
			<?php if ( count( $samadi_cats ) > 1 ) : ?>
				<nav class="se-chips" aria-label="<?php esc_attr_e( 'دسته‌بندی مطالب', 'samadi-electric' ); ?>">
					<a class="se-chip<?php echo is_home() ? ' is-active' : ''; ?>" href="<?php echo esc_url( $samadi_blog_url ); ?>"<?php echo is_home() ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'همه مطالب', 'samadi-electric' ); ?></a>
					<?php foreach ( $samadi_cats as $samadi_cat ) : ?>
						<?php $samadi_current = is_category( $samadi_cat->term_id ); ?>
						<a class="se-chip<?php echo $samadi_current ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $samadi_cat ) ); ?>"<?php echo $samadi_current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $samadi_cat->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="se-post-grid se-post-grid--journal se-post-grid--archive">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/archive/post-card', null, array( 'heading' => 'h2', 'style' => 'journal' ) );
				endwhile;
				?>
			</div>
			<?php samadi_pagination(); ?>
		<?php else : ?>
			<section class="se-empty">
				<span class="se-empty__icon"><?php samadi_icon( 'search', array( 'size' => 32 ) ); ?></span>
				<h2 class="se-empty__title"><?php esc_html_e( 'مطلبی پیدا نشد', 'samadi-electric' ); ?></h2>
				<p class="se-empty__text"><?php esc_html_e( 'عبارت دیگری را جستجو کنید یا از دسته‌بندی محصولات دیدن کنید.', 'samadi-electric' ); ?></p>
				<div class="se-empty__search"><?php get_search_form(); ?></div>
				<?php if ( samadi_is_wc() ) : ?>
					<div class="se-empty__actions">
						<a class="se-btn se-btn--primary" href="<?php echo esc_url( samadi_shop_url() ); ?>"><?php esc_html_e( 'مشاهده محصولات', 'samadi-electric' ); ?><?php samadi_icon( 'arrow-left', array( 'size' => 18 ) ); ?></a>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>
	</div>
</main>
