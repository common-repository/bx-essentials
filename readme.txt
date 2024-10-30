=== BX Essentials ===
Contributors: buildbox, fagnerjb, ivandercarlos
Donate link: https://doe.greenpeace.org.br
Tags: tools, dev
Requires at least: 5.5
Tested up to: 6.6.2
Requires PHP: 7.4
Stable tag: 2.1.1
License: GPL v2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ferramentas e configurações padrões recomendados pelo time WordPress Buildbox.

== Description ==

Este plugin inclui funções e classes úteis para desenvolvedores de temas e plugins WordPress.

Também contém hooks que estendem funcionalidades básicas do WordPress e configurações padrões para temas.

Este plugin se destina a desenvolvedores. A leitura do código é recomendada para total ciência de todas as funções e recursos presentes.

== Changelog ==

= 2.1.1 =
* Adiciona opção para mudar cor do painel pelo tipo de ambiente.
* Atualiza debug() para registar e mostrar logs de acordo com configuração de WP_DEBUG.

= 2.1.0 =
* Adiciona class Form.
* Adiciona class Validator.
* Adiciona $clipboard, $action (crud), intersect, mask, persist ao Alpine.js.
* Atualiza PHPDoc.

= 2.0.2 =
* Correções

= 2.0.0 =
* Adiciona AutoLoader de classes.
* Adiciona render_svg().
* Estende virtual pages com endpoint_base.
* Altera actions do Gearbox.js
* Altera cache do wp_remote_get de disk para transient.
* Configurações são desativadas por padrão.
* Remove path_to_url().

= 1.4.1 =
* Adiciona suporte a dataset ao Gearbox.js

= 1.4.0 =
* Adiciona localizações alternativas para templates.
* Adiciona funções para localizações alternativas de templates.

= 1.3.1 =
* Correções no DiskCache e Gearbox.

= 1.3.0 =
* get_template_part agora pode chamar pastas com _index.php.
* Correções em DiskCache, debug, make_dir.
* Adiciona update_cache() em DiskCache.
* Adiciona action trigger-content no Gearbox.js.
* Adiciona página de configurações.

= 1.2.0 =
* Adicionado actions reload, open-content e go-content no Gearbox.js

= 1.1.0 =
* Adicionado Toast no Gearbox.js.
* Adicionado Form Validation no Gearbox.js
* Adicionado actions scrollTo, remove, addClass e removeClass no Gearbox.js

= 1.0.1 =
* Adicionado Gearbox.js.
* Adicionado opções defer e async nos scripts.
* Adicionado virtual pages e templates.
* Adicionado comentários nos hooks.
* Corrigido Text Domain.
* Adicionado $post_name no body_class().

== Upgrade Notice ==

* Adiciona localizações alternativas para templates.

== Screenshots ==

1. Cria um diretório.
