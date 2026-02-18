<?php
/**
 * PHPUnit Bootstrap
 *
 * @package Slemb
 */

namespace Slemb\Tests;

// テスト環境のセットアップ
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// テストスイートのコアファイルをロード
require_once $_tests_dir . '/includes/functions.php';

// プラグインを手動でロード
function _manually_load_plugin() {
    // プラグインメインファイルをロード
    require dirname( dirname( __FILE__ ) ) . '/simple-link-embed.php';
}

tests_add_filter('muplugins_loaded', __NAMESPACE__ . '\\_manually_load_plugin');

// テストスイートを開始
require $_tests_dir . '/includes/bootstrap.php';

// オートローダーのセットアップ
require_once dirname(dirname(__FILE__)) . '/inc/class-cache-manager.php';
require_once dirname(dirname(__FILE__)) . '/inc/class-ogp-fetcher.php';
require_once dirname(dirname(__FILE__)) . '/inc/class-rest-api.php';
require_once dirname(dirname(__FILE__)) . '/inc/class-renderer.php';
require_once dirname(dirname(__FILE__)) . '/inc/class-block-registration.php';
