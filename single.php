<?php
/**
 * Template: Single Post
 */
get_header();

/* Helpers (mesmos do front-page; pode mover para functions.php depois) */
$img_fallback = get_template_directory_uri() . '/assets/img/placeholder.jpg';

$antenatec_get_img = function($post_id, $size = 'large') use ($img_fallback) {
  $thumb_id = get_post_thumbnail_id($post_id);
  if ($thumb_id) {
    $url = wp_get_attachment_image_url($thumb_id, $size);
    if ($url) return $url;
  }
  return $img_fallback;
};

$antenatec_excerpt = function($post_id, $len = 18) {
  $txt = get_the_excerpt($post_id);
  if (!$txt) $txt = wp_strip_all_tags(get_the_content(null, false, $post_id));
  $words = preg_split('/\s+/', trim($txt));
  if (count($words) <= $len) return implode(' ', $words);
  return implode(' ', array_slice($words, 0, $len)) . '…';
};
?>

<main class="single-post py-5">
  <div class="container">

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <?php
      $post_id = get_the_ID();
      $cover   = $antenatec_get_img($post_id, 'full');

      $cats = get_the_category($post_id);
      $cat_name = (!empty($cats) && !is_wp_error($cats)) ? $cats[0]->name : '';
      ?>

      <!-- HERO -->
      <section class="single-hero mb-4">
        <div class="single-hero-card" style="background-image:url('<?php echo esc_url($cover); ?>');">
          <div class="single-hero-overlay">
            <h1 class="single-title"><?php the_title(); ?></h1>

            <div class="single-meta">
              <?php if ($cat_name) : ?>
                <span class="single-meta-item"><?php echo esc_html($cat_name); ?></span>
                <span class="single-meta-dot">•</span>
              <?php endif; ?>
              <span class="single-meta-item"><?php echo esc_html(get_the_date('d M y')); ?></span>
            </div>
          </div>
        </div>
      </section>

      <!-- CONTEÚDO + ADS -->
      <section class="single-body">
        <div class="row g-4 align-items-start">

          <!-- Conteúdo -->
          <article class="col-12 col-lg-8">
            <div class="single-content">
              <?php the_content(); ?>
            </div>
          </article>

          <!-- ADS lateral -->
          <aside class="col-12 col-lg-4">
            <div class="single-aside">
              <div class="ads-box ads-box--square d-flex align-items-center justify-content-center">
                <span>ADS</span>
              </div>
            </div>
          </aside>

        </div>
      </section>

      <?php
      // Relacionados (Veja também) por categoria (prioridade) ou por tag (fallback)
      $cat_ids = [];
      if (!empty($cats) && !is_wp_error($cats)) {
        $cat_ids = wp_list_pluck($cats, 'term_id');
      }

      $related_args = [
        'post_type'           => 'post',
        'posts_per_page'      => 3,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'post__not_in'        => [$post_id],
      ];

      if (!empty($cat_ids)) {
        $related_args['category__in'] = $cat_ids;
      } else {
        $tags = wp_get_post_tags($post_id, ['fields' => 'slugs']);
        if (!empty($tags)) $related_args['tag_slug__in'] = $tags;
      }

      $related = new WP_Query($related_args);
      ?>

      <!-- VEJA TAMBÉM -->
      <section class="single-related mt-5">
        <h3 class="single-section-title mb-3">Veja Tambem</h3>

        <div class="row g-3">
          <?php if ($related->have_posts()) : while ($related->have_posts()) : $related->the_post(); ?>
            <?php $rimg = $antenatec_get_img(get_the_ID(), 'large'); ?>
            <div class="col-12 col-md-4">
              <a class="related-card d-block text-decoration-none" href="<?php the_permalink(); ?>">
                <div class="related-thumb" style="background-image:url('<?php echo esc_url($rimg); ?>');"></div>
                <div class="related-body">
                  <h4 class="related-title"><?php the_title(); ?></h4>
                  <div class="related-date"><?php echo esc_html(get_the_date('j M Y')); ?></div>
                </div>
              </a>
            </div>
          <?php endwhile; else: ?>
            <div class="col-12">
              <p class="text-muted mb-0">Nenhuma sugestão encontrada.</p>
            </div>
          <?php endif; wp_reset_postdata(); ?>
        </div>
      </section>

      <!-- ADS grande -->
      <section class="single-ads mt-4">
        <div class="ads-box ads-box--wide d-flex align-items-center justify-content-center">
          <span>ADS</span>
        </div>
      </section>

    <?php endwhile; endif; ?>

  </div>
</main>

<?php get_footer(); ?>
