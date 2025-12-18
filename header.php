<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php
  /* ============================
     SEO / SOCIAL META (Antena Tec)
     ============================ */

  global $post;

  $site_name  = get_bloginfo('name');
  $site_desc  = get_bloginfo('description');
  $site_url   = home_url('/');
  $theme_img  = get_template_directory_uri() . '/assets/img/social-default.jpg';

  if (is_singular() && isset($post)) {
    setup_postdata($post);

    $meta_title = get_the_title() . ' | ' . $site_name;
    $meta_desc  = wp_strip_all_tags(
      has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 25)
    );
    $meta_url   = get_permalink();
    $meta_img   = has_post_thumbnail()
      ? get_the_post_thumbnail_url($post->ID, 'large')
      : $theme_img;

    wp_reset_postdata();
  } else {
    // Home / arquivos
    $meta_title = $site_name . ' – ' . $site_desc;
    $meta_desc  = $site_desc;
    $meta_url   = $site_url;
    $meta_img   = $theme_img;
  }
  ?>

  <!-- Primary Meta -->
  <title><?php echo esc_html($meta_title); ?></title>
  <meta name="description" content="<?php echo esc_attr($meta_desc); ?>">

  <!-- Open Graph / Facebook / Instagram -->
  <meta property="og:type" content="<?php echo is_singular() ? 'article' : 'website'; ?>">
  <meta property="og:title" content="<?php echo esc_attr($meta_title); ?>">
  <meta property="og:description" content="<?php echo esc_attr($meta_desc); ?>">
  <meta property="og:url" content="<?php echo esc_url($meta_url); ?>">
  <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
  <meta property="og:image" content="<?php echo esc_url($meta_img); ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">

  <!-- Locale -->
  <meta property="og:locale" content="pt_BR">

  <!-- Twitter (extra, mas recomendado) -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo esc_attr($meta_title); ?>">
  <meta name="twitter:description" content="<?php echo esc_attr($meta_desc); ?>">
  <meta name="twitter:image" content="<?php echo esc_url($meta_img); ?>">

  <?php wp_head(); ?>
</head>


<body <?php body_class(); ?>>
<?php wp_body_open(); ?>


<header class="site-header bg-dark text-white">
  <div class="container">
    <nav class="navbar navbar-expand-lg navbar-dark py-3">
      <a class="navbar-brand fw-bold" href="<?php echo esc_url(home_url('/')); ?>">
        <?php
        if (function_exists('the_custom_logo') && has_custom_logo()) {
          the_custom_logo();
        } else {
          echo esc_html(get_bloginfo('name'));
        }
        ?>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#antenatecNavbar"
        aria-controls="antenatecNavbar" aria-expanded="false" aria-label="<?php echo esc_attr__('Abrir menu', 'antenatec'); ?>">
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
      </div>
    </nav>
  </div>
</header>




