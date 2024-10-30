<?php

if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('bx_sntls_DiskCache')) {
   /**
    * Provides a way to store data in files for a period of time.
    *
    * **IMPORTANT: This file must be included before use.**
    *
    * Based on a key name, a file is created, storing any kind of data serialized, for a period of time.
    *
    * When this file is read, and this time has passed, the file is invalid and will be deleted.
    *
    * Code example:
    * ```php
    * bx_sntls_Utils::load_class('DiskCache');
    * $BXCache = new bx_sntls_DiskCache($label);
    * ```
    * #### Hooks
    *
    * By default, the files are store in `wp-content/cache/bx_sntls_cache/`.
    *
    * But this can be changed with this filter: `bx_sntls_DiskCache_path`, with a absolute path, as an array of paths to resolve.
    *
    * Code example:
    * ```php
    * add_filter('bx_sntls_DiskCache_path', function(){
    *    return [__DIR__, 'path', 'path'];
    * }
    * ```
    */
   class bx_sntls_DiskCache
   {
      /**
       * @ignore
       */
      private $filename;

      /**
       * Initiate a cache file handler.
       *
       * @param string $label A unique key for the file.
       *                      Will be sanitized for a filename.
       */
      public function __construct(string $label)
      {
         $path = apply_filters(
            'bx_sntls_DiskCache_path',
            [WP_CONTENT_DIR, 'cache', 'bx_sntls_cache']
         );
         $folder = bx_sntls_Utils::make_dir($path);

         if (false === $folder) {
            trigger_error(
               sprintf(
                  esc_attr__('%s não é acessível', 'bx-essentials'),
                  $folder
               ),
               E_USER_ERROR
            );

            return;
         }

         $folder .= DIRECTORY_SEPARATOR;

         $this->filename = $folder . sanitize_file_name($label) . '.serial';
      }

      /**
       * Creates or replaces a cache file with $data, valid until $duration.
       *
       * @param mixed $data Any type of data to store. Will be serialized.
       * @param int|string $duration Optional. A valid period of time to keep the data. A timestamp, or a string compatible with `strtotime`. Default: 1 day.
       *
       * @return bool|int Returns the number of bytes that were store, or false on failure
       */
      public function set(mixed $data, int|string $duration = '1 day')
      {
         $expires = is_int($duration) ? $duration : strtotime($duration);

         if (false === $expires) {
            trigger_error(
               sprintf(
                  esc_attr__(
                     '%s não é uma duração aceitável',
                     'bx-essentials'
                  ),
                  $duration
               ),
               E_USER_ERROR
            );
         }

         $cached_data = serialize([
            'expires' => $expires,
            'content' => $data,
         ]);

         return file_put_contents($this->filename, $cached_data);
      }

      /**
       * If the cache file stills exists, updates the $data without change the expiration. If not, create a new cache file with $data and $duration.
       *
       * @param mixed $data Any type of data to store. Will be serialized.
       * @param int|string $duration Optional. If the cache does not exists, a valid period of time to keep the data. A timestamp, or a string compatible with `strtotime`. Default: 1 day.
       *
       * @return false|int Returns the number of bytes that were store, or false on failure.
       */
      public function update(mixed $data, int|string $duration = '1 day')
      {
         $cached_file = $this->get(false);

         if (false === $cached_file) {
            return $this->set($data, $duration);
         }

         return $this->set($data, $cached_file['expires']);
      }

      /**
       * Checks and gets the file content previous stored.
       *
       * @param bool $only_content Set to return be only `content`, or also has the `expires`, as a array.
       *
       * @return array|bool|mixed If the cache file exists and is valid, returns as set in $only_content. If not, false.
       */
      public function get(bool $only_content = true)
      {
         if (file_exists($this->filename) && is_readable($this->filename)) {
            $file_content = unserialize(file_get_contents($this->filename));

            if ($file_content['expires'] > time()) {
               if ($only_content) {
                  return $file_content['content'];
               }

               return $file_content;
            }

            unlink($this->filename);
         }

         return false;
      }

      /**
       * Deletes the cache file previous created, if it still exists.
       *
       * @return bool Returns `true` on success; or `false` on failure.
       */
      public function delete()
      {
         if (file_exists($this->filename)) {
            if (is_readable($this->filename)) {
               return unlink($this->filename);
            }

            return false;
         }

         return true;
      }
   }
}
