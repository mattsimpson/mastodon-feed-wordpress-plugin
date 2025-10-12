# Languages Directory

This directory contains translation files for the Mastodon Feed plugin.

## Translation Files

- `mastodon-feed.pot` - Translation template (POT file)
- `mastodon-feed-{locale}.po` - Translation files for specific languages
- `mastodon-feed-{locale}.mo` - Compiled translation files

## Generating POT File

To regenerate the POT file with all translatable strings from the plugin:

### Using WP-CLI (Recommended)
```bash
wp i18n make-pot . languages/mastodon-feed.pot --domain=mastodon-feed
```

### Using Poedit
1. Open Poedit
2. Create new translation from POT file
3. Extract from sources: Browse to plugin directory
4. Keywords: `__`, `_e`, `_n`, `_x`, `_ex`, `_nx`, `esc_attr__`, `esc_attr_e`, `esc_html__`, `esc_html_e`

## Contributing Translations

To contribute a translation:

1. Copy `mastodon-feed.pot` to `mastodon-feed-{locale}.po` (e.g., `mastodon-feed-fr_FR.po`)
2. Translate strings using Poedit or a text editor
3. Compile to `.mo` file
4. Submit via GitHub pull request

## Language Codes

Common locale codes:
- `en_US` - English (United States)
- `fr_FR` - French (France)
- `de_DE` - German (Germany)
- `es_ES` - Spanish (Spain)
- `it_IT` - Italian (Italy)
- `pt_BR` - Portuguese (Brazil)
- `ja` - Japanese
- `zh_CN` - Chinese (Simplified)

## WordPress.org Translations

Once submitted to WordPress.org, translations can also be contributed via:
https://translate.wordpress.org/projects/wp-plugins/mastodon-feed
