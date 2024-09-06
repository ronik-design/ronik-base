import React, { useState, useEffect } from 'react';
import ContentBlock from '../components/ContentBlock.jsx';


const MediaCleanerSettings = () => {
    var fileSizeDefault = document.querySelector('#ronik-base_settings-media-cleaner').getAttribute("data-file-size");

    const [formValues, setFormValues] = useState({ ['filesize-option']: fileSizeDefault });
    const [dataResponse, setDataResponse] = useState('');
    const [backupEnabled, setBackupEnabled] = useState(false);



    // On page render lets detect if the option field is populated.
    useEffect(()=>{
        if( formValues['filesize-option'] > 0  ){
            handlePostData( formValues['filesize-option'], 'changed' ,  'invalid' , 'invalid' );
        }

        if( formValues['fileimport-option'] ){
            handlePostData( 'invalid' , 'invalid', formValues['fileimport-option'], 'changed');
        }         
    }, [formValues])



    useEffect(()=>{
        var filebackupenabled = document.querySelector('#ronik-base_settings-media-cleaner').getAttribute("data-file-backup");
        if(filebackupenabled == 'on'){
            // filebackupenabled = true;
            setBackupEnabled('valid');
        } else {
            setBackupEnabled('invalid');
        }
    
    }, [])
        

    // Lets handle the input changes and store the changes to form values.
    const handleChange = (e) => {
        setTimeout(function(){
            setFormValues({ ...formValues, 'filesize-option': e.target.value });
        }, 400);

        console.log(formValues);
    }

    
    
    
    // Lets handle the input changes and store the changes to form values.
    const handleImportChange = (e) => {
        if(e.target.checked){
            setTimeout(function(){
                setBackupEnabled('valid');    
                alert('Files will automatically be backed up within the ronik plugin /ronik-base/admin/media-cleaner/ronikdetached');
                setFormValues({ ...formValues, 'fileimport-option': 'on' });
            }, 400);
             
        } else {
            setTimeout(function(){
                setBackupEnabled('invalid');
                setFormValues({ ...formValues, 'fileimport-option': 'off' });
            }, 400);
        }
    }


    const handlePostData = async ( fileSizeSelector , fileSizeSelectorChanged,  fileImportSelector , fileImportSelectorChanged  ) => {
        const data = new FormData();
            data.append( 'action', 'rmc_ajax_media_cleaner_settings' );
            data.append( 'nonce', wpVars.nonce );
            data.append( 'file_size_selector',  fileSizeSelectorChanged );
            data.append( 'file_size_selection',  fileSizeSelector );
            data.append( 'file_import_selector',  fileImportSelectorChanged );
            data.append( 'file_import_selection',  fileImportSelector );


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
                <input type="number" id="file-size-selector" name="file-size-selector" min="0" max="1000" defaultValue={formValues['filesize-option']}  step=".01" />
                <p id="file-size-selector_val">{formValues['filesize-option']} MB</p>
            </div>



           	<ContentBlock
				title= "Media Cleaner Backup Settings"
				description= "Files will automatically be backed up within the ronik plugin /ronik-base/admin/media-cleaner/ronikdetached. Upon Deletion, <br> Based on size of site this may take a while"
			/>
			<br></br>
            <div className='media-cleaner-item-settings__file-size'>
                <label className="switch">
                    <input type="checkbox" defaultChecked={backupEnabled ? backupEnabled=='valid':true} className={backupEnabled}  onChange={handleImportChange} />
                    <span className="slider round"></span>
                </label>

                {/* <p id="file-size-selector_val">{formValues['fileimport-option']} MB</p> */}
            </div>



		</div>
	);
}
export default MediaCleanerSettings;

