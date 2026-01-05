<footer class="site-footer bg-dark text-light">
  <div class="container py-5">
    <div class="row gy-4">

      <!-- LOGO + DESCRIÇÃO -->
      <div class="col-12 col-lg-4">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo d-inline-block mb-3">
          <?php
          if (function_exists('the_custom_logo') && has_custom_logo()) {
            the_custom_logo();
          } else {
            echo '<strong>Antena Tec</strong>';
          }
          ?>
        </a>

        <p class="footer-desc">
          Conteúdo, tecnologia, reviews e ofertas selecionadas.
          Informação com qualidade e curadoria.
        </p>
      </div>

      <!-- LINKS -->
      <div class="col-6 col-lg-2">
        <h6 class="footer-title">Antena Tec</h6>
        <?php
        wp_nav_menu([
          'theme_location' => 'menu-footer',
          'container'      => false,
          'menu_class'     => 'footer-menu list-unstyled',
          'depth'          => 1,
          'fallback_cb'    => '__return_false',
        ]);
        ?>
      </div>

      <!-- CONTATO -->
      <div class="col-6 col-lg-3">
        <h6 class="footer-title">Contato</h6>
        <ul class="footer-contact list-unstyled">
          <li>
            📧 <a href="mailto:contato@antenatec.com.br" class="text-light text-decoration-none">
              contato@antenatec.com.br
            </a>
          </li>

          <li class="mt-2">
            📞 <a href="tel:+5567993108021" class="text-light text-decoration-none">
              (67) 99310-8021
            </a>
          </li>

          <li class="mt-1">
            💬 <a href="https://wa.me/5567993108021" target="_blank" rel="noopener"
                 class="text-light text-decoration-none">
              WhatsApp
            </a>
          </li>

          <li class="mt-2">📍 Brasil</li>
        </ul>
      </div>

      <!-- NEWSLETTER -->
      <div class="col-12 col-lg-3">
        <h6 class="footer-title">Fique por dentro</h6>
        <form class="footer-newsletter" method="post" action="#">
          <div class="input-group">
            <input type="email" class="form-control" placeholder="Seu e-mail" required>
            <button class="btn btn-primary" type="submit">Enviar</button>
          </div>
          <small class="text-muted d-block mt-2">
            Sem spam. Apenas conteúdos relevantes.
          </small>
        </form>
      </div>

    </div>
  </div>

  <!-- BARRA FINAL -->
  <div class="footer-bottom text-center py-3">
    <div class="container">
      <small>
        © <?php echo date('Y'); ?> Antena Tec. Todos os direitos reservados.
      </small>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
