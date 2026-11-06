=== Traccar Map ===
Contributors: tananaev
Donate link: https://www.traccar.org/donate/
Tags: traccar, gps, tracking, map, shortcode
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embed the existing Traccar web app using a server URL and a public read-only token.

== Description ==

Embed your Traccar tracking map in a WordPress page or post. You need an existing Traccar server and a dedicated account for public access.

Settings → Traccar Map stores the web app URL, public token and default height. Use `[traccar_map]` in a Shortcode block, page or post. Override the height with `[traccar_map height="80vh"]` or `[traccar_map height="500"]`. Supported units: px, vh, rem and em. Unitless values are pixels; default is 600px. Invalid shortcode heights fall back to the configured default.

The map fills the available width and loads the existing Traccar application in an iframe. It does not duplicate the map, proxy the API or hide Traccar controls. Only administrators with `manage_options` can change settings. Unconfigured maps are hidden from visitors and show a setup hint to administrators.

== Installation ==

1. In WordPress, go to Plugins → Add New, search for "Traccar Map", and click Install Now.
2. Activate Traccar Map.
3. Create a dedicated non-administrator Traccar account, enable Read-Only and assign only the devices/groups intended for public access.
4. Sign in as that account and generate a token under Settings → Preferences → Token. Choose an appropriate expiration time.
5. Enter the web app base URL and token in Settings → Traccar Map, then save.
6. Add `[traccar_map]` to a page using the Shortcode block.

== External service and privacy ==

Visitors load the configured Traccar server directly. Their browser sends normal web requests, including their IP address and the public token, to that server. Traccar and its configured map providers may use cookies and request map tiles or other resources. Their privacy policies apply. No vendor server is hardcoded and this plugin makes no server-side API requests.

The token is public in the page HTML and browser requests. It is not a secret. Read-only permissions must be configured on Traccar; this plugin cannot verify or enforce them. Never use an administrator or private account token. To revoke access, revoke the token in Traccar and clear any WordPress/CDN page caches. Replacing the setting alone does not revoke an old token.

== Troubleshooting ==

* Use an HTTPS Traccar URL when WordPress uses HTTPS to avoid mixed-content blocks.
* Enter the web app root (optionally including a port or subdirectory), not an /api endpoint or a URL containing a query string or fragment.
* Traccar/reverse-proxy Content-Security-Policy frame-ancestors and X-Frame-Options must permit the WordPress origin. WordPress CSP frame-src must allow Traccar.
* Cross-site iframe sessions can require Secure; SameSite=None cookies. Browsers may still block third-party cookies. Prefer a same-site deployment and verify anonymous access in the browsers your visitors use.
* If login fails, check token expiry, account status and device permissions in Traccar. Test the token URL directly, then inspect browser console/network errors.
* The plugin retains settings on deactivation or deletion.

Setup reference: [Traccar user management](https://www.traccar.org/user-management/).

== Support and donations ==

* [Documentation](https://www.traccar.org/documentation/)
* [Community forums](https://www.traccar.org/forums/)
* [Plugin issues](https://github.com/traccar/traccar-wordpress/issues)

Support Traccar development through [GitHub Sponsors](https://github.com/sponsors/traccar) or see [other donation options](https://www.traccar.org/donate/).

== Screenshots ==

1. Traccar embedded in a WordPress page, displaying sample vehicles from a local demo.
2. Plugin settings for the server URL, public read-only token, and default map height.

== License ==

Copyright 2026 Tananaev Solutions.

This plugin is free software, licensed under the GNU General Public License, version 2 or (at your option) any later version. See LICENSE.txt for the full license text. It is distributed without any warranty, including the implied warranties of merchantability or fitness for a particular purpose.

== Changelog ==

= 1.0.0 =
* Initial version with settings, public token embedding and per-shortcode height.
