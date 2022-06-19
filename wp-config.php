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

/** Dominio do seu site */
define('WP_SITEURL','http://www.SEUSITE.com.br/PASTA'); // ALTERE AQUI PARA O SEU ENDEREÇO DE INSTALACAO SEM BARRA NO FINAL

define('WP_HOME', 'http://www.SEUSITE.com.br/PASTA');	// ALTERE AQUI PARA O SEU ENDEREÇO DE INSTALACAO SEM BARRA NO FINAL


// ** Configurações do MySQL - Você pode pegar essas informações com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define('DB_NAME', 'USUARIO_CPANEL');

/** Usuário do banco de dados MySQL */
define('DB_USER', 'USUARIO_CPANEL');

/** Senha do banco de dados MySQL */
define('DB_PASSWORD', 'SENHA');

/** nome do host do MySQL */
define('DB_HOST', 'localhost'); // SERVIDOR MYSQL, GERALMENTE É localhost MESMO

/** Conjunto de caracteres do banco de dados a ser usado na criação das tabelas. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         '/{lVuIj;6b5x?4wKF8Yk@~p50c4fsU;1@0ce3<,!_q|WI9*OJSF>SP+xafwbO S2');
define('SECURE_AUTH_KEY',  ']L|Mctsh4 S[7;u{V_Ax>d7FfvsL%8= Y~Hs$T =p+>: boXT+xmD =Qxge#wIP1');
define('LOGGED_IN_KEY',    '~TH >6*Mz$N7 >xexy$qTkxW7~z{$v}8K9 ]]t%hJE*n]:[i;.10N_4/`<H>YVh9');
define('NONCE_KEY',        'F|jf4M/r0-Qdj||DF(~oLBr(yKId)b!R>iy_V*ZN$Ey;+G0+3?=7aPe<mshU|Dkl');
define('AUTH_SALT',        '(IA,MH;oymYst=bibArCA+uXD2#Uz[X b6x3F:5nwgNY=J(|(G1$?ecy9.-&[c^9');
define('SECURE_AUTH_SALT', '$ Z]tq[M?5O<b=xF(-O(FM#K`_M[B@lPC/0-AQROaVo]qC|j(NSgF?x3q5p,-7X8');
define('LOGGED_IN_SALT',   '^ArX05JMu1:8/uLwM`SLw- D/kaQi^Gfx8MPbh7S&s],Nd`*[|S^>.gVS0Rc{}l|');
define('NONCE_SALT',       '*9,mS+<7]S76-?yFRPTWl;M)0:*|{Tt@^`AfpMO~xdnjYd9kg|d@D~*D(zt$Oy7e');

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der para cada um um único
 * prefixo. Somente números, letras e sublinhados!
 */
$table_prefix  = 'wp_';


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