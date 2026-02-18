<?php
/**
 * Block Rendering Integration Tests
 *
 * @package Slemb
 */

namespace Slemb\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Slemb\Renderer;

/**
 * Class BlockRenderingTest
 */
class BlockRenderingTest extends TestCase {

    /**
     * Renderer instance
     *
     * @var Renderer
     */
    private $renderer;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->renderer = Renderer::get_instance();
    }

    /**
     * Test render with empty URL returns empty string
     */
    public function test_render_with_empty_url_returns_empty_string() {
        $result = $this->renderer->render(array('url' => ''));

        $this->assertEquals('', $result);
    }

    /**
     * Test render with valid URL returns HTML
     */
    public function test_render_with_valid_url_returns_html() {
        $attributes = array(
            'url'             => 'https://example.com',
            'imagePosition'   => 'left',
            'showSiteName'    => true,
            'showDescription' => true,
            'openInNewTab'    => true,
            'ogpData'         => array(
                'title'       => 'Test Title',
                'description' => 'Test Description',
                'image'       => 'https://example.com/image.jpg',
                'url'         => 'https://example.com',
                'site_name'   => 'Example Site',
                'domain'      => 'example.com',
                'favicon'     => 'https://example.com/favicon.ico',
            ),
        );

        $result = $this->renderer->render($attributes);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('wp-block-simple-link-embed', $result);
        $this->assertStringContainsString('slemb-card', $result);
        $this->assertStringContainsString('Test Title', $result);
    }

    /**
     * Test render with OGP data includes expected elements
     */
    public function test_render_with_ogp_data_includes_expected_elements() {
        $attributes = array(
            'url'             => 'https://example.com',
            'imagePosition'   => 'left',
            'showSiteName'    => true,
            'showDescription' => true,
            'openInNewTab'    => true,
            'ogpData'         => array(
                'title'       => 'Test Page Title',
                'description' => 'This is a test description for the page.',
                'image'       => 'https://example.com/image.jpg',
                'url'         => 'https://example.com',
                'site_name'   => 'Example Site',
                'domain'      => 'example.com',
                'favicon'     => 'https://example.com/favicon.ico',
            ),
        );

        $result = $this->renderer->render($attributes);

        // Check for title
        $this->assertStringContainsString('Test Page Title', $result);
        $this->assertStringContainsString('slemb-card__title', $result);

        // Check for description
        $this->assertStringContainsString('This is a test description', $result);
        $this->assertStringContainsString('slemb-card__description', $result);

        // Check for site name
        $this->assertStringContainsString('Example Site', $result);
        $this->assertStringContainsString('slemb-card__site', $result);

        // Check for image
        $this->assertStringContainsString('https://example.com/image.jpg', $result);
        $this->assertStringContainsString('slemb-card__image', $result);
    }

    /**
     * Test render with right image position
     */
    public function test_render_with_right_image_position() {
        $attributes = array(
            'url'           => 'https://example.com',
            'imagePosition' => 'right',
            'ogpData'       => array(
                'title' => 'Test Title',
                'image' => 'https://example.com/image.jpg',
                'url'   => 'https://example.com',
            ),
        );

        $result = $this->renderer->render($attributes);

        $this->assertStringContainsString('slemb-card--image-right', $result);
    }

    /**
     * Test render with hidden image
     */
    public function test_render_with_hidden_image() {
        $attributes = array(
            'url'           => 'https://example.com',
            'imagePosition' => 'hide',
            'ogpData'       => array(
                'title' => 'Test Title',
                'url'   => 'https://example.com',
            ),
        );

        $result = $this->renderer->render($attributes);

        $this->assertStringContainsString('slemb-card--no-image', $result);
    }

    /**
     * Test render without description
     */
    public function test_render_without_description() {
        $attributes = array(
            'url'             => 'https://example.com',
            'showDescription' => false,
            'ogpData'         => array(
                'title'       => 'Test Title',
                'description' => 'This should not be displayed',
                'url'         => 'https://example.com',
            ),
        );

        $result = $this->renderer->render($attributes);

        $this->assertStringNotContainsString('This should not be displayed', $result);
        $this->assertStringNotContainsString('slemb-card__description', $result);
    }

    /**
     * Test render without site name
     */
    public function test_render_without_site_name() {
        $attributes = array(
            'url'          => 'https://example.com',
            'showSiteName' => false,
            'ogpData'      => array(
                'title'     => 'Test Title',
                'site_name' => 'This should not be displayed',
                'url'       => 'https://example.com',
            ),
        );

        $result = $this->renderer->render($attributes);

        $this->assertStringNotContainsString('This should not be displayed', $result);
        $this->assertStringNotContainsString('slemb-card__site', $result);
    }

    /**
     * Test render without new tab
     */
    public function test_render_without_new_tab() {
        $attributes = array(
            'url'          => 'https://example.com',
            'openInNewTab' => false,
            'ogpData'      => array(
                'title' => 'Test Title',
                'url'   => 'https://example.com',
            ),
        );

        $result = $this->renderer->render($attributes);

        $this->assertStringNotContainsString('target="_blank"', $result);
        $this->assertStringNotContainsString('noopener', $result);
    }

    /**
     * Test render with new tab includes security attributes
     */
    public function test_render_with_new_tab_includes_security_attributes() {
        $attributes = array(
            'url'          => 'https://example.com',
            'openInNewTab' => true,
            'ogpData'      => array(
                'title' => 'Test Title',
                'url'   => 'https://example.com',
            ),
        );

        $result = $this->renderer->render($attributes);

        $this->assertStringContainsString('target="_blank"', $result);
        $this->assertStringContainsString('rel="noopener noreferrer"', $result);
    }

    /**
     * Test render includes aria-label for accessibility
     */
    public function test_render_includes_aria_label() {
        $attributes = array(
            'url'     => 'https://example.com',
            'ogpData' => array(
                'title' => 'Test Page Title',
                'url'   => 'https://example.com',
            ),
        );

        $result = $this->renderer->render($attributes);

        $this->assertStringContainsString('aria-label="', $result);
    }

    /**
     * Test slemb_card_html filter is applied
     */
    public function test_card_html_filter_is_applied() {
        $attributes = array(
            'url'     => 'https://example.com',
            'ogpData' => array(
                'title' => 'Test Title',
                'url'   => 'https://example.com',
            ),
        );

        $filter_applied = false;
        $filtered_html = '';

        add_filter( 'slemb_card_html', function( $html ) use ( &$filter_applied, &$filtered_html ) {
            $filter_applied = true;
            $filtered_html = $html;
            return $html . '<!-- Filtered -->';
        }, 10, 1 );

        $result = $this->renderer->render($attributes);

        $this->assertTrue($filter_applied);
        $this->assertStringContainsString('<!-- Filtered -->', $result);
    }

    /**
     * Test render placeholder
     */
    public function test_render_placeholder() {
        $result = $this->renderer->render_placeholder();

        $this->assertStringContainsString('slemb-placeholder', $result);
        $this->assertStringContainsString('URL', $result);
    }

    /**
     * Test render error
     */
    public function test_render_error() {
        $error_message = 'Test error message';
        $result = $this->renderer->render_error($error_message);

        $this->assertStringContainsString('slemb-error', $result);
        $this->assertStringContainsString($error_message, $result);
    }

    /**
     * Test render loading
     */
    public function test_render_loading() {
        $result = $this->renderer->render_loading();

        $this->assertStringContainsString('slemb-loading', $result);
        $this->assertStringContainsString('slemb-spinner', $result);
    }

    /**
     * Test render sanitizes URL
     */
    public function test_render_sanitizes_url() {
        $attributes = array(
            'url'     => 'javascript:alert(1)',
            'ogpData' => array(
                'title' => 'Test Title',
                'url'   => 'https://example.com',
            ),
        );

        $result = $this->renderer->render($attributes);

        // JavaScript URL should be sanitized to empty
        $this->assertEmpty($result);
    }
}
