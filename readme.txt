=== Mastodon Feed ===
Contributors: mattsimpson
Tags: mastodon, fediverse, social, feed, shortcode
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.4
License: MIT
License URI: https://directory.fsf.org/wiki/License:Expat

Embed Mastodon feeds into your WordPress site using the Block Editor or shortcode. Built-in account lookup tool makes setup easy.

== Description ==

Mastodon Feed is a WordPress plugin that allows you to easily embed Mastodon feeds directly into your WordPress pages and posts. Use the visual Block Editor (Gutenberg) block or the simple `[mastodon-feed]` shortcode.

= Features =

* Block Editor (Gutenberg) support with visual account configuration
* Built-in Mastodon Account ID lookup tool
* Display personal Mastodon feeds by Account ID
* Display tag-based feeds from any Mastodon instance
* Customizable styling with flexible color options
* Filter options: exclude boosts, exclude replies, show only pinned, show only media
* Caching system to reduce API calls
* Fully translatable
* Privacy-friendly: No tracking, no external resources except Mastodon API calls

= Usage =

= Block Editor =

In the WordPress block editor, click the + button and search for "Mastodon Feed". The block includes:

* **Account Lookup Tool** - Enter your Mastodon handle (e.g., username@mastodon.social) to automatically find your account ID
* **Visual Configuration** - Configure all settings directly in the block inspector
* **Live Preview** - See your feed as you configure it

= Shortcode =

You can also add the shortcode to any page or post:

`[mastodon-feed instance="mastodon.social" account="YOUR-ACCOUNT-ID"]`

For tag feeds:

`[mastodon-feed instance="mastodon.social" tag="photography"]`

= Available Shortcode Attributes =

* `instance` - Mastodon instance domain (required)
* `account` - Your Mastodon account ID (required for personal feeds)
* `tag` - Tag name for tag feeds (alternative to account)
* `limit` - Maximum number of posts to display (default: 10)
* `excludeBoosts` - Exclude boosted posts (default: false)
* `excludeReplies` - Exclude reply posts (default: false)
* `onlyPinned` - Show only pinned posts (default: false)
* `onlyMedia` - Show only posts with media (default: false)

See the [GitHub repository](https://github.com/mattsimpson/mastodon-feed) for full documentation.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/mastodon-feed/` or install through the WordPress plugins screen
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings > Mastodon Feed to configure default options
4. Add the `[mastodon-feed]` shortcode to any page or post

== Frequently Asked Questions ==

= How do I find my Mastodon account ID? =

The plugin includes a built-in account lookup tool! You can find it in:

- **Block Editor**: When adding a Mastodon Feed block, use the Account Lookup panel in the sidebar. Simply enter your full Mastodon handle (e.g., `username@mastodon.social`) and click "Lookup Account ID"
- **Admin Settings**: Go to Settings > Mastodon Feed > Account Lookup tab

The built-in tool will automatically find your account ID and configure the instance for you.

**Alternative methods:**

You can also use [this handy external lookup tool](https://wolfgang.lol/code/include-mastodon-feed-wordpress-plugin/) from [WolfGang](https://mastodon.social/@w101).

Or, as a logged-in user, you can use an API v2 search to find your ID:

```https://example.org/api/v2/search?q=username@example.org&resolve=true&limit=5```

* Change `example.org` to your instance
* Replace `username` with your handle
* Open the URL in your web browser (you must be logged in)

= Can I customize the appearance? =

Yes! Visit Settings > Mastodon Feed to customize colors, border radius, and other styling options. The plugin also supports alpha transparency for backgrounds.

= How often does the plugin fetch new posts? =

By default, posts are cached for 1 hour. You can adjust this in the settings or clear the cache manually using the "Clear Feed Cache" button.

= Does this work with any Mastodon instance? =

Yes, as long as the instance has public API access enabled.

== Screenshots ==

1. Example of embedded Mastodon feed
2. Admin settings page with tabbed interface
3. Customizable styling options

== Changelog ==

= 1.0.0 =
* Initial release
* Block Editor (Gutenberg) support with visual configuration
* Built-in account lookup tool
* Shortcode support with extensive attributes
* Tag and personal feed support
* Customizable styling options with color picker
* Separate background and text color controls
* Filter options: exclude boosts, exclude replies, only pinned, only media
* Display options: show/hide preview cards, author info, timestamps
* Cache management with configurable expiration
* Tabbed settings interface
* Privacy-friendly: No tracking, no external resources

== Upgrade Notice ==

= 1.0.0 =
Initial release of Mastodon Feed plugin.

== Credits ==

This plugin was inspired by and builds upon the work of others in the WordPress and Mastodon communities:

* WolfGang (https://mastodon.social/@w101) - For the original Include Mastodon Feed WordPress Plugin (https://wolfgang.lol/code/include-mastodon-feed-wordpress-plugin) which served as inspiration for this implementation
* Automattic - For the WordPress ActivityPub Plugin (https://github.com/Automattic/wordpress-activitypub) which provided inspiration for integrating federated social networks with WordPress

Thank you to these projects and their contributors for helping make the WordPress ecosystem more connected to the Fediverse!
