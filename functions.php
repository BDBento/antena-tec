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