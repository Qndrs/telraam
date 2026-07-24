=== Qndrs Telraam Inzicht ===
Contributors: qndrs
Tags: telraam, traffic, statistics, mobility, shortcode
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.3
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display Telraam traffic statistics on your WordPress website using a shortcode.

== Description ==

Qndrs Telraam Inzicht displays traffic statistics from the Telraam API on a WordPress website.

The plugin currently provides a shortcode for showing traffic totals for a Telraam segment. It uses the WordPress HTTP API for requests and WordPress transients for caching, so Telraam API limits are respected.

Frontend output uses semantic HTML with labelled sections, summary data, and accessible table captions.

Current shortcode:

`[qndrs_telraam_segment]`

With attributes:

`[qndrs_telraam_segment id="9000010390" days="7"]`

Table view:

`[qndrs_telraam_segment id="9000010390" days="7" view="table"]`

The plugin requires a Telraam API token. You can configure the token and default segment settings in Settings > Qndrs Telraam Inzicht.

The settings page includes an API connection test. The test uses the saved token and default segment ID, but never displays the token.

This plugin is not affiliated with or endorsed by Telraam. Telraam API use is subject to Telraam's own terms, rate limits, and data licensing conditions.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/qndrs-telraam-inzicht`.
2. Activate Qndrs Telraam Inzicht through the WordPress Plugins screen.
3. Go to Settings > Qndrs Telraam Inzicht.
4. Enter your Telraam API token.
5. Configure a default Telraam segment ID and cache duration.
6. Use the API connection test to verify the token and segment.
7. Add the shortcode to a post or page.

== Frequently Asked Questions ==

= Do I need a Telraam API token? =

Yes. The plugin uses the Telraam API and needs a valid API token.

= Does the plugin store my API token? =

Yes. The token is stored as a WordPress option. It is not shown on the frontend.

= Does the plugin call an external service? =

Yes. The plugin sends requests to the Telraam API endpoint at `https://telraam-api.net`.

= Why is caching enabled? =

Telraam API access is rate limited. Caching reduces repeated API requests and protects your API quota.

= Can I use a public Telraam location URL instead of a segment ID? =

Not yet. This is planned for a later version.

= Is the plugin translation-ready? =

Yes. The plugin uses the `qndrs-telraam-inzicht` text domain and is prepared for WordPress.org language packs.

== Changelog ==

= 0.2.0 =

* Added visible API token status without exposing the token.
* Added API connection test.
* Added traffic report normalizer.
* Improved accessible frontend HTML structure.

= 0.1.0 =

* Initial plugin scaffold.
* Added settings page.
* Added Telraam API client.
* Added transient caching.
* Added `[qndrs_telraam_segment]` shortcode.
* Prepared internationalization.
