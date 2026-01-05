<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php
  /**
   * SEO / SOCIAL META (Antena Tec)
   * - Remove <title> manual (evita duplicar com WP/SEO plugins)
   * - Mantém description + OG/Twitter “na mão”
   * - Sanitiza termo de busca placeholder {search_term_string}
   */

  global $post;

  $site_name = get_bloginfo('name');
  $site_desc = get_bloginfo('description');
  $site_url  = home_url('/');
  $theme_img = get_template_directory_uri() . '/assets/img/social-default.png';

  // Termo de busca (sanitiza placeholder)
  $search_q = '';
  if ( is_search() ) {
    $search_q = get_search_query();
    if ( $search_q === '{search_term_string}' || stripos($search_q, '{search_term_string}') !== false ) {
      $search_q = '';
    }
  }

  if ( is_singular() && isset($post) ) {
    setup_postdata($post);

    $meta_title = get_the_title() . ' | ' . $site_name;
    $meta_desc  = wp_strip_all_tags(
      has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags(get_the_content()), 25 )
    );
    $meta_url = get_permalink();
    $meta_img = has_post_thumbnail()
      ? get_the_post_thumbnail_url($post->ID, 'large')
      : $theme_img;

    wp_reset_postdata();

  } elseif ( is_search() ) {

    $meta_title = $search_q
      ? ('Busca por "' . $search_q . '" | ' . $site_name)
      : ('Busca | ' . $site_name);

    $meta_desc = $search_q
      ? ('Resultados de busca para: ' . $search_q . '.')
      : ('Resultados de busca no site.');

    $meta_url = $site_url . '?s=' . rawurlencode($search_q);
    $meta_img = $theme_img;

  } else {

    // Home / arquivos
    $meta_title = $site_name . ' – ' . $site_desc;
    $meta_desc  = $site_desc;
    $meta_url   = $site_url;
    $meta_img   = $theme_img;
  }

  // Fallback
  if ( empty($meta_desc) ) $meta_desc = $site_desc;
  ?>

  <!-- Primary Meta -->
  <meta name="description" content="<?php echo esc_attr($meta_desc); ?>">

  <!-- Open Graph -->
  <meta property="og:type" content="<?php echo esc_attr( is_singular() ? 'article' : 'website' ); ?>">
  <meta property="og:title" content="<?php echo esc_attr($meta_title); ?>">
  <meta property="og:description" content="<?php echo esc_attr($meta_desc); ?>">
  <meta property="og:url" content="<?php echo esc_url($meta_url); ?>">
  <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
  <meta property="og:image" content="<?php echo esc_url($meta_img); ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:locale" content="pt_BR">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo esc_attr($meta_title); ?>">
  <meta name="twitter:description" content="<?php echo esc_attr($meta_desc); ?>">
  <meta name="twitter:image" content="<?php echo esc_url($meta_img); ?>">

  <?php
  /**
   * Deixe o WordPress/SEO plugin gerar o <title>.
   * Garanta no functions.php: add_theme_support('title-tag');
   */
  wp_head();
  ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header bg-dark text-white">
  <div class="container">
    <nav class="navbar navbar-expand-lg navbar-dark py-3">

      <?php if ( function_exists('the_custom_logo') && has_custom_logo() ) : ?>
        <div class="navbar-brand m-0 p-0 d-flex align-items-center">
          <?php the_custom_logo(); ?>
        </div>
      <?php else : ?>
        <a class="navbar-brand fw-bold" href="<?php echo esc_url(home_url('/')); ?>">
          <?php echo esc_html(get_bloginfo('name')); ?>
        </a>
      <?php endif; ?>

      <button class="navbar-toggler" type="button"
              data-bs-toggle="collapse"
              data-bs-target="#antenatecNavbar"
              aria-controls="antenatecNavbar"
              aria-expanded="false"
              aria-label="<?php echo esc_attr__('Abrir menu', 'antenatec'); ?>">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="antenatecNavbar">

        <?php
        wp_nav_menu([
          'theme_location' => 'menu-principal',
          'container'      => false,
          'menu_class'     => 'navbar-nav ms-auto gap-lg-3',
          'fallback_cb'    => '__return_false',
          'depth'          => 2,
        ]);
        ?>

        <!-- BUSCA -->
        <form role="search"
              method="get"
              class="d-flex ms-lg-3 mt-3 mt-lg-0"
              action="<?php echo esc_url(home_url('/')); ?>">

          <input type="search"
                 class="form-control form-control-sm me-2"
                 name="s"
                 placeholder="Buscar…"
                 value="<?php echo esc_attr(
                   (get_search_query() === '{search_term_string}') ? '' : get_search_query()
                 ); ?>"
                 aria-label="<?php echo esc_attr__('Buscar no site', 'antenatec'); ?>">

          <button class="btn btn-outline-light btn-sm" type="submit">
            Buscar
          </button>
        </form>

      </div>
    </nav>
  </div>
</header>
