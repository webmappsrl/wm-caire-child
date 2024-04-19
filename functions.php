<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

require('shortcodes/single_track.php');
require('shortcodes/single_poi.php');
require('shortcodes/grid_track.php');
require('shortcodes/grid_poi.php');
require('shortcodes/single_layer.php');

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if (!function_exists('chld_thm_cfg_locale_css')) :
    function chld_thm_cfg_locale_css($uri)
    {
        if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css'))
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

// END ENQUEUE PARENT ACTION

//Swiper Slider CSS da CDN
function child_theme_enqueue_swiper()
{
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'child_theme_enqueue_swiper');

//Font awesome
function load_font_awesome()
{
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'load_font_awesome');

//Slug
function wm_custom_slugify($title)
{
    $title = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
    $title = str_replace('–', '-', $title);
    $title = str_replace("’", '', $title);
    $title = preg_replace('!\s+!', ' ', $title);
    $slug = sanitize_title_with_dashes($title);
    return $slug;
}
