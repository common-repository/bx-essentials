<?php

if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('bx_sntls_Plugin_Admin')) {
   class bx_sntls_Plugin_Admin
   {
      public function __construct()
      {
         $plugin_base = plugin_basename(BX_MAIN_FILE);

         add_action('admin_init', [$this, 'register_page']);
         add_action('admin_menu', [$this, 'admin_page_options']);
         add_filter('plugin_action_links_' . $plugin_base, [$this, 'add_settings_link']);
      }

      public function register_page()
      {
         $options = BX_Essentials::get_options(false);

         register_setting('bx_sntls', 'bx_sntls_options');

         add_settings_section(
            'bx_sntls_page_options_section',
            esc_html__('Configurações', 'bx-essentials'),
            [$this, 'main_description'],
            'bx_sntls',
         );

         foreach ($options as $option => $description) {
            add_settings_field(
               $option,
               $description,
               [$this, 'field_content'],
               'bx_sntls',
               'bx_sntls_page_options_section',
               [
                  'label_for' => $option,
               ],
            );
         }
      }

      public function main_description($args)
      {
         $options = get_option('bx_sntls_options', false);

         if (false === $options) {
            BX_Essentials::activation();
         }

         BX_Essentials::update_options();

?>
         <p id="<?php echo esc_attr($args['id']); ?>">
            <?php esc_html_e('Configura funcionalidades do plugin.', 'bx-essentials'); ?>
         </p>
      <?php

      }

      public function field_content($args)
      {
         $options = get_option('bx_sntls_options', false);

      ?>
         <label>
            <?php esc_html_e('Ativado', 'bx-essentials'); ?>
            <input id="<?php echo esc_attr($args['label_for']); ?>" name="bx_sntls_options[<?php echo esc_attr($args['label_for']); ?>]" type="radio" value="on" <?php checked($options[$args['label_for']], 'on'); ?>>
         </label>
         &nbsp;
         <label>
            <?php esc_html_e('Desativado', 'bx-essentials'); ?>
            <input name="bx_sntls_options[<?php echo esc_attr($args['label_for']); ?>]" type="radio" value="off" <?php checked($options[$args['label_for']], 'off'); ?>>
         </label>
      <?php
      }

      public function add_settings_link($actions)
      {
         $settings_link = [
            '<a href="' . admin_url('options-general.php?page=' . sanitize_title(get_plugin_data(BX_MAIN_FILE)['Name'])) . '">' . esc_html__('Configurações', 'bx-essentials') . '</a>',
         ];

         return array_merge($actions, $settings_link);
      }

      public function admin_page_options()
      {
         add_options_page(
            get_plugin_data(BX_MAIN_FILE)['Name'],
            get_plugin_data(BX_MAIN_FILE)['Name'],
            'manage_options',
            sanitize_title(get_plugin_data(BX_MAIN_FILE)['Name']),
            [$this, 'page_options_content'],
            99,
         );
      }

      public function page_options_content()
      {
      ?>
         <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
               <?php

               settings_fields('bx_sntls');

               do_settings_sections('bx_sntls');

               submit_button(esc_html__('Save'));

               ?>
            </form>
         </div>
<?php

      }
   }

   $Plugin_Admin = new bx_sntls_Plugin_Admin();
}
