<?php
/**
 * Template Name: No Header/Footer
 * Template Post Type: page, post
 *
 * The file name matches bb-theme's so pages using it keep it after a switch.
 * Beaver_Light_Chrome::show_header_footer() reads the template and drops both slots.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

// locate_template() prefers a child theme's singular.php over this theme's.
locate_template( 'singular.php', true, false );
