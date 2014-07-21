=== WP-Portability ===
Contributors: frasmage
Author: Plank Design Inc.
Tags: portability, installation, mobility, url, shortcode, portable, domain-agnostic, relocation
Requires at least: 3.1
Tested up to: 3.9
Stable tag: 1.0
License: GPLv2 or later

Polylang adds multilingual content management support to WordPress.

== Description ==

= Summary =

WP-Portability is a plugin designed to quietly make your site more portable. It is to designed to facilitate the work that needs to be done when and if a website needs to be moved to another directory or server or if the domain name changes. This plugin does not assist with the actual relocation of the WordPress. Instead, it makes WordPress much less attached and dependant on its install directory and domain name making the process much easier to do.

Though anyone can make use of WP-Portability, it was designed with developers in mind. It greatly facilitates designing sites locally amongst a team of developers, moving the site to a staging server and finally pushing it live.

= Features =

WP-Portability provides the following optimizations:

* **Always local URLs**: Perhaps the biggest issues with relocating a wordpress install is the fact that every url pointing to other content in the same site (links to other pages, uploaded media, etc.) in the content of posts will break. This can be a nightmare to fix for larger sites. WP-Portability solves this by storing all local urls as a hidden shortcode in the database, which it can then expand to the correct path for whatever domain/install directory you view the site from. You will not see anything differently when you edit your posts, but rest assured that things are being handled dynamically in the background.
* **Dynamic file paths**: This plugin will automatically determine the url of your WordPress install path. This will set the `WP_SITEURL` and `WP_HOME` constants. If these are already set in wp-config, this feature will not work. This feature will automatically disable itself on WordPress Multisite installations (network), as it will not work properly.
* **Relative Rewrites**: This plugin changes the way WordPress creates apache-level rewrites, to always use relative paths, rather than absolute paths. This will enable access to the same installation via different routes (e.g. `http://domain.com` and `http://127.0.0.1/path/to/install`). This feature will automatically disable itself on WordPress Multisite installations (network), as it will not work properly.

== Installation ==

1. Make sure you are using WordPress 3.1 or later and that your server is running PHP5
1. Download the plugin
1. Extract all the files.
1. Upload everything to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in the WordPress admin.
1. Go to the portability settings page in the WordPress admin and adjust your settings.
1. (optional) You can force the plugin to parse every post in your site for local links by pressing the Insert Shortcode button. This may take a few minutes.
1. That's it. WP-Portability works silently.

== Deactivation / Uninstallation ==

Unless you intend to reactivate the plugin shortly, you will need to clear all of the hidden shortcodes out of your database.

1. Go to the portability settings page in the WordPress Admin
1. Press the 'Purge Shortcode' button. This may take a few minutes.
1. Go to the Plugins page.
1. Press the deactivate and/or delete plugin button.


== Frequently Asked Questions ==

= Can I use that mysterious shortcode myself? =

Yes! The shortcode introduced by this plugin can absolutely be used to help you write out local links. The only caveat is that it will automatically be rendered back to you whenever you save the post (save draft, publish or update). The syntax is the following:

    [url {location}]

Where `{location}` is a part of the site to link to. Possible values include:

* site : Will generate a url for the root installation directory
* uploads: Will generate a url for the media upload directory
* plugins: Will generate a url for the plugins directory
* theme: Will generate a url for the main theme directory
* stylesheet: Will generate a url for the child theme directory (if any). Otherwise, this is identical to theme.

You can then append your more specific path to the shortcode. For example:

    <img src="[url uploads]/2014/07/picture.jpg" alt="A vacation picture">

    <!-- Will evaluate to this: -->
    <img src="http://domain.com/wp-content/uploads/2014/07/picture.jpg" alt="A vacation picture">

= Why don't all features work with a WordPress MultiSite install? =

WordPress Multisite (network) installs greatly change the way wordpress structures itself. As such, the fixes that this plugin introduces may conflict with this configuration. Until further testing and development can be done, we have disabled the problematic features automatically for the time being. This may be fixed in a future release.


= I deactivated/uninstalled the plugin and now my links don't work and/or I see [url site] littered throughout my post. How do I fix this? ==

If you intend to get rid of this plugin entirely (we are sorry to see you go!), then you should let the plugin purge its shortcodes from your database first. Before you deactivate or uninstall the plugin, Go to the portability settings page in the WordPress admin and press the Purge Shortcode button. This will safely convert all local link shortcodes in your database back to absolute links.

