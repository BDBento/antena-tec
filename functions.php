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



/**
 * Cotações via CoinGecko (reduz requests e evita 429 da AwesomeAPI)
 * Retorna:
 * USD (US$->R$), EUR, BTC, ETH e OURO (R$/g)
 */
function antenatec_get_quotes() {
  $cache_key = 'antenatec_quotes';
  $cached = get_transient($cache_key);

  if ($cached !== false && is_array($cached)) {
    return $cached;
  }

  $data = [];

  $get_json = function($url) {
    $res = wp_remote_get($url, [
      'timeout' => 15,
      'headers' => [
        'Accept'     => 'application/json',
        'User-Agent' => 'AntenatecWP/1.0; ' . home_url('/'),
      ],
    ]);

    if (is_wp_error($res)) return null;

    $code = (int) wp_remote_retrieve_response_code($res);
    if ($code < 200 || $code >= 300) return null;

    $json = json_decode(wp_remote_retrieve_body($res), true);
    return is_array($json) ? $json : null;
  };

  // 1) Crypto em BRL (BTC/ETH)
  $crypto_url = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum&vs_currencies=brl';
  if ($body = $get_json($crypto_url)) {
    $btc = (float) ($body['bitcoin']['brl'] ?? 0);
    $eth = (float) ($body['ethereum']['brl'] ?? 0);
    if ($btc > 0) $data['BTC'] = $btc;
    if ($eth > 0) $data['ETH'] = $eth;
  }

  // 2) Moedas fiat em BRL (USD/EUR)
  // Endpoint de câmbio do CoinGecko
  $fx_url = 'https://api.coingecko.com/api/v3/exchange_rates';
  $usd_brl = 0;

  if ($body = $get_json($fx_url)) {
    // No CoinGecko, 'rates' normalmente é relativo a BTC (base). Para obter BRL por USD/EUR,
    // usamos a razão (BRL per BTC) / (USD per BTC) etc.
    $rates = $body['rates'] ?? [];

    $brl_per_btc = (float) ($rates['brl']['value'] ?? 0);
    $usd_per_btc = (float) ($rates['usd']['value'] ?? 0);
    $eur_per_btc = (float) ($rates['eur']['value'] ?? 0);

    if ($brl_per_btc > 0 && $usd_per_btc > 0) {
      $usd_brl = $brl_per_btc / $usd_per_btc; // BRL por 1 USD
      $data['USD'] = $usd_brl;
    }

    if ($brl_per_btc > 0 && $eur_per_btc > 0) {
      $eur_brl = $brl_per_btc / $eur_per_btc; // BRL por 1 EUR
      $data['EUR'] = $eur_brl;
    }
  }

  // 3) OURO (XAU) por grama em BRL
  // CoinGecko: commodities/metals podem variar por disponibilidade.
  // A forma mais consistente é via XAU/USD e converter com USD/BRL.
  // Vamos pegar XAU/USD via endpoint simples se disponível.
  // Fallback: não mostra ouro se não conseguir.

  $gold_url = 'https://api.coingecko.com/api/v3/simple/price?ids=tether-gold&vs_currencies=brl';
  // Observação: tether-gold (XAUT) não é exatamente spot XAU, mas funciona como proxy de ouro em muitas implementações.
  // Se quiser spot XAU estrito, trocamos para outra fonte (ver nota ao final).

  if ($body = $get_json($gold_url)) {
    $gold_brl = (float) ($body['tether-gold']['brl'] ?? 0); // preço aproximado por 1 XAUT ~ 1 onça troy
    if ($gold_brl > 0) {
      $data['OURO'] = $gold_brl / 31.1034768; // R$/grama
    }
  }

  set_transient($cache_key, $data, 10 * MINUTE_IN_SECONDS);
  return $data;
}



/**
 * =========================
 * ADSENSE – Configuração + Render
 * =========================
 */

