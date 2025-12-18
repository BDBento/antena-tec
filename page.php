<?php
/**
 * Template: Page
 */
get_header();
?>

<main class="page-default py-5">
  <div class="container">

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

      <?php
      $cover = has_post_thumbnail()
        ? get_the_post_thumbnail_url(get_the_ID(), 'full')
        : '';
      ?>

      <!-- HERO (opcional: só aparece se tiver imagem destacada) -->
      <?php if ($cover) : ?>
        <section class="page-hero mb-4">
          <div class="page-hero-card" style="background-image:url('<?php echo esc_url($cover); ?>');">
            <div class="page-hero-overlay">
              <h1 class="page-title"><?php the_title(); ?></h1>
            </div>
          </div>
        </section>
      <?php else : ?>
        <header class="page-header mb-4">
          <h1 class="page-title"><?php the_title(); ?></h1>
        </header>
      <?php endif; ?>

      <div class="row g-4 align-items-start">

        <!-- Conteúdo -->
        <article class="col-12 col-lg-8">
          <div class="page-content">
            <?php the_content(); ?>
          </div>

          <?php
          // Paginação de páginas (quando usar <!--nextpage-->)
          wp_link_pages([
            'before' => '<div class="page-links mt-4"><span class="me-2">Páginas:</span>',
            'after'  => '</div>',
          ]);
          ?>
        </article>

        <!-- Sidebar (opcional) -->
        <aside class="col-12 col-lg-4">
          <div class="page-aside">

            <div class="ads-box ads-box--square d-flex align-items-center justify-content-center mb-3">
              <?php
              // Se quiser ads em páginas, reutilize um slot existente:
              // antenatec_ad('single_sidebar');
              ?>
              <span>ADS</span>
            </div>

            <?php
            // Se você tiver sidebar.php e widgets cadastrados, pode usar:
            // if (is_active_sidebar('sidebar-1')) dynamic_sidebar('sidebar-1');
            ?>

          </div>
        </aside>

      </div>

    <?php endwhile; endif; ?>

  </div>
</main>

<?php get_footer(); ?>
