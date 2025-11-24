<?php//Begin Really Simple Security key
define('RSSSL_KEY', 'ANdpJwLLq9csgp5AmBX02prr8naTq4PWyAyhUvtnRFX3Q4FLARFT42dDGTNQ67oh');
//END Really Simple Security key

//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ingapt' );

/** MySQL database username */
define( 'DB_USER', 'ingapt' );

/** MySQL database password */
define( 'DB_PASSWORD', 'ing91624' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ',TD$lWagir ;2lY}DAO.dH[yf+80MDIm(&!uWKX!W(hDGMD=xZb^61Mmo0:6{a$Z' );
define( 'SECURE_AUTH_KEY',  'M~/tY+ scFM1Td%7YK9^A1-*l1ib!mpD9nLYb:hw(3L0KyIn:DfL7PT:fr|!iJ$ ' );
define( 'LOGGED_IN_KEY',    '*4SEt3z<16#@nQq,VwDFU}P4V!zBu+{k9H.f(n>Z2E`I*U(t:{aw~P75Q.I<F=HW' );
define( 'NONCE_KEY',        '9lcsFCx7D,9<] zl#f=x5{zqL`M&-*1T{KP_|[Q;oc3HK*3i; K 948dz }e:Ra2' );
define( 'AUTH_SALT',        '|Myg1E`*|<Y7]tidk%&Cjz[RW*% Z|  A]l,3Pfp*9FkC>wNPDh7^[a#:wGP EJ<' );
define( 'SECURE_AUTH_SALT', '*6_E$A^flu7X1&9]S3:$t!LnZ!!S5RRTz@VS7V/a4U7LBV#){BR|9.Js]cc6]W<X' );
define( 'LOGGED_IN_SALT',   'T5JEYb,WS|*bJ}OBf8fv:T4LJN-]nPm!QjeAoDGw/J^iKjanDt7&[FOp8!q7H#dZ' );
define( 'NONCE_SALT',       'c$]u[oK*EP@HzY7.+k&K*&[CJ(VB{l O/)r{M|($7#yIMB4l6<w.okUSB!lCtx2D' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
/* custom security setting */
define('DISALLOW_FILE_EDIT',true);
define('WP_POST_REVISIONS',7);
define('IMAGE_EDIT_OVERWRITE',true);
define('DISABLE_WP_CRON',true);
define('EMPTY_TRASH_DAYS',7);
