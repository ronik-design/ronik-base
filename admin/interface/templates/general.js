import ContentBlock from '../components/ContentBlock.jsx';
import TopNav from '../components/MediaCleaner/TopNav.jsx';
import SyncStatus from '../components/MediaCleaner/SyncStatus.jsx';

function General() {

	return (
		<div className='general-container mediacleaner-container'>

			{/* SyncStatus manages global state independently */}
			<SyncStatus />

			<TopNav mode="dark" />

			<ContentBlock
				mode="dark"
				title="Plugin Info"
				description="Thanks for using the Media Harmony !"
			/>

			<div className="container">
				<div className="section">
					<div className="section-content">
						<p>In the future, we expect to offer a full suite of functionality; for now we have Media Harmony.</p>
						<p>For developers and content admins alike, we know how tricky it can be to manage a media library, especially for a large or aging website with multiple hands involved in asset creation and use. When not actively managed, the space used by old files quickly grows, negatively impacting site load times, performance, and internal hygiene. As soon as you have more than one person producing content, you get a messy basement, which also makes it hard for admins to locate and make use of the correct files for front-end publishing. Attempts to manually mitigate the issue by regularly reviewing, removing, or editing unlinked or improper files could take hours and is likely to result in mistakes.</p>
						<p>The solution we saw to this issue is a tool that reviews your database for any unlinked files and allows for easy removal. Our plugin is compatible with most common frameworks and does a full database scan to reveal a holistic picture of unlinked files of all types in the media library. Editors can refine their results thanks to an array of sort and filter criteria and then preserve, individually target, or bulk delete their unlinked and matching files.</p>
						<p>Most plugins out there that offer similar functionality are less reliable, lack granularity of selection, and fail to go as deep as ours to find things like nested clones of assets or corrupt files.</p>
					</div>
				</div>

				<div className="section">
					<h2>Feature List</h2>
					<div className="section-content">
						<h3>Comprehensive Media Scanning</h3>
						<ul>
							<li><strong>Full Site Scan:</strong> Analyzes all media files used across posts, pages, custom post types, widgets, and theme settings to identify unused files.</li>
							<li><strong>Cross-Reference:</strong> Compares media files in your library with those used in your content to detect and flag potentially unused media.</li>
						</ul>

						<h3>Safe Media Deletion</h3>
						<ul>
							<li><strong>Thorough Verification:</strong> Ensures that only truly unused media files are flagged for deletion.</li>
							<li><strong>Review and Backup:</strong> Allows you to review flagged files before deletion.</li>
						</ul>

						<h3>Manual and Automated Scan</h3>
						<ul>
							<li><strong>Automated Scan:</strong> Performs nightly scan of unused media files.</li>
							<li><strong>Manual Scan:</strong> Initiates a scan to allow for more frequent or immediate review.</li>
						</ul>

						<h3>Performance Optimization</h3>
						<ul>
							<li><strong>Efficient Algorithms:</strong> Utilizes optimized algorithms for scanning and deletion tasks to minimize impact on site performance.</li>
							<li><strong>Custom Throttle System:</strong> Features a throttle system to cool down your server before proceeding with heavy tasks, reducing the risk of strain or slowdowns.</li>
						</ul>

						<h3>Comprehensive File Usage Checking</h3>
						<ul>
							<li><strong>Includes Widgets and Theme Settings:</strong> Checks for media files used in widgets and theme settings, not just posts and pages, to prevent accidental deletion of important files.</li>
						</ul>

						<h3>Compatibility and Support</h3>
						<ul>
							<li><strong>Standard Compatibility:</strong> Designed to work with standard WordPress media libraries.</li>
							<li><strong>Support and Issue Reporting:</strong> Team contact information available for support and issue reporting, in addition to extensive built-in error-logging.</li>
						</ul>
					</div>
				</div>


				<div className="section">
					<h2>Plugin Usage</h2>
					<div className="usage">
						<h3>1. Accessing Media Harmony</h3>
						<ul>
							<li><strong>In WordPress Admin Dashboard:</strong> After activation, you’ll find Media Harmony listed under the Settings menu or as a separate menu item in your WordPress Admin Dashboard.</li>
						</ul>

						<h3>2. Performing Media Scan</h3>
						<ul>
							<li><strong>Initiate Manual Scan:</strong> In addition to the automated scans, scan your site at any time using the Initiate Scan options in the plugin or WP-Admin bar.</li>
							<li><strong>Reviewing Results:</strong> Once the scan is complete, review the unlinked files found and individually delete, bulk delete, and preserve them.</li>
						</ul>

						<h3>3. Checking Performance</h3>
						<ul>
							<li><strong>Monitor Plugin Impact:</strong> Media Harmony includes a custom throttle system designed to minimize performance impact. For optimal performance, consider running the plugin during off-peak hours.</li>
						</ul>

						<h3>4. Troubleshooting and Support</h3>
						<ul>
							<li><strong>Consult the FAQ:</strong> For common questions and troubleshooting tips, refer to the FAQ or Support sections of the plugin.</li>
							<li><strong>Contact Support:</strong> If you encounter issues or need assistance, contact our support team via the support email.</li>
						</ul>
					</div>
				</div>
			</div>





		</div>
	);
}
export default General;

