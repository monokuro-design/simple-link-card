<?php
/**
 * Analytics
 *
 * GA4クリック計測設定を管理するクラス
 *
 * @package Slemb
 */

namespace Slemb;

// 万全の防御策
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Analytics
 */
class Analytics {
    /**
     * インスタンス
     *
     * @var Analytics|null
     */
    private static $instance = null;

    /**
     * オプションキー
     */
    const OPTION_ENABLED = 'slemb_analytics_enabled';
    const OPTION_GA4_ID   = 'slemb_ga4_measurement_id';

    /**
     * インスタンスを取得
     *
     * @return Analytics
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * コンストラクタ
     */
    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
    }

    /**
     * 設定メニュー追加
     */
    public function add_admin_menu() {
        add_options_page(
            'Simple Link Embed',
            'Simple Link Embed',
            'manage_options',
            'simple-link-embed-analytics',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * 設定登録
     */
    public function register_settings() {
        register_setting(
            'slemb_analytics',
            self::OPTION_ENABLED,
            array(
                'type'              => 'string',
                'sanitize_callback' => array( $this, 'sanitize_enabled_option' ),
                'default'           => '0',
            )
        );

        register_setting(
            'slemb_analytics',
            self::OPTION_GA4_ID,
            array(
                'type'              => 'string',
                'sanitize_callback' => array( $this, 'sanitize_ga4_measurement_id' ),
                'default'           => '',
            )
        );
    }

    /**
     * 有効化オプションのサニタイズ
     *
     * @param mixed $value 保存値
     * @return string
     */
    public function sanitize_enabled_option( $value ) {
        return ( ! empty( $value ) && '0' !== (string) $value ) ? '1' : '0';
    }

    /**
     * GA4計測IDのサニタイズ
     *
     * @param mixed $value 保存値
     * @return string
     */
    public function sanitize_ga4_measurement_id( $value ) {
        $measurement_id = strtoupper( trim( (string) $value ) );
        if ( '' === $measurement_id ) {
            return '';
        }

        if ( $this->is_valid_ga4_measurement_id( $measurement_id ) ) {
            return $measurement_id;
        }

        add_settings_error(
            self::OPTION_GA4_ID,
            'slemb_invalid_ga4_measurement_id',
            __( 'GA4 計測IDの形式が正しくありません。', 'simple-link-embed' ),
            'error'
        );

        return '';
    }

    /**
     * フロントエンドスクリプト読み込み
     */
    public function enqueue_frontend_scripts() {
        if ( ! $this->is_tracking_active() ) {
            return;
        }

        wp_enqueue_script(
            'slemb-analytics',
            SLEMB_PLUGIN_URL . 'assets/analytics.js',
            array(),
            SLEMB_VERSION,
            true
        );

        wp_localize_script( 'slemb-analytics', 'slembAnalytics', array(
            'enabled'       => true,
            'measurementId' => $this->get_ga4_id(),
            'eventName'     => apply_filters( 'slemb_analytics_event_name', 'link_card_click' ),
        ) );
    }

    /**
     * 設定画面描画
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'slemb_analytics' );
                do_settings_sections( 'slemb_analytics' );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="<?php echo esc_attr( self::OPTION_ENABLED ); ?>">
                                <?php esc_html_e( 'クリック計測を有効化', 'simple-link-embed' ); ?>
                            </label>
                        </th>
                        <td>
                            <input type="checkbox"
                                   id="<?php echo esc_attr( self::OPTION_ENABLED ); ?>"
                                   name="<?php echo esc_attr( self::OPTION_ENABLED ); ?>"
                                   value="1"
                                   <?php checked( $this->is_enabled(), true ); ?>>
                            <p class="description">
                                <?php esc_html_e( '有効にすると、GA4計測IDが正しく設定されている場合にリンクカードのクリックをGA4へ送信します。', 'simple-link-embed' ); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo esc_attr( self::OPTION_GA4_ID ); ?>">
                                <?php esc_html_e( 'GA4 計測ID', 'simple-link-embed' ); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text"
                                   id="<?php echo esc_attr( self::OPTION_GA4_ID ); ?>"
                                   name="<?php echo esc_attr( self::OPTION_GA4_ID ); ?>"
                                   value="<?php echo esc_attr( $this->get_ga4_id() ); ?>"
                                   class="regular-text"
                                   placeholder="G-XXXXXXXXXX">
                            <p class="description">
                                <?php esc_html_e( 'Google Analytics 4 の計測ID（例: G-XXXXXXXXXX）。未設定または不正な場合、イベントは送信されません。', 'simple-link-embed' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <hr>

            <h2><?php esc_html_e( '送信されるイベントについて', 'simple-link-embed' ); ?></h2>

            <h3><?php esc_html_e( 'イベント名', 'simple-link-embed' ); ?></h3>
            <p><code>link_card_click</code></p>

            <h3><?php esc_html_e( 'イベントパラメータ', 'simple-link-embed' ); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'パラメータ名', 'simple-link-embed' ); ?></th>
                        <th><?php esc_html_e( '内容', 'simple-link-embed' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>link_url</code></td>
                        <td><?php esc_html_e( 'クリックされたカードのリンク先URL', 'simple-link-embed' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>card_title</code></td>
                        <td><?php esc_html_e( 'カードに表示されているタイトル', 'simple-link-embed' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>link_domain</code></td>
                        <td><?php esc_html_e( 'リンク先ドメイン', 'simple-link-embed' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>page_title</code></td>
                        <td><?php esc_html_e( 'カードが設置されているページのタイトル', 'simple-link-embed' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>page_url</code></td>
                        <td><?php esc_html_e( 'カードが設置されているページのURL', 'simple-link-embed' ); ?></td>
                    </tr>
                </tbody>
            </table>

            <h3><?php esc_html_e( '実際に送信されるコマンド例', 'simple-link-embed' ); ?></h3>
            <pre><code>gtag('event', 'link_card_click', {
    link_url: 'https://example.com/article',
    card_title: '記事タイトル',
    link_domain: 'example.com',
    page_title: '現在のページタイトル',
    page_url: 'https://mysite.com/post',
    send_to: 'G-XXXXXXXXXX'
});</code></pre>

            <h3><?php esc_html_e( '確認方法', 'simple-link-embed' ); ?></h3>
            <p>
                <?php esc_html_e( 'GA4管理画面の「レポート > リアルタイム」または「DebugView」でイベントを確認できます。', 'simple-link-embed' ); ?>
            </p>
            <p>
                <?php esc_html_e( '※ サイトに gtag.js がインストールされている必要があります。', 'simple-link-embed' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * 計測が有効かどうか
     *
     * @return bool
     */
    public function is_enabled() {
        return (bool) get_option( self::OPTION_ENABLED, false );
    }

    /**
     * 計測実行可能な設定かどうか
     *
     * @return bool
     */
    public function is_tracking_active() {
        if ( ! $this->is_enabled() ) {
            return false;
        }

        return $this->is_valid_ga4_measurement_id( $this->get_ga4_id() );
    }

    /**
     * GA4 Measurement IDを取得
     *
     * @return string
     */
    public function get_ga4_id() {
        return strtoupper( trim( (string) get_option( self::OPTION_GA4_ID, '' ) ) );
    }

    /**
     * GA4計測ID形式のバリデーション
     *
     * @param string $measurement_id 計測ID
     * @return bool
     */
    private function is_valid_ga4_measurement_id( $measurement_id ) {
        return (bool) preg_match( '/^G-[A-Z0-9]+$/', strtoupper( $measurement_id ) );
    }
}
