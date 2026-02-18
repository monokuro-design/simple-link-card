<?php
/**
 * WordPress PHPUnit config for this plugin.
 *
 * @package Slemb
 */

$wp_root = rtrim( dirname( __DIR__, 4 ), '/\\' ) . '/';

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', getenv( 'WP_TESTS_ABSPATH' ) ?: $wp_root );
}

/**
 * Local の sites.json から DB 接続情報を推測する。
 *
 * @return array<string, string>
 */
function slemb_detect_local_db_config() {
    $config = array();
    $home   = getenv( 'HOME' );

    if ( ! is_string( $home ) || '' === $home ) {
        return $config;
    }

    $sites_json = $home . '/Library/Application Support/Local/sites.json';
    if ( ! is_readable( $sites_json ) ) {
        return $config;
    }

    $raw = file_get_contents( $sites_json );
    if ( false === $raw ) {
        return $config;
    }

    $sites = json_decode( $raw, true );
    if ( ! is_array( $sites ) ) {
        return $config;
    }

    $wp_root      = rtrim( defined( 'ABSPATH' ) ? ABSPATH : '', '/\\' );
    $detected_site = null;

    foreach ( $sites as $site ) {
        if ( ! is_array( $site ) || empty( $site['path'] ) || ! is_string( $site['path'] ) ) {
            continue;
        }

        $site_public = rtrim( $site['path'], '/\\' ) . '/app/public';
        if ( $site_public === $wp_root ) {
            $detected_site = $site;
            break;
        }
    }

    if ( ! is_array( $detected_site ) ) {
        return $config;
    }

    if ( isset( $detected_site['mysql']['database'] ) && is_string( $detected_site['mysql']['database'] ) ) {
        $config['DB_NAME'] = $detected_site['mysql']['database'];
    }

    if ( isset( $detected_site['mysql']['user'] ) && is_string( $detected_site['mysql']['user'] ) ) {
        $config['DB_USER'] = $detected_site['mysql']['user'];
    }

    if ( isset( $detected_site['mysql']['password'] ) && is_string( $detected_site['mysql']['password'] ) ) {
        $config['DB_PASSWORD'] = $detected_site['mysql']['password'];
    }

    if (
        isset( $detected_site['services']['mysql']['ports']['MYSQL'][0] ) &&
        is_numeric( $detected_site['services']['mysql']['ports']['MYSQL'][0] )
    ) {
        $config['DB_HOST'] = '127.0.0.1:' . (string) (int) $detected_site['services']['mysql']['ports']['MYSQL'][0];
    }

    if ( isset( $detected_site['id'] ) && is_string( $detected_site['id'] ) && '' !== $detected_site['id'] ) {
        $socket = $home . '/Library/Application Support/Local/run/' . $detected_site['id'] . '/mysql/mysqld.sock';
        if ( file_exists( $socket ) ) {
            // Prefer Unix socket for Local: root@localhost typically disallows TCP (127.0.0.1).
            $config['DB_HOST'] = 'localhost:' . $socket;
        }
    }

    return $config;
}

$detected = slemb_detect_local_db_config();

if ( ! defined( 'DB_NAME' ) ) {
    define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ?: ( $detected['DB_NAME'] ?? 'local' ) );
}

if ( ! defined( 'DB_USER' ) ) {
    define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ?: ( $detected['DB_USER'] ?? 'root' ) );
}

if ( ! defined( 'DB_PASSWORD' ) ) {
    define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASSWORD' ) ?: ( $detected['DB_PASSWORD'] ?? 'root' ) );
}

if ( ! defined( 'DB_HOST' ) ) {
    define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ?: ( $detected['DB_HOST'] ?? 'localhost' ) );
}

if ( ! defined( 'DB_CHARSET' ) ) {
    define( 'DB_CHARSET', getenv( 'WP_TESTS_DB_CHARSET' ) ?: 'utf8' );
}

if ( ! defined( 'DB_COLLATE' ) ) {
    define( 'DB_COLLATE', getenv( 'WP_TESTS_DB_COLLATE' ) ?: '' );
}

if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {
    define( 'WP_TESTS_DOMAIN', 'example.org' );
}

if ( ! defined( 'WP_TESTS_EMAIL' ) ) {
    define( 'WP_TESTS_EMAIL', 'admin@example.org' );
}

if ( ! defined( 'WP_TESTS_TITLE' ) ) {
    define( 'WP_TESTS_TITLE', 'Simple Link Embed Test Site' );
}

if ( ! defined( 'WP_PHP_BINARY' ) ) {
    define( 'WP_PHP_BINARY', PHP_BINARY );
}

if ( ! defined( 'WP_DEBUG' ) ) {
    define( 'WP_DEBUG', false );
}

$table_prefix = 'wptests_';
