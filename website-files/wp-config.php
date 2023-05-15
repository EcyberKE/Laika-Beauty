<?php
//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL cookie settings

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'shinewebt_laikademo' );

/** Database username */
define( 'DB_USER', 'shinewebt_laikademo' );

/** Database password */
define( 'DB_PASSWORD', '@Laika.101!@#' );

/** Database hostname */
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
define('AUTH_KEY',         'i-}6a]Br(!]qB6QgKc9%t?K+8_w|<+P?kxvMqz[2eU4e:~:-`}v0Q|roAJVV2`eL');
define('SECURE_AUTH_KEY',  '.`oFt8o?Ak7-dJ45<!st@,#|AP Aw$)i<F!;U.B({|K@KkVqPS*#bTyNWsXi[eiM');
define('LOGGED_IN_KEY',    'olKb]^v#;!PxZy4q@$CGM6u lGdG!QLkwd1xiirL^~$E5cW $BDY66|G +5<`meO');
define('NONCE_KEY',        'r?hzW|wPZ]JT/c9!s_AK,++*=O5~++GgR!P%;~|%k?P)px)HG^&+Qqj58M:$fW`s');
define('AUTH_SALT',        ':pD-r-L{BSm!L4Wq9d3R uw7du]hbWcendZMECn,^%XXwP,uiuB+v/@;X)8g.-L,');
define('SECURE_AUTH_SALT', 'LIGn>(!~d9%:mI&Tw@S:c|ie?~K10[w(3P}<JmY>BV4,,~@yjjz[h]D)7[fgZY6`');
define('LOGGED_IN_SALT',   'rUPNt[oC7Wle7|=#VtQHwgh`!2 +FFzH-T<y!xa-2B7c,m]EZc9GXns|2tI^7gZ$');
define('NONCE_SALT',       '.3$p|mtCi3SBGI//p5a?#9+ Sunatd{}9-oOX =m;sPq)`x!=qGz607?/A&2It=F');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'lkabuty7_';

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













