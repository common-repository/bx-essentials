<?php

if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('bx_sntls_Form')) {
   /**
    * Provides a standard way to create forms elements. Includes input, select, textarea and button.
    *
    * ```php
    * $input_args = [
    *    'id'    => '',
    *    'value' => '',
    *    'echo'  => false,
    *    'value' => '',
    *    'attrs' => [
    *       'name'      => '',
    *       'minlength' => '',
    *       'required'  => true,
    *    ],
    *    'icon'          => '',
    *    'action'        => '',
    *    'action_icon'   => '',
    *    'label'         => 'Nome:',
    *    'label_between' => '<br>',
    *
    *    'label_position'=> 'after'
    *    'label_attrs'   => [],
    *
    *    'container' => 'div',
    *    'container_attrs' => '',
    *    'options'       => [
    *       'value' => 'Label',
    *    ],
    * ];
    * ```
    */
   class bx_sntls_Form
   {
      protected $tag;
      protected $args;
      protected $globals;

      public function __construct(array $globals = [])
      {
         $defaults = [
            'id_prefix'      => '',
            'name_prefix'    => '',
            'options'        => [],
            'label'          => false,
            'label_between'  => '',
            'label_position' => 'before',
            'label_attrs'    => [
               'class' => 'form-label',
            ],
            'container'       => 'div',
            'container_attrs' => [
               'class' => 'form-container',
            ],
            'echo'        => true,
            'icon'        => false,
            'action'      => false,
            'action_icon' => false,
         ];

         $this->globals = $this->parse_args($globals, $defaults);
      }

      public function input(array $input_args = [])
      {
         $this->tag = 'input';

         $defaults = [
            'value' => '',
            'type'  => 'text',
            'attrs' => [
               'class' => 'form-control form-input',
            ],
         ];

         $element_args = $this->parse_args($input_args, $defaults);

         if (!in_array($element_args['type'], ['button', 'checkbox', 'color', 'date', 'datetime-local', 'email', 'file', 'hidden', 'image', 'month', 'number', 'password', 'radio', 'range', 'reset', 'search', 'submit', 'tel', 'text', 'time', 'url', 'week'])) {
            trigger_error(sprintf(esc_attr__('%s não é um tipo de input válido.', 'bx-essentials'), $element_args['type']), E_USER_ERROR);
         }

         $element_args = $this->add_class_extra($element_args);

         return $this->new_element($element_args);
      }

      public function select(array $select_args = [])
      {
         $this->tag = 'select';

         $defaults = [
            'value' => '',
            'attrs' => [
               'class' => 'form-select',
            ],
         ];

         $element_args = $this->parse_args($select_args, $defaults);

         $element_args = $this->add_class_extra($element_args);

         return $this->new_element($element_args);
      }

      public function textarea(array $textarea_args = [])
      {
         $this->tag = 'textarea';

         $defaults = [
            'value' => '',
            'attrs' => [
               'class' => 'form-control form-textarea',
            ],
         ];

         $element_args = $this->parse_args($textarea_args, $defaults);

         $element_args = $this->add_class_extra($element_args);

         return $this->new_element($element_args);
      }

      public function button(array $button_args = [])
      {
         $this->tag = 'button';

         $defaults = [
            'type'  => 'button',
            'attrs' => [
               'class' => 'btn form-button',
            ],
         ];

         $element_args = $this->parse_args($button_args, $defaults);

         if (!in_array($element_args['type'], ['button', 'reset', 'submit'])) {
            trigger_error(sprintf(esc_attr__('%s não é um tipo de button válido.', 'bx-essentials'), $element_args['type']), E_USER_ERROR);
         }

         $element_args = $this->add_class_extra($element_args);

         return $this->new_element($element_args);
      }

      private function render_element()
      {
         $element  = $this->render_tag_start();
         $element .= $this->render_tag_content();
         $element .= $this->render_tag_end();

         if ($this->args['label']) {
            $label_start = $this->render_label_start();
            $label_end   = $this->render_label_end();

            $output = $label_start . $label_end . $element;

            if (isset($this->args['label_position'])) {
               if ('after' === $this->args['label_position']) {
                  $output = $element . $label_start . $label_end;
               } elseif ('envolve' === $this->args['label_position']) {
                  $output = $label_start . $element . $label_end;
               }
            }
         } else {
            $output = $element;
         }

         if (is_string($this->args['container'])) {
            $container_start = $this->render_container_start();
            $container_end   = $this->render_container_end();

            $output = $container_start . $output . $container_end;
         }

         return $this->return($output);
      }

      private function return(string $element)
      {
         if ($this->args['echo']) {
            echo $element;
         } else {
            return $element;
         }
      }

      private function new_element(array $element_args = [])
      {
         if (empty($element_args['id'])) {
            trigger_error(esc_attr__('ID é obrigatório.', 'bx-essentials'), E_USER_ERROR);
         }

         $element_args['attrs']['id']        = $this->globals['id_prefix'] . $element_args['id'];
         $element_args['label_attrs']['for'] = $this->globals['id_prefix'] . $element_args['id'];

         if (!isset($element_args['attrs']['name'])) {
            $element_args['attrs']['name'] = $this->globals['name_prefix'] . $element_args['id'];
         } else {
            $element_args['attrs']['name'] = $this->globals['name_prefix'] . $element_args['attrs']['name'];
         }

         if (!empty($element_args['type'])) {
            $element_args['attrs']['type'] = $element_args['type'];
         }

         if ('input' === $this->tag) {
            $element_args['attrs']['value'] = $element_args['value'];
         }

         if ($this->has_datalist($element_args)) {
            $element_args['attrs']['list'] = "list-{$this->globals['id_prefix']}{$element_args['id']}";
         }

         if (!isset($element_args['attrs']['data-cy'])) {
            $element_args['attrs']['data-cy'] = "{$this->tag}-{$this->globals['id_prefix']}{$element_args['id']}";
         }

         $this->args = $this->parse_args($element_args, $this->globals);

         return $this->render_element();
      }

      private function render_tag_start()
      {
         $tag = '';

         if (!empty($this->args['action_icon'])) {
            $tag .= '<div class="form-action">';
         }

         $attrs = $this->render_attrs();
         $tag  .= "<{$this->tag}{$attrs}>\r\n";

         return $tag;
      }

      private function render_tag_content()
      {
         if (!isset($this->args['value']) || false === $this->args['value']) {
            return '';
         }

         if (in_array($this->tag, ['textarea', 'button'])) {
            $content = '';

            if (!empty($this->args['icon']) && 'button' === $this->tag) {
               $content .= render_svg($this->args['icon'], 'form-button-icon', false);
            }

            $content .= $this->args['value'];

            return $content;
         }

         if (in_array($this->tag, ['select', 'input'])) {
            return $this->render_options();
         }

         return '';
      }

      private function render_tag_end()
      {
         $tag = '';

         if ('input' !== $this->tag) {
            $tag .= "</{$this->tag}>\r\n";
         }

         if (!empty($this->args['action_icon'])) {
            if (!empty($this->args['action'])) {
               $tag .= '<button type="button" onclick="' . esc_attr($this->args['action']) . '">';
            }

            if (is_array($this->args['action_icon'])) {
               foreach ($this->args['action_icon'] as $key => $icon) {
                  $slug = sanitize_title($icon);

                  $action_classes = [
                     'form-action-icon',
                     "form-action-icon-$key",
                     "form-action-icon-$slug",
                  ];

                  $tag .= render_svg($icon, implode(' ', $action_classes), false);
               }
            } else {
               $slug = sanitize_title($this->args['action_icon']);

               $action_classes = [
                  'form-action-icon',
                  "form-action-icon-$slug",
               ];

               $tag .= render_svg($this->args['action_icon'], implode(' ', $action_classes), false);
            }

            if (!empty($this->args['action'])) {
               $tag .= '</button>';
            }

            $tag .= '</div>';
         }

         return $tag;
      }

      private function render_attrs(string $element = 'input')
      {
         switch ($element) {
            case 'input':
               $initial_attrs = $this->args['attrs'];

               break;
            case 'label':
               $initial_attrs = $this->args['label_attrs'];

               break;
            case 'container':
               $initial_attrs = $this->args['container_attrs'];

               break;
            default:
               trigger_error(sprintf(esc_attr__('%s não é um element válido.', 'bx-essentials'), $element), E_USER_ERROR);

               break;
         }

         if (empty($initial_attrs)) {
            return '';
         }

         foreach ($initial_attrs as $key => $value) {
            if (is_bool($value)) {
               if ($value !== false) {
                  $attrs[] = $key;
               }

               continue;
            }

            $attrs[] = $key . '="' . esc_attr($value) . '"';
         }

         return ' ' . implode(' ', $attrs);
      }

      private function render_container_start()
      {
         if (empty($this->args['container'])) {
            return '';
         }

         $attrs = $this->render_attrs('container');

         return "<{$this->args['container']}{$attrs}>\r\n";
      }

      private function render_container_end()
      {
         if (empty($this->args['container'])) {
            return '';
         }

         return "</{$this->args['container']}>\r\n";
      }

      private function render_label_start()
      {
         $attrs = $this->render_attrs('label');

         $label = "<label{$attrs}>\r\n";
         $label .= $this->args['label'];

         if (!empty($this->args['label_between'])) {
            $label .= $this->args['label_between'] . "\r\n";
         }

         return $label;
      }

      private function render_label_end()
      {
         return '</label>' . "\r\n";
      }

      private function render_options()
      {
         if (empty($this->args['options'])) {
            return '';
         }

         if (!in_array($this->tag, ['input', 'select'])) {
            return '';
         }

         $list = '';

         if ($this->has_datalist()) {
            $list .= "<datalist id=\"list-{$this->args['id']}\">\r\n";

            foreach ($this->args['options'] as $value) {
               $list .= "<option value=\"{$value}\"></option>\r\n";
            }
            $list .= '</datalist>' . "\r\n";

            return $list;
         }

         if (isset($this->args['options'][0]['label'], $this->args['options'][0]['itens'])) {
            foreach ($this->args['options'] as $group) {
               $list .= "<optgroup label=\"{$group['label']}\">\r\n";

               foreach ($group['itens'] as $key => $value) {
                  $list .= "<option value=\"{$key}\">{$value}</option>\r\n";
               }

               $list .= '</optgroup>' . "\r\n";
            }

            return $list;
         }

         foreach ($this->args['options'] as $key => $value) {
            $list .= "<option value=\"{$key}\">{$value}</option>\r\n";
         }

         return $list;
      }

      private function has_datalist($element_args = null)
      {
         if (is_null($element_args)) {
            $element_args = $this->args;
         }

         if (empty($element_args['options'])) {
            return false;
         }

         return 'input' === $this->tag && in_array($element_args['type'], ['text', 'search', 'url', 'tel', 'email', 'number', 'month', 'week', 'date', 'time', 'datetime-local', 'range', 'color']);
      }

      private function parse_args(array $current_args, array $merged_args)
      {
         foreach ($current_args as $key => $value) {
            if (isset($merged_args[$key]) && 'class' === $key) {
               $merged_args[$key] .= ' ' . (is_array($value) ? implode(' ', $value) : $value);

               continue;
            }

            if (isset($merged_args[$key]) && is_array($value) && is_array($merged_args[$key])) {
               $merged_args[$key] = $this->parse_args($value, $merged_args[$key]);
            } else {
               $merged_args[$key] = $value;
            }
         }

         return $merged_args;
      }

      private function add_class_extra(array $merged_args)
      {
         if (isset($merged_args['type'], $merged_args['attrs']['class'])) {
            $merged_args['attrs']['class'] .= ' ' . "form-{$this->tag}-{$merged_args['type']}";
         }

         return $merged_args;
      }
   }
}
