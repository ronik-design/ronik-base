import ContentBlock from '../components/ContentBlock.jsx';
import TriggerAjaxRequest from '../components/TriggerAjaxRequest.jsx';
import MediaCollector from '../components/MediaCollector.jsx';

function Mediacleaner() {

	return (
		<div className='mediacleaner-container'>
			<ContentBlock
				title= "Media Cleaner"
				description= "Media Cleaner will go through all unattached JPG, PNG, and GIF files. Based on media size this may take a while. Please click the 'Init Unused Media Migration' then review the selected images for deletion. Then click 'Init Deletion of Unused Media'. Please backup site before clicking the button! Keep in mind that if any pages or post are in the trash. The images that are attached to those pages will be deleted.Also please keep in mind that the older the website the higher possibility of a huge number of images being detached."
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

