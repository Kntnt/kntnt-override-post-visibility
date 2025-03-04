# Kntnt Override Post Visibility

WordPress plugin that allows making pending, scheduled, or private posts visible to anyone with the URL, with options for automatic notices and noindex meta tags.

* **Contributors:** TBarregren
* **Tags:** visibility, post status, pending, scheduled, private
* **Requires at least:** 6.7
* **Tested up to:** 6.7
* **Requires PHP:** 8.3
* **Stable tag:** 1.1.0
* **License:** GPL-3.0-or-later
* **License URI:** http://www.gnu.org/licenses/gpl-3.0.txt

## Description

This plugin allows editors to make posts with pending, scheduled, or private status visible to anyone who has the direct URL. This is particularly useful for pre-publishing review, sharing scheduled content with stakeholders before the official publication date, or maintaining accessible archived content with private status.

Key features:

* **Visibility Override:** Make pending, scheduled, or private posts accessible through direct URLs
* **Status Notices:** Optional automatic visitor notifications with status-specific styling:
    * Yellow alert for pending posts
    * Blue alert for scheduled posts with publication date/time
    * Red alert for private posts
* **SEO Protection:** Optional noindex meta tag to prevent search engines from indexing unpublished content
* **Easy Management:** Controls available in the Block Editor, Quick Edit, and Bulk Edit interfaces
* **Dark Mode Support:** Automatic adaptation to user's preferred color scheme

The plugin is designed to seamlessly integrate with WordPress workflows while providing flexibility for content teams who need to share unpublished content.

## Installation

1. [Download the plugin zip archive.](https://github.com/Kntnt/kntnt-override-post-visibility/releases/latest/download/kntnt-override-post-visibility.zip)
2. Go to WordPress admin panel → Plugins → Add New.
3. Click "Upload Plugin" and select the downloaded zip archive.
4. Activate the plugin.

## Usage

### In Block Editor

1. Edit a post with pending, scheduled, or private status.
2. Find the "Visibility Override" panel in the Document sidebar (under the Post tab).
3. Check "Override visibility" to make the post accessible via direct URL.
4. Optionally, check "Add notice" to display a status message to visitors.
5. Optionally, check "Add noindex meta tag" to prevent search engine indexing.

### In Quick Edit

1. Hover over a pending, scheduled, or private post in the Posts list.
2. Click "Quick Edit".
3. Find the "Override visibility" dropdown.
4. Select your preferred option:
    - No
    - Yes, with automatic visitor alert and noindex meta tag
    - Yes, with noindex meta tag
    - Yes, with automatic visitor alert
    - Yes, without extras

### In Bulk Edit

1. Select multiple pending, scheduled, or private posts in the Posts list.
2. Click "Bulk Edit".
3. Find the "Override visibility" dropdown.
4. Select your preferred option and update the posts.

## Frequently Asked Questions

### Will this make my posts appear in archives, feeds, or search results?

No. The plugin only makes posts accessible via direct URL. The posts will not appear in archives, feeds, or search results.

### Can I override visibility for any post type?

Yes, the plugin works with all public post types by default.

### How can I get help?

If you have questions about the plugin and cannot find an answer here, start by looking at issues and pull requests on our GitHub repository. If you still cannot find the answer, feel free to ask in the plugin's issue tracker on GitHub.

### How can I report a bug?

If you have found a potential bug, please report it on the plugin's issue tracker on GitHub.

### How can I contribute?

Contributions to the code or documentation are much appreciated.

If you are familiar with Git, please do a pull request.

If you are not familiar with Git, please create a new ticket on the plugin's issue tracker on GitHub.