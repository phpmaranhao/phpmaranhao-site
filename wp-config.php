<?php
/** 
 * As configurações básicas do WordPress.
 *
 * Esse arquivo contém as seguintes configurações: configurações de MySQL, Prefixo de Tabelas,
 * Chaves secretas, Idioma do WordPress, e ABSPATH. Você pode encontrar mais informações
 * visitando {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. Você pode obter as configurações de MySQL de seu servidor de hospedagem.
 *
 * Esse arquivo é usado pelo script ed criação wp-config.php durante a
 * instalação. Você não precisa usar o site, você pode apenas salvar esse arquivo
 * como "wp-config.php" e preencher os valores.
 *
 * @package WordPress
 */

// ** Configurações do MySQL - Você pode pegar essas informações com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define('DB_NAME', 'phpmaranhao');

/** Usuário do banco de dados MySQL */
define('DB_USER', 'root');

/** Senha do banco de dados MySQL */
define('DB_PASSWORD', '1234');

/** nome do host do MySQL */
define('DB_HOST', 'localhost');

/** Conjunto de caracteres do banco de dados a ser usado na criação das tabelas. */
define('DB_CHARSET', 'utf8');

/** O tipo de collate do banco de dados. Não altere isso se tiver dúvidas. */
define('DB_COLLATE', '');

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Você pode alterá-las a qualquer momento para desvalidar quaisquer cookies existentes. Isto irá forçar todos os usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'y#B$#[Ph{o)r6s1004qN}Tc:#`pisWl{=|e;;Pq<aT4?t (T0G9efFV~9YC#%G:Q');
define('SECURE_AUTH_KEY',  '-f]ka)O.U1ZH1-E~rN66}l0jI_8admq3BehUQv]+g.ZYn*gfB}mI]cSUj>L~<uLx');
define('LOGGED_IN_KEY',    'jgH*?!Q{u==FTp+_#nKW7H8~ZC<L(#4q&<8F:$BUG+R=F&g)5?HFj+w n8z[qwf1');
define('NONCE_KEY',        '<>(Q<bkb?6+F_8cc6Ko&R48/)L+cOKp=qE{,kD9*t^*X.0[S{|wOv;WLRtmx!l}n');
define('AUTH_SALT',        '`6~S+!G<*~L==>o%(/xraEE,F2|oY][~En6s]xcx|0N2)-S?mfeKMD(BzN+)(C)u');
define('SECURE_AUTH_SALT', 't<{_A):7]/~r7c)FqTkf}7.548h_B%@e8dJFy+{1(<J*/:N#cqZy0-t+rZ!nu|$b');
define('LOGGED_IN_SALT',   'H_nbelB`|i*`4DJ,amw_qRgymH$/I.jffRV>JGL#+DD~xW+dsCfl#)}BP1-B2uMY');
define('NONCE_SALT',       'J3{uxI-ue|%/h#tE/PD$@hukg+*c|aY>mI|{LteJy){{R7#K+Bgo-qs|DA0B[uVu');

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der para cada um um único
 * prefixo. Somente números, letras e sublinhados!
 */
$table_prefix  = 'wp_';

/**
 * O idioma localizado do WordPress é o inglês por padrão.
 *
 * Altere esta definição para localizar o WordPress. Um arquivo MO correspondente ao
 * idioma escolhido deve ser instalado em wp-content/languages. Por exemplo, instale
 * pt_BR.mo em wp-content/languages e altere WPLANG para 'pt_BR' para habilitar o suporte
 * ao português do Brasil.
 */
define('WPLANG', 'pt_BR');

/**
 * Para desenvolvedores: Modo debugging WordPress.
 *
 * altere isto para true para ativar a exibição de avisos durante o desenvolvimento.
 * é altamente recomendável que os desenvolvedores de plugins e temas usem o WP_DEBUG
 * em seus ambientes de desenvolvimento.
 */
define('WP_DEBUG', false);

/* Isto é tudo, pode parar de editar! :) */

/** Caminho absoluto para o diretório WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
	
/** Configura as variáveis do WordPress e arquivos inclusos. */
require_once(ABSPATH . 'wp-settings.php');

