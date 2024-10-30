<?php

if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('bx_sntls_Utils')) {
   /**
    * Utilities for many proposes.
    */
   class bx_sntls_Utils
   {
      /**
       * @see debug() See the alias for full documentation.
       */
      public static function debug()
      {
         $args       = func_get_args();
         $file_index = 0;

         if (isset($args[1]) && 'DEBUG_ARRAY_IN_SECOND_LEVEL' === $args[1]) {
            $args       = $args[0];
            $file_index = 1;
         }

         if (!WP_DEBUG) {
            return $args[0];
         }

         $files = debug_backtrace();
         $log   = "\n[DEBUG] {$files[$file_index]['file']}:{$files[$file_index]['line']}";

         foreach ($args as $key => $arg) {
            $key  = str_pad($key, 3, '0', STR_PAD_LEFT);

            $log .= "\n[ {$key} ] ";
            $log .= var_export($arg, 1);
         }

         if (WP_DEBUG_LOG) {
            error_log($log);
         }

         if (
            WP_DEBUG_DISPLAY &&
            !defined('XMLRPC_REQUEST') &&
            !defined('REST_REQUEST') &&
            !defined('MS_FILES_REQUEST') &&
            !(defined('WP_INSTALLING') && WP_INSTALLING) &&
            !wp_doing_ajax() &&
            !wp_is_json_request()
         ) {
            print_r("<pre class=\"bx_sntls_debug\">$log</pre>");
         }

         do_action('bx_sntls_debug', $log);

         return $args[0];
      }

      /**
       * @see get_component() See the alias for full documentation.
       */
      public static function get_component(array|string $slug, array $args = [], bool $once = false)
      {
         if (is_array($slug)) {
            $slug = implode('/components/', $slug);
         }

         $templates[] = "components/{$slug}/_index.php";
         $templates[] = "components/{$slug}.php";

         if (!locate_template($templates, true, $once, $args)) {
            return false;
         }
      }

      /**
       * @see get_page_component() See the alias for full documentation.
       */
      public static function get_page_component(string $page, array|string $slug, array $args = [], bool $once = false)
      {
         if (is_array($slug)) {
            $slug = implode('/components/', $slug);
         }

         $templates[] = "pages/{$page}/components/{$slug}/_index.php";
         $templates[] = "pages/{$page}/components/{$slug}.php";

         if (!locate_template($templates, true, $once, $args)) {
            return false;
         }
      }

      /**
       * Generates a key.
       *
       * @param string $key A text to be convert into a safe key.
       * @param string $method Optional. The method of conversion. Accepts `hash` (for md5 hashing), `sanitize` (for using in transient keys) or `default` (to keep the same).
       *  If the plaintext must be  or sanitize.
       *
       * @return string Returns the string if no method is given or string with prefix 'bx_' for other methods
       *
       * @throws WP_Error when no valid method is given or the key is too long
       */
      public static function generate_key(string $key, string $method = 'default')
      {
         if ('default' === $method) {
            return $key;
         }

         $prefix = 'bx_';

         if ('hash' === $method) {
            return $prefix . hash('md5', $key);
         }

         if ('sanitize' === $method) {
            if (strlen($prefix . $key) > 172) {
               throw new WP_Error('key_too_long', esc_attr__('A chave é muito longa.', 'bx-essentials'));
            }

            return $prefix . sanitize_key($key);
         }

         throw new WP_Error('key_invalid', esc_attr__('É necessário informar um método válido.', 'bx-essentials'));
      }

      /**
       * Includes PHP files from a folder.
       *
       * @param string[]|string $paths Path where files are. Usually `__DIR__`, or an array of directories to resolve.
       * @param string[]|string $files List of files or single filename. Default: All PHP files in $paths without `_` (underline) prefix.
       * @param string $method The method to import file. Accepts `include_once` or `require_once`. Default: `require_once`.
       */
      public static function include_from(array|string $paths, array|string $files = [], string $method = 'require_once')
      {
         $path = is_array($paths) ? self::path_resolve($paths) : $paths;

         if (!is_dir($path)) {
            trigger_error(sprintf(esc_attr__('%s não é um diretório.', 'bx-essentials'), $path), E_USER_ERROR);
         }

         if (empty($files)) {
            $scandir          = true;
            $files_to_include = scandir($path);
         } else {
            $scandir          = false;
            $files_to_include = is_array($files) ? $files : [$files];
         }

         foreach ($files_to_include as $file) {
            $path_file = $path . DIRECTORY_SEPARATOR . $file;

            if ($scandir) {
               if (!is_file($path_file)) {
                  continue;
               }

               if ('.php' !== substr($file, -4, 4)) {
                  continue;
               }

               if ('_' === substr($file, 0, 1)) {
                  continue;
               }

               if ('index.php' === $file) {
                  continue;
               }
            } else {
               if ('.php' !== substr($file, -4, 4)) {
                  $path_file .= '.php';
               }
            }

            if (!file_exists($path_file)) {
               trigger_error(sprintf(esc_attr__('%s não existe.', 'bx-essentials'), $path_file), E_USER_ERROR);
            }

            if ('require_once' === $method) {
               require_once $path_file;
            } elseif ('include_once' === $method) {
               include_once $path_file;
            } else {
               trigger_error(sprintf(esc_attr__('%s não é include_once ou require_once.', 'bx-essentials'), $method), E_USER_ERROR);
            }
         }
      }

      /**
       * @see is_bot_user_agent() See the alias for full documentation.
       */
      public static function is_bot(string $user_agent = '')
      {
         if (empty($user_agent)) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
         }

         if (empty($user_agent)) {
            return false;
         }

         $ua = stripslashes($user_agent);

         /* spellchecker: disable */
         $bot_agents = [
            'ahrefsbot',
            'alexa',
            'altavista',
            'applebot',
            'ask jeeves',
            'attentio',
            'baiduspider',
            'bingbot',
            'chtml generic',
            'cloudflare-alwaysonline',
            'crawler',
            'Discordbot',
            'domaintunocrawler',
            'facebot',
            'fastmobilecrawl',
            'feedfetcher-google',
            'firefly',
            'froogle',
            'germcrawler',
            'gigabot',
            'googlebot-mobile',
            'googlebot',
            'grapeshotcrawler',
            'grokkit-crawler',
            'heritrix',
            'httrack',
            'ia_archiver',
            'iescholar',
            'infoseek',
            'irlbot',
            'jumpbot',
            'kraken',
            'linkcheck',
            'linkdexbot',
            'livelapbot',
            'lycos',
            'mediapartners',
            'mediobot',
            'motionbot',
            'mshots',
            'msnbot',
            'openbot',
            'openhosebot',
            'pingdom.com_bot',
            'pss-webkit-request',
            'python-requests',
            'pythumbnail',
            'queryseekerspider',
            'scooter',
            'slurp',
            'snapbot',
            'spider',
            'taptubot',
            'technoratisnoop',
            'teoma',
            'tweetmemebot',
            'twiceler',
            'twitterbot',
            'WhatsApp',
            'yahooseeker',
            'yahooysmcm',
            'yammybot',
            'yandexbot',
         ];
         /* spellchecker: enable */

         foreach ($bot_agents as $bot_agent) {
            if (false !== stripos($ua, $bot_agent)) {
               return true;
            }
         }

         return false;
      }

      /**
       * @see is_environment() See the alias for full documentation.
       */
      public static function is_environment(string|array $environments)
      {
         if (!is_array($environments)) {
            $environments = [$environments];
         }

         return in_array(wp_get_environment_type(), $environments);
      }

      /**
       * Includes a class file, from the classes plugin folder.
       *
       * @param string $class_name The class file name
       * @param string $method     The method to include the file. Accepts `include_once` or `require_once`. Default: `require_once`.
       */
      public static function load_class($class_name, $method = 'require_once')
      {
         $class_path = self::path_resolve([dirname(BX_MAIN_FILE), 'includes', 'classes', "{$class_name}.php"]);

         if (file_exists($class_path)) {
            if ('require_once' === $method) {
               require_once $class_path;
            } elseif ('include_once' === $method) {
               include_once $class_path;
            } else {
               trigger_error(sprintf(esc_attr__('%s não é include_once ou require_once.', 'bx-essentials'), $method), E_USER_ERROR);
            }
         }
      }

      /**
       * Creates a directory.
       *
       * @param array|string $path The path for the new directory; or an array of directories to resolve.
       *
       * @return bool|string If success, the path of the new directory. If fail, `false`.
       */
      public static function make_dir(string|array $path)
      {
         if (!is_array($path)) {
            $path = [$path];
         }

         $folder = self::path_resolve($path);

         if (!file_exists($folder)) {
            $chmod_dir = 0755;

            if (defined('FS_CHMOD_DIR')) {
               $chmod_dir = FS_CHMOD_DIR;
            }

            mkdir($folder, $chmod_dir, true);
         }

         if (is_dir($folder) && is_writable($folder)) {
            return $folder;
         }

         return false;
      }

      /**
       * Resolves an array of directories, with the correct slash for the current operational system.
       *
       * @param array $paths array of string of directories names.
       *
       * @return string Returns the full path.
       */
      public static function path_resolve(array $paths)
      {
         if (!is_array($paths)) {
            trigger_error(esc_attr__('Primeiro argumento precisa ser um array.', 'bx-essentials'), E_USER_ERROR);
         }

         return implode(DIRECTORY_SEPARATOR, $paths);
      }

      /**
       * @see render_svg() See the alias for full documentation.
       */
      public static function render_svg(string $file, string $classes = '', bool $echo = true)
      {
         $file        = str_replace('.svg', '', $file);
         $file        = self::path_resolve(['assets', 'icons', "{$file}.svg"]);
         $svg_file    = get_theme_file_path($file);
         $svg_content = '';

         if (file_exists($svg_file)) {
            $svg_content = file_get_contents($svg_file);

            if (!empty($classes)) {
               $classes = trim($classes);

               if (stripos($svg_content, 'class="')) {
                  $svg_content = str_replace('class="', 'class="' . $classes . ' ', $svg_content);
               } else {
                  $svg_content = str_replace('<svg ', '<svg class="' . $classes . '" ', $svg_content);
               }
            }

            $svg_content = str_replace('<svg ', '<svg aria-hidden="true" ', $svg_content);
         } else {
            self::debug('Icon not found: ' . $file);
         }

         if ($echo) {
            echo $svg_content;
         } else {
            return $svg_content;
         }
      }

      /**
       * Identifies if a URL is from a self hosted file and return his path in the server.
       *
       * @param string $url
       *
       * @return bool|string Returns the full path, or false on failure or external assets.
       */
      public static function url_to_path(string $url)
      {
         if (0 === strpos($url, content_url('cache'))) {
            return false;
         }

         if (0 === strpos($url, get_bloginfo('wpurl'))) {
            $parsed   = parse_url(str_replace(get_bloginfo('wpurl'), ABSPATH, $url));
            $filepath = $parsed['path'];

            if (isset($parsed['scheme'])) {
               $filepath = $parsed['scheme'] . ':' . $filepath;
            }
         }

         if (0 === strpos($url, '/')) {
            $filepath = ABSPATH . substr($url, 1);
         }

         if (isset($filepath)) {
            $filepath = str_replace('/', DIRECTORY_SEPARATOR, $filepath);

            if (file_exists($filepath)) {
               return $filepath;
            }
         }

         return false;
      }
   }
}