function antenatec_ads_defaults() {
  return [
    'adsense_client' => 'ca-pub-5434406728601594',

    // Slots (preencha no admin depois)
    'home_top_slot'     => '',
    'home_middle_slot'  => '',
    'home_sidebar_slot' => '',
    'single_sidebar_slot' => '',
    'single_bottom_slot'  => '',
  ];
}

function antenatec_ads_get_option($key) {
  $opts = get_option('antenatec_ads_options', []);
  $defaults = antenatec_ads_defaults();
  $opts = is_array($opts) ? array_merge($defaults, $opts) : $defaults;
  return $opts[$key] ?? ($defaults[$key] ?? '');
}

/**
 * Carrega o script do AdSense UMA vez (só no front).
 * Só carrega se tiver client configurado.
 */
add_action('wp_head', function () {
  if (is_admin()) return;

  $client = antenatec_ads_get_option('adsense_client');
  if (!$client) return;

  echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client='
    . esc_attr($client)
    . '" crossorigin="anonymous"></script>' . "\n";
}, 5);

/**
 * Render de anúncio por posição.
 * Ex.: antenatec_ad('home_top', ['format' => 'auto', 'responsive' => true]);
 */
function antenatec_ad($position, $args = []) {
  $client = antenatec_ads_get_option('adsense_client');
  if (!$client) return;

  $map = [
    'home_top'      => 'home_top_slot',
    'home_middle'   => 'home_middle_slot',
    'home_sidebar'  => 'home_sidebar_slot',
    'single_sidebar'=> 'single_sidebar_slot',
    'single_bottom' => 'single_bottom_slot',
  ];

  if (!isset($map[$position])) return;

  $slot = antenatec_ads_get_option($map[$position]);
  if (!$slot) {
    // sem slot configurado => não renderiza nada
    return;
  }

  $format = $args['format'] ?? 'auto';
  $responsive = isset($args['responsive']) ? (bool)$args['responsive'] : true;

  echo '<ins class="adsbygoogle" style="display:block"'
    . ' data-ad-client="' . esc_attr($client) . '"'
    . ' data-ad-slot="' . esc_attr($slot) . '"'
    . ' data-ad-format="' . esc_attr($format) . '"'
    . ($responsive ? ' data-full-width-responsive="true"' : '')
    . '></ins>' . "\n";

  echo '<script>(adsbygoogle=window.adsbygoogle||[]).push({});</script>' . "\n";
}

/**
 * Página de configurações no admin:
 * Aparência > Antena Tec Ads
 */
add_action('admin_menu', function () {
  add_theme_page(
    'Antena Tec – Ads',
    'Antena Tec Ads',
    'manage_options',
    'antenatec-ads',
    'antenatec_ads_settings_page'
  );
});

