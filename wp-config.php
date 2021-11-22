<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'dcbg1z++rgcXhC1m0Z9XkCNOuVCaFBjxIfwdn3GxJg7DvFajmho+XjQGbK8gL6W591aET/p70cHp1MB2NP/6rw==');
define('SECURE_AUTH_KEY',  '9oOrRiNvdT+epgmVVC7jhocmenGaZ8xBd+I+5fej+dfI5Z56i5supCndAEDtxRbVq7sSplozRCg5bJfngChWWQ==');
define('LOGGED_IN_KEY',    'SB+dhwzI4lRaQb6/M2Nj+i5pvFhw6KYFzhs1DYYv6of9/R2M9+2d+bFMVIMLLyQB5kTdft+uVONq2hUgSU0x2g==');
define('NONCE_KEY',        'yVE0h3eOyIES3ctBgijfRBHzfgS2yi+xfaFB7OHaDX8KXdxYDQwVdEke2/kAEnGZPjDxmPj0q6czW5Kbfo9oeg==');
define('AUTH_SALT',        '+ZaN65yB9mhQFKtDqI7lj4SqOho0ZgHsgCL+aHKvAbj3cAR/a/TJbKktgYFKba0pVxqynuZtrXWeBBu8DUoDIw==');
define('SECURE_AUTH_SALT', 'SW6Rqy673qC+pDG/wqUi4nBdlihP5zL6QHa3gnz/reQ/MhnD+pmPNTJc/Qmc3k+ZiMNm4dy7ASiZjGoRzt/70w==');
define('LOGGED_IN_SALT',   '8uD98YQ9LDKXEKC4qqEsStgJb4yRHNrVemYehuOHpu4QnGwSr7vtZMNmqm1erzTZpms3FhPdIbZsp3xpnMzINA==');
define('NONCE_SALT',       'j5xU3TVasIQAhVgBe5uK8cUnXlvOv8DQIrHSM1ZbFjW6654UJvdtApu6PpQdAWOfJYnHFZZTAHGouNkUcmbkkA==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
