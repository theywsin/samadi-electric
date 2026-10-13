<?php
/**
 * Document head + Elementor header for classic themes.
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="shf-page site">
<?php SHF_Renderer::render( 'header' ); ?>
