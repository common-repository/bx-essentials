<?php

if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('bx_sntls_Theme_Extensions')) {
   /**
    * Adds adicional resources to the theme.
    */
   class bx_sntls_Theme_Extensions
   {
      private $page_templates = [
         '404',
         'archive',
         'attachment',
         'author',
         'category',
         'date',
         'embed',
         'frontpage',
         'home',
         'index',
         'page',
         'paged',
         'privacypolicy',
         'search',
         'single',
         'singular',
         'tag',
         'taxonomy',
      ];

      /**
       * @ignore
       */
      public function __construct()
      {
         add_filter('body_class', [$this, 'body_class_add_slug']);
         add_filter('script_loader_tag', [$this, 'scripts_add_attrs'], 10, 3);
         add_action('after_setup_theme', [$this, 'add_supports']);

         if (BX_Essentials::check_option('css_ver_timestamp')) {
            add_filter('style_loader_src', [$this, 'assets_dynamic_version']);
         }

         if (BX_Essentials::check_option('js_ver_timestamp')) {
            add_filter('script_loader_src', [$this, 'assets_dynamic_version']);
         }

         if (BX_Essentials::check_option('hide_admin_bar')) {
            add_action('after_setup_theme', [$this, 'hide_admin_bar']);
         }

         if (BX_Essentials::check_option('title_tag_theme_support')) {
            add_action('after_setup_theme', [$this, 'add_support_title_tag']);
         }

         if (BX_Essentials::check_option('wp_cleaner')) {
            add_action('after_setup_theme', [$this, 'cleaner_hooks']);
         }

         if (BX_Essentials::check_option('get_template_index')) {
            add_action('get_template_part', [$this, 'get_template_part_index'], 10, 4);
         }

         if (BX_Essentials::check_option('templates_pages_first')) {
            foreach ($this->page_templates as $template) {
               add_filter("{$template}_template_hierarchy", [$this, 'templates_in_page_folder']);
            }
         }
      }

      /**
       * WHAT IT DOES
       * Search for templates files in `pages` folder, based in hierarchy.
       *
       * EXAMPLES
       * pages/home/_index.php
       * pages/taxonomy-personal.php
       *
       * @param mixed $templates
       */
      public function templates_in_page_folder($templates)
      {
         if (false !== strpos($templates[0], 'pages/')) {
            return $templates;
         }

         foreach ($templates as $template) {
            $index           = str_replace('.php', '/_index.php', $template);
            $new_templates[] = "pages/{$index}";
            $new_templates[] = "pages/{$template}";
         }

         return array_merge($new_templates, $templates);
      }

      /**
       * WHAT IT DOES
       * Load _index.php if a directory path is inform.
       *
       * EXAMPLES
       * get_template_part('pages/front');
       *
       * @param mixed $_slug
       * @param mixed $_name
       * @param mixed $template_names
       * @param mixed $args
       */
      public function get_template_part_index($_slug, $_name, $template_names, $args)
      {
         unset($_slug, $_name);

         $located = '';

         foreach ((array) $template_names as $template_name) {
            if (!$template_name) {
               continue;
            }

            $template_name = rtrim($template_name, '.php');

            $get_stylesheet_directory = get_stylesheet_directory();
            if (file_exists($get_stylesheet_directory . '/' . $template_name)) {
               $located = $get_stylesheet_directory . '/' . $template_name;

               break;
            }

            $get_template_directory = get_template_directory();
            if (file_exists($get_template_directory . '/' . $template_name)) {
               $located = $get_template_directory . '/' . $template_name;

               break;
            }

            if (file_exists(ABSPATH . WPINC . '/theme-compat/' . $template_name)) {
               $located = ABSPATH . WPINC . '/theme-compat/' . $template_name;

               break;
            }
         }

         if ('' !== $located && is_dir($located) && file_exists("{$located}/_index.php")) {
            load_template("{$located}/_index.php", true, $args);
         }

         return $located;
      }

      public function add_supports()
      {
         add_theme_support('post-thumbnails');
         add_theme_support('responsive-embed');
         add_theme_support('html5', [
            'comment-form',
            'comment-list',
            'search-form',
            'gallery',
            'caption',
            'script',
            'style',
         ]);
      }

      /**
       * WHAT IT DOES
       * Adds body class with post_type-post_slug.
       *
       * EXAMPLES
       * <body class="page-contact">
       * <body class="post-hello-world">
       *
       * @param mixed $classes
       */
      public function body_class_add_slug($classes)
      {
         global $post;

         if (!isset($post)) {
            return $classes;
         }

         $classes[] = $post->post_type . '-' . $post->post_name;

         return $classes;
      }

      /**
       * WHAT IT DOES
       * Adds defer OR async attribute to a enqueued script.
       *
       * HOW TO USE IT
       * Add #defer OR #async to $url when enqueueing a script.
       * async: Makes the .js download same time the HTML, and run it when the download of it is finished.
       * defer: Makes the .js download same time the HTML, and run it when the download of the HTML finished.
       *
       * EXAMPLE
       * wp_enqueue_script('lib', get_theme_file_uri('assets/js/lib.min.js#async'));
       * wp_enqueue_script('handle-dom', get_theme_file_uri('assets/js/handle-dom.min.js#defer'), ['lib']);
       */
      public function scripts_add_attrs($tag, $_handle, $src)
      {
         unset($_handle);

         if (is_admin()) {
            return $tag;
         }

         if (false !== strpos($src, '#defer')) {
            $tag = str_replace('#defer', '', $tag);
            $tag = str_replace('src=', 'defer src=', $tag);
         } elseif (false !== strpos($src, '#async')) {
            $tag = str_replace('#async', '', $tag);
            $tag = str_replace('src=', 'async src=', $tag);
         }

         return $tag;
      }

      /**
       * WHAT IT DOES
       * Allows themes to add document title tag to HTML <head>.
       */
      public function add_support_title_tag()
      {
         add_theme_support('title-tag');
      }

      /**
       * WHAT IT DOES
       * Change ver= to all self hosted files to the file modification timestamp.
       *
       * When enqueues and the $ver parameter is null, no version is added.
       *
       * EXAMPLES
       * <script src="script.js?ver=1777777">
       *
       * @param mixed $url
       */
      public function assets_dynamic_version($url)
      {
         if (stripos($url, get_template_directory_uri()) === false) {
            return $url;
         }

         if (stripos($url, '?ver=') === false) {
            return $url;
         }

         $file = bx_sntls_Utils::url_to_path($url);

         if (false === $file) {
            return $url;
         }

         $ver = filemtime($file);

         if (!isset($ver) || false === $ver) {
            return $url;
         }

         return add_query_arg('ver', $ver, $url);
      }

      public function hide_admin_bar()
      {
         show_admin_bar(false);
      }

      public function cleaner_hooks()
      {
         /*
          * Removes the link to the Really Simple Discovery service endpoint.
          * <link rel="EditURI" type="application/rsd+xml" title="RSD" href="xmlrpc.php?rsd">
          */
         remove_action('wp_head', 'rsd_link');

         /*
          * Removes the XHTML generator that is generated on the wp_head hook.
          * <meta name="generator" content="WordPress X.XX">
          */
         remove_action('wp_head', 'wp_generator');

         /*
          * Remove the REST API link tag into page header.
          * <link rel="https://api.w.org/" href="*">
          * <link rel="alternate" type="application/json" href="*">
          */
         remove_action('wp_head', 'rest_output_link_wp_head');

         /*
          * Removes the links to the general feeds (RSS).
          * <link rel="alternate" type="*" title="*" href="*">
          */
         remove_action('wp_head', 'feed_links', 2);

         /*
          * Removes the links to the extra feeds (RSS) such as category feeds.
          * <link rel="alternate" type="*" title="*" href="*">
          */
         remove_action('wp_head', 'feed_links_extra', 3);

         /*
          * Removes the link to the Windows Live Writer manifest file.
          * <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="wlwmanifest.xml">
          */
         remove_action('wp_head', 'wlwmanifest_link');

         // // remove the next and previous post links
         // remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
         // remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

         /*
          * Removes rel=shortlink from the head if a shortlink is defined for the current page.
          * <link rel='shortlink' href='*'>
          */
         remove_action('wp_head', 'wp_shortlink_wp_head');

         /*
          * Removes the inline Emoji detection script.
          * <script>*</script>
          */
         remove_action('wp_head', 'print_emoji_detection_script', 7);
         remove_action('admin_print_scripts', 'print_emoji_detection_script');
         remove_action('embed_head', 'print_emoji_detection_script');

         /*
          * Remove the emoji-related styles.
          * <style>*</style>
          */
         remove_action('wp_print_styles', 'print_emoji_styles');
         remove_action('admin_print_styles', 'print_emoji_styles');

         // Remove emoji to img convertor in feed, comments and e-mail.
         remove_filter('the_content_feed', 'wp_staticize_emoji');
         remove_filter('comment_text_rss', 'wp_staticize_emoji');
         remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

         /*
          * Removes oEmbed discovery links in the website.
          * <link rel="alternate" type="application/json+oembed" href="*">
          * <link rel="alternate" type="text/xml+oembed" href="*">
          */
         remove_action('wp_head', 'wp_oembed_add_discovery_links');

         /*
          * Removes the necessary JavaScript to communicate with the embedded iframe.
          * Remove oEmbed-specific JavaScript from the front-end and back-end.
          */
         remove_action('wp_head', 'wp_oembed_add_host_js');

         // Turn off inspect the given URL for discoverable link tags.
         add_filter('embed_oembed_discover', '__return_false');

         // Disables JSONP for the REST API.
         add_filter('rest_jsonp_enabled', '__return_false');

         // Disables XMLRPC
         add_filter('xmlrpc_enabled', '__return_false');
         add_filter('xmlrpc_methods', '__return_empty_array');

         // Disable Link header for the REST API.
         remove_action('template_redirect', 'rest_output_link_header', 11, 0);

         /*
          * Disable alias redirects to /wp-admin and /wp-login.
          *
          * /admin != /wp-admin
          * /dashboard != /wp-admin
          * /login != /wp-login.php
          */
         remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);
      }
   }

   $Theme_Extensions = new bx_sntls_Theme_Extensions();
}
