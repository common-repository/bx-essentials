<?php

if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('bx_sntls_HTML_Compression')) {
   class bx_sntls_HTML_Compression
   {
      protected $html             = '';
      protected $compress_js      = false;
      protected $compress_css     = false;
      protected $info_comment     = true;
      protected $remove_comments  = true;
      protected $rewrite_settings = [];

      public function __construct($html)
      {
         if (!empty($html)) {
            $this->compress_js  = BX_Essentials::check_option('minify_html_js');
            $this->compress_css = BX_Essentials::check_option('minify_html_css');

            $this->rewrite_settings = [
               'cdn_hostname'    => apply_filters('bx_sntls_rewriter_remote_hostname', ''),
               'file_extensions' => apply_filters('bx_sntls_rewriter_file_extensions', ''),
            ];

            $this->parse_HTML($html);
         }
      }

      public function __toString()
      {
         return $this->html;
      }

      public function parse_HTML($html)
      {
         $html       = $this->minify_HTML($html);
         $this->html = $this->rewriter_local_urls($html);
      }

      protected function minify_HTML($html)
      {
         $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';

         preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

         $overriding = false;
         $raw_tag    = false;
         $html       = '';

         foreach ($matches as $token) {
            $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;

            $content = $token[0];

            if (is_null($tag)) {
               if (!empty($token['script'])) {
                  $strip = $this->compress_js;
               } elseif (!empty($token['style'])) {
                  $strip = $this->compress_css;
               } elseif ('<!--wp-html-compression no compression-->' === $content) {
                  $overriding = !$overriding;

                  continue;
               } elseif ($this->remove_comments) {
                  if (!$overriding && 'textarea' !== $raw_tag) {
                     $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
                  }
               }
            } else {
               if ('pre' === $tag || 'textarea' === $tag) {
                  $raw_tag = $tag;
               } elseif ('/pre' === $tag || '/textarea' === $tag) {
                  $raw_tag = false;
               } else {
                  if ($raw_tag || $overriding) {
                     $strip = false;
                  } else {
                     $strip   = true;
                     $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);
                     $content = str_replace(' />', '/>', $content);
                  }
               }
            }

            if ($strip) {
               $content = $this->remove_white_space($content, $token['script']);
            }

            $html .= $content;
         }

         return $html;
      }

      protected function remove_white_space($string, $script)
      {
         if (!empty($script)) {
            $string = preg_replace('/\\/\\*[\\s\\S]*?\\*\\/|([^\\:]|^)\\/\\/.*$/m', '$1', $string);
         }

         $string = str_replace("\t", ' ', $string);
         $string = str_replace("\n", '', $string);
         $string = str_replace("\r", '', $string);

         while (stristr($string, '  ')) {
            $string = str_replace('  ', ' ', $string);
         }

         return $string;
      }

      protected function rewriter_local_urls($html)
      {
         if (!bx_sntls_Utils::is_environment('local')) {
            return $html;
         }

         if (empty($this->rewrite_settings['cdn_hostname'])) {
            return $html;
         }

         if (!is_string($html)) {
            return $html;
         }

         $file_extensions = quotemeta($this->rewrite_settings['file_extensions']);

         $urls_regex = '#(?:(?:[\"\'\s=>,]|url\()\K|^)[^\"\'\s(=>,]+(' . $file_extensions . ')(\?[^\/?\\\"\'\s)>,]+)?(?:(?=\/?[?\\\"\'\s)>,])|$)#i';

         return preg_replace_callback($urls_regex, ['self', 'rewrite_url'], $html);
      }

      protected function rewrite_url($matches)
      {
         $file_url      = $matches[0];
         $site_hostname = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : parse_url(home_url(), PHP_URL_HOST);
         $cdn_hostname  = $this->rewrite_settings['cdn_hostname'];

         if (stripos($file_url, '//' . $site_hostname) !== false || stripos($file_url, '\/\/' . $site_hostname) !== false) {
            return substr_replace($file_url, $cdn_hostname, stripos($file_url, $site_hostname), strlen($site_hostname));
         }

         if (strpos($file_url, '//') !== 0 && strpos($file_url, '/') === 0) {
            return '//' . $cdn_hostname . $file_url;
         }

         if (strpos($file_url, '\/\/') !== 0 && strpos($file_url, '\/') === 0) {
            return '\/\/' . $cdn_hostname . $file_url;
         }

         return $file_url;
      }
   }
}
