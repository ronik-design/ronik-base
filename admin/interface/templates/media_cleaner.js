import ContentBlock from '../components/ContentBlock.jsx';
import TriggerAjaxRequest from '../components/MediaCleaner/TriggerAjaxRequest.jsx';
import MediaCollector from '../components/MediaCleaner/MediaCollector.jsx';

function Mediacleaner() {

	return (
		<div className='mediacleaner-container'>
			<ContentBlock
				title= "Welcome to Media Harmony!"
				description= "Media Harmony will scan your media library for all unlinked JPG, PNG, and GIF files. <br>The total size of your library will determine the time required to scan. <br><br> Use the toggle to initiate a scan of your media library or to permanently delete all unlinked, unpreserved files. Change your file size threshold for the scan in the Settings tab. Use the search bar to filter for title keywords and sort files by size below.  <br><br>Review scanned files and individually delete files or preserve files to exclude them from bulk deletion. Use the Bulk Delete Media button to delete all unlinked media listed below that you have not selected for preservation. <br>Use the Bulk Delete Media button to delete all unlinked media listed below that you have not selected for preservation. "
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

