=== Lasso Lite - Affiliate Link Manager, URL Shortener & Link Tracking ===
Contributors: lassoanalytics, mollusk, khangwithlasso, phucdolasso, chuongwithlasso, triwithlasso, lanhvo94, caitlinwithlasso, genewithlasso, lassoteam
Plugin link: https://getlasso.co/?utm_source=lasso-lite&utm_medium=wporg&utm_campaign=repo-description
Tags: affiliate link manager, affiliate links, amazon affiliate, link cloaking, url shortener
Requires at least: 5.1
Requires PHP: 7.2
Tested up to: 7.1
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free Lasso Lite (Simple URLs): cloak affiliate links with a URL shortener and link tracking, add product displays in WordPress, and connect Lasso Hub when you want clearer click reports — the same Simple URLs listing on WordPress.org.

== Description ==

**Lasso Lite** is the free WordPress affiliate plugin from Lasso — the same **Simple URLs** listing you may already know. Cloak links behind short URLs on your site, add **product displays** to posts and pages, and connect **Lasso Hub** when you want clearer reporting — without paying for core cloaking and displays.

Managing affiliate links gets messy fast. You paste a long tracking URL into a post, then another, then another. When an offer changes, you dig through old content hoping you catch every instance.

Lasso Lite keeps your links in one dashboard, cloaks them behind short URLs on your site, and turns them into product displays with images, prices, and buttons (including Amazon Associates and other programs).

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

Lasso Lite covers everyday link management on its own. If you later want deeper reporting, link monitoring, or richer display layouts, you can move up to [Lasso](https://getlasso.co/?utm_source=lasso-lite&utm_medium=wporg&utm_campaign=repo-upgrade). Your existing links and displays come with you.

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

= Is Lasso Lite a Pretty Links alternative? =
Yes for link cloaking, short URLs on your site, and importing from Pretty Links. It is not a one-to-one Pretty Links Pro feature match — Lasso Lite focuses on free cloaking, product displays, and basic click counts.

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
= 163 =
* Released: November 9, 2026
* New Lite installs open the link dashboard directly without a separate onboarding flow
* Activation events reach Lasso Hub with Lite identity headers even before a license is stored
* Activation completion is recorded once the dashboard is ready to use

= 162 =
* Released: November 4, 2026
* Onboarding focuses on activation first, with optional setup steps you can finish later
* Connect flow on Amazon and Imports matches other Lite setup screens, with a clearer skip option
* Link or reconnect your Lasso account from the dashboard, including if you already have an account
* Connect footer shows a login link and version stamp; version badge styling aligned with Lasso Pro
* Renamed Auto-upgrade to Commission Upgrades in settings labels
* Retired Affiliate+ wording in the admin promotions notice
* Safer Hub account linking when a site is already associated with another account
* Reduced unnecessary automatic Hub checks on sites that have not linked an account yet
* Tour dismiss actions no longer conflict with Lasso Pro on sites running both plugins

= 161 =
* Released: October 21, 2026
* Amazon product refresh cron runs more reliably (including Crontrol Run now)
* Recognize amzlink.to as an Amazon shortlink
* Updated bundled connect snippet to the current lasso.link CDN build
* Tour CSS loads correctly with the prefixed Intro.js handle
* Preserve non-Amazon destination URLs when saving links
* General settings save keeps click tracking on when the checkbox is omitted from the form

= 160 =
* Released: October 8, 2026
* Interactive dashboard product tour for new Lite users
* PostHog product analytics in wp-admin for clearer usage insights
* Marketplace Add Link polish: wider modal, clearer product titles, and improved search
* Dashboard layout improvements for link titles and account status
* Review prompt as a corner card with milestone-based re-ask
* PHP 7.2 compatibility for older hosting environments
* Fix Marketplace Amazon Image Refresh when Prime badge logic failed
* Fix setup-progress dropdown blocking Settings tabs when closed
* Clearer review card support step without a misleading alternate review link

= 159 =
* Released: September 23, 2026
* Add Link modal with URL and Marketplace tabs, including Marketplace search and create
* Unlicensed sites can force Marketplace/BLS data and Refresh
* Manual overrides keep your Amazon image and price across cron and store updates
* Clearer first-link onboarding before upgrade prompts
* Prefer Marketplace product data for Amazon add-link and Refresh
* Link-integrity self-check and import recovery diagnostics
* Fix onboarding Continue loop that bounced back to the dashboard
* Point the in-plugin review ask at the WordPress.org form

= 158 =
* Released: September 10, 2026
* More reliable Amazon Creators credential validate/save during onboarding
* Cron scheduling no longer drops evening due minutes on the hour-1 tick
* Upgraded Lite sites are no longer hashed into UTC hour 0 for cron load

= 157 =
* Released: August 26, 2026
* More reliable WordPress.org plugin updates with stricter release packaging checks before deploy

= 156 =
* Released: August 26, 2026
* Maintenance release with improved plugin packaging for WordPress.org distribution

= 155 =
* Released: August 26, 2026
* Analytics beacons use a first-party POST `/js/e` proxy so click tracking stays more reliable when ad blockers are present
* Background cron work is spread across UTC load slots (hashed by site) with an evening BLS catch-up after midnight for more even scheduling
* Skip missing background process classes so cron does not fatal when a process class is unavailable
* Safer WP-cron bucket scheduling when cron entries are not in the expected array shape

= 154 =
* Released: August 18, 2026
* WordPress 7.1 compatibility (Tested up to)
* Onboarding step persistence improvements

