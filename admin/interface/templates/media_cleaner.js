import ContentBlock from '../components/ContentBlock.jsx';
import MediaCollector from '../components/MediaCleaner/MediaCollector.jsx';
import TopNav from '../components/MediaCleaner/TopNav.jsx';
import StatsContainer from '../components/MediaCleaner/StatsContainer.jsx';
import MediaFilter from '../components/MediaCleaner/MediaFilter.jsx';
import SyncStatus from '../components/MediaCleaner/SyncStatus.jsx';
import PageMediaRatioAlert from '../components/MediaCleaner/PageMediaRatioAlert.jsx';

function Mediacleaner() {

	return (
		<div className='mediacleaner-container'>
			{/* SyncStatus manages global state independently */}
			<SyncStatus />

			<TopNav /> 
			<PageMediaRatioAlert />
			<StatsContainer />

			<ContentBlock
				title="All Unlinked Files"
				description={``}
			/>

			<MediaFilter type="media_cleaner" />


			<MediaCollector type="media_cleaner" />
		</div>
	);
}
export default Mediacleaner;

