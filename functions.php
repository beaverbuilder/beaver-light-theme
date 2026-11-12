<?php
/**
 * Beaver Light bootstrap.
 *
 * A thin loader: behavior lives in single-purpose files under inc/. The theme
 * deliberately never defines FL_THEME_VERSION — that constant is how Beaver
 * Themer and the builder recognize bb-theme, and claiming it would load
 * bb-theme's Themer shim and drop the builder's button defaults.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

define( 'BEAVER_LIGHT_VERSION', '0.1.0-demo' );
define( 'BEAVER_LIGHT_DIR', get_template_directory() );
define( 'BEAVER_LIGHT_URI', get_template_directory_uri() );

require BEAVER_LIGHT_DIR . '/inc/setup.php';
require BEAVER_LIGHT_DIR . '/inc/template-tags.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-hooks.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-compat-fl.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-themer.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-chrome.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-global-styles.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-design-system.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-color.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-design-tokens.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-color-palette.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-color-rules.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-color-inference.php';
require BEAVER_LIGHT_DIR . '/inc/class-beaver-light-styles.php';

Beaver_Light_Compat_FL::init();
Beaver_Light_Themer::init();
Beaver_Light_Chrome::init();
Beaver_Light_Design_System::init();
Beaver_Light_Styles::init();
