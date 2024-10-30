<?php

/**
 * WHAT IT DOES
 * Registers URL, params, title, endpoint_base and template for virtual pages.
 *
 * HOW TO USE IT
 * Set a array to the filter 'bx_sntls_virtual_pages_list' with title, filename, endpoint_base (opcional) and rewrites[url, params?].
 *
 * The template must be in pages folder inside the theme. There is a queue to locate the template and the first template found will be used.
 *
 * pages/$filename/_index.php
 * pages/$filename.php
 * pages/virtual-$filename/_index.php
 * pages/virtual-$filename.php
 *
 * When using endpoint_base it's possible to define a subfolder inside the pages and use the endpoints folder to organize all templates.
 *
 * pages/$endpoint_base/endpoints/$filename/_index.php
 * pages/$endpoint_base/endpoints/$filename.php
 * pages/$endpoint_base/endpoints/virtual-$filename/_index.php
 * pages/$endpoint_base/endpoints/virtual-$filename.php
 *
 * EXAMPLE
 * add_filter('bx_sntls_virtual_pages_list', function ($pages)
 * {
 *    $pages[] = [
 *       'title'         => 'Title',
 *       'filename'      => 'filename',
 *       'endpoint_base' => 'base',
 *       'rewrites'      => [
 *          [
 *             'url'    => 'url/%',
 *             'params' => 'params=([0-9]*)'
 *          ]
 *       ]
 *    ];
 *
 *    return $pages;
 * });
 */
if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('bx_sntls_Virtual_Pages')) {
   /**
    * Tool to create URL endpoint to loads specifics a file templates.
    */
   class bx_sntls_Virtual_Pages
   {
      /**
       * @ignore
       */
      public function __construct()
      {
         add_action('init', [$this, 'add_tags'], 11);
         add_filter('template_include', [$this, 'load_template'], 11);
         add_filter('wp_title_parts', [$this, 'set_title'], 11);
      }

      public function add_tags()
      {
         $pages = apply_filters('bx_sntls_virtual_pages_list', []);

         if (empty($pages)) {
            return;
         }

         $tags[] = ['virtual_page', '([a-zA-Z0-9_-]*)'];

         foreach ($pages as $page) {
            $template = $page['filename'];

            foreach ($page['rewrites'] as $rewrite) {
               $query = 'index.php?virtual_page=' . $template;
               $url   = $rewrite['url'];

               if (isset($rewrite['params']) && !empty($rewrite['params'])) {
                  $params_url = $rewrite['params'];
                  $params     = explode('&', $params_url);

                  foreach ($params as $param) {
                     $tags[] = explode('=', $param);
                  }

                  $i = 1;

                  while ($pos = strpos($url, '%')) {
                     $url        = substr_replace($url, $tags[$i][1], $pos, 1);
                     $pos_p      = strpos($params_url, $tags[$i][1]);
                     $params_url = substr_replace($params_url, '$matches[' . $i . ']', $pos_p, strlen($tags[$i][1]));
                     $i++;
                  }

                  $query .= '&' . $params_url;
               }

               add_rewrite_rule('^' . $url . '/?$', $query, 'top');
            }
         }

         foreach ($tags as $tag) {
            add_rewrite_tag('%' . $tag[0] . '%', $tag[1]);
         }
      }

      public function load_template($template_path)
      {
         $template = get_query_var('virtual_page');
         $pages    = apply_filters('bx_sntls_virtual_pages_list', []);

         if (empty($template) || empty($pages)) {
            return $template_path;
         }

         $template_candidates = [];

         foreach ($pages as $page) {
            if ($page['filename'] !== $template) {
               continue;
            }

            if (isset($page['endpoint_base']) && !empty($page['endpoint_base'])) {
               $endpoint_base = $page['endpoint_base'];

               $template_candidates[] = "pages/{$endpoint_base}/endpoints/{$template}/_index.php";
               $template_candidates[] = "pages/{$endpoint_base}/endpoints/{$template}.php";
               $template_candidates[] = "pages/virtual-{$endpoint_base}/endpoints/{$template}/_index.php";
               $template_candidates[] = "pages/virtual-{$endpoint_base}/endpoints/{$template}.php";
            } else {
               $template_candidates[] = "pages/virtual-{$template}/_index.php";
               $template_candidates[] = "pages/virtual-{$template}.php";
            }

            break;
         }

         foreach ($template_candidates as $template_candidate) {
            $get_template_directory = get_template_directory();
            if (file_exists($get_template_directory . DIRECTORY_SEPARATOR . $template_candidate)) {
               return $get_template_directory . DIRECTORY_SEPARATOR . $template_candidate;
            }
         }

         return $template_path;
      }

      public function set_title($titles)
      {
         $pages = apply_filters('bx_sntls_virtual_pages_list', []);

         if (empty($pages)) {
            return $titles;
         }

         $template = get_query_var('virtual_page');

         foreach ($pages as $page) {
            if ($page['filename'] !== $template) {
               continue;
            }

            $titles[] = $page['title'];

            break;
         }

         return $titles;
      }
   }

   $Virtual_Pages = new bx_sntls_Virtual_Pages();
}