function antenatec_ads_settings_page() {
  if (!current_user_can('manage_options')) return;

  if (isset($_POST['antenatec_ads_save']) && check_admin_referer('antenatec_ads_save_nonce')) {
    $defaults = antenatec_ads_defaults();
    $new = [];

    foreach ($defaults as $k => $v) {
      $new[$k] = isset($_POST[$k]) ? sanitize_text_field($_POST[$k]) : $v;
    }

    update_option('antenatec_ads_options', $new);
    echo '<div class="updated"><p>Configurações salvas.</p></div>';
  }

  $opts = get_option('antenatec_ads_options', []);
  $opts = is_array($opts) ? array_merge(antenatec_ads_defaults(), $opts) : antenatec_ads_defaults();
  ?>
  <div class="wrap">
    <h1>Antena Tec – Ads (AdSense)</h1>
    <form method="post">
      <?php wp_nonce_field('antenatec_ads_save_nonce'); ?>

      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="adsense_client">AdSense Client</label></th>
          <td>
            <input type="text" id="adsense_client" name="adsense_client" class="regular-text"
              value="<?php echo esc_attr($opts['adsense_client']); ?>" placeholder="ca-pub-xxxxxxxxxxxxxxxx">
          </td>
        </tr>

        <tr><th colspan="2"><h2>Home</h2></th></tr>

        <tr>
          <th scope="row"><label for="home_top_slot">Home – Topo (slot)</label></th>
          <td><input type="text" id="home_top_slot" name="home_top_slot" class="regular-text"
            value="<?php echo esc_attr($opts['home_top_slot']); ?>"></td>
        </tr>

        <tr>
          <th scope="row"><label for="home_sidebar_slot">Home – Sidebar (slot)</label></th>
          <td><input type="text" id="home_sidebar_slot" name="home_sidebar_slot" class="regular-text"
            value="<?php echo esc_attr($opts['home_sidebar_slot']); ?>"></td>
        </tr>

        <tr>
          <th scope="row"><label for="home_middle_slot">Home – Meio (slot)</label></th>
          <td><input type="text" id="home_middle_slot" name="home_middle_slot" class="regular-text"
            value="<?php echo esc_attr($opts['home_middle_slot']); ?>"></td>
        </tr>

        <tr><th colspan="2"><h2>Single</h2></th></tr>

        <tr>
          <th scope="row"><label for="single_sidebar_slot">Single – Sidebar (slot)</label></th>
          <td><input type="text" id="single_sidebar_slot" name="single_sidebar_slot" class="regular-text"
            value="<?php echo esc_attr($opts['single_sidebar_slot']); ?>"></td>
        </tr>

        <tr>
          <th scope="row"><label for="single_bottom_slot">Single – Rodapé (slot)</label></th>
          <td><input type="text" id="single_bottom_slot" name="single_bottom_slot" class="regular-text"
            value="<?php echo esc_attr($opts['single_bottom_slot']); ?>"></td>
        </tr>
      </table>

      <p class="submit">
        <button type="submit" name="antenatec_ads_save" class="button button-primary">Salvar</button>
      </p>
    </form>

    <p><strong>Como usar no tema:</strong> <code>&lt;?php antenatec_ad('home_top'); ?&gt;</code></p>
  </div>
  <?php
}



// <!-- ------------------------------------------------- -->

/**
 * CPT: Ofertas (Choice Day)
 */
add_action('init', function () {
  register_post_type('ofertas', [
    'labels' => [
      'name'          => 'Ofertas',
      'singular_name' => 'Oferta',
      'add_new_item'  => 'Adicionar nova oferta',
      'edit_item'     => 'Editar oferta',
    ],
    'public'       => true,
    'has_archive'  => true,
    'menu_icon'    => 'dashicons-tag',
    'supports'     => ['title', 'thumbnail'],
    'rewrite'      => ['slug' => 'ofertas'],
    'show_in_rest' => true,
  ]);
});

/**
 * Metabox: dados da oferta
 */
add_action('add_meta_boxes', function () {
  add_meta_box(
    'antenatec_oferta_meta',
    'Dados da Oferta',
    'antenatec_oferta_meta_box',
    'ofertas',
    'normal',
    'high'
  );
});

