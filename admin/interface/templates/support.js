
import ContentBlock from '../components/ContentBlock.jsx';
import MediaCollector from '../components/MediaCleaner/MediaCollector.jsx';
import TopNav from '../components/MediaCleaner/TopNav.jsx';
import StatsContainer from '../components/MediaCleaner/StatsContainer.jsx';
import MediaFilter from '../components/MediaCleaner/MediaFilter.jsx';
import SyncStatus from '../components/MediaCleaner/SyncStatus.jsx';
import FAQ from '../components/Faq.jsx';


function Support() {
	let faqItems = [
		["Question 1", "Answer 1"],
		["Question 2", "Answer 2"],
		["Question 3", "Answer 3"]
	];

	return (
		<div className='support-container mediacleaner-container'>
			{/* SyncStatus manages global state independently */}
			<SyncStatus />

			<TopNav />
			<StatsContainer />




			<ContentBlock
				title="Support Message"
				description="If you have any questions, need a hand with a technical issue, or just want to say hi, we've got you covered. Get in touch with us and we'll be happy to assist you!"
			/>
			<br></br>
			<ContentBlock
				title="Frequently asked questions"
				description="Here, you'll find answers to commonly asked questions about using Media Harmony Plugin. If you don't see your question listed, you can have a look at the section below."
			/>
			<FAQ
				items={faqItems}
			/>
			<br></br>
			<ContentBlock
				title="Contact our support team"
				description="If you don't find the answers you're looking for and need personalized help, you can get 24/7 support from one of our support engineers. <br><br>Support languages: English & Spanish"
			/>
		</div>
	);
}
export default Support;












