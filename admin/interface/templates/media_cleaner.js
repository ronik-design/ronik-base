import ContentBlock from '../components/ContentBlock.jsx';
import TriggerAjaxRequest from '../components/MediaCleaner/TriggerAjaxRequest.jsx';
import MediaCollector from '../components/MediaCleaner/MediaCollector.jsx';

function Mediacleaner() {

	return (
		<div className='mediacleaner-container'>
			<ContentBlock
				title= "Welcome to Media Harmony!"
				description= "Media Harmony will scan your media library for all unlinked JPG, PNG, and GIF files. The total size of your library will determine the time required to scan. Change your file size threshold in the Settings tab. Sort, filter, and delete or preserve your files in the list below."
			/>
			<br></br>
            <TriggerAjaxRequest 
                requestType= "rmc_ajax_media_cleaner"
            />  
            <MediaCollector />
		</div>
	);
}
export default Mediacleaner;

