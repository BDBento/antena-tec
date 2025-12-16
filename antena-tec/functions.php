<?php
if (!defined('ABSPATH')) exit;

function antenatec_setup() {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);

  register_nav_menus([
    'menu-principal' => __('Menu principal', 'antenatec'),
    'menu-footer'    => __('Menu do rodapé', 'antenatec'),
  ]);

  // (opcional) logo via Customizer
  add_theme_support('custom-logo', [
    'height'      => 60,
    'width'       => 200,
    'flex-height' => true,
    'flex-width'  => true,
  ]);
}
add_action('after_setup_theme', 'antenatec_setup');

function antenatec_assets() {
  $ver = wp_get_theme()->get('Version');

  // Bootstrap 5.3 (CSS)
  wp_enqueue_style(
    'bootstrap',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    [],
    '5.3.3'
  );

  /**
   * Tailwind (compilado no seu tema)
   * Gere um arquivo tipo: /assets/css/tailwind.css
   */
  wp_enqueue_style(
    'antenatec-tailwind',
    get_template_directory_uri() . '/assets/css/tailwind.css',
    [],
    $ver
  );

  // CSS do tema (se você usar)
  wp_enqueue_style(
    'antenatec-main',
    get_template_directory_uri() . '/assets/css/main.css',
    ['bootstrap','antenatec-tailwind'],
    $ver
  );

  // Bootstrap 5.3 (bundle com Popper)
  wp_enqueue_script(
    'bootstrap',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    [],
    '5.3.3',
    true
  );

  // JS do tema (fetch/REST; sem jQuery)
  wp_enqueue_script(
    'antenatec-main',
    get_template_directory_uri() . '/assets/js/main.js',
    [],
    $ver,
    true
  );
}
add_action('wp_enqueue_scripts', 'antenatec_assets');


add_theme_support('post-thumbnails');