=== Simple Link Embed ===
Contributors: monokurodesign
Tags: link card, blog card, ogp, embed, block
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Create beautiful blog cards by simply entering a URL. Automatically fetches OGP data and displays stylish link previews in the block editor.

== Description ==

Simple Link Embed automatically fetches OGP data from any URL and displays it as a beautiful blog card in your posts. Just paste a link and the plugin retrieves the title, description, image, and site name automatically.

**Features:**

* **WordPress Block (Gutenberg) Support** - Fully compatible with modern block editor
* **Automatic OGP Fetching** - Just enter a URL to automatically retrieve OGP information
* **Caching System** - 7-day cache for fast performance
* **Responsive Design** - Mobile-friendly layout
* **Theme Customization** - Customize with CSS variables
* **No JavaScript Library Dependencies** - Lightweight, no JavaScript libraries required

== External Services ==

This plugin uses the following external services:

1. **Google Favicon service**
   * Service URL: `https://www.google.com/s2/favicons`
   * Purpose: Retrieve site favicon images for card display.
   * Data sent: The target domain name extracted from the selected URL.
   * Terms of Service: https://policies.google.com/terms
   * Privacy Policy: https://policies.google.com/privacy

2. **X (Twitter) static favicon**
   * Service URL: `https://abs.twimg.com/favicons/twitter.2.ico`
   * Purpose: Display a favicon for X/Twitter links.
   * Data sent: No user-specific payload; only a standard HTTPS request for a static icon file.
   * Terms of Service: https://x.com/en/tos
   * Privacy Policy: https://x.com/en/privacy

3. **YouTube oEmbed API**
   * Service URL: `https://www.youtube.com/oembed`
   * Purpose: Retrieve metadata for YouTube URLs (title, author, thumbnail).
   * Data sent: The selected YouTube URL.
   * Terms of Service: https://www.youtube.com/t/terms
   * Privacy Policy: https://policies.google.com/privacy

4. **YouTube thumbnail image service**
   * Service URL: `https://i.ytimg.com/vi/{video_id}/hqdefault.jpg`
   * Purpose: Fallback thumbnail retrieval for YouTube video cards.
   * Data sent: Video ID extracted from the selected YouTube URL.
   * Terms of Service: https://www.youtube.com/t/terms
   * Privacy Policy: https://policies.google.com/privacy

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/simple-link-embed`, or install through the WordPress plugins screen
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the 'Simple Link Embed' block in the editor
4. Enter a URL

== Frequently Asked Questions ==

= What are the requirements? =

WordPress 5.8+ and PHP 7.4+ are required.

= Which OGP tags are supported? =

The following OGP tags are supported:
* og:title
* og:description
* og:image
* og:url
* og:site_name

= Can I change the cache duration? =

Currently fixed at 7 days. This will be configurable in a future update.

= Can I customize the styling? =

Yes, you can customize using CSS variables:
* --slemb-card-bg
* --slemb-card-border
* --slemb-card-shadow
* --slemb-title-color
* --slemb-desc-color
* --slemb-border-radius

== Screenshots ==

1. Block editor interface showing the Simple Link Embed block with URL input and settings panel
2. Frontend display example of a link card with image, title, description, and site information
3. Block settings sidebar with image position, display options, and link behavior controls

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade required.
