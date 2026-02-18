<?php
/**
 * Cache Manager Unit Tests
 *
 * @package Slemb
 */

namespace Slemb\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Slemb\Cache_Manager;

/**
 * Class CacheManagerTest
 */
class CacheManagerTest extends TestCase {

    /**
     * Cache Manager instance
     *
     * @var Cache_Manager
     */
    private $cache_manager;

    /**
     * Test URL
     *
     * @var string
     */
    private $test_url = 'https://example.com/test';

    /**
     * Test OGP data
     *
     * @var array
     */
    private $test_data;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->cache_manager = Cache_Manager::get_instance();

        $this->test_data = array(
            'title'       => 'Test Title',
            'description' => 'Test Description',
            'image'       => 'https://example.com/image.jpg',
            'url'         => $this->test_url,
            'site_name'   => 'Example Site',
            'domain'      => 'example.com',
            'favicon'     => 'https://example.com/favicon.ico',
        );
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void {
        // Clean up transients
        $reflection = new \ReflectionClass($this->cache_manager);
        $method = $reflection->getMethod('get_cache_key');
        $key = $method->invoke($this->cache_manager, $this->test_url);
        delete_transient($key);

        parent::tearDown();
    }

    /**
     * Test cache miss returns false
     */
    public function test_cache_miss_returns_false() {
        $result = $this->cache_manager->get('https://nonexistent-' . time() . '.com');

        $this->assertFalse($result);
    }

    /**
     * Test cache set and get
     */
    public function test_cache_set_and_get() {
        // Set cache
        $set_result = $this->cache_manager->set($this->test_url, $this->test_data);
        $this->assertTrue($set_result);

        // Get cache
        $result = $this->cache_manager->get($this->test_url);

        $this->assertIsArray($result);
        $this->assertEquals($this->test_data['title'], $result['title']);
        $this->assertEquals($this->test_data['url'], $result['url']);
    }

    /**
     * Test cache delete
     */
    public function test_cache_delete() {
        // Set cache first
        $this->cache_manager->set($this->test_url, $this->test_data);

        // Verify it exists
        $result = $this->cache_manager->get($this->test_url);
        $this->assertIsArray($result);

        // Delete cache
        $delete_result = $this->cache_manager->delete($this->test_url);
        $this->assertTrue($delete_result);

        // Verify it's gone
        $result = $this->cache_manager->get($this->test_url);
        $this->assertFalse($result);
    }

    /**
     * Test get stats returns expected structure
     */
    public function test_get_stats_returns_expected_structure() {
        $stats = $this->cache_manager->get_stats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_cache_count', $stats);
        $this->assertArrayHasKey('expiration_seconds', $stats);
        $this->assertArrayHasKey('expiration_days', $stats);
        $this->assertIsInt($stats['total_cache_count']);
        $this->assertIsInt($stats['expiration_seconds']);
    }
}
