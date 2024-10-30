<?php

if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('bx_sntls_AutoLoader')) {
   /**
    * Automatically includes classes based on file names and namespaces. Similar to the AutoLoader from composer.
    *
    * **IMPORTANT: This file must be included before use.**
    *
    * This class must be included and configured manually in the file `functions.php` before the theme.
    *
    * This class can handle multiple namespaces in multiple folders, utilizing an associative array where the key represents a namespace prefix and the value represents an array of base directories for classes in that namespace.
    *
    * The class to be included must be in a namespace, and have the same name as its file name.
    *
    * Code example:
    * ```php
    * bx_sntls_Utils::load_class('AutoLoader');
    * $BXLoader = new bx_sntls_AutoLoader();
    * ```
    *
    * For more, see bx_sntls_AutoLoader::add_namespace().
    *
    * @since 2.0.0
    */
   class bx_sntls_AutoLoader
   {
      /**
       * @ignore
       */
      protected $prefixes = [];

      /**
       * @ignore
       */
      public function __construct()
      {
         spl_autoload_register([$this, 'load_class']);
      }

      /**
       * Adds a base directory for a namespace prefix.
       *
       * ##### Description
       *
       * All classes to be autoload must be in namespace, and follow a specific path pattern.
       *
       * Code example:
       *
       * ```php
       * $BXLoader->add_namespace(
       *    'BX_Theme',
       *    bx_sntls_Utils::path_resolve([__DIR__, 'includes', 'entities'])
       * );
       * ```
       *
       * Here `BX_Theme` is the namespace; and `includes/entities` is the path to search for the respective file.
       *
       * Then when this class is required:
       * ```php
       * new BX_Theme\Posts\Video();
       * ```
       *
       * The autoloader will try to find the class file in `includes/entities/Posts/Video.php`
       * ```
       *
       * @param string $prefix   The namespace prefix.
       * @param string $base_dir A base directory for class files in the
       *                         namespace.
       */
      public function add_namespace(string $prefix, string $base_dir)
      {
         $prefix    = str_replace(['/', '\\'], '', $prefix);
         $prefix   .= DIRECTORY_SEPARATOR;
         $base_dir  = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $base_dir);
         $base_dir .= DIRECTORY_SEPARATOR;

         $this->prefixes[$prefix][] = $base_dir;
      }

      /**
       * @ignore
       */
      public function load_class(string $class)
      {
         $prefix = $class;

         while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix         = substr($class, 0, $pos + 1);
            $relative_class = substr($class, $pos + 1);
            $mapped_file    = $this->load_mapped_file($prefix, $relative_class);

            if ($mapped_file) {
               return $mapped_file;
            }

            $prefix = rtrim($prefix, '\\');
         }

         return false;
      }

      /**
       * @ignore
       */
      protected function load_mapped_file(string $prefix, string $relative_class)
      {
         $prefix = rtrim($prefix, '\\') . DIRECTORY_SEPARATOR;

         if (!isset($this->prefixes[$prefix])) {
            return false;
         }

         foreach ($this->prefixes[$prefix] as $base_dir) {
            $file  = $base_dir;
            $file .= str_replace(['\\'], DIRECTORY_SEPARATOR, $relative_class);
            $file .= '.php';

            if ($this->require_file($file)) {
               return $file;
            }
         }

         return false;
      }

      /**
       * @ignore
       */
      protected function require_file(string $file)
      {
         if (file_exists($file)) {
            require_once $file;

            return true;
         }

         return false;
      }
   }
}
