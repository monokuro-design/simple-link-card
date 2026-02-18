<?php
/**
 * REST API Unit Tests
 *
 * @package Slemb
 */

namespace Slemb\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Slemb\REST_API;

/**
 * Class RestApiTest
 */
class RestApiTest extends TestCase {

    /**
     * REST API instance
     *
     * @var REST_API
     */
    private $rest_api;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->rest_api = REST_API::get_instance();
    }

    /**
     * Test URL validation
     */
    public function test_url_validation() {
        $this->assertTrue($this->rest_api->validate_url('https://example.com'));
        $this->assertTrue($this->rest_api->validate_url('http://example.com/test?query=value'));
        $this->assertFalse($this->rest_api->validate_url(''));
        $this->assertFalse($this->rest_api->validate_url('not-a-url'));
        $this->assertFalse($this->rest_api->validate_url('javascript:alert(1)'));
    }

    /**
     * Test rate limit returns error when exceeded
     */
    public function test_rate_limit_returns_error_when_exceeded() {
        $user_id = 999;

        // Simulate 30 requests
        set_transient('slemb_rate_limit_' . $user_id, 30, MINUTE_IN_SECONDS);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->rest_api);
        $method = $reflection->getMethod('check_rate_limit');

        $result = $method->invoke($this->rest_api, $user_id);

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertSame('rate_limit_exceeded', $result->get_error_code());
        $this->assertSame(429, $result->get_error_data('rate_limit_exceeded')['status']);
    }

    /**
     * Test rate limit allows requests under limit
     */
    public function test_rate_limit_allows_requests_under_limit() {
        $user_id = 998;

        // Set transient to 29 (just under limit)
        set_transient('slemb_rate_limit_' . $user_id, 29, MINUTE_IN_SECONDS);

        $reflection = new \ReflectionClass($this->rest_api);
        $method = $reflection->getMethod('check_rate_limit');

        $result = $method->invoke($this->rest_api, $user_id);

        $this->assertTrue($result);
        // Check that counter was incremented
        $this->assertEquals(30, get_transient('slemb_rate_limit_' . $user_id));
    }
}
