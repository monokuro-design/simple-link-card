<?php
/**
 * OGP Fetcher Unit Tests
 *
 * @package Slemb
 */

namespace Slemb\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Slemb\OGP_Fetcher;

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
     * Test private IPv6 range blocking
     */
    public function test_private_ipv6_is_blocked() {
        $result = $this->ogp_fetcher->fetch('http://[fd00::1]/test');

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
     * Test slemb_ogp_data filter is applied
     */
    public function test_ogp_data_filter_is_applied() {
        $test_data = array(
            'title' => 'Test Title',
            'url'   => 'https://example.com',
        );

        // Add filter to modify OGP data
        add_filter( 'slemb_ogp_data', function( $data ) {
            $data['filtered'] = true;
            return $data;
        } );

        $filtered = apply_filters( 'slemb_ogp_data', $test_data, 'https://example.com' );

        $this->assertArrayHasKey('filtered', $filtered);
        $this->assertTrue($filtered['filtered']);
    }

    /**
     * Test YouTube watch URL video ID extraction
     */
    public function test_extracts_youtube_video_id_from_watch_url() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'extract_youtube_video_id' );

        $video_id = $method->invoke( $this->ogp_fetcher, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' );

        $this->assertSame( 'dQw4w9WgXcQ', $video_id );
    }

    /**
     * Test youtu.be short URL video ID extraction
     */
    public function test_extracts_youtube_video_id_from_short_url() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'extract_youtube_video_id' );

        $video_id = $method->invoke( $this->ogp_fetcher, 'https://youtu.be/dQw4w9WgXcQ' );

        $this->assertSame( 'dQw4w9WgXcQ', $video_id );
    }

    /**
     * Test YouTube shorts URL video ID extraction
     */
    public function test_extracts_youtube_video_id_from_shorts_url() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'extract_youtube_video_id' );

        $video_id = $method->invoke( $this->ogp_fetcher, 'https://www.youtube.com/shorts/dQw4w9WgXcQ' );

        $this->assertSame( 'dQw4w9WgXcQ', $video_id );
    }

    /**
     * Test incomplete YouTube cache detection
     */
    public function test_detects_incomplete_youtube_cache() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'is_incomplete_youtube_cache' );

        $is_incomplete = $method->invoke(
            $this->ogp_fetcher,
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            array(
                'title' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'image' => '',
                'url'   => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            )
        );

        $this->assertTrue( $is_incomplete );
    }

    /**
     * Test non-YouTube cache is not treated as incomplete by YouTube rule
     */
    public function test_non_youtube_cache_is_not_marked_incomplete() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'is_incomplete_youtube_cache' );

        $is_incomplete = $method->invoke(
            $this->ogp_fetcher,
            'https://example.com/article',
            array(
                'title' => 'https://example.com/article',
                'image' => '',
                'url'   => 'https://example.com/article',
            )
        );

        $this->assertFalse( $is_incomplete );
    }

    /**
     * Test YouTube channel cache can be valid without image
     */
    public function test_youtube_channel_cache_without_image_is_not_marked_incomplete() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'is_incomplete_youtube_cache' );

        $is_incomplete = $method->invoke(
            $this->ogp_fetcher,
            'https://www.youtube.com/@denkenanima',
            array(
                'title' => '@denkenanima - YouTube',
                'image' => '',
                'url'   => 'https://www.youtube.com/@denkenanima',
            )
        );

        $this->assertFalse( $is_incomplete );
    }

    /**
     * Test YouTube channel label extraction from @handle URL
     */
    public function test_extracts_youtube_channel_label_from_handle_url() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'extract_youtube_channel_label' );

        $channel_label = $method->invoke( $this->ogp_fetcher, 'https://www.youtube.com/@denkenanima' );

        $this->assertSame( '@denkenanima', $channel_label );
    }

    /**
     * Test YouTube channel URL detection
     */
    public function test_detects_youtube_channel_url() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'is_youtube_channel_url' );

        $this->assertTrue( $method->invoke( $this->ogp_fetcher, 'https://www.youtube.com/@denkenanima' ) );
        $this->assertFalse( $method->invoke( $this->ogp_fetcher, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' ) );
    }

    /**
     * Test channel metadata extraction from JSON-LD
     */
    public function test_extracts_youtube_channel_metadata_from_json_ld() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'extract_youtube_channel_metadata_from_json_ld' );

        $html = '<script type="application/ld+json">{"@context":"http://schema.org","@type":"Person","name":"Demo Channel","description":"Demo Description","url":"https://www.youtube.com/@demo","image":"https://example.com/channel.jpg"}</script>';

        $metadata = $method->invoke( $this->ogp_fetcher, $html, 'https://www.youtube.com/@demo' );

        $this->assertSame( 'Demo Channel', $metadata['title'] );
        $this->assertSame( 'Demo Description', $metadata['description'] );
        $this->assertSame( 'https://example.com/channel.jpg', $metadata['image'] );
    }

    /**
     * Test channel metadata extraction from YouTube initial data
     */
    public function test_extracts_youtube_channel_metadata_from_initial_data() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'extract_youtube_channel_metadata_from_initial_data' );

        $html = '<script>var ytInitialData = {"metadata":{"channelMetadataRenderer":{"title":"\\u30c7\\u30e2\\u30c1\\u30e3\\u30f3\\u30cd\\u30eb","description":"\\u8aac\\u660e\\u6587\\u3067\\u3059"}},"avatar":{"thumbnails":[{"url":"https:\\/\\/example.com\\/avatar.jpg"}]}};</script>';

        $metadata = $method->invoke( $this->ogp_fetcher, $html );

        $this->assertSame( 'デモチャンネル', $metadata['title'] );
        $this->assertSame( '説明文です', $metadata['description'] );
        $this->assertSame( 'https://example.com/avatar.jpg', $metadata['image'] );
    }

    /**
     * Test YouTube share URL normalization removes si parameter
     */
    public function test_normalize_youtube_url_removes_si_query() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'normalize_youtube_url' );

        $normalized = $method->invoke(
            $this->ogp_fetcher,
            'https://youtube.com/@denkenanima?si=abc123&t=1'
        );

        $this->assertSame( 'https://youtube.com/@denkenanima?t=1', $normalized );
    }

    /**
     * Test generic YouTube channel fallback cache is marked incomplete
     */
    public function test_generic_youtube_channel_fallback_cache_is_marked_incomplete() {
        $method = new \ReflectionMethod( OGP_Fetcher::class, 'is_incomplete_youtube_cache' );

        $is_incomplete = $method->invoke(
            $this->ogp_fetcher,
            'https://www.youtube.com/@denkenanima',
            array(
                'title'       => '@denkenanima - YouTube',
                'description' => 'YouTubeチャンネルページです。',
                'image'       => '',
                'url'         => 'https://www.youtube.com/@denkenanima',
            )
        );

        $this->assertTrue( $is_incomplete );
    }
}
