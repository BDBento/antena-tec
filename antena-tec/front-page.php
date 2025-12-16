<?php
/**
 * Template: Front Page (Home)
 */
get_header();

/* Helpers */
$img_fallback = get_template_directory_uri() . '/assets/img/placeholder.jpg';

$antenatec_get_img = function($post_id, $size = 'large') use ($img_fallback) {
  if (has_post_thumbnail($post_id)) return get_the_post_thumbnail_url($post_id, $size);
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

<main class="home">

 <?php
// 1 destaque grande (tag: destaque-principal)
$hero_main = new WP_Query([
  'post_type'           => 'post',
  'posts_per_page'      => 1,
  'ignore_sticky_posts' => true,
  'tag'                 => 'destaque-principal',
]);

$main_id = (!empty($hero_main->posts) && isset($hero_main->posts[0]->ID))
  ? (int) $hero_main->posts[0]->ID
  : 0;

// 3 destaques menores (tag: destaque), excluindo o principal
$hero_side = new WP_Query([
  'post_type'           => 'post',
  'posts_per_page'      => 3,
  'ignore_sticky_posts' => true,
  'tag'                 => 'destaque',
  'post__not_in'        => $main_id ? [$main_id] : [],
]);
?>

<section class="home-hero py-4">
  <div class="container">
    <div class="row g-3">

      <!-- Principal -->
      <div class="col-12 col-lg-8">
        <?php if ($hero_main->have_posts()) : $hero_main->the_post(); ?>
          <a class="hero-card hero-card--main d-block text-decoration-none" href="<?php the_permalink(); ?>">
            <?php $bg = $antenatec_get_img(get_the_ID(), 'large'); ?>
            <div class="hero-media" style="background-image:url('<?php echo esc_url($bg); ?>');">
              <div class="hero-overlay">
                <div class="hero-meta small">
                  <span><?php echo esc_html(get_the_date()); ?></span>
                </div>
                <h2 class="hero-title"><?php the_title(); ?></h2>
              </div>
            </div>
          </a>
        <?php endif; wp_reset_postdata(); ?>
      </div>

      <!-- Laterais -->
      <div class="col-12 col-lg-4">
        <div class="d-flex flex-column gap-3">
          <?php if ($hero_side->have_posts()) : ?>
            <?php while ($hero_side->have_posts()) : $hero_side->the_post(); ?>
              <a class="hero-card hero-card--side d-flex text-decoration-none" href="<?php the_permalink(); ?>">
                <?php $thumb = $antenatec_get_img(get_the_ID(), 'medium'); ?>
                <div class="hero-side-thumb" style="background-image:url('<?php echo esc_url($thumb); ?>');"></div>
                <div class="hero-side-content">
                  <div class="hero-meta small"><?php echo esc_html(get_the_date()); ?></div>
                  <div class="hero-side-title"><?php the_title(); ?></div>
                </div>
              </a>
            <?php endwhile; ?>
          <?php endif; wp_reset_postdata(); ?>
        </div>
      </div>

    </div>
  </div>
</section>

  <!-- ADS (topo) -->
  <section class="home-ads py-4">
    <div class="container">
      <div class="ads-box d-flex align-items-center justify-content-center">
        <span>ADS</span>
      </div>
    </div>
  </section>

  <!-- BARRA (ícones / moedas / info rápida) -->
  <section class="home-ticker pb-4">
    <div class="container">
      <div class="ticker-box d-flex flex-wrap gap-3 justify-content-between">
        <?php
        // placeholder visual; depois você troca por CPT/widget/dados reais
        for ($i=0; $i<6; $i++): ?>
          <div class="ticker-item d-flex align-items-center gap-2">
            <span class="ticker-dot"></span>
            <div>
              <div class="ticker-label">Bitcoin</div>
              <div class="ticker-value">R$ 000.000</div>
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  <!-- SEMANAL + SIDEBAR (Em Alta) -->
  <section class="home-weekly py-4">
    <div class="container">
      <div class="row g-4">

        <!-- Lista Semanal -->
        <div class="col-12 col-lg-8">
          <div class="section-head d-flex align-items-center justify-content-between mb-3">
            <h3 class="section-title m-0">SEMANAL</h3>
          </div>

          <?php
          $weekly = new WP_Query([
            'post_type'           => 'post',
            'posts_per_page'      => 4,
            'ignore_sticky_posts' => true,
            'offset'              => 4,
          ]);
          ?>

          <div class="weekly-list d-flex flex-column gap-3">
            <?php if ($weekly->have_posts()) : while ($weekly->have_posts()) : $weekly->the_post(); ?>
              <article class="weekly-item d-flex gap-3">
                <?php $img = $antenatec_get_img(get_the_ID(), 'medium'); ?>
                <a class="weekly-thumb" href="<?php the_permalink(); ?>" style="background-image:url('<?php echo esc_url($img); ?>');"></a>
                <div class="weekly-content">
                  <h4 class="weekly-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                  <p class="weekly-excerpt"><?php echo esc_html($antenatec_excerpt(get_the_ID(), 22)); ?></p>
                  <div class="weekly-meta small"><?php echo esc_html(get_the_date()); ?></div>
                </div>
              </article>
            <?php endwhile; endif; wp_reset_postdata(); ?>
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="col-12 col-lg-4">
          <div class="sidebar-sticky">

            <div class="ads-box ads-box--side d-flex align-items-center justify-content-center mb-3">
              <span>ADS</span>
            </div>

            <div class="sidebar-card">
              <h4 class="sidebar-title">EM ALTA</h4>

              <?php
              $trending = new WP_Query([
                'post_type'           => 'post',
                'posts_per_page'      => 5,
                'ignore_sticky_posts' => true,
                'orderby'             => 'date',
                'order'               => 'DESC',
              ]);
              ?>

              <div class="trending-list d-flex flex-column gap-2">
                <?php if ($trending->have_posts()) : while ($trending->have_posts()) : $trending->the_post(); ?>
                  <?php $timg = $antenatec_get_img(get_the_ID(), 'thumbnail'); ?>
                  <a class="trending-item d-flex gap-2 text-decoration-none" href="<?php the_permalink(); ?>">
                    <span class="trending-thumb" style="background-image:url('<?php echo esc_url($timg); ?>');"></span>
                    <span class="trending-title"><?php the_title(); ?></span>
                  </a>
                <?php endwhile; endif; wp_reset_postdata(); ?>
              </div>
            </div>

          </div>
        </aside>

      </div>
    </div>
  </section>

  <!-- ADS (meio) -->
  <section class="home-ads py-4">
    <div class="container">
      <div class="ads-box d-flex align-items-center justify-content-center">
        <span>ADS</span>
      </div>
    </div>
  </section>

  <!-- CHOICE DAY (grade de produtos) -->
  <section class="home-choice py-4">
    <div class="container">
      <div class="section-head mb-3">
        <h3 class="section-title m-0">CHOICE DAY</h3>
      </div>

      <div class="row g-3">
        <?php
        // Placeholder para “cards de produto” (pode virar CPT/shortcode/afiliado depois)
        for ($i=0; $i<6; $i++): ?>
          <div class="col-6 col-md-4 col-lg-2">
            <div class="product-card">
              <div class="product-thumb" style="background-image:url('<?php echo esc_url($img_fallback); ?>');"></div>
              <div class="product-info">
                <div class="product-price">R$ 25,00</div>
                <div class="product-sub small">frete grátis</div>
              </div>
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  <!-- PROMOÇÕES / GAMES / DESTAQUE -->
  <section class="home-mix py-4">
    <div class="container">
      <div class="row g-4">

        <div class="col-12 col-lg-3">
          <h3 class="section-title m-0 mb-3">PROMOÇÕES</h3>
          <div class="mini-list d-flex flex-column gap-2">
            <?php for ($i=0; $i<4; $i++): ?>
              <div class="mini-item d-flex gap-2 align-items-center">
                <span class="mini-thumb" style="background-image:url('<?php echo esc_url($img_fallback); ?>');"></span>
                <div>
                  <div class="mini-title">Elden Ring</div>
                  <div class="mini-price small">R$ 59,90</div>
                </div>
              </div>
            <?php endfor; ?>
          </div>
        </div>

        <div class="col-12 col-lg-3">
          <h3 class="section-title m-0 mb-3">GAMES</h3>
          <div class="mini-posts d-flex flex-column gap-2">
            <?php
            $games = new WP_Query([
              'post_type'           => 'post',
              'posts_per_page'      => 3,
              'ignore_sticky_posts' => true,
              'category_name'       => 'games',
            ]);
            ?>
            <?php if ($games->have_posts()) : while ($games->have_posts()) : $games->the_post(); ?>
              <a class="game-item d-flex gap-2 text-decoration-none" href="<?php the_permalink(); ?>">
                <span class="badge bg-primary">GUIA</span>
                <span class="game-title"><?php the_title(); ?></span>
              </a>
            <?php endwhile; endif; wp_reset_postdata(); ?>
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <?php
          $featured = new WP_Query([
            'post_type'           => 'post',
            'posts_per_page'      => 1,
            'ignore_sticky_posts' => true,
            'offset'              => 10,
          ]);
          ?>
          <?php if ($featured->have_posts()) : $featured->the_post(); ?>
            <?php $fimg = $antenatec_get_img(get_the_ID(), 'large'); ?>
            <a class="feature-wide d-block text-decoration-none" href="<?php the_permalink(); ?>">
              <div class="feature-wide-media" style="background-image:url('<?php echo esc_url($fimg); ?>');">
                <div class="feature-wide-overlay">
                  <div class="small"><?php echo esc_html(get_the_date()); ?></div>
                  <h3 class="feature-wide-title"><?php the_title(); ?></h3>
                </div>
              </div>
            </a>
          <?php endif; wp_reset_postdata(); ?>
        </div>

      </div>
    </div>
  </section>

  <!-- REVIEWS -->
  <section class="home-reviews py-4">
    <div class="container">
      <div class="section-head mb-3">
        <h3 class="section-title m-0">REVIEWS</h3>
      </div>

      <?php
      $reviews = new WP_Query([
        'post_type'           => 'post',
        'posts_per_page'      => 4,
        'ignore_sticky_posts' => true,
        'category_name'       => 'reviews',
      ]);
      ?>

      <div class="row g-3">
        <?php if ($reviews->have_posts()) : while ($reviews->have_posts()) : $reviews->the_post(); ?>
          <?php $rimg = $antenatec_get_img(get_the_ID(), 'large'); ?>
          <div class="col-6 col-lg-3">
            <a class="review-card d-block text-decoration-none" href="<?php the_permalink(); ?>">
              <div class="review-media" style="background-image:url('<?php echo esc_url($rimg); ?>');">
                <div class="review-overlay">
                  <span class="badge bg-primary">REVIEW</span>
                  <div class="review-title"><?php the_title(); ?></div>
                </div>
              </div>
            </a>
          </div>
        <?php endwhile; endif; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>

  <!-- NOVIDADES (lista + destaque) -->
  <section class="home-news py-5">
    <div class="container">
      <div class="row g-4 align-items-stretch">

        <div class="col-12 col-lg-4">
          <h3 class="section-title text-white m-0 mb-3">Novidades</h3>

          <?php
          $news_list = new WP_Query([
            'post_type'           => 'post',
            'posts_per_page'      => 3,
            'ignore_sticky_posts' => true,
            'offset'              => 12,
          ]);
          ?>

          <div class="news-list d-flex flex-column gap-3">
            <?php if ($news_list->have_posts()) : while ($news_list->have_posts()) : $news_list->the_post(); ?>
              <a class="news-item d-flex gap-3 text-decoration-none" href="<?php the_permalink(); ?>">
                <?php $nimg = $antenatec_get_img(get_the_ID(), 'thumbnail'); ?>
                <span class="news-thumb" style="background-image:url('<?php echo esc_url($nimg); ?>');"></span>
                <span class="news-title text-white"><?php the_title(); ?></span>
              </a>
            <?php endwhile; endif; wp_reset_postdata(); ?>
          </div>
        </div>

        <div class="col-12 col-lg-8">
          <?php
          $news_feat = new WP_Query([
            'post_type'           => 'post',
            'posts_per_page'      => 1,
            'ignore_sticky_posts' => true,
            'offset'              => 15,
          ]);
          ?>
          <?php if ($news_feat->have_posts()) : $news_feat->the_post(); ?>
            <?php $nfimg = $antenatec_get_img(get_the_ID(), 'large'); ?>
            <a class="news-feature d-block text-decoration-none" href="<?php the_permalink(); ?>">
              <div class="news-feature-media" style="background-image:url('<?php echo esc_url($nfimg); ?>');">
                <div class="news-feature-overlay">
                  <div class="small text-white"><?php echo esc_html(get_the_date()); ?></div>
                  <h3 class="news-feature-title text-white"><?php the_title(); ?></h3>
                </div>
              </div>
            </a>
          <?php endif; wp_reset_postdata(); ?>
        </div>

      </div>
    </div>
  </section>

  <!-- DIÁRIO -->
  <section class="home-daily py-5">
    <div class="container">
      <div class="row g-4">

        <aside class="col-12 col-lg-3">
          <div class="ads-box ads-box--side d-flex align-items-center justify-content-center mb-3">
            <span>ADS</span>
          </div>

          <div class="sidebar-card">
            <h4 class="sidebar-title">EM ALTA</h4>
            <?php
            $trending2 = new WP_Query([
              'post_type'           => 'post',
              'posts_per_page'      => 5,
              'ignore_sticky_posts' => true,
              'offset'              => 3,
            ]);
            ?>
            <div class="trending-list d-flex flex-column gap-2">
              <?php if ($trending2->have_posts()) : while ($trending2->have_posts()) : $trending2->the_post(); ?>
                <?php $t2img = $antenatec_get_img(get_the_ID(), 'thumbnail'); ?>
                <a class="trending-item d-flex gap-2 text-decoration-none" href="<?php the_permalink(); ?>">
                  <span class="trending-thumb" style="background-image:url('<?php echo esc_url($t2img); ?>');"></span>
                  <span class="trending-title"><?php the_title(); ?></span>
                </a>
              <?php endwhile; endif; wp_reset_postdata(); ?>
            </div>
          </div>
        </aside>

        <div class="col-12 col-lg-9">
          <div class="section-head mb-3">
            <h3 class="section-title m-0">DIÁRIO</h3>
          </div>

          <?php
          $daily = new WP_Query([
            'post_type'           => 'post',
            'posts_per_page'      => 4,
            'ignore_sticky_posts' => true,
            'offset'              => 18,
          ]);
          ?>

          <div class="row g-3">
            <?php if ($daily->have_posts()) : while ($daily->have_posts()) : $daily->the_post(); ?>
              <?php $dimg = $antenatec_get_img(get_the_ID(), 'large'); ?>
              <div class="col-12 col-md-6">
                <article class="daily-card d-flex gap-3">
                  <a class="daily-thumb" href="<?php the_permalink(); ?>" style="background-image:url('<?php echo esc_url($dimg); ?>');"></a>
                  <div class="daily-content">
                    <h4 class="daily-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                    <div class="daily-meta small"><?php echo esc_html(get_the_date()); ?></div>
                  </div>
                </article>
              </div>
            <?php endwhile; endif; wp_reset_postdata(); ?>
          </div>

        </div>
      </div>

      <div class="pt-4">
        <div class="ads-box d-flex align-items-center justify-content-center">
          <span>ADS</span>
        </div>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
