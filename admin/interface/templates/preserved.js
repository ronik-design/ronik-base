import ContentBlock from '../components/ContentBlock.jsx';
import MediaCollector from '../components/MediaCleaner/MediaCollector.jsx';
import TopNav from '../components/MediaCleaner/TopNav.jsx';
import StatsContainer from '../components/MediaCleaner/StatsContainer.jsx';
import MediaFilter from '../components/MediaCleaner/MediaFilter.jsx';
import SyncStatus from '../components/MediaCleaner/SyncStatus.jsx';

function Mediacleaner() {

	return (
		<div className='mediacleaner-container'>
			{/* SyncStatus manages global state independently */}
			<SyncStatus />
			
			<TopNav />
			<StatsContainer />

			<ContentBlock
				title="All Preserved Files"
				description={``}
			/>

			<MediaFilter  type="preserved"  />


			<MediaCollector type="preserved" />
		</div>
	);
}
export default Mediacleaner;

