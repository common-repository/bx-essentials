<?php

if (!defined('ABSPATH')) {
   exit;
}

if (!function_exists('debug')) {
   /**
    * Prints on the default debug file or shows in screen all arguments variables passed.
    *
    * ##### Description
    *
    * Also can be used in hooks. Code example:
    * ```php
    * add_action('edit_comment', 'debug', 10, 2);
    * add_filter('the_content', 'debug', 10);
    * ```
    *
    * Configuration:
    *
    * - If `WP_DEBUG` is false, does not do anything.
    * - If `WP_DEBUG_LOG` is false, does not print on the debug file.
    * - If `WP_DEBUG_DISPLAY` is false, does not print on screen.
    *
    * @param mixed $args Any quantity of arguments to debug.
    *
    * @return mixed The first argument passed.
    *
    * @since 1.0.0
    */
   function debug(...$args)
   {
      return bx_sntls_Utils::debug(func_get_args(), 'DEBUG_ARRAY_IN_SECOND_LEVEL');
   }
}

if (!function_exists('get_component')) {
   /**
    * Loads a template part, located in global components theme folder, as a folder or single file.
    *
    * ##### Description
    *
    * Code exemple:
    *
    * ```php
    * get_component('header');
    * # will try load in this order:
    * # components/header/_index.php
    * # components/header.php
    * ```
    *
    * @param string[]|string $slug The slug name for the global component, or array of slug for sub-components.
    * @param mixed[] $args Optional. Additional arguments passed to the template. Default empty array.
    * @param bool $once Optional. Sets to use `require_once` (true) or `require` (false). Default `require`.
    *
    * @return false|void void on success; false if the template does not exist.
    *
    * @since 1.4.0
    */
   function get_component(array|string $slug, array $args = [], bool $once = false)
   {
      return bx_sntls_Utils::get_component($slug, $args, $once);
   }
}

if (!function_exists('get_page_component')) {
   /**
    * Loads a template part, located in a page components theme folder, as a folder or single file.
    *
    * ##### Description
    *
    * Code exemple:
    *
    * ```php
    * get_page_component('home', 'header');
    * # will try load in this order:
    * # pages/home/components/header/_index.php
    * # pages/home/components/header.php
    *
    * get_page_component('home', ['header', 'menu']);
    * # will try load in this order:
    * # pages/home/components/header/components/menu/_index.php
    * # pages/home/components/header/components/menu.php
    * ```
    *
    * @param string $page The page name for the page.
    * @param string[]|string $slug The slug name for the component, or array of slug for sub-components.
    * @param mixed[] $args Optional. Additional arguments passed to the template. Default empty array.
    * @param bool $once Optional. Use `require` or `require_once`. Default `require`.
    *
    * @return false|void Returns void on success; false if the template does not exist.
    *
    * @since 1.4.0
    */
   function get_page_component(string $page, array|string $slug, array $args = [], bool $once = false)
   {
      return bx_sntls_Utils::get_page_component($page, $slug, $args, $once);
   }
}

if (!function_exists('is_bot_user_agent')) {
   /**
    * Checks if the an user agent is a known bot.
    *
    * @param string $user_agent Optional. An user agent to check.
    *                           Default `$_SERVER['HTTP_USER_AGENT']`.
    *
    * @return bool Returns if is a bot or not.
    *
    * @since 2.1.0
    */
   function is_bot_user_agent(string $user_agent = '')
   {
      return bx_sntls_Utils::is_bot($user_agent);
   }
}

if (!function_exists('is_environment')) {
   /**
    * Checks the current environment.
    *
    * @param string[]|string $environments Environment(s) to check. Accepts 'local', 'development', 'staging' or ‘production’.
    *
    * @return bool Returns if is in that environments or not.
    *
    * @since 2.0.0
    */
   function is_environment(array|string $environments)
   {
      return bx_sntls_Utils::is_environment($environments);
   }
}

if (!function_exists('render_svg')) {
   /**
    * Echo or return a SVG file content, changing classes if needed.
    *
    * ##### Description
    *
    * Code exemple:
    * ```php
    * render_svg('profile');
    * # will try load:
    * # assets/icons/profile.svg
    * ```
    *
    * @param string $file File name to get on theme folder assets/icons.
    * @param string $classes Optional. List of classes separated with spaces. Default is empty string.
    * @param bool $echo Optional. If the content should be echo or return. Default is true.
    *
    * @return string|void Returns the SVG content if $echo is false
    *
    * @since 2.0.0
    */
   function render_svg(string $file, string $classes = '', bool $echo = true)
   {
      return bx_sntls_Utils::render_svg($file, $classes, $echo);
   }
}

if (!function_exists('bx_sntls_include_from')) {
   /**
    * Includes PHP files from a folder.
    *
    * @since 1.0.0
    * @deprecated 2.0.0 We don't recommend include all files from a folder.
    * @see bx_sntls_Utils::include_from() If needed, use this instead.
    */
   function bx_sntls_include_from(array $paths, array $files = [], string $method = 'require_once')
   {
      return bx_sntls_Utils::include_from($paths, $files, $method);
   }
}
