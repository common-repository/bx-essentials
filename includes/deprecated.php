<?php

if (!defined('ABSPATH')) {
   exit;
}

if (!function_exists('bx_sntls_include_from')) {
   function bx_sntls_include_from($paths, $files = [], $method = 'require_once')
   {
      BX_Essentials::deprecated_function(__FUNCTION__, '2.0.0', 'bx_sntls_Utils::include_from()');

      return bx_sntls_Utils::include_from($paths, $files, $method);
   }
}
