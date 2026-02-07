<?php
/**
 * OGP Fetcher Unit Tests
 *
 * @package Simple_Link_Card
 */

namespace Simple_Link_Card\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Simple_Link_Card\OGP_Fetcher;

/**
 * Class OgpFetcherTest
 */
class OgpFetcherTest extends TestCase {

    /**
     * OGP Fetcher instance
     *
     * @var OGP_Fetcher
     */
    private $ogp_fetcher;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->ogp_fetcher = OGP_Fetcher::get_instance();
    }

    /**
     * Test empty URL validation
     */
    public function test_empty_url_returns_error() {
        $result = $this->ogp_fetcher->fetch('');

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertSame('empty_url', $result->get_error_code());
    }

    /**
     * Test invalid URL validation
     */
    public function test_invalid_url_returns_error() {
        $result = $this->ogp_fetcher->fetch('not-a-valid-url');

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertSame('invalid_url', $result->get_error_code());
    }

    /**
     * Test blocked URL schemes
     */
    public function test_blocked_url_scheme_returns_error() {
        $result = $this->ogp_fetcher->fetch('file:///etc/passwd');

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertSame('invalid_scheme', $result->get_error_code());
    }

    /**
     * Test localhost blocking
     */
    public function test_localhost_is_blocked() {
        $result = $this->ogp_fetcher->fetch('http://localhost/test');

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertSame('blocked_host', $result->get_error_code());
    }

    /**
     * Test 127.0.0.1 blocking
     */
    public function test_loopback_ip_is_blocked() {
        $result = $this->ogp_fetcher->fetch('http://127.0.0.1/test');

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertSame('blocked_host', $result->get_error_code());
    }

    /**
     * Test private IP range blocking
     */
    public function test_private_ip_is_blocked() {
        $result = $this->ogp_fetcher->fetch('http://192.168.1.1/test');

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertSame('private_ip_blocked', $result->get_error_code());
    }

    /**
     * Test metadata cloud instance blocking
     */
    public function test_metadata_cloud_instance_is_blocked() {
        $result = $this->ogp_fetcher->fetch('http://169.254.169.254/latest/meta-data/');

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertSame('blocked_host', $result->get_error_code());
    }

    /**
     * Test simple_link_card_ogp_data filter is applied
     */
    public function test_ogp_data_filter_is_applied() {
        $test_data = array(
            'title' => 'Test Title',
            'url'   => 'https://example.com',
        );

        // Add filter to modify OGP data
        add_filter( 'simple_link_card_ogp_data', function( $data ) {
            $data['filtered'] = true;
            return $data;
        } );

        $filtered = apply_filters( 'simple_link_card_ogp_data', $test_data, 'https://example.com' );

        $this->assertArrayHasKey('filtered', $filtered);
        $this->assertTrue($filtered['filtered']);
    }
}
