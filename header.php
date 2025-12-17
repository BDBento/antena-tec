<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
