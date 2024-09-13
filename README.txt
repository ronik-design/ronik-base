=== Plugin Name ===
Contributors: (this should be a list of wordpress.org userid's)
Donate link: https://www.ronikdesign.com/
Tags: comments, spam
Requires at least: 6.6.0
Tested up to: 6.6.2
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Clean out unused media -- your website will thank you! This plugin uses Advanced Custom Fields to run. 

== Description ==

Feature List

Comprehensive Media Scanning
*   Full Site Scan: Analyzes all media files used across posts, pages, custom post types, widgets, and theme settings to identify unused files.
*   Cross-Reference: Compares media files in your library with those used in your content to detect and flag potentially unused media.
Safe Media Deletion
*   Thorough Verification: Ensures that only truly unused media files are flagged for deletion.
*   Review and Backup: Allows you to review flagged files before deletion.
Manual and Automated Cleanup
*   Automatic Cron Job: Performs nightly cleanup of unused media files at midnight.
*   Force Sync Option: Provides a manual "Force Sync" option in the WP-Admin bar to trigger a cleanup at any time, allowing for more frequent checks as needed.
Performance Optimization
*   Efficient Algorithms: Utilizes optimized algorithms for scanning and deletion tasks to minimize impact on site performance.
*   Custom Throttle System: Features a throttle system to cool down your server before proceeding with heavy tasks, reducing the risk of significant strain or slowdowns.
*   Recommended Scheduling: Suggests running the plugin during off-peak hours for optimal performance.
Comprehensive File Usage Checking
*   Includes Widgets and Theme Settings: Checks for media files used in widgets and theme settings, not just posts and pages, to prevent accidental deletion of important files.
Compatibility and Support
*   Standard Compatibility: Designed to work with standard WordPress media libraries; compatibility with other media management plugins should be verified.
Support and Issue Reporting: Provides support through a dedicated support page and email for assistance and issue resolution.



Plugin Usage Instructions
1. Accessing
*   In WordPress Admin Dashboard:
*   *   After activation, you’ll find [Plugin Name] listed under the Settings menu or as a separate menu item in your WordPress Admin Dashboard.
2. Performing Media CleanupAutomatic Cleanup:
*   [Plugin Name] automatically performs a media cleanup every night at midnight via its built-in cron job. You do not need to take any action for this scheduled task.
Manual Cleanup:
*   Triggering Manual Sync:
*   *   To perform a cleanup at any time, use the “Force Sync” option available in the WP-Admin bar. Simply click the “Force Sync” button to initiate a media scan and cleanup process manually.
*   Reviewing Results:
*   *   After a cleanup, check the plugin’s results to review which files were flagged and ensure that no important media was inadvertently removed.
3. Managing Deleted Media
*   Backup and Recovery:
*   *   [Plugin Name] allows you to back up your media files before deletion. Ensure that you have created a backup of your media files before initiating cleanup to restore any files if needed.
4. Checking Performance
*   Monitor Plugin Impact:
*   *   [Plugin Name] includes a custom throttle system designed to minimize performance impact. For optimal performance, consider running the plugin during off-peak hours.
5. Troubleshooting and Support
*   Consult the FAQ:
*   *   For common questions and troubleshooting tips, refer to the FAQ section of the plugin or the official support page.
Contact Support:
*   If you encounter issues or need assistance, contact our support team via the support email or visit our support page.




== Installation ==

*   From WordPress Repository: Go to the WordPress Plugin Directory and search for “[Plugin Name]”. Click on the “Download” button to obtain the plugin ZIP file.
*   From Website: Download the plugin ZIP file directly from the official website if available.
*   Install the PluginUsing WordPress Admin Dashboard:
*   Log in to your WordPress Admin Dashboard.
*   Navigate to the Plugins Menu:

*   Download the Plugin
*   *   From WordPress Repository: Go to the WordPress Plugin Directory and search for “[Plugin Name]”. Click on the “Download” button to obtain the plugin ZIP file.
*   *   From Website: Download the plugin ZIP file directly from the official website if available.
*   Install the PluginUsing WordPress Admin Dashboard:
*   *   Log in to your WordPress Admin Dashboard.
*   *   Navigate to the Plugins Menu:
*   *   Go to Plugins > Add New.

Upload the Plugin:
*   Click on the “Upload Plugin” button at the top of the page.
*   Click “Choose File” and select the downloaded [Plugin Name] ZIP file.
*   Click “Install Now” to upload and install the plugin.
Activate the Plugin:
*   Once installed, click the “Activate Plugin” link to enable [Plugin Name] on your site.
*   Using FTP: Extract the ZIP File. Extract the contents of the plugin ZIP file on your local computer and upload to server. 
*   Connect to your website server using an FTP client (e.g., FileZilla).
*   Navigate to wp-content/plugins/ directory on your server.
*   Upload the extracted [Plugin Name] folder to this directory.
Activate the Plugin:
*   Log in to your WordPress Admin Dashboard.
*   Go to Plugins > Installed Plugins.
*   Find [Plugin Name] in the list and click “Activate”.


== Frequently Asked Questions ==

= What does Media Harmony do? = 

Media Harmony is a WordPress plugin designed to help you identify and remove unused media files from your WordPress site. It ensures that only media files that are no longer used in posts, pages, or other content are safely deleted, helping to reduce your media library's size and improve site performance.

= How does Media Harmony determine which media files are unused? = 

The plugin scans your entire WordPress site, including posts, pages, and custom post types, to check for media usage. It cross-references the media files in your library with those used in your content. Any media files not found in your content are flagged as potentially unused.

= Is it safe to use Media Harmony to delete media files? = 

Yes, Media Harmony is developed with safety in mind. The plugin performs a thorough check to ensure that only truly unused media files are flagged for deletion. Before any files are permanently deleted, you have the option to review them and are encouraged to perform a backup to ensure that you can restore any files if needed.

= Can I recover deleted media files? = 

Once media files are deleted using Media Harmony, they are permanently removed from your server and cannot be recovered through the plugin. However, if you have created a backup of your media files before deletion, you can restore them from the backup.

= Will Media Harmony delete media files that are used in widgets or theme settings? = 

Media Harmony checks for media files used not only in posts, pages, and custom post types but also in widgets and theme settings, but only unlinked files we be loaded for deletion. The plugin performs a comprehensive scan of your site, including these areas, to ensure that no important media files are inadvertently deleted. We still recommend reviewing your media library and widget/theme settings periodically to confirm that all necessary files are accounted for.

= How often should I run Media Harmony to scan for unused media? = 

Media Harmony automatically performs a media library scan every 24 hours for you to review your files. You can also manually initiate a scan at any time. For most sites, the nightly automatic cleanup is plenty.

= Does Media Harmony have any performance impact on my site? = 

One of the key benefits to using Media Harmony is that it improves site performance, utilizing efficient algorithms and a custom throttle system to minimize impact on your site when it works. The plugin scans and deletes unused media in a way that allows your server to cool down before proceeding, preventing any significant strain on site performance. Scans of larger media libraries may more time. For optimal performance, we recommend running the plugin during low-usage periods. 

= Contact our team: = 

Need more help? Drop us a line at dev@ronikdesign.com!
Want to report an issue? Send us a note describing your issue here: [Google Form link]


== Changelog ==

= 1.0 =
* A change since the previous version.
* Another change.

= 0.5 =
* List versions from most recent at top to oldest at bottom.

== Upgrade Notice ==

= 1.0 =
Upgrade notices describe the reason a user should upgrade.  No more than 300 characters.

= 0.5 =
This version fixes a security related bug.  Upgrade immediately.

== Arbitrary section ==

You may provide arbitrary sections, in the same format as the ones above.  This may be of use for extremely complicated
plugins where more information needs to be conveyed that doesn't fit into the categories of "description" or
"installation."  Arbitrary sections will be shown below the built-in sections outlined above.

== A brief Markdown Example ==

Ordered list:

1. Some feature
1. Another feature
1. Something else about the plugin

Unordered list:

* something
* something else
* third thing

Here's a link to [WordPress](http://wordpress.org/ "Your favorite software") and one to [Markdown's Syntax Documentation][markdown syntax].
Titles are optional, naturally.

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"

Markdown uses email style notation for blockquotes and I've been told:
> Asterisks for *emphasis*. Double it up  for **strong**.

`<?php code(); // goes in backticks ?>`