<?php

if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('bx_sntls_Plugin_Resources')) {
   /**
    * Provides resources for themes and plugins development.
    */
   class bx_sntls_Plugin_Resources
   {
      /**
       * @ignore
       */
      public function __construct()
      {
         add_action('wp_enqueue_scripts', [$this, 'register_assets']);

         if (!is_admin()) {
            add_action('wp_enqueue_scripts', [$this, 'register_jquery_extras']);
         }

         if (BX_Essentials::check_option('breakpoints_icon') && bx_sntls_Utils::is_environment('local')) {
            add_action('wp_head', [$this, 'add_style_breakpoints_indicator'], 99);
         }

         if (BX_Essentials::check_option('acf_show_key')) {
            add_action('acf/get_field_label', [$this, 'acf_show_key'], 5, 2);
         }

         if (BX_Essentials::check_option('cache_get_request') && !is_admin()) {
            add_filter('pre_http_request', [$this, 'cache_request_get'], 10, 3);
            add_filter('http_response', [$this, 'cache_request_save'], 10, 3);
         }

         if (BX_Essentials::check_option('minify_html')) {
            bx_sntls_Utils::load_class('HTML-Compression');
            add_action('get_header', [$this, 'html_compression_start']);
         }

         if (BX_Essentials::check_option('change_admin_schema_colors') && 'production' !== wp_get_environment_type()) {
            add_filter('get_user_option_admin_color', [$this, 'change_admin_scheme_colors_by_env']);
         }

         add_filter('wp_new_user_notification_email_admin', [$this, 'add_more_user_info_to_new_user_mail'], 10, 2);
      }

      /**
       * Registers Alpine, Gearbox, Axios globally with defer attribute.
       *
       * HOW TO USE IT
       * Add 'alpine', 'gearbox' and/or 'axios' as a dependence when enqueueing a script.
       *
       * #### Alpine
       * 'alpine' includes:
       * -  [Alpine](https://alpinejs.dev/start-here) - incluído em alpine
       * - [Alpine toolkit](https://github.com/alpine-collective/toolkit) - parcialmente
       * - [Alpine JS Scroll To](https://github.com/markmead/alpinejs-scroll-to)
       * - [Alpine JS Textarea Grow](https://github.com/markmead/alpinejs-textarea-autogrow)
       * - [Alpine.js plugin Screen](https://github.com/victoryoalli/alpinejs-screen)
       * - [Alpine JS Money](https://github.com/markmead/alpinejs-money)
       *
       * ##### Magics
       *
       * ```js

       *
       * // rola a página para o elemento em href="#" ou element (querySelector).
       * $scrollTo({element:'', offsetHeader: false, useSmooth: true})
       *
       * // retorna a largura atual da janela.
       * $width
       *
       * // retorna a altura atual da janela.
       * $height
       *
       * // imprimir todos argumentos no console.
       * $debug(...args)
       *
       * // cria uma array de elementos numéricos.
       * $range(start, stop, step = 1)
       *
       * // mostra uma notificação toast.
       * $toast(message, type = 'success', link = '', {duration = 7, target = '_blank'})
       * ```
       *
       * #### Gearbox
       *
       *
       * #### Axios
       * -  [Axios](https://axios-http.com/docs/intro) - incluído em axios
       *
       * EXAMPLE
       * wp_enqueue_script('all', get_theme_file_uri('assets/js/all.min.js'), ['alpine']);
       */
      public function register_assets()
      {
         if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
         }

         $suffix = bx_sntls_Utils::is_environment(['local', 'development']) ? '' : '.min';

         $js_files = [
            'alpine' => [
               'src'     => 'assets/js/alpine.min.js',
               'version' => '3.13.3'
            ],
            'axios' => [
               'src'     => 'assets/js/axios.min.js',
               'version' => '1.6.2',
            ],
            'gearbox' => [
               'src'     => "assets/js/gearbox{$suffix}.js",
               'version' => get_plugin_data(BX_MAIN_FILE)['Version'],
            ],
         ];

         foreach ($js_files as $handle => $js_file) {
            wp_register_script(
               $handle,
               plugin_dir_url(BX_MAIN_FILE) . $js_file['src'] . '#defer',
               [],
               $js_file['version']
            );
         }

         wp_register_script('tailwind-cdn', 'https://cdn.tailwindcss.com');

         $form_validation_messages = [
            'types' => [
               'date'           => esc_attr__('uma data válida', 'bx-essentials'),
               'month'          => esc_attr__('um mês válido', 'bx-essentials'),
               'datetime-local' => esc_attr__('uma data com hora válida', 'bx-essentials'),
               'time'           => esc_attr__('uma hora válida', 'bx-essentials'),
               'week'           => esc_attr__('uma semana válida', 'bx-essentials'),
               'number'         => esc_attr__('um número válido', 'bx-essentials'),
            ],
            'date' => [
               'rangeOverflow'  => esc_attr__('Precisa ser %max ou antes', 'bx-essentials'),
               'rangeUnderflow' => esc_attr__('Precisa ser %min ou depois', 'bx-essentials'),
            ],
            'number' => [
               'rangeOverflow'  => esc_attr__('Precisa ser menor ou igual a %max', 'bx-essentials'),
               'rangeUnderflow' => esc_attr__('Precisa ser maior ou igual a %min', 'bx-essentials'),
            ],
            'badInput'        => esc_attr__('Precisa ser %type', 'bx-essentials'),
            'patternMismatch' => esc_attr__('Precisa corresponder ao formato esperado', 'bx-essentials'),
            'stepMismatch'    => esc_attr__('Precisa ser múltiplo de %step', 'bx-essentials'),
            'tooLong'         => esc_attr__('Precisa ter %maxLength ou menos caracteres', 'bx-essentials'),
            'tooShort'        => esc_attr__('Precisa ter %minLength ou mais caracteres', 'bx-essentials'),
            'valueMissing'    => esc_attr__('Precisa ser preenchido', 'bx-essentials'),
         ];

         wp_localize_script('gearbox', 'gearbx', [
            'debug'   => bx_sntls_Utils::is_environment(['local', 'development']),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'lang'    => get_bloginfo('language'),
         ]);
         wp_localize_script('gearbox', 'gearbxValidity', $form_validation_messages);

         wp_localize_script('alpine', 'alpineGlobal', [
            'debug'    => bx_sntls_Utils::is_environment(['local', 'development']),
            'ajaxUrl'  => admin_url('admin-ajax.php'),
            'locale'   => str_replace('_', '-', get_user_locale()),
            'currency' => apply_filters('woocommerce_currency', get_option('woocommerce_currency', 'BRL'))
         ]);
         wp_localize_script('alpine', 'alpineValidity', $form_validation_messages);

         wp_register_style('toast-css', plugin_dir_url(BX_MAIN_FILE) . 'assets/css/toast.min.css');
      }

      /**
       * WHAT IT DOES
       * Registes jQuery with jquery-async, jquery-defer and jquery-footer.
       *
       * HOW TO USE IT
       * Add 'jquery-async', 'jquery-defer', 'jquery-footer' as a dependence when enqueueing a script.
       *
       * EXAMPLE
       * wp_enqueue_script('all', get_theme_file_uri('assets/js/all.min.js'), ['jquery-async']);
       */
      public function register_jquery_extras()
      {
         $suffix = bx_sntls_Utils::is_environment(['local', 'development']) ? '' : '.min';

         $jquery = [
            'url'     => includes_url("js/jquery/jquery{$suffix}.js"),
            'version' => '3.6.1',
         ];

         $migrate = [
            'url'     => includes_url("js/jquery/jquery-migrate{$suffix}.js"),
            'version' => '3.3.2',
         ];

         wp_register_script('jquery-core-async', $jquery['url'] . '#async', [], $jquery['version']);
         wp_register_script('jquery-migrate-async', $migrate['url'] . '#async', [], $migrate['version']);
         wp_register_script('jquery-async', false, ['jquery-core-async', 'jquery-migrate-async'], $jquery['version']);

         wp_register_script('jquery-core-defer', $jquery['url'] . '#defer', [], $jquery['version']);
         wp_register_script('jquery-migrate-defer', $migrate['url'] . '#defer', [], $migrate['version']);
         wp_register_script('jquery-defer', false, ['jquery-core-defer', 'jquery-migrate-defer'], $jquery['version']);

         wp_register_script('jquery-core-footer', $jquery['url'], [], $jquery['version'], true);
         wp_register_script('jquery-migrate-footer', $migrate['url'], [], $migrate['version'], true);
         wp_register_script('jquery-footer', false, ['jquery-core-footer', 'jquery-migrate-footer'], $jquery['version'], true);
      }

      /**
       * WHAT IT DOES
       * Displays the field name key before the ACF field.
       *
       * @param mixed $label
       * @param mixed $field
       */
      public function acf_show_key($label, $field)
      {
         if (!current_user_can('manage_options')) {
            return $label;
         }

         $screen = get_current_screen();

         if (!isset($screen->post_type) || 'acf-field-group' === $screen->post_type) {
            return $label;
         }

         if (!isset($field['_name'])) {
            return $label;
         }

         return "{$label}<br>(<em>key: {$field['_name']}</em>)";
      }

      /**
       * WHAT IT DOES
       * Add badge indicating the current Tailwind breakpoints, only in local environments.
       *
       * % -> https://gs.statcounter.com/screen-resolution-stats/all/brazil
       */
      public function add_style_breakpoints_indicator()
      {
?>
         <style>
            html::before {
               content: 'default';
               position: fixed;
               bottom: 0;
               left: 50%;
               z-index: 9999;
               padding: 0.15rem 0.4rem;
               background-color: rgba(255, 50, 50, .9);
               color: white;
               transform: translateX(-50%);
               font: 0.75rem/1 system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            }

            @media (min-width: 640px) {
               html::before {
                  content: 'sm:';
               }
            }

            @media (min-width: 768px) {
               html::before {
                  content: 'md:';
               }
            }

            @media (min-width: 1024px) {
               html::before {
                  content: 'lg:';
               }
            }

            @media (min-width: 1280px) {
               html::before {
                  content: 'xl:';
               }
            }

            @media (min-width: 1536px) {
               html::before {
                  content: '2xl:';
               }
            }
         </style>
<?php

      }

      /**
       * WHAT IT DOES
       * Adds cache to all HTTP request using the GET method.
       *
       * HOW TO USE IT
       * Cache is generated to all GET requests. Arguments are optional.
       *
       * To disable cache, use 'cache' => false. (Optional. Default is true)
       * To change cache duration use 'cache_duration' => $period. (Optional. Default is 6 hours)
       * To define a custom key use 'cache_key' => $string. (Optional. Default is the request's URL)
       *
       * EXAMPLE
       * wp_remote_get($url, [
       * 'cache'          => false,
       * 'cache_duration' => '7 days',
       * 'cache_key'      => 'custom_cache_key',
       * ]);
       *
       * @param mixed $continue
       * @param mixed $args
       * @param mixed $url
       */
      public function cache_request_get($continue, $args, $url)
      {
         if ('GET' !== $args['method']) {
            return $continue;
         }

         if (isset($args['cache']) && false === $args['cache']) {
            return $continue;
         }

         if (isset($args['cache_key']) && !empty($args['cache_key'])) {
            $cache_key = bx_sntls_Utils::generate_key($args['cache_key']);
         } else {
            $cache_key = bx_sntls_Utils::generate_key($url, 'hash');
         }

         $cached_data = get_transient($cache_key);

         if (false !== $cached_data) {
            return $cached_data;
         }

         return $continue;
      }

      public function cache_request_save($response, $args, $url)
      {
         if ('GET' !== $args['method']) {
            return $response;
         }

         if (isset($args['cache']) && false === $args['cache']) {
            return $response;
         }

         $cache_duration = $args['cache_duration'] ?? '6 hours';
         $cache_duration = strtotime($cache_duration) - time();

         if (isset($args['cache_key']) && !empty($args['cache_key'])) {
            $cache_key = bx_sntls_Utils::generate_key($args['cache_key']);
         } else {
            $cache_key = bx_sntls_Utils::generate_key($url, 'hash');
         }

         set_transient($cache_key, $response, $cache_duration);

         return $response;
      }

      /**
       * WHAT IT DOES
       * Minify all HTML content, including inline CSS and JS, accordingly dashboard configuration.
       * And rewrite URL of all assets if needed.
       *
       * HOW TO USE IT
       * Set a value to the filter 'bx_sntls_rewriter_remote_hostname' with $domain.
       * Set a value to the filter 'bx_sntls_rewriter_file_extensions' with $file_extensions.
       *
       * EXAMPLE
       * add_filter('bx_sntls_rewriter_remote_hostname', function ()
       * {
       *    return 'prod.site.example';
       * });
       *
       * add_filter('bx_sntls_rewriter_file_extensions', function ()
       * {
       *    return 'jpg|png|jpeg|gif|mp4';
       * });
       */
      public function html_compression_start()
      {
         ob_start([$this, 'html_compression_finish']);
      }

      public function html_compression_finish($html)
      {
         return new bx_sntls_HTML_Compression($html, true);
      }

      /**
       * WHAT IT DOES
       * Change admin color scheme by environment type. Only works for local, development and staging. The scheme colors are:
       *
       * Local: coffee
       * Development: blue
       * Staging: ectoplasm
       *
       * @param string $color_scheme
       */
      public function change_admin_scheme_colors_by_env($color_scheme)
      {
         global $pagenow;

         if (in_array($pagenow, ['profile.php', 'user-edit.php'])) {
            return $color_scheme;
         }

         $env = wp_get_environment_type();

         $env_scheme_colors = [
            'local'       => 'coffee',
            'development' => 'blue',
            'staging'     => 'ectoplasm',
         ];

         if (empty($env_scheme_colors[$env])) {
            return $color_scheme;
         }

         return $env_scheme_colors[$env];
      }

      /**
       * WHAT IT DOES
       * Include domain, user role and user creator info in new user email template.
       *
       * @param array $email
       * @param WP_User $user
       */
      public function add_more_user_info_to_new_user_mail(array $email, WP_User $user)
      {
         $created_by_user = wp_get_current_user();

         $content_list[] = sprintf(
            "%s: %s (%s)",
            esc_html__('Origem', 'bx-essentials'),
            get_home_url(),
            wp_get_environment_type()
         );

         $content_list[] = sprintf(
            "%s: %s",
            esc_html__('Função', 'bx-essentials'),
            $user->roles[0],
         );

         if ($created_by_user->ID !== 0) {
            $content_list[] = sprintf(
               "%s: %s (%s)",
               esc_html__('Criado por', 'bx-essentials'),
               $created_by_user->display_name,
               $created_by_user->user_email,
            );
         }

         $email_additional_content = implode("\n\n", $content_list);

         $email['message'] = $email['message'] . "\n" . $email_additional_content;

         return $email;
      }
   }

   $Plugin_Resources = new bx_sntls_Plugin_Resources();
}
