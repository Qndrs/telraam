=== Qndrs Telraam Inzicht ===
Contributors: qndrs
Tags: telraam, traffic, statistics, mobility, shortcode
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.3
Stable tag: 0.3.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display Telraam traffic statistics on your WordPress website using a shortcode.

== Description ==

Qndrs Telraam Inzicht displays traffic statistics from the Telraam API on a WordPress website.

The plugin currently provides a shortcode for showing traffic totals for a Telraam segment. It uses the WordPress HTTP API for requests and WordPress transients for caching, so Telraam API limits are respected.

Frontend output uses semantic HTML with labelled sections, summary data, accessible table captions, and a neutral responsive base style.

Current shortcode:

`[qndrs_telraam_segment]`

With attributes:

`[qndrs_telraam_segment id="9000010390" days="7"]`

Table view:

`[qndrs_telraam_segment id="9000010390" days="7" view="table"]`

Limit table rows:

`[qndrs_telraam_segment id="9000010390" days="7" view="table" rows="24"]`

Table rows are shown from newest to oldest. Use `rows="all"` to show all returned rows. The default is `24`.

Customize or visually hide the plugin heading:

`[qndrs_telraam_segment title="Traffic in our street"]`

`[qndrs_telraam_segment title=""]`

The plugin requires a Telraam API token. You can configure the token and default segment settings in Settings > Qndrs Telraam Inzicht.

The settings page includes an API connection test. The test uses the saved token and default segment ID, but never displays the token.

The saved API token can be cleared from the settings page without entering a replacement token.

The settings page uses a compact card layout with token and cache actions next to the related fields.

### External services

This plugin connects to the Telraam API to retrieve traffic statistics for the Telraam segment configured by the site administrator or provided in a shortcode.

The plugin sends an HTTPS POST request to `https://telraam-api.net/v1/reports/traffic` when cached data is not available, when the API connection test is used, or when the cache is manually refreshed. The request includes the configured Telraam API token in the `X-Api-Key` header and a JSON body containing the requested segment ID, start time, end time, report level, and report format. The plugin does not send WordPress user data or visitor data to Telraam.

Traffic data returned by Telraam is displayed on the site frontend and may be cached in WordPress transients for the configured cache duration.

Telraam service: https://telraam.net/

Telraam API documentation: https://faq.telraam.net/en/category/2/data-interpretation-and-the-telraam-api

Telraam terms of use: https://telraam.net/en/terms-of-use

Telraam privacy policy: https://telraam.net/en/privacy-policy

Telraam data license information: https://faq.telraam.net/en/article/9/telraam-data-license-what-can-i-do-with-the-telraam-data

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

Yes. The token is stored as a WordPress option. It is not shown on the frontend and can be cleared from the settings page.

= Does the plugin call an external service? =

Yes. The plugin sends requests to the Telraam API endpoint at `https://telraam-api.net`.

= Why is caching enabled? =

Telraam API access is rate limited. Caching reduces repeated API requests and protects your API quota.

= Can I use a public Telraam location URL instead of a segment ID? =

Not yet. This is planned for a later version.

= Is the plugin translation-ready? =

Yes. The plugin uses the `qndrs-telraam-inzicht` text domain and is prepared for WordPress.org language packs.

== Changelog ==

= 0.3.2 =

* Added Telraam S2 night traffic counts as a separate traffic category.
* Improved Telraam API rate-limit handling during cold-cache page renders.
* Refined responsive summary card breakpoints for narrow and wide containers.
* Added a `title` shortcode attribute, including `title=""` to visually hide the plugin heading.
* Changed table output to show the newest traffic rows first.

= 0.3.1 =

* Improved sidebar display for compact layouts.
* Prevented duplicate settings saved notices.
* Shortened frontend labels for compact placements.

= 0.3.0 =

* Added a `rows` shortcode attribute for table output.
* Improved frontend table timestamps.
* Added a neutral responsive frontend base style.
* Improved settings page layout and admin styling.
* Fixed API token clearing so the saved token is actually removed.
* Clear cached segment traffic data when the API token is removed.

= 0.2.0 =

* Added visible API token status without exposing the token.
* Added API token clearing action.
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
