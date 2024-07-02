import React, { useState, useEffect } from 'react';
import ContentBlock from '../components/ContentBlock.jsx';


const MediaCleanerSettings = () => {
    var fileSizeDefault = document.querySelector('#ronik-base_settings-media-cleaner').getAttribute("data-file-size");

    const [formValues, setFormValues] = useState({ ['filesize-option']: fileSizeDefault });
    const [dataResponse, setDataResponse] = useState('');


    // On page render lets detect if the option field is populated.
    useEffect(()=>{
        if( formValues['filesize-option'] > 0  ){
            handlePostData(formValues['filesize-option']);
        }
    }, [formValues])

        


    // Lets handle the input changes and store the changes to form values.
    const handleChange = (e) => {
        setTimeout(function(){
            setFormValues({ ...formValues, 'filesize-option': e.target.value });
        }, 400);

        console.log(formValues);
    }


    const handlePostData = async ( fileSizeSelector ) => {
        const data = new FormData();
            data.append( 'action', 'rmc_ajax_media_cleaner_settings' );
            data.append( 'nonce', wpVars.nonce );
            data.append( 'file_size_selector',  'changed' );
            data.append( 'file_size_selection',  fileSizeSelector );


        fetch(wpVars.ajaxURL, {
            method: "POST",
            credentials: 'same-origin',
            body: data
        })
        .then((response) => response.json())
        .then((data) => {
            if (data) {
                console.log(data);
                if(data.data == 'Done'){
                    setTimeout(function(){
                        // alert('Data Saved!');
                        setDataResponse('complete');
                        // location.reload();
                    }, 500);
                }
            }
        })
        .catch((error) => {
            console.log('[WP Pageviews Plugin]');
            console.error(error);
        });
    }



	return (
		<div className='settings-container'>
			<ContentBlock
				title= "Media Cleaner Settings Message"
				description= "Tell us which features you want to use."
			/>
			<br></br>
            <div className='media-cleaner-item-settings__file-size' onChange={handleChange}>
                <label htmlFor="file-size-selector">Minimum File Size Limit</label>
                <p>This will change the overall targeted media file size.</p>
                <input type="number" id="file-size-selector" name="file-size-selector" min="0" max="1000" defaultValue="3" step=".1" />
                <p id="file-size-selector_val">{formValues['filesize-option']} MB</p>
            </div>
            
		</div>
	);
}
export default MediaCleanerSettings;

