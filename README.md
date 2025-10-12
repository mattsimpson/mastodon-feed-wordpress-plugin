# Mastodon Feed WordPress Plugin

Plugin that provides both a Gutenberg Block and `[mastodon-feed]` shortcode to easily integrate Mastodon feeds into WordPress pages. Supports personal and tag feeds with customizable styling options.

## Table of contents
* [Features](#features)
* [Usage](#usage)
  * [Supported shortcode attributes](#supported-shortcode-attributes)
  * [Additional customization](#additional-customizations)
* [Settings](#settings)
* [Installation](#installation)
  * [Installation via ZIP file](#installation-via-zip-file)
  * [Installation via git checkout](#installation-via-git-checkout)
* [FAQ](#faq)
  * [How do I find my account ID?](#how-do-i-find-my-account-id)
  * [Can I customize the appearance?](#can-i-customize-the-appearance)
  * [Can I modify the plugin?](#can-i-modify-the-plugin)

## Features

* Display personal Mastodon feeds by account ID
* Display tag-based feeds from any Mastodon instance
* Gutenberg Block editor support with visual preview and built-in account lookup
* Customizable styling with flexible color options
* Alpha transparency support for backgrounds and text colors
* Separate background and text color controls for optimal readability
* Filter options: exclude boosts, exclude replies, show only pinned, show only media
* Caching system to reduce API calls (configurable interval)
* Tabbed admin interface for easy configuration
* Fully translatable
* Privacy-friendly: No tracking, no external resources except Mastodon API calls

## Usage

Place the following shortcode into your WordPress page as a shortcode block or just copy and paste right within a text block:

```[mastodon-feed instance="YOUR-INSTANCE" account="YOUR-ACCOUNT-ID"]```

For tag feeds:

```[mastodon-feed instance="mastodon.social" tag="photography"]```

### Using the Gutenberg Block

The plugin includes a Gutenberg Block that provides a visual interface for embedding Mastodon feeds:

1. **Add the block**: In the WordPress block editor, click the (+) button and search for "Mastodon Feed"
2. **Account Lookup**: Use the built-in lookup tool to find your account ID:
   - Enter your full Mastodon handle (e.g., `username@mastodon.social`)
   - Click "Lookup Account ID"
   - The block will automatically configure your instance and account ID
3. **Configure settings**: Use the settings panels in the right sidebar:
   - **Account Lookup**: Convert Mastodon handles to account IDs
   - **Feed Settings**: Configure instance, account/tag, and post limit
   - **Filter Options**: Set display filters (pinned, media, exclude boosts/replies)
   - **Display Options**: Toggle preview cards, author info, and date/time display

**Note**: Date and time formatting for blocks is inherited from the admin settings page (Settings > Mastodon Feed > Display Options). This ensures consistent formatting across all feeds on your site.

### Supported shortcode attributes

| Attribute           | Default value        | Example                       | Description                                                                                    |
| ------------------- |----------------------|-------------------------------|------------------------------------------------------------------------------------------------|
| **instance**        | mastodon.social      | instance="mastodon.social"    | (required) Domain name of the instance without https://                                        |
| **account**         | —                    | account="012345678910"        | (required) Your account ID ([a long number](#how-do-i-find-my-account-id))                     |
| tag                 | —                    | tag="travel"                  | Use **tag** instead of **account** if you want to embed a tag feed instead of a personal feed  |
| limit               | 10                   | limit="10"                    | Maximum number of posts to display                                                             |
| excludeBoosts       | false                | excludeBoosts="true"          | Exclude boosted posts                                                                          |
| excludeReplies      | false                | excludeReplies="true"         | Exclude replies to other accounts                                                              |
| onlyPinned          | false                | onlyPinned="true"             | Show only pinned posts                                                                         |
| onlyMedia           | false                | onlyMedia="true"              | Show only posts containing media                                                               |
| tagged              | —                    | tagged="tagname"              | Show only posts that are tagged with given tag name (without # symbol)                         |
| linkTarget          | _blank               | linkTarget="_self"            | Target for all links (_blank, _self, _parent, _top)                                            |
| showPreviewCards    | true                 | showPreviewCards="false"      | Show preview cards from posts                                                                  |
| showPostAuthor      | true                 | showPostAuthor="false"        | Show post author information                                                                   |
| showDateTime        | true                 | showDateTime="false"          | Show date and time in status meta information                                                  |
| dateTimeFormat      | "Y-m-d h:i a"        | dateTimeFormat="F j, Y"       | PHP date format ([see documentation](https://www.php.net/manual/en/datetime.format.php))       |
| text-noPosts        | "No posts available" | text-noPosts="Empty!"         | Text displayed when no posts are available                                                     |
| text-boosted        | "boosted 🚀"         | text-boosted="🚀"             | Boosted post indicator text                                                                    |
| text-showContent    | "Show content"       | text-showContent="View"       | Text for content warning buttons                                                               |
| text-preDateTime    | "on"                 | text-preDateTime="posted on"  | Text before post permalink (date & time)                                                       |
| text-postDateTime   | "" (empty)           | text-postDateTime="UTC"       | Text after post permalink (date & time)                                                        |
| text-edited         | "(edited)"           | text-edited="✏️"              | Text indicating edited posts                                                                   |

### Additional customizations

You can define several plugin constants to set custom default options that will be applied site-wide.

1. Open your `wp-config.php` file
2. Search for the line `/* Add any custom values between this line and the "stop editing" line. */`
3. Define the options you want to override between this line and `/* That's all, stop editing! Happy publishing. */`

See [config-example.php](config-example.php) for a full list of supported settings.

## Settings

The plugin includes a comprehensive settings page accessible via **Settings > Mastodon Feed** in your WordPress admin. The settings are organized into tabs:

### General Settings
* Default Mastodon instance hostname
* Default number of posts to display
* Feed cache timeout (seconds)
* HTTP request timeout (seconds)

### Feed Filters
* Only show pinned posts
* Only show posts containing media
* Only show posts tagged with specific tag
* Exclude boosts
* Exclude replies

### Display Options
* Link target for post links (_blank, _self, _parent, _top)
* Show preview cards from posts
* Show post author
* Show date and time
* PHP date/time format

### Styling
* Post background color (with alpha transparency support)
* Post font color (with alpha transparency support)
* Accent color (for links and highlights)
* Accent font color
* Border radius

### Text Customization
* Customize all text strings used in the feed display

### Account Lookup
* Built-in tool to lookup account IDs from Mastodon handles
* Validates instance connectivity and account existence

### Cache Management
* Clear feed cache button to force immediate refresh

## Installation

The plugin is available through the official WordPress plugin directory https://wordpress.org/plugins/mastodon-feed/

1. Log into your WordPress installation
2. Go to "Plugins" and select "Add New"
3. Search for "Mastodon Feed"
4. Hit the "Install" button
5. After installation hit the "Activate" button
6. Go to **Settings > Mastodon Feed** to configure default options

### Installation via ZIP file

1. Click on the `<>Code` in the top right of this page
2. Select `Download ZIP`
3. Create a `mastodon-feed` folder in your WordPress plugins directory
4. Unpack all the files from the ZIP there (files only, no sub-directory)
5. Enable plugin in WordPress admin
6. Configure settings and use the shortcode

### Installation via git checkout

If you are familiar with GitHub you can clone the repository right into your WordPress plugins folder

1. SSH into your webserver
2. `cd /path/to/wordpress/wp-content/plugins`
3. `git clone https://github.com/mattsimpson/mastodon-feed-wordpress-plugin.git mastodon-feed`
4. Enable plugin in WordPress admin
5. Configure settings and use the shortcode

## FAQ

### How do I find my Mastodon account ID?

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

### Can I customize the appearance?
Yes! The plugin includes a comprehensive settings page under **Settings > Mastodon Feed** where you can customize:

* Post background and text colors
* Accent colors for links and highlights
* All text strings displayed in the feed
* Display options (show/hide various elements)

You can also override default values by defining constants in your `wp-config.php` file. See [config-example.php](config-example.php) for all available options.

### Can I modify the plugin?
The plugin is released under the [Expat License](LICENSE) (MIT) which is very permissive. Knock yourself out!
