<?php

if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('bx_sntls_Validator')) {
   /**
    * Provides resources to validades data.
    */
   class bx_sntls_Validator
   {
      private $errors = [];
      private $fields = [];
      private $key;
      private $label;
      private $value;
      private $type;
      private $optional;
      private $required_message;
      private $date_format;

      /**
       * Add new field to validated.
       *
       * @param mixed $value the value to be validated
       */
      public function add(string $key, string $label, mixed $value)
      {
         $this->key              = $key;
         $this->label            = $label;
         $this->value            = $value;
         $this->optional         = true;
         $this->type             = '';
         $this->required_message = '';
         $this->date_format      = '';

         return $this;
      }

      /**
       * Set field as required, can't be empty.
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function required(string|array $optionals = [])
      {
         $optionals = $this->parse_optionals($optionals);

         $this->optional         = false;
         $this->required_message = $optionals['message'];

         return $this;
      }

      /**
       * Set field as string. Sets a type.
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       * @param bool $optionals['multiline'] flag indicating if the field has multiline
       */
      public function is_string(string|array $optionals = [])
      {
         $this->set_type();

         if ($this->checks_required()) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'multiline' => false,
         ]);

         if (!is_string($this->value)) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser texto.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $this->value = $optionals['multiline'] ? sanitize_textarea_field($this->value) : sanitize_text_field($this->value);

         $this->add_field($this->value);

         return $this;
      }

      /**
       * Set field as e-mail. Sets a type.
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_email(string|array $optionals = [])
      {
         $this->set_type();

         if ($this->checks_required()) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         /**
          * https://support.google.com/mail/answer/9211434?hl=pt-BR
          * yahoo
          * hotmail.
          *
          * Você não pode ter mais de um ‘.’ ou ‘_’ em uma sequência.
          */
         $filtered_email = filter_var($this->value, FILTER_VALIDATE_EMAIL);

         if (false === $filtered_email) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um e-mail válido.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $this->add_field(trim($filtered_email));

         return $this;
      }

      /**
       * Set field as integer. Sets a type.
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_int(string|array $optionals = [])
      {
         $this->set_type('number');

         if ($this->optional && !is_numeric($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $filtered_value = filter_var($this->value, FILTER_VALIDATE_INT);

         if (false === $filtered_value) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um número inteiro.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $this->add_field($filtered_value);

         return $this;
      }

      /**
       * Set field as float. Sets a type.
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_float(string|array $optionals = [])
      {
         $this->set_type('number');

         if ($this->checks_required()) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $filtered_value = filter_var($this->value, FILTER_VALIDATE_FLOAT);

         if (false === $filtered_value) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um número.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $this->add_field($filtered_value);

         return $this;
      }

      /**
       * Set field as boolean. Sets a type.
       * Accept also string for "true" "false".
       *
       * @param string|array $optionals if string, a custom error message, or a array with ['message', 'allow_string']
       * @param bool $optionals['allow_string'] Accept also "1", "on", "yes", "0", "off" and "no"
       */
      public function is_bool(string|array $optionals = [])
      {
         $this->set_type('bool');

         if ($this->checks_required()) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'allow_string' => true,
         ]);

         if ($optionals['allow_string']) {
            $filtered_value = filter_var($this->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
         } else {
            if (is_bool($this->value)) {
               $filtered_value = $this->value;
            } elseif (is_string($this->value)) {
               $value = strtolower($this->value);

               if ('false' === $value) {
                  $filtered_value = false;
               } elseif ('true' === $value) {
                  $filtered_value = true;
               } else {
                  $filtered_value = null;
               }
            } else {
               $filtered_value = null;
            }
         }

         if (null === $filtered_value) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser verdadeiro ou falso.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $this->add_field($filtered_value);

         return $this;
      }

      /**
       * @see bx_sntls_Validator::is_datetime()
       */
      public function is_date(string|array $optionals = [])
      {
         $optionals = $this->parse_optionals($optionals, [
            'format' => 'Y-m-d',
         ]);

         return $this->is_datetime($optionals);
      }

      /**
       * @see bx_sntls_Validator::is_datetime()
       */
      public function is_time(string|array $optionals = [])
      {
         $optionals = $this->parse_optionals($optionals, [
            'format' => 'H:i:s',
         ]);

         return $this->is_datetime($optionals);
      }

      /**
       * Set field as datetime. Sets a type.
       *
       * @param string|array $optionals if string, a custom error message, or a array with ['message', 'format']
       * @param string $optionals['format']
       */
      public function is_datetime(string|array $optionals = [])
      {
         $this->set_type('date');

         if ($this->checks_required()) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'format' => 'Y-m-d\TH:i',
         ]);

         $this->date_format = $optionals['format'];

         $filtered_date = DateTime::createFromFormat($this->date_format, $this->value);

         if (false === $filtered_date || $filtered_date->format($this->date_format) !== $this->value) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ter um formato válido.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $this->add_field(date_format($filtered_date, $this->date_format));

         return $this;
      }

      /**
       * Set field as array. Sets a type.
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_array(string|array $optionals = [])
      {
         $this->set_type('array');

         if ($this->checks_required()) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         if (!is_array($this->value)) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser uma lista.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $this->add_field($this->value);

         return $this;
      }

      /**
       * Set field as url. Sets a type.
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_url(string|array $optionals = [])
      {
         $this->set_type();

         if ($this->checks_required()) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         if (!preg_match('/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}((:[0-9]{1,5})?\\/.*)?$/i', $this->value)) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser uma URL.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $this->add_field(trim($this->value));

         return $this;
      }

      /**
       * Checks if it is a file.
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'mimetype']
       * @param array $optionals['mimetype'] list of allowed mimetypes. Mimetypes, for example, "audio/mpeg" or "image/jpeg". Wildcards are allowed, like as "image" or "image/*".
       */
      public function is_file(string|array $optionals = [])
      {
         $this->set_type('file');

         if ($this->checks_required()) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'mimetype' => ['*'],
         ]);

         if (!is_file($this->value['tmp_name'])) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um arquivo.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $file_mimetype = mime_content_type($this->value['tmp_name']);

         $mimetype_labels = implode(', ', $optionals['mimetype']);

         if (!wp_match_mime_types($mimetype_labels, $file_mimetype)) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um arquivo com formato %s.', 'bx-essentials'), $this->label, $mimetype_labels),
               $optionals['message']
            );

            return $this;
         }

         $this->add_field($this->value);

         return $this;
      }

      /**
       * Checks if it is a CNPJ.
       * Type needs to be "string".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'mask']
       * @param string $optionals['mask']
       */
      public function is_cnpj(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'mask' => 'cnpj',
         ]);

         $this->checks_field_type(['string']);

         $filtered_cnpj = preg_replace('/[^0-9]/is', '', $this->value);

         if (strlen($filtered_cnpj) !== 14) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ter 14 dígitos.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $this->mask($optionals['mask']);

         for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $m = ($t - 7), $i = 0; $i < $t; $i++) {
               $d += $filtered_cnpj[$i] * $m;
               $m = (2 == $m ? 9 : --$m);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($filtered_cnpj[$i] != $d) {
               $this->add_error(
                  sprintf(esc_attr__('%s: Precisa ser um CNPJ válido.', 'bx-essentials'), $this->label),
                  $optionals['message'],
               );

               return $this;
            }
         }

         return $this;
      }

      /**
       * Checks if it is a CPF.
       * Type needs to be "string".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'mask']
       * @param string $optionals['mask']
       */
      public function is_cpf(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'mask' => 'cpf',
         ]);

         $this->checks_field_type(['string']);

         $filtered_cpf = preg_replace('/[^0-9]/is', '', $this->value);

         if (strlen($filtered_cpf) !== 11) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ter 11 dígitos.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         if (preg_match('/(\d)\1{10}/', $filtered_cpf)) {
            $this->add_error(
               sprintf(esc_attr__('%s: Não é possível validar a sequência de dígitos.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $this->mask($optionals['mask']);

         for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
               $d += $filtered_cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($filtered_cpf[$c] != $d) {
               $this->add_error(
                  sprintf(esc_attr__('%s: Precisa ser um CPF válido.', 'bx-essentials'), $this->label),
                  $optionals['message'],
               );

               return $this;
            }
         }

         return $this;
      }

      /**
       * Checks if it is a CPF or CNPJ.
       * Type needs to be "string" or "number".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_cpf_or_cnpj(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $filtered_cpf_cnpj = preg_replace('/[^0-9]/is', '', $this->value);

         if (strlen($filtered_cpf_cnpj) === 11) {
            return $this->is_cpf($optionals);
         }

         if (strlen($filtered_cpf_cnpj) === 14) {
            return $this->is_cnpj($optionals);
         }

         $this->add_error(
            sprintf(esc_attr__('%s: Precisa ser um CPF ou CNPJ.', 'bx-essentials'), $this->label),
            $optionals['message'],
         );

         return $this;
      }

      /**
       * Checks if it is a hex color.
       * Type needs to be "string".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_hex_color(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['string']);

         if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/u', $this->value)) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser uma cor em hexadecimal.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Checks if is in the provided list.
       * Type needs to be "string", "date" or "number".
       *
       * @param array $haystack
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_in(array $haystack, string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['string', 'date', 'number']);

         if (!in_array($this->value, $haystack)) {
            $haystack = json_encode($haystack);

            $this->add_error(
               sprintf(esc_attr__('%s: Precisa estar em %s.', 'bx-essentials'), $this->label, $haystack),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Checks if is not in the provided list.
       * Type needs to be "string", "date" or "number".
       *
       * @param array $haystack
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_not_in(array $haystack, string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['string', 'date', 'number']);

         if (in_array($this->value, $haystack)) {
            $haystack = json_encode($haystack);

            $this->add_error(
               sprintf(esc_attr__('%s: Não deve estar em %s.', 'bx-essentials'), $this->label, $haystack),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Check if matches a regular expression pattern.
       * Type needs to be "string", "number" or "array".
       *
       * @param string $pattern the regular expression pattern to match against the variable
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function regex(string $pattern, string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['string', 'number', 'array']);

         $values = is_array($this->value) ? $this->value : [$this->value];

         foreach ($values as $value) {
            if (!preg_match($pattern, $value)) {
               $this->add_error(
                  sprintf(esc_attr__('%s: Precisa ter o formato esperado.', 'bx-essentials'), $this->label),
                  $optionals['message'],
               );

               break;
            }
         }

         return $this;
      }

      /**
       * Check if it contains at least one or is all numbers.
       * Type needs to be "string", "number", "date" or "array".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'only_number']
       * @param bool $optionals['only_number'] if all string must be a number
       */
      public function has_number(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'only_number' => false,
         ]);

         $this->checks_field_type(['string']);

         if ($optionals['only_number']) {
            if (!ctype_digit($this->value)) {
               $this->add_error(
                  sprintf(esc_attr__('%s: Precisa conter somente números.', 'bx-essentials'), $this->label),
                  $optionals['message'],
               );
            }

            return $this;
         }

         if (preg_match('/\d/', $this->value) < 1) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa conter pelo menos um número.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Check if it has at least one lowercase character or if all characters should be lowercase.
       * Type needs to be "string".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'only_lowercase']
       * @param bool $optionals['only_lowercase'] flag indicating if all characters should be lowercase (true) or not (false)
       */
      public function has_lowercase(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'only_lowercase' => false,
         ]);

         $this->checks_field_type(['string']);

         if ($optionals['only_lowercase']) {
            if (!ctype_lower($this->value)) {
               $this->add_error(
                  sprintf(esc_attr__('%s: Precisa conter somente letras minúsculas.', 'bx-essentials'), $this->label),
                  $optionals['message'],
               );
            }

            return $this;
         }

         if (preg_match('/[a-z]/', $this->value) < 1) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa conter pelo menos uma letra minúscula.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Check if it has at least one uppercase character or if all characters should be uppercase.
       * Type needs to be "string".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'only_uppercase']
       * @param bool $optionals['only_uppercase'] flag indicating if all characters should be uppercase (true) or not (false)
       */
      public function has_uppercase(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'only_uppercase' => false,
         ]);

         $this->checks_field_type(['string']);

         if ($optionals['only_uppercase']) {
            if (!ctype_upper($this->value)) {
               $this->add_error(
                  sprintf(esc_attr__('%s: Precisa conter somente letras maiúsculas.', 'bx-essentials'), $this->label),
                  $optionals['message'],
               );
            }

            return $this;
         }

         if (preg_match('/[A-Z]/', $this->value) < 1) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa conter pelo menos uma letra maiúscula.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Check if it contains at least one character from a provided list.
       * Type needs to be "string".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'quantity', 'chars']
       * @param int $optionals['quantity'] the minimum number of characters needed from the list (default: 1)
       * @param string $optionals['chars'] the list of characters to check against
       */
      public function has_chars(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'quantity' => 1,
            'chars'    => '!"#$%&\'()*+,-./:;>=<?@\\[]^_`{|}~',
         ]);

         $this->checks_field_type(['string']);

         $chars = str_split($optionals['chars']);

         $count = 0;

         foreach ($chars as $char) {
            if (strpos($this->value, $char) !== false) {
               $count++;
            }

            if ($count >= $optionals['quantity']) {
               return $this;
            }
         }

         $this->add_error(
            sprintf(esc_attr__('%s: Precisa conter pelo menos %d caracteres especiais.', 'bx-essentials'), $this->label, $optionals['quantity']),
            $optionals['message'],
         );

         return $this;
      }

      /**
       * Check if it a valid credit card number.
       * Type needs to be "string" or "number".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_credit_card(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['string', 'number']);

         $filtered_credit_card = preg_replace('/[^0-9]/is', '', $this->value);

         $length = strlen($filtered_credit_card);

         if ($length < 13 || $length > 19) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um número de cartão válido.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $sum     = 0;
         $is_even = false;

         for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $filtered_credit_card[$i];

            if ($is_even) {
               $digit *= 2;

               if ($digit > 9) {
                  $digit = ($digit % 10) + 1;
               }
            }

            $sum += $digit;
            $is_even = !$is_even;
         }

         if ($sum % 10 !== 0) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um número de cartão válido.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Checks if the string contains.
       * Type needs to be "string", "number", "date" or "array".
       *
       * @param int|string $needle
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'case_sensitive']
       * @param bool $optionals['case_sensitive'] flag indicating if is case sensitive
       */
      public function contains(int|string $needle, string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'case_sensitive' => false,
         ]);

         $this->checks_field_type(['string', 'number', 'date', 'array']);

         switch ($this->type) {
            case 'array':
               if (!in_array($needle, $this->value, $optionals['case_sensitive'])) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa conter %s.', 'bx-essentials'), $this->label, $needle),
                     $optionals['message'],
                  );
               }

               break;
            default:
               if (
                  (false === strpos($this->value, $needle) && $optionals['case_sensitive'])
                  || (false === stristr($this->value, $needle) && !$optionals['case_sensitive'])
               ) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa conter %s.', 'bx-essentials'), $this->label, $needle),
                     $optionals['message'],
                  );
               }

               break;
         }

         return $this;
      }

      /**
       * Checks if the string does not contain.
       * Type needs to be "string", "number", "date" or "array".
       *
       * @param int|string $needle
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'case_sensitive']
       * @param bool $optionals['case_sensitive'] flag indicating if is case sensitive
       */
      public function not_contain(int|string $needle, string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'case_sensitive' => false,
         ]);

         $this->checks_field_type(['string', 'number', 'date', 'array']);

         switch ($this->type) {
            case 'array':
               if (in_array($needle, $this->value, $optionals['case_sensitive'])) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Não deve conter %s.', 'bx-essentials'), $this->label, $needle),
                     $optionals['message'],
                  );
               }

               break;
            default:
               if (
                  (false !== strpos($this->value, $needle) && $optionals['case_sensitive'])
                  || (false !== stristr($this->value, $needle) && !$optionals['case_sensitive'])
               ) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Não deve conter %s.', 'bx-essentials'), $this->label, $needle),
                     $optionals['message'],
                  );
               }

               break;
         }

         return $this;
      }

      /**
       * Checks if it is empty. Cant be optional.
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_empty(string|array $optionals = [])
      {
         $optionals = $this->parse_optionals($optionals);

         switch ($this->type) {
            case 'file':
               if (4 !== $this->value['error']) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Não deve conter um arquivo.', 'bx-essentials'), $this->label),
                     $optionals['message'],
                  );
               }

               break;
            case 'bool':
               if ('' !== $this->value) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Não deve estar vazio.', 'bx-essentials'), $this->label),
                     $optionals['message'],
                  );
               }

               break;
            default:
               if (!empty($this->value)) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Não deve estar vazio.', 'bx-essentials'), $this->label),
                     $optionals['message'],
                  );
               }

               break;
         }

         return $this;
      }

      /**
       * Checks that the value is not less than the minimum.
       * Type needs to be "string", "number", "file", "date" or "array".
       *
       * @param float|string $length if field type is "file", must be informed in MB
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function min(float|string $length, string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['string', 'number', 'file', 'date', 'array']);

         switch ($this->type) {
            case 'array':
               if (count((array) $this->value) < $length) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ter no mínimo %s itens.', 'bx-essentials'), $this->label, round($length)),
                     $optionals['message'],
                  );
               }

               break;
            case 'number':
               if ($this->value < $length) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ser maior ou igual a %s.', 'bx-essentials'), $this->label, $length),
                     $optionals['message'],
                  );
               }

               break;
            case 'file':
               if ($this->value['size'] < $length * 1048576) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ser um arquivo maior ou igual a %s MB.', 'bx-essentials'), $this->label, number_format($length, 2)),
                     $optionals['message'],
                  );
               }

               break;
            case 'date':
               $length_date = DateTime::createFromFormat($this->date_format, $length);
               $input_date  = DateTime::createFromFormat($this->date_format, $this->value);

               if (false === $length_date) {
                  trigger_error(
                     sprintf(esc_attr__('O formato esperado é %s.', 'bx-essentials'), $this->date_format),
                     E_USER_ERROR
                  );
               } else {
                  if ($input_date > $length_date) {
                     $this->add_error(
                        sprintf(esc_attr__('%s: Precisa ser anterior a data %s.', 'bx-essentials'), $this->label, $length_date->format($this->date_format)),
                        $optionals['message'],
                     );
                  }
               }

               break;
            default:
               if (strlen($this->value) < $length) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ter no mínimo %s caracteres.', 'bx-essentials'), $this->label, round($length)),
                     $optionals['message'],
                  );
               }

               break;
         }

         return $this;
      }

      /**
       * Checks that the value does not exceed the maximum.
       * Type needs to be "string", "number", "file", "date" or "array".
       *
       * @param float|string $length if field type is "file", must be informed in MB
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function max(float|string $length, string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['string', 'number', 'file', 'date', 'array']);

         switch ($this->type) {
            case 'array':
               if ($length < count((array) $this->value)) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ter no máximo %s itens.', 'bx-essentials'), $this->label, round($length)),
                     $optionals['message'],
                  );
               }

               break;
            case 'number':
               if ($length < $this->value) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ser menor ou igual a %s.', 'bx-essentials'), $this->label, $length),
                     $optionals['message'],
                  );
               }

               break;
            case 'file':
               if ($length * 1048576 < $this->value['size']) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ser um arquivo menor ou igual a %s MB.', 'bx-essentials'), $this->label, number_format($length, 2)),
                     $optionals['message'],
                  );
               }

               break;
            case 'date':
               $length_date = DateTime::createFromFormat($this->date_format, $length);
               $input_date  = DateTime::createFromFormat($this->date_format, $this->value);

               if (false === $length_date) {
                  trigger_error(
                     sprintf(esc_attr__('O formato esperado é %s.', 'bx-essentials'), $this->date_format),
                     E_USER_ERROR
                  );
               } else {
                  if ($input_date < $length_date) {
                     $this->add_error(
                        sprintf(esc_attr__('%s: Precisa ser uma data posterior a %s.', 'bx-essentials'), $this->label, $length_date->format($this->date_format)),
                        $optionals['message'],
                     );
                  }
               }

               break;
            default:
               if ($length < strlen($this->value)) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ter no máximo %s caracteres.', 'bx-essentials'), $this->label, round($length)),
                     $optionals['message'],
                  );
               }

               break;
         }

         return $this;
      }

      /**
       * Checks if it is only letters.
       * Type needs to be "string" or "array".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_alpha(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['string', 'array']);

         $values = is_array($this->value) ? $this->value : [$this->value];

         foreach ($values as $value) {
            if (!preg_match('/^[a-zA-Z]+$/', $value)) {
               $this->add_error(
                  sprintf(esc_attr__('%s: Precisa conter apenas letras sem acentos.', 'bx-essentials'), $this->label),
                  $optionals['message'],
               );

               break;
            }
         }

         return $this;
      }

      /**
       * Checks if it is only letters or numbers.
       * Type needs to be "string" or "array".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_alphanumeric(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['string', 'array']);

         $values = is_array($this->value) ? $this->value : [$this->value];

         foreach ($values as $value) {
            if (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
               $this->add_error(
                  sprintf(esc_attr__('%s: Precisa conter apenas letras sem acentos ou números.', 'bx-essentials'), $this->label),
                  $optionals['message'],
               );

               break;
            }
         }

         return $this;
      }

      /**
       * Checks if it is a positive number.
       * Type needs to be "number".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_positive(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['number']);

         if ($this->value <= 0) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um número positivo.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Checks if it is a negative number.
       * Type needs to be "number".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_negative(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['number']);

         if ($this->value >= 0) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um número negativo.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Checks if it is a zero.
       * Type needs to be "number".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_zero(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['number']);

         if (0 == $this->value) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser zero.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Checks if it is the same value.
       *
       * @param mixed $compare
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'also_type']
       * @param bool $optionals['also_type'] flag indicating if the type must be the same
       */
      public function is_equals(mixed $compare, string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'also_type' => true,
         ]);

         switch ($this->type) {
            case 'date':
               $date_compare = DateTime::createFromFormat($this->date_format, $compare);

               if (false === $date_compare || $date_compare->format($this->date_format) !== $this->value) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ser uma data igual a %s.', 'bx-essentials'), $this->label, $compare),
                     $optionals['message'],
                  );

                  return $this;
               }

               break;

            default:
               if (($this->value != $compare && !$optionals['also_type']) || ($this->value !== $compare && $optionals['also_type'])) {
                  if (is_array($compare)) {
                     $compare = json_encode($compare);
                  }

                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ser um valor igual a %s.', 'bx-essentials'), $this->label, $compare),
                     $optionals['message'],
                  );
               }

               break;
         }

         return $this;
      }

      /**
       * Checks if it is not the same value.
       *
       * @param mixed $compare
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'also_type']
       * @param bool $optionals['also_type'] flag indicating if the type must be the same
       */
      public function is_not_equals(mixed $compare, string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'also_type' => true,
         ]);

         switch ($this->type) {
            case 'date':
               $date_compare = DateTime::createFromFormat($this->date_format, $compare);

               if (false === $date_compare || $date_compare->format($this->date_format) === $this->value) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ser uma data diferente a %s.', 'bx-essentials'), $this->label, $compare),
                     $optionals['message'],
                  );
               }

               break;

            default:
               if (($this->value == $compare && !$optionals['also_type']) || ($this->value === $compare && $optionals['also_type'])) {
                  if (is_array($compare)) {
                     $compare = json_encode($compare);
                  }

                  $this->add_error(
                     sprintf(esc_attr__('%s: Precisa ser um valor diferente a %s', 'bx-essentials'), $this->label, $compare),
                     $optionals['message'],
                  );
               }

               break;
         }

         return $this;
      }

      /**
       * Checks if it is IP.
       * Type needs to be "string".
       *
       * @param int $version
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function is_ip(int $version = 4, string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['string']);

         if (4 === $version) {
            $version_flag = FILTER_FLAG_IPV4;
         } elseif (6 === $version) {
            $version_flag = FILTER_FLAG_IPV6;
         } else {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser uma versão válida.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         $filtered_ip = filter_var($this->value, FILTER_VALIDATE_IP, $version_flag);

         if (false === $filtered_ip) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um IP v%s válido.', 'bx-essentials'), $this->label, $version),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Checks if it is a phone.
       * Type needs to be "string" or "number".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'ddd', 'mask]
       * @param string $optionals['ddd'] flag indicating if the phone number contains an area code
       * @param string $optionals['mask']
       */
      public function is_phone(string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'ddd'  => true,
            'mask' => 'phone_br'
         ]);

         $this->checks_field_type(['string', 'number']);

         $ddd = array_merge(
            range(11, 19),
            [21, 22, 24, 27, 28],
            range(31, 35),
            [37, 38],
            range(41, 49),
            [51],
            range(53, 55),
            range(61, 69),
            [71],
            range(73, 75),
            [77, 79],
            range(81, 89),
            range(91, 99)
         );

         $filtered_phone = preg_replace('/[^0-9]/is', '', $this->value);

         if ($optionals['ddd']) {
            $phone_ddd = substr($filtered_phone, 0, 2);

            if (!in_array($phone_ddd, $ddd)) {
               $this->add_error(
                  sprintf(esc_attr__('%s: Precisa ter um DDD válido.', 'bx-essentials'), $this->label),
                  $optionals['message'],
               );
            }
         }

         $phone_number = $optionals['ddd'] ? substr($filtered_phone, 2) : $filtered_phone;
         $phone_length = strlen($phone_number);

         if ($phone_length !== 8 && $phone_length !== 9) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um número de telefone válido.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );

            return $this;
         }

         if ($phone_length === 9 && $phone_number[0] !== '9') {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um número de telefone válido iniciado com o dígito 9.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         } elseif ($phone_length === 8 && ($phone_number[0] === '0' || $phone_number[0] === '1')) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ser um número de telefone válido não iniciado com os dígitos 0 ou 1.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         }

         if ($this->type === 'string') {
            $this->mask($optionals['mask']);
         }

         return $this;
      }

      /**
       * Checks if it matches the mask.
       * Use "0" for numbers and "X" for letters. ("x" for lowercase).
       * Is it possible to use the "?" to make the previous element optional.
       *
       * Type needs to be "string".
       *
       * @param string $format
       * @param string|array $optionals if string, a custom error message or an array with ['message']
       */
      public function mask(string $format, string|array $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals);

         $this->checks_field_type(['string']);

         $defaults = [
            'cep'      => '00000-000',
            'cnpj'     => '00.000.000/0000-00',
            'cpf'      => '000.000.000-00',
            'phone_br' => '(00) ?0?0000-0000',
         ];

         $format = $defaults[$format] ?? $format;

         $pattern = preg_quote($format, '/');
         $pattern = str_replace('0', '[0-9]', $pattern);
         $pattern = str_replace(['x', 'X'], '[a-zA-Z]', $pattern);
         $pattern = str_replace(['y', 'Y'], ['[a-z]', '[A-Z]'], $pattern);
         $pattern = str_replace('\?', '?', $pattern);

         if (!preg_match('/^' . $pattern . '$/', $this->value)) {
            $this->add_error(
               sprintf(esc_attr__('%s: Precisa ter uma formatação válida.', 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         }

         return $this;
      }

      /**
       * Checks if it is CEP.
       * Type needs to be "string".
       *
       * @param string|array $optionals if string, a custom error message or an array with ['message', 'mask']
       * @param string $optionals['mask']
       */
      public function is_cep(array|string $optionals = [])
      {
         if ($this->optional && empty($this->value)) {
            return $this;
         }

         $optionals = $this->parse_optionals($optionals, [
            'mask' => 'cep'
         ]);

         $this->checks_field_type(['string']);

         $this->mask($optionals['mask']);

         return $this;
      }

      /**
       * Checks if a number is divisible to a number.
       * Type needs to be "number".
       *
       * @param string $message a custom error message
       */
      public function is_divisible_by(float $divider, array|string $optionals = [])
      {
         $is_not_divisible = (bool) $this->value % $divider;

         $this->checks_field_type(['number']);

         $optionals = $this->parse_optionals($optionals);

         if ($is_not_divisible) {
            $this->add_error(
               sprintf(esc_attr__("%s: Precisa ser divisível por {$divider}.", 'bx-essentials'), $this->label),
               $optionals['message'],
            );
         }

         return $this;
      }

      private function parse_optionals(string|array $optionals, array $defaults = [])
      {
         $_defaults = wp_parse_args($defaults, [
            'message' => '',
         ]);

         if (is_string($optionals)) {
            $_optionals['message'] = $optionals;
         } else {
            $_optionals = $optionals;
         }

         return wp_parse_args($_optionals, $_defaults);
      }

      /**
       * Gets all errors.
       */
      public function get_errors()
      {
         return $this->errors;
      }

      /**
       * Gets all errors parsed for Gearbox.js.
       */
      public function parse_errors(string $container, string $content_tag = 'li', string $action = 'append')
      {
         if (empty($content_tag)) {
            $content_tag_start = '';
            $content_tag_end   = '';
         } else {
            $content_tag_start = "<{$content_tag}>";
            $content_tag_end   = "</{$content_tag}>";
         }

         foreach ($this->errors as $errors) {
            foreach ($errors as $error) {
               $errors_result[] = [
                  'action'  => $action,
                  'content' => $content_tag_start . $error . $content_tag_end,
                  'element' => $container,
               ];
            }
         }

         return $errors_result;
      }

      /**
       * Gets all fields registered.
       */
      public function get_fields()
      {
         return $this->fields;
      }

      /**
       * Checks if all fields are valid.
       */
      public function success()
      {
         return empty($this->get_errors());
      }

      /**
       * Checks required fields.
       *
       * @return bool true if invalid. false if valid.
       * @ignore
       */
      private function checks_required()
      {
         if ($this->optional && empty($this->value)) {
            return true;
         }

         switch ($this->type) {
            case 'file':
               if (!is_array($this->value) || 4 === $this->value['error']) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Arquivo obrigatório.', 'bx-essentials'), $this->label),
                     $this->required_message,
                  );

                  return true;
               }

               break;
            case 'bool':
               if ('' === $this->value) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Campo obrigatório.', 'bx-essentials'), $this->label),
                     $this->required_message,
                  );

                  return true;
               }

               break;
            default:
               if (empty($this->value)) {
                  $this->add_error(
                     sprintf(esc_attr__('%s: Campo obrigatório.', 'bx-essentials'), $this->label),
                     $this->required_message,
                  );

                  return true;
               }

               break;
         }

         return false;
      }

      private function set_type(string $type = 'string')
      {
         if ($this->type === $type) {
            return;
         }

         if (!empty($this->type)) {
            trigger_error(esc_attr__('Tipo já foi definido.', 'bx-essentials'), E_USER_ERROR);
         }

         $this->type = $type;
      }

      private function add_error(string $default, string $message)
      {
         $this->errors[$this->key][] = empty($message) ? $default : $message;
      }

      private function add_field(mixed $value)
      {
         $this->fields[$this->key] = $value;
      }

      private function checks_field_type(array $field_types)
      {
         if (in_array($this->type, $field_types)) {
            return true;
         }

         trigger_error(
            sprintf(esc_attr__('%s: Tipo do campo inválido.', 'bx-essentials'), $this->label),
            E_USER_ERROR,
         );
      }
   }
}
