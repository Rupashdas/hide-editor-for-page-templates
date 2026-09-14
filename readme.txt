=== Hide Editor for Page Templates ===
Contributors: rupash
Tags: Hide editor, Remove Editor, Remove post support, page templates, user roles
Requires at least: 4.7
Tested up to: 6.6
Requires PHP: 5.6
Stable tag: 1.1.0
License: GPL v2
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

A simple and nice plugin to hide classic editor or gutenberg editor from specific page templates, optionally restricted to selected user roles.

== Description ==
A simple and nice plugin to hide classic editor or gutenberg from pre selected page templates. If the installation is okay, go to the settings > hide editor. If your current theme has pre-made page templates, you can select page templates where you want to remove/hide editor. You can also restrict the hiding to specific user roles, so other roles still see the editor. It's very easy to do.

== Installation ==
Installation is fairly straight forward. Install it from the WordPress plugin repository.

== Frequently Asked Questions ==

Q: Can I hide the editor only for certain user roles?
A: Yes. On the Hide Editor settings page, use the "Restrict to User Roles" field to select which roles the editor should be hidden for. Leave it empty to hide the editor for everyone, as before.

== Changelog ==
= 1.1.0 =
* Add: restrict editor hiding to selected user roles, leave empty to apply to everyone.
* Fix: settings page assets could be queued with an unchecked `$_GET['page']` value.
* Fix: script cache-busting used `time()`, forcing a fresh download of plugin JS on every page load.
* Fix: removed the discouraged `load_plugin_textdomain()` call and the `Domain Path` header pointing at a nonexistent `languages/` folder - WordPress.org has auto-loaded plugin translations since 4.6.
* Fix: post ID from the URL is now read with `absint()` instead of a `sanitize_text_field()`/`esc_html()` combination meant for output escaping, not input handling.
* Housekeeping: plugin options are now removed on uninstall.

= 1.0.0 =
* Initial Release