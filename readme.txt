=== Draft from Google Docs ===
Contributors: davidfcarr
Donate: http://www.rsvpmaker.com
Tags: google docs, import, utility
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.6
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 1.1

Simplify the process of bringing Google Docs content into WordPress.

== Description ==

If you use Google Docs to edit and revise content with a team of people, you can now create a draft blog post or page in WordPress without cluttering your web content with inappropriate HTML and inline styles. The tool extracts images and allows you to download them or import them directly into WordPress.

This simplifies what would otherwise be a multistep process of

* Either pasting into the rich text editor or cleaning up the HTML or pasting into code view and recreating styling
* Downloading images from Google Docs, one at a time
* Uploading images into WordPress

Originally created for use by the [Insights Newsroom team at Similarweb](https://www.similarweb.com/corp/blog/insights/), this plugin allows editorial teams to collaborate on the writing and editing of a document, including image placement, in Google Docs, quickly turn that content into a draft blog post, and then tweak as necessary in the blog editor.

Works with either the block editor or the classic editor.

For the block editor, basic block metadata is added for paragraphs, headings, unordered lists, and images. Any other content will be displayed as classic blocks, with the option to use the Convert to Blocks function of the editor.

The shortcode [draft_from_google_docs] is available for including this functionality on a public web page. In that case, the content is not imported directly into WordPress but displayed in a format that allows it to be copied and pasted into the WordPress editor. In that case, images associated with the document are displayed in a format that allows you to download them into your computer and upload them one by one into your post or page. Including the download_ok="1" attribute within the shortcode means the images will be downloaded into the local WordPress instance. Otherwise, the download links will point to the Google Cloud url for those images.

[youtube https://youtu.be/E_eetgU0GJ0]

[__Plugin Page www.rsvpmaker.com/draft-from-google-docs/__](http://www.rsvpmaker.com/draft-from-google-docs/)

Thank you to Miroslav Mitev for a [code sample](https://gist.github.com/m1r0/f22d5237ee93bcccb0d9) on how to download from a url to create WordPress attachments.

Butterfly image Image by [JamesDeMers](https://pixabay.com/users/jamesdemers-3416/?utm_source=link-attribution&amp;utm_medium=referral&amp;utm_campaign=image&amp;utm_content=55995) from [Pixabay](https://pixabay.com/?utm_source=link-attribution&amp;utm_medium=referral&amp;utm_campaign=image&amp;utm_content=55995)

== Installation ==

1. Upload the entire folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Credits ==

    RSVPMaker
    Copyright (C) 2022 David F. Carr

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    See the GNU General Public License at <http://www.gnu.org/licenses/gpl-2.0.html>.
	
== Changelog ==

= 1.1 =

* Fix for image import, accounting for images coming through as base64 instead of urls

= 1.0.9 =

* Use keywords entered by user as basis for url slug as well as image file names

= 1.0.8 =

* Set a category for post drafts. Choice is saved as a user preference for next time.

= 1.0.7 =

* Improved method for extracting document title

= 1.0.6 =

* Fix for issue where image is wrapped in a heading tag rather than p or figure
* Option to display Google HTML for debugging

= 1.0.5 =

* Default image size now 600px by 600px

= 1.0.3 =

* Checks if Classic Editor is active. If not, adds block metadata for paragraph, heading, and images.
* Improved method for stripping unwanted tags on attributes, correcting for the way Google Docs handles unordered lists (bullets)
* Better handling of image sizes. Will use "large" size set in WordPress Media screen under Settings as size to include in copy.

= 1.0.2 =

* Additional HTML cleanup functions
* Option to add _blank attribute (open in new tab) to external links 

= 1.0 =

* Initial release
