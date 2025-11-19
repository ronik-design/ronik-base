import FAQ from '../components/Faq.jsx';
import ContentBlock from '../components/ContentBlock.jsx';
import TopNav from '../components/MediaCleaner/TopNav.jsx';

function Support() {
	let faqItems = [
		["What does Media Harmony do?", "Media Harmony is a WordPress plugin designed to help you identify and remove unused media files from your WordPress site. It ensures that only media files that are no longer used in posts, pages, or other content are safely deleted, helping to reduce your media library's size and improve site performance."],
		["How does Media Harmony determine which media files are unused?", "The plugin scans your entire WordPress site, including posts, pages, and custom post types, to check for media usage. It cross-references the media files in your library with those used in your content. Any media files not found in your content are flagged as potentially unused."],
		["Is it safe to use Media Harmony to delete media files?", "Yes, Media Harmony is developed with safety in mind. The plugin performs a thorough check to ensure that only truly unused media files are flagged for deletion. Before any files are permanently deleted, you have the option to review them and are encouraged to perform a backup to ensure that you can restore any files if needed."],
		["Can I recover deleted media files?", "Once media files are deleted using Media Harmony, they are permanently removed from your server and cannot be recovered through the plugin. However, if you have created a backup of your media files before deletion, you can restore them from the backup."],
		["Will Media Harmony delete media files that are used in widgets or theme settings?", "Media Harmony checks for media files used not only in posts, pages, and custom post types but also in widgets and theme settings, but only unlinked files we be loaded for deletion. The plugin performs a comprehensive scan of your site, including these areas, to ensure that no important media files are inadvertently deleted. We still recommend reviewing your media library and widget/theme settings periodically to confirm that all necessary files are accounted for."],
		["How often should I run Media Harmony to scan for unused media?", 'Media Harmony automatically performs a media library scan every 24 hours for you to review your files. You can also manually initiate a scan at any time. For most sites, the nightly automatic cleanup is plenty.'],
		["Does Media Harmony have any performance impact on my site?", "One of the key benefits to using Media Harmony is that it improves site performance, utilizing efficient algorithms and a custom throttle system to minimize impact on your site when it works. The plugin scans and deletes unused media in a way that allows your server to cool down before proceeding, preventing any significant strain on site performance. Scans of larger media libraries may more time. For optimal performance, we recommend running the plugin during low-usage periods. "]
	];

	return (
		<div className='support-container'>


			<TopNav mode="dark" />


			<ContentBlock
				mode="dark"
				title="Frequently asked questions"
				description="Here, you'll find answers to commonly asked questions about using Media Harmony Plugin. If you don't see your question listed, you can have a look at the section below."
			/>

			<div className='support-container__article'>
				<FAQ
					items={faqItems}
				/>
				<ContentBlock
					mode="dark"
					className='support-container__article__content-block'
					title="Contact our team:"
					description="
					Need more help? Drop us a line at <a href='mailto:dev@ronikdesign.com'>dev@ronikdesign.com</a>! 
					<br><br>
					<a class='button-rmc' href='mailto:dev@ronikdesign.com'>Email Us</a>
					<br><br>
					Want to report an issue? Send us a note describing your issue <a target='_blank' href='https://forms.gle/qhBq6qi22BWE7cRA8'>here</a> 
					<br><br>
					<a class='button-rmc' href='https://forms.gle/qhBq6qi22BWE7cRA8'>Report an Issue</a>
				
					"
				/>
			</div>
		</div>
	);
}
export default Support;

