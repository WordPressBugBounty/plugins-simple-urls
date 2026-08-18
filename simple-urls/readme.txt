=== Lasso Lite - Affiliate Link Manager & Product Displays ===
Contributors: lassoanalytics, mollusk, khangwithlasso, phucdolasso, chuongwithlasso, triwithlasso, caitlinwithlasso, genewithlasso, lassoteam
Plugin link: https://getlasso.co/?utm_source=lasso-lite&utm_medium=wp&utm_campaign=repo-description
Tags: affiliate link manager, affiliate links, amazon affiliate, link cloaking, product displays
Requires at least: 5.1
Requires PHP: 7.2
Tested up to: 7.1
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Stop pasting long affiliate URLs into every post. Cloak your links, add product displays, and track clicks in WordPress.

== Description ==

Managing affiliate links gets messy fast. You paste a long tracking URL into a post, then another, then another. When an offer changes, you dig through old content hoping you catch every instance.

Lasso Lite is a free WordPress plugin for people who promote products with those links (including Amazon Associates). It keeps your links in one dashboard, cloaks them behind short URLs on your site, and turns them into product displays with images, prices, and buttons.

Example: use `yoursite.com/go/best-kayak` instead of a long Amazon URL. Change the destination once, and every place that link appears stays current.

### Why Lasso Lite?

* Fix a broken or outdated offer in one place instead of hunting through posts
* Turn a plain link into a product display without leaving WordPress
* See basic click counts so you know which links people actually use
* Import an existing link library instead of rebuilding it
* Start free, with no account required for core features
* Go from install to a live cloaked link in a few minutes

### How this is different from a basic URL shortener

Most shorteners and cloaking plugins mainly create redirects. Lasso Lite also gives you:

* A dashboard for all your links
* Product displays for posts and pages
* Groups for organizing links
* Basic click counts
* Imports from other link plugins

### Your first five minutes

1. Install and activate **Lasso Lite**
2. Click **Add New Link** and paste an offer URL
3. Insert it into a post with the Lasso Lite block or the `[lasso]` shortcode
4. Publish. Your cloaked link is live (add a product display if you want one).

### What's included

**All your links in one place**
Search, edit, and organize offers from a single dashboard.

**Clean short URLs you control**
Replace long tracking URLs with links like `/go/your-slug/`. Update the destination once when an offer changes.

**Product displays for your content**
Add image, title, price, badge, and buttons to reviews and roundups. Insert with the block or shortcode.

**See which links get clicked**
Each cloaked link stores a basic click count. Connect a free Lasso account for clearer reports.

**Optional Amazon product details**
Connect Amazon credentials to help fill product info on displays.

**Insert links while you write**
Works with the Block Editor and Classic Editor.

**Organize with groups**
Sort links by niche, merchant, or campaign.

**Import from other plugins**
Pretty Links, ThirstyAffiliates, Easy Affiliate Links, AAWP, EasyAzon, AmaLinks Pro, Earnist, and Affiliate URLs.

### Already using another link plugin?

Import your existing links, tidy up groups, and keep publishing. No need to rebuild your library by hand.

### When you need more later

