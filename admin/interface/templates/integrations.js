import ContentBlock from '../components/ContentBlock.jsx';
import TopNav from '../components/MediaCleaner/TopNav.jsx';
import SyncStatus from '../components/MediaCleaner/SyncStatus.jsx';
import FetchAddon from '../components/PluginBase/FetchApiAddon.jsx';

function Integrations() {
	return (
		<div className='general-container mediacleaner-container integrations-container'>
			
			{/* SyncStatus manages global state independently */}
			<SyncStatus />

			<TopNav mode="dark" />

			<ContentBlock
				mode="dark"
				title="Integrations"
				description="Media Harmony can integrate with other products to help you further improve your website. You can enable or disable these integrations below."
			/>

			<div className="container">
				<div className="section">
					<h2>Available Integrations</h2>
					<div className="section-content">
						<div className="tile-block">
							<div className="tile-block__inner">
								<FetchAddon 
									pluginName="Media Harmony Cleaner"
									pluginSlug="ronik_media_cleaner"
									title="Speed Up your website"
									description="Media Cleaner is a highly effective plugin that aids in the organization and maintenance of your WordPress media library. It accomplishes this by removing unused media entries and files, while also repairing any broken entries present.<br><br>To unlock updates, please enter your license key below. If you don't have a licence key, please see details & pricing."
									linkName="details & pricing"
									linkHref="https://together.nbcudev.local"
								/>

								<FetchAddon 
									pluginName="Media Harmony Optimization"
									pluginSlug="ronik_optimization"
									title="Speed Up your website"
									description="Optimization is a highly effective plugin that aids in the organization and maintenance of your WordPress media library. It accomplishes this by removing unused media entries and files, while also repairing any broken entries present.<br><br>To unlock updates, please enter your license key below. If you don't have a licence key, please see details & pricing."
									linkName="details & pricing"
									linkHref="https://together.nbcudev.local"
								/>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	);
}
export default Integrations;

