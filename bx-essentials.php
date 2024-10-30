<?php

/**
 * Plugin Name:       BX Essentials
 * Description:       Ferramentas e configurações padrões para projetos WordPress.
 * Version:           2.1.1
 * Requires at least: 5.5
 * Requires PHP:      8.0.0
 * Plugin URI:        https://buildbox.one/bx-essentials
 * Author:            Buildbox WordPress Team
 * Author URI:        https://www.buildbox.com.br/
 * License:           GPL v2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bx-essentials
 * Global Prefix:     bx_sntls
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
   exit;
}

if (!function_exists('add_action')) {
   trigger_error('WordPress not initialized', E_USER_ERROR);
}

if (!defined('BX_MAIN_FILE')) {
   /**
    * Sets the main file path of the plugin globally.
    */
   define('BX_MAIN_FILE', __FILE__);
}

if (!class_exists('BX_Essentials')) {
   /**
    * Setups the plugin required files, settings, activation and uninstall processes.
    */
   final class BX_Essentials
   {
      public static function init()
      {
         add_action('plugins_loaded', ['BX_Essentials', 'load_textdomain']);

         include_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'includes', 'classes', 'Utils.php']);
         bx_sntls_Utils::load_class('Theme-Extensions');
         bx_sntls_Utils::load_class('Plugin-Resources');
         bx_sntls_Utils::load_class('Virtual-Pages');
         bx_sntls_Utils::load_class('Plugin-Admin');
         include_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'includes', 'alias.php']);
      }

      public static function get_options($only_keys = true)
      {
         $options = [
            'minify_html'                => esc_html__('Minimizar HTML (Minify)', 'bx-essentials'),
            'minify_html_js'             => esc_html__('Minimizar JS em linha no HTML (Minify JS)', 'bx-essentials'),
            'minify_html_css'            => esc_html__('Minimizar CSS em linha no HTML (Minify CSS)', 'bx-essentials'),
            'cache_get_request'          => esc_html__('Cache das requests GET', 'bx-essentials'),
            'css_ver_timestamp'          => esc_html__('Versionamento dinâmico dos CSS', 'bx-essentials'),
            'js_ver_timestamp'           => esc_html__('Versionamento dinâmico dos JS', 'bx-essentials'),
            'get_template_index'         => esc_html__('Carregar componentes em pastas com _index.php', 'bx-essentials'),
            'templates_pages_first'      => esc_html__('Busca templates da hierarquia primeiro na pasta pages', 'bx-essentials'),
            'breakpoints_icon'           => esc_html__('Indicador de breakpoints', 'bx-essentials'),
            'wp_cleaner'                 => esc_html__('WP Cleaner', 'bx-essentials'),
            'hide_admin_bar'             => esc_html__('Ocultar WP Admin bar', 'bx-essentials'),
            'title_tag_theme_support'    => esc_html__('Adicionar suporte ao Title Tag', 'bx-essentials'),
            'acf_show_key'               => esc_html__('[ACF] Mostrar chave dos campos no UI', 'bx-essentials'),
            'change_admin_schema_colors' => esc_html__('Mudar cores do /wp-admin de acordo com o ambiente', 'bx-essentials'),
         ];

         if ($only_keys) {
            return array_keys($options);
         }

         return $options;
      }

      public static function set_options()
      {
         $options = self::get_options(false);
         $options = array_map(function () {
            return 'off';
         }, $options);

         add_option('bx_sntls_options', $options);

         return $options;
      }

      public static function update_options()
      {
         $options         = self::get_options();
         $current_options = get_option('bx_sntls_options');

         if (count($options) === count($current_options)) {
            return;
         }

         foreach ($options as $option) {
            if (!array_key_exists($option, $current_options)) {
               $current_options[$option] = 'off';
            }
         }

         update_option('bx_sntls_options', $current_options);
      }

      public static function check_option($option)
      {
         $options = get_option('bx_sntls_options', false);

         if (false === $options) {
            self::activation();
         }

         if (!isset($options[$option])) {
            return false;
         }

         return (bool) ('on' === $options[$option]);
      }

      public static function load_textdomain()
      {
         load_plugin_textdomain('bx-essentials', false, dirname(BX_MAIN_FILE) . '/languages/');
      }

      public static function activation()
      {
         self::set_options();
      }

      public static function uninstall()
      {
         delete_option('bx_sntls_options');
      }
   }
}

register_activation_hook(BX_MAIN_FILE, ['BX_Essentials', 'activation']);
register_uninstall_hook(BX_MAIN_FILE, ['BX_Essentials', 'uninstall']);

BX_Essentials::init();