Lasso Lite covers everyday link management on its own. If you later want deeper reporting, link monitoring, or richer display layouts, you can move up to [Lasso](https://getlasso.co/?utm_source=lasso-lite&utm_medium=wp&utm_campaign=repo-upgrade). Your existing links and displays come with you.

### Help & support

* Docs: [support.getlasso.co](https://support.getlasso.co/)
* WordPress.org forum: [wordpress.org/support/plugin/simple-urls](https://wordpress.org/support/plugin/simple-urls/)

== Installation ==

1. Go to **Plugins > Add New** and search for **Lasso Lite** (Formerly Simple URLs).
2. Click **Install Now**, then **Activate**.
3. Open **Lasso Lite** in the admin menu.
4. Click **Add New Link**, paste an offer URL, and save.
5. Edit a post, insert the **Lasso Lite** block (or `[lasso]` shortcode), and choose your link.
6. **Publish**, then click the cloaked link to confirm the redirect.

== Frequently Asked Questions ==

= Is Lasso Lite free? =
Yes. The dashboard, cloaking, groups, imports, product displays, and basic click counts work without paying. A free Lasso account is optional for support and richer reports.

= Do I need a Lasso account? =
No. Create one when you want help or clearer click reports.

= Does it work with Amazon? =
Yes. Cloak Amazon links and optionally connect credentials for product details on displays. Other programs work too.

= Can I import links from another plugin? =
Yes. Pretty Links, ThirstyAffiliates, Easy Affiliate Links, AAWP, EasyAzon, AmaLinks Pro, Earnist, and Affiliate URLs.

= Will it work with my theme and the Block Editor? =
Yes. Standard WordPress themes, plus Block Editor and Classic Editor.

= Does it work with Elementor? =
Yes. Lasso Lite includes an Elementor widget for adding displays while you edit a page.

= What's the difference between Lasso Lite and Lasso? =
Lite covers cloaking, displays, groups, imports, Amazon setup, and basic click counts. Lasso adds deeper reporting and monitoring. What you build in Lite carries over.

= Where do I get help? =
Docs: [support.getlasso.co](https://support.getlasso.co/). Forum: [wordpress.org/support/plugin/simple-urls](https://wordpress.org/support/plugin/simple-urls/).

= Is this the same plugin as Simple URLs? =
Yes. Lasso Lite is the current name (**Formerly Simple URLs**). Same WordPress.org listing (slug `simple-urls`).

== Screenshots ==

1. Edit a link and preview the product display before you publish, so you know what readers will see.
2. Group related links by topic so your library stays organized as it grows.
3. Browse every link from one dashboard. Find and update offers without digging through old posts.

== Changelog ==

= 153 =
* Released: August 5, 2026
* Improved Amazon Creators API product URL and country handling
* Hardened admin/shortcode output and quick-detail AJAX against XSS
* Improved WordPress.org listing copy

= 152 =
* Released: August 3, 2026
* Amazon Creators API: improved product fetch reliability in Lite
* Fixed discount pricing display after Creators product fetch
* Maintenance and tooling updates

= 151 =
* Released: 2026
* Gutenberg block: Block API v3 support and display CSS in the block editor iframe
* Amazon settings: Creators credentials and image refresh improvements
* Hardened thumbnail upload and license AJAX auth
* Earnings / orphan-account banner reliability improvements

= 150 =
* Released: 2026
* PHP 8 stability: safer handling of empty/null API responses (license, Amazon, BLS)
* Amazon Creators product fetch improvements
* Analytics link updates

= 149 =
* Amazon settings: Lite account validate/connect flow with clearer Creators UX and signup handoff
* Hub URL override support for the Lite/hub integration path used from Amazon settings
* Lite account email can fall back to the WordPress admin_email when needed
* Additional unit tests for settings/Ajax behavior

= 148 =
* Released: April 29, 2026
* Added Learn link in Amazon settings for Product Advertising API documentation

= 147 =
* Released: April 22, 2026
* Improved persistence for Amazon Creators settings when saving
* Refined Amazon Creators migration notice with a clearer dismiss flow

= 146 =
* Released: April 6, 2026
* Realtime click tracking on the dashboard with improved connect CTAs
* Updated Amazon Creators API validation settings
* Added banner for Amazon credentials updates when needed

= 145 =
* Released: March 3, 2026
* Deployment packaging improvements

= 144 =
* Released: March 2, 2026
* Improved site ID retrieval logic
* Documentation updates

= 143 =
* Released: January 23, 2026
* Added simple click counts for links
* Improved link optimizations for all free users

= 142 =
* Released: January 14, 2026
* API endpoint fix

= 141 =
* Released: January 13, 2026
* Added in-app account creation
* Improved Lasso account connection
* Various small bug fixes

= 140 =
* Released: November 6, 2025
* Link handling updates
* JavaScript Cleanup

= 139 =
* Released: October 23, 2025
* Fixed the copy shortcode button
* Misc JavaScript improvements

= 138 =
* Released: September 18, 2025
* Fixed an issue with click tracking

= 137 =
* Released: September 16, 2025
* Improved JavaScript external handling

= 136 =
* Released: September 5, 2025
* Revised plugin description and details

= 135 =
* Released: August 29, 2025
* Additional JS improvements

= 134 =
* Released: August 26, 2025
* Improved the way Lasso Lite handles JavaScript

= 133 =
* Released: August 18, 2025
* Improved code stability for upgrades

= 132 =
* Released: May 8, 2025
* Fixed a bug related to new PHP versions
* Updated in-app banners

= 131 =
* Released: March 21, 2025
* Updated tracking and link updates

= 130 =
* Released: December 30, 2024
* Updated Amazon attribution link handling to comply with new policies
* Improved importing and exporting of displays between plugin versions
* Fixed an edge case issue that stopped users from accessing the "Add a link" modals

= 129 =
* Released: November 5, 2024
* Fixed an issue with promotional banners showing by mistake
* Added the ability to refresh manually Amazon product data
* Fixed an issue with Lasso Lite blocks

= 128 =
* Released: October 8, 2024
* Stability improvements

= 127 =
* Released: October 2, 2024
* Fixed promotional banners that were showing by mistake
* Added auto-upgrade functionality for link handling

= 126 =
* Released: August 26, 2024
* Updated the plugin's onboarding flow
* Fixed icons, modals, and external URLs in the plugin that were depreciated
* Enhanced the ability to connect to admin tools

= 125 =
* Released: February 22, 2024
* Updated the plugin's name in the WP admin dashboard sidebar and plugin page

= 124 =
* Released: February 21, 2024
* Fixed an issue where banners couldn't be dismissed

= 123 =
* Released: January 17, 2024
* Support for WordPress 6.4.2
* Improved Amazon price-checking consistency
* Improved ability to import large numbers of links to Lasso Pro

= 122 =
* Released: January 16, 2024
* Just an internal test to improve the release process

= 121 =
* Released: October 19, 2023
* Support for WordPress 6.3.2
* Security Fix: CSRF vulnerability patched

= 120 =
* Released: October 6, 2023
* Fixed an encoding issue on some display links

= 119 =
* Released: September 26, 2023
* Support for WordPress 6.3.1
* Security Fix
* Resolved deprecation notices in PHP 8.2

= 118 =
* Released: August 31, 2023
* Support for WordPress 6.3
* Fixed Access Controls
* Security Fix: Cross Site Scripting (XSS)

= 117 =
* Released: May 30, 2023
* Support for WordPress 6.2.1
* Better Import detection and flexibility

= 116 =
* Released: Feb 28, 2023
* Fixed an edge case where you wouldn't see imports
* Ability to disable support