function antenatec_oferta_meta_box($post) {
  wp_nonce_field('antenatec_oferta_save', 'antenatec_oferta_nonce');

  $url   = get_post_meta($post->ID, '_oferta_url', true);
  $price = get_post_meta($post->ID, '_oferta_price', true);
  $store = get_post_meta($post->ID, '_oferta_store', true);
  $badge = get_post_meta($post->ID, '_oferta_badge', true);
  $ship  = get_post_meta($post->ID, '_oferta_ship', true);
  $choice= get_post_meta($post->ID, '_oferta_choice', true);

  ?>
  <table class="form-table" role="presentation">
    <tr>
      <th><label for="oferta_url">Link da oferta</label></th>
      <td>
        <input type="url" id="oferta_url" name="oferta_url" class="regular-text"
          value="<?php echo esc_attr($url); ?>" placeholder="https://...">
        <p class="description">Cole o link do Mercado Livre, Shopee, Amazon etc.</p>
      </td>
    </tr>

    <tr>
      <th><label for="oferta_price">Preço</label></th>
      <td>
        <input type="text" id="oferta_price" name="oferta_price" class="regular-text"
          value="<?php echo esc_attr($price); ?>" placeholder="R$ 199,90">
      </td>
    </tr>

    <tr>
      <th><label for="oferta_store">Loja</label></th>
      <td>
        <select id="oferta_store" name="oferta_store">
          <?php
          $stores = ['Mercado Livre','Amazon','Shopee','AliExpress','Magazine Luiza','Outro'];
          foreach ($stores as $s) {
            printf(
              '<option value="%s" %s>%s</option>',
              esc_attr($s),
              selected($store, $s, false),
              esc_html($s)
            );
          }
          ?>
        </select>
      </td>
    </tr>

    <tr>
      <th><label for="oferta_badge">Selo (opcional)</label></th>
      <td>
        <input type="text" id="oferta_badge" name="oferta_badge" class="regular-text"
          value="<?php echo esc_attr($badge); ?>" placeholder="OFERTA / CUPOM / TOP / -30%">
      </td>
    </tr>

    <tr>
      <th><label for="oferta_ship">Texto menor (opcional)</label></th>
      <td>
        <input type="text" id="oferta_ship" name="oferta_ship" class="regular-text"
          value="<?php echo esc_attr($ship); ?>" placeholder="frete grátis / entrega amanhã / cupom no carrinho">
      </td>
    </tr>

    <tr>
      <th>Choice Day</th>
      <td>
        <label>
          <input type="checkbox" name="oferta_choice" value="1" <?php checked($choice, '1'); ?>>
          Mostrar na seção “CHOICE DAY”
        </label>
      </td>
    </tr>
  </table>
  <?php
}

/**
 * Salvar metabox
 */
add_action('save_post_ofertas', function ($post_id) {
  if (!isset($_POST['antenatec_oferta_nonce']) || !wp_verify_nonce($_POST['antenatec_oferta_nonce'], 'antenatec_oferta_save')) return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  $url   = isset($_POST['oferta_url']) ? esc_url_raw($_POST['oferta_url']) : '';
  $price = isset($_POST['oferta_price']) ? sanitize_text_field($_POST['oferta_price']) : '';
  $store = isset($_POST['oferta_store']) ? sanitize_text_field($_POST['oferta_store']) : '';
  $badge = isset($_POST['oferta_badge']) ? sanitize_text_field($_POST['oferta_badge']) : '';
  $ship  = isset($_POST['oferta_ship']) ? sanitize_text_field($_POST['oferta_ship']) : '';
  $choice= isset($_POST['oferta_choice']) ? '1' : '0';

  update_post_meta($post_id, '_oferta_url', $url);
  update_post_meta($post_id, '_oferta_price', $price);
  update_post_meta($post_id, '_oferta_store', $store);
  update_post_meta($post_id, '_oferta_badge', $badge);
  update_post_meta($post_id, '_oferta_ship', $ship);
  update_post_meta($post_id, '_oferta_choice', $choice);
});

/**
 * Helper: obter imagem da oferta
 */
function antenatec_oferta_img($post_id, $fallback_url) {
  if (has_post_thumbnail($post_id)) {
    $url = get_the_post_thumbnail_url($post_id, 'medium');
    if ($url) return $url;
  }
  return $fallback_url;
}

/**
 * Remove placeholder inválido do termo de busca: {search_term_string}
 * Evita aparecer no título/H1/campos e impede comportamento estranho de plugins.
 */
add_filter('request', function ($qv) {
	if (!isset($qv['s'])) return $qv;

	$s = (string) $qv['s'];

	// pega variações comuns
	if ($s === '{search_term_string}' || stripos($s, '{search_term_string}') !== false) {
		$qv['s'] = '';
	}

	return $qv;
}, 1);

/**
 * (Opcional) Redireciona URL “placeholder” para a home (ou para /?s=)
 */
add_action('template_redirect', function () {
	if (!is_search()) return;

	$s = (string) get_query_var('s');
	if ($s === '{search_term_string}' || stripos($s, '{search_term_string}') !== false) {
		wp_safe_redirect(home_url('/'), 301); // ou home_url('/?s=')
		exit;
	}
}, 1);
