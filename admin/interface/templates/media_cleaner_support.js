import ContentBlock from '../components/ContentBlock.jsx';
import FAQ from '../components/Faq.jsx';


function Support() {
	let faqItems = [
		["What does [Plugin Name] do?", "[Plugin Name] is a WordPress plugin designed to help you identify and remove unused media files from your WordPress site. It ensures that only media files that are no longer used in posts, pages, or other content are safely deleted, helping to reduce your media library's size and improve site performance."],
		["How does [Plugin Name] determine which media files are unused?", "The plugin scans your entire WordPress site, including posts, pages, and custom post types, to check for media usage. It cross-references the media files in your library with those used in your content. Any media files not found in your content are flagged as potentially unused."],
		["Is it safe to use [Plugin Name] to delete media files?", "Yes, [Plugin Name] is designed with safety in mind. The plugin performs a thorough check to ensure that only truly unused media files are flagged for deletion. Before any files are permanently deleted, you have the option to review them and perform a backup to ensure that you can restore any files if needed."],
		["Can I recover deleted media files?", "Once media files are deleted using [Your Plugin Name], they are permanently removed from your server and cannot be recovered through the plugin. However, if you have created a backup of your media files before deletion, you can restore them from the backup."],
		["Will [Plugin Name] delete media files that are used in widgets or theme settings?", "Yes, [Plugin Name] checks for media files used not only in posts, pages, and custom post types but also in widgets and theme settings. The plugin performs a comprehensive scan of your site, including these areas, to ensure that no important media files are inadvertently deleted. We still recommend reviewing your media library and widget/theme settings periodically to confirm that all necessary files are accounted for."],
		["How often should I run [Plugin Name] to clean up unused media?", '[Plugin Name] automatically performs a media cleanup every night at midnight through its built-in cron job. However, you can also manually trigger a cleanup at any time using the "Force Sync" option available in the WP-Admin bar. For most sites, the nightly automatic cleanup should be sufficient. If you prefer more frequent checks, or if you’ve made significant changes to your media library, you can use the "Force Sync" feature to ensure your media library remains organized and free of unused files.'],
		["Does [Plugin Name] have any performance impact on my site?", "[Plugin Name] is designed with performance in mind, utilizing efficient algorithms and a custom throttle system to minimize impact on your site. The plugin scans and deletes unused media in a way that allows your server to cool down before proceeding, helping to prevent any significant strain on site performance. While the plugin generally operates smoothly, a large media library might cause occasional slowdowns. For optimal performance, we recommend running the plugin during off-peak hours. If you experience any rare performance issues, the throttle system is in place to mitigate these effects and ensure your site remains responsive."],
		["Is [Plugin Name] compatible with other media management plugins?", "[Plugin Name] is designed to work with standard WordPress media libraries. If you use other media management plugins, ensure they do not alter how media files are referenced or stored in the database. Compatibility issues are rare, but if you encounter any, please reach out to our support team for assistance."],
		["Where can I get support or report issues with [Plugin Name]?", "If you need support or wish to report issues, please visit our support page at [support URL] or contact us directly at [support email]. Our team is here to assist you with any questions or concerns you may have."]
	];

	return (
		<div className='support-container'>
			{/* <ContentBlock
				title= "Support Message"
				description= "If you have any questions, need a hand with a technical issue, or just want to say hi, we've got you covered. Get in touch with us and we'll be happy to assist you!"
			/>
			<br></br> */}
			<ContentBlock
				title= "Frequently asked questions"
				description= "Here, you'll find answers to commonly asked questions about using Ronik Plugin. If you don't see your question listed, you can have a look at the section below."
			/>
			<FAQ
				items= {faqItems}
			/>
			<br></br>
			<ContentBlock
				title= "Contact our team:"
				description= "Need more help? Drop us a line at <a href='mailto:dev@ronikdesign.com'>dev@ronikdesign.com</a>! <br>Want to report an issue? Send us a note describing your issue here: [Google Form link]"
			/>
		</div>
	);
}
export default Support;

