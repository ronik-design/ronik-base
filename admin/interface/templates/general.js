import ContentBlock from '../components/ContentBlock.jsx';


function General() {

	return (
		<div className='general-container'>
			<ContentBlock
				title= "Plugin Info"
				description= "Thanks for using the Ronik plugin! <br><br>In the future, we expect to offer a full suite of functionality; for now we have Media Harmony. <br><br>For developers and content admins alike, we know how tricky it can be to manage a media library, especially for a large or aging website with multiple hands involved in asset creation and use. When not actively managed, the space used by old files quickly grows, negatively impacting site load times, performance, and internal hygiene. As soon as you have more than one person producing content, you get a messy basement, which also makes it hard for admins to locate and make use of the correct files for front-end publishing. Attempts to manually mitigate the issue by regularly reviewing, removing, or editing unlinked or improper files could take hours and is likely to result in mistakes. <br><br>The solution we saw to this issue is a tool that reviews your database for any unlinked files and allows for easy removal. Our plugin is compatible with most common frameworks and does a full database scan to reveal/ provide a holistic picture of unlinked files of all types in the media library. Editors can refine their results thanks to an array of sort and filter criteria and then preserve, individually target, or bulk delete their unlinked and matching files.<br><br>Most plugins out there that offer similar functionality are less reliable, lack granularity of selection, and fail to go as deep as ours to find things like nested clones of assets or corrupt files. Check our full feature list, installation instructions, and usage instructions below."
			/>
			<br></br>

		</div>
	);
}
export default General;

