<?php
/**
 * Plugin Name: Simple Link Card
 * Description: URLを入力するだけでOGP情報を自動取得し、美しいブログカードを表示。投稿検索機能で内部リンクも簡単に作成できます。
 * Version: 1.0.0
 * Author: MONOKURO DESIGN
 * Author URI: https://monokuro.design
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: simple-link-card
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package Simple_Link_Card
 */

// 万全の防御策
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * プラグイン定数
 */
if ( ! defined( 'SIMPLE_LINK_CARD_VERSION' ) ) {
    define( 'SIMPLE_LINK_CARD_VERSION', '1.0.0' );
}
if ( ! defined( 'SIMPLE_LINK_CARD_PLUGIN_FILE' ) ) {
    define( 'SIMPLE_LINK_CARD_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'SIMPLE_LINK_CARD_PLUGIN_DIR' ) ) {
    define( 'SIMPLE_LINK_CARD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'SIMPLE_LINK_CARD_PLUGIN_URL' ) ) {
    define( 'SIMPLE_LINK_CARD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SIMPLE_LINK_CARD_CACHE_EXPIRATION' ) ) {
    define( 'SIMPLE_LINK_CARD_CACHE_EXPIRATION', WEEK_IN_SECONDS ); // 7日間キャッシュ
}

/**
 * クラスオートローダー
 *
 * @param string $class クラス名
 */
function simple_link_card_autoloader( $class ) {
    // 名前空間プレフィックスのチェック
    $prefix = 'Simple_Link_Card\\';
    $base_dir = SIMPLE_LINK_CARD_PLUGIN_DIR . 'inc/';

    $len = strlen( $prefix );
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }

    // 相対クラス名の取得
    $relative_class = substr( $class, $len );

    // クラスファイル名の変換
    $file = $base_dir . 'class-' . str_replace( '_', '-', strtolower( str_replace( '\\', '-', $relative_class ) ) ) . '.php';

    // ファイルが存在すれば読み込み
    if ( file_exists( $file ) ) {
        require_once $file;
    }
}
spl_autoload_register( 'simple_link_card_autoloader' );

/**
 * プラグイン初期化
 */
function simple_link_card_init() {
    // キャッシュマネージャーの初期化
    Simple_Link_Card\Cache_Manager::get_instance();

    // REST API の初期化
    Simple_Link_Card\REST_API::get_instance();

    // ブロック登録の初期化
    Simple_Link_Card\Block_Registration::get_instance();

    // レンダラーの初期化
    Simple_Link_Card\Renderer::get_instance();
}
add_action( 'plugins_loaded', 'simple_link_card_init' );

/**
 * プラグイン有効化時の処理
 */
function simple_link_card_activate() {
    // デフォルトオプションの設定
    add_option( 'slc_cache_expiration', SIMPLE_LINK_CARD_CACHE_EXPIRATION );
    add_option( 'slc_cache_index_keys', array() );
}
register_activation_hook( __FILE__, 'simple_link_card_activate' );

/**
 * プラグイン無効化時の処理
 */
function simple_link_card_deactivate() {
    // OGPキャッシュのクリア
    Simple_Link_Card\Cache_Manager::get_instance()->clear_all();
}
register_deactivation_hook( __FILE__, 'simple_link_card_deactivate' );
