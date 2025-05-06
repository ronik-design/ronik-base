import ContentBlock from '../components/ContentBlock.jsx';
import TriggerAjaxRequest from '../components/MediaCleaner/TriggerAjaxRequest.jsx';
import MediaCollector from '../components/MediaCleaner/MediaCollector.jsx';

function Mediacleaner() {

	return (
		<div className='mediacleaner-container'>
			<ContentBlock
				title="Preserved Media Library"
				description={`
					Unlinked files selected for preservation in the Dashboard will be exempted from bulk deletion and appear in the Preserved Media Library instead of the Dashboard. 
					<br><br>Remove a file from the Preserved Media Library to add it to the Dashboard, where it can be deleted. 
					<br><br>If no files appear below, no unlinked files have been added to the Preserved Media Library. 
					`}
			/>
			<br></br>

			<MediaCollector type="preserved" />
		</div>
	);
}
export default Mediacleaner;

