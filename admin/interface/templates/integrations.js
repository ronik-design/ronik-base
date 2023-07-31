import ContentBlock from '../components/ContentBlock.jsx';
import FetchAddon from '../components/FetchAddon.jsx';

function Integrations() {
	return (
		<div className='integrations-container'>
			<ContentBlock
				title= "Integrations Message"
				description= "Ronik Base can integrate with other products, to help you further improve your website. You can enable or disable these integrations below."
			/>
			<br></br>
			<ContentBlock
				title= "Recommended integrations"
				description= "Unlock rich results in Google search by using plugins that integrate with the Yoast Schema API."
			/>

			<div className="tile-block">
				<div className="tile-block__inner">
					<FetchAddon 
						pluginName= "Ronik Media Cleaner"
						pluginSlug= "ronik_media_cleaner"
						title= "Speed Up your website"
						description= "Media Cleaner is a highly effective plugin that aids in the organization and maintenance of your WordPress media library. It accomplishes this by removing unused media entries and files, while also repairing any broken entries present. <br> <br>To unlock updates, please enter your license key below. If you don't have a licence key, please see details & pricing.						"
						linkName= "details & pricing"
						linkHref= "https://together.nbcudev.local"
					/>

					<FetchAddon 
						pluginName= "Ronik Optimization"
						pluginSlug= "ronik_optimization"
						title= "Speed Up your website"
						description= "Optimization is a highly effective plugin that aids in the organization and maintenance of your WordPress media library. It accomplishes this by removing unused media entries and files, while also repairing any broken entries present. <br> <br>To unlock updates, please enter your license key below. If you don't have a licence key, please see details & pricing.						"
						linkName= "details & pricing"
						linkHref= "https://together.nbcudev.local"
					/>
				</div>
			</div>

		</div>
	);
}
export default Integrations;

