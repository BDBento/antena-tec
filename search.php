<?php
/**
 * Template de resultados de busca (search.php) — Tema Antena Tec
 * Coloque este arquivo na raiz do tema: /wp-content/themes/antenatec/search.php
 */

if ( ! defined('ABSPATH') ) exit;

get_header();

// Termo buscado
$search_query = get_search_query();

// Total de resultados (WordPress define $wp_query globalmente)
global $wp_query;
$total_results = isset($wp_query->found_posts) ? (int) $wp_query->found_posts : 0;

/**
 * Render de anúncio “in-feed” (tenta usar o que existir no tema)
 * - Se você tiver uma função/shortcode próprio, ajuste aqui.
 */
function antenatec_render_infeed_ad($slot = 'search_infeed') {
	// 1) Se existir uma função no seu tema para ads
	if ( function_exists('antenatec_render_ad') ) {
		// Ex: antenatec_render_ad('infeed') ou antenatec_render_ad($slot)
		return antenatec_render_ad($slot);
	}

	// 2) Se existir um template-part de anúncio
	$tpl = locate_template('template-parts/ad-infeed.php');
	if ( $tpl ) {
		ob_start();
		include $tpl;
		return ob_get_clean();
	}

	// 3) Se você usa ads.php com funções internas, ele já foi required no functions.php
	// Se não houver nada, não renderiza.
	return '';
}
?>

<main id="primary" class="site-main">
	<div class="container py-4">

		<header class="mb-4">
			<h1 class="h3 mb-2">
				Resultados para:
				<span class="fw-bold"><?php echo esc_html($search_query); ?></span>
			</h1>

			<p class="text-muted mb-0">
				<?php
				// Texto simples com plural
				if ( $total_results === 0 ) {
					echo 'Nenhum resultado encontrado.';
				} elseif ( $total_results === 1 ) {
					echo '1 resultado encontrado.';
				} else {
					echo esc_html($total_results) . ' resultados encontrados.';
				}
				?>
			</p>
		</header>

		<?php if ( have_posts() ) : ?>

			<div class="row g-4">

				<?php
				$i = 0;

				while ( have_posts() ) :
					the_post();
					$i++;

					$post_id = get_the_ID();
					$permalink = get_permalink($post_id);
					$title = get_the_title($post_id);

					$cats = get_the_category($post_id);
					$primary_cat = (!empty($cats) && !is_wp_error($cats)) ? $cats[0] : null;
					?>

					<div class="col-12 col-md-6 col-lg-4">

						<article <?php post_class('card h-100 shadow-sm'); ?>>

							<?php if ( has_post_thumbnail($post_id) ) : ?>
								<a href="<?php echo esc_url($permalink); ?>" class="ratio ratio-16x9 overflow-hidden">
									<?php
									the_post_thumbnail('medium_large', [
										'class' => 'w-100 h-100 object-fit-cover',
										'alt'   => esc_attr($title),
										'loading' => 'lazy',
									]);
									?>
								</a>
							<?php endif; ?>

							<div class="card-body d-flex flex-column">

								<?php if ( $primary_cat ) : ?>
									<div class="mb-2">
										<a class="badge text-bg-secondary text-decoration-none"
										   href="<?php echo esc_url(get_category_link($primary_cat->term_id)); ?>">
											<?php echo esc_html($primary_cat->name); ?>
										</a>
									</div>
								<?php endif; ?>

								<h2 class="h5 card-title mb-2">
									<a class="text-decoration-none text-dark" href="<?php echo esc_url($permalink); ?>">
										<?php echo esc_html($title); ?>
									</a>
								</h2>

								<p class="card-text text-muted mb-3">
									<?php
									// Excerpt limpo e curto
									$excerpt = get_the_excerpt($post_id);
									echo esc_html(wp_trim_words($excerpt, 22, '…'));
									?>
								</p>

								<div class="mt-auto d-flex align-items-center justify-content-between">
									<small class="text-muted">
										<?php echo esc_html(get_the_date('', $post_id)); ?>
									</small>

									<a class="btn btn-sm btn-outline-primary"
									   href="<?php echo esc_url($permalink); ?>"
									   aria-label="<?php echo esc_attr('Ler: ' . $title); ?>">
										Ler mais
									</a>
								</div>

							</div>
						</article>

					</div>

					<?php
					/**
					 * ANÚNCIO IN-FEED NA BUSCA
					 * - após o 3º resultado, e depois a cada 4 resultados
					 * - renderiza em linha completa (col-12)
					 */
					if ( $i === 3 || ( $i > 3 && (($i - 3) % 4 === 0) ) ) :
						$ad_html = antenatec_render_infeed_ad('search_infeed');
						if ( ! empty($ad_html) ) :
							?>
							<div class="col-12">
								<div class="card border-0 bg-light">
									<div class="card-body p-3">
										<?php echo $ad_html; // já deve vir sanitizado pelo seu sistema de ads ?>
									</div>
								</div>
							</div>
							<?php
						endif;
					endif;
				endwhile;
				?>

			</div>

			<div class="mt-4">
				<?php
				the_posts_pagination([
					'mid_size'  => 1,
					'prev_text' => '← Anterior',
					'next_text' => 'Próxima →',
				]);
				?>
			</div>

		<?php else : ?>

			<div class="card border-0 bg-light">
				<div class="card-body p-4">
					<h2 class="h5 mb-2">Nada por aqui.</h2>
					<p class="text-muted mb-3">Tente buscar com outros termos.</p>

					<?php get_search_form(); ?>
				</div>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
