<?php
/**
 * Beaver Light Theme bootstrap.
 *
 * A thin loader: behavior lives in single-purpose files under inc/. The theme
 * deliberately never defines FL_THEME_VERSION — that constant is how Beaver
 * Themer and the builder recognize bb-theme, and claiming it would load
 * bb-theme's Themer shim and drop the builder's button defaults.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

define( 'BLT_VERSION', '0.1.0-demo' );
define( 'BLT_DIR', get_template_directory() );
define( 'BLT_URI', get_template_directory_uri() );

require BLT_DIR . '/inc/setup.php';
require BLT_DIR . '/inc/template-tags.php';
require BLT_DIR . '/inc/class-blt-hooks.php';
require BLT_DIR . '/inc/class-blt-compat-fl.php';
require BLT_DIR . '/inc/class-blt-themer.php';
require BLT_DIR . '/inc/class-blt-chrome.php';
require BLT_DIR . '/inc/class-blt-global-styles.php';
require BLT_DIR . '/inc/class-blt-design-system.php';
require BLT_DIR . '/inc/class-blt-styles.php';

BLT_Compat_FL::init();
BLT_Themer::init();
BLT_Chrome::init();
BLT_Design_System::init();
BLT_Styles::init();
