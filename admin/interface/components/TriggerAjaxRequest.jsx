import React, { useState, useEffect } from 'react';
import parse from 'html-react-parser';

const FetchAddon = ({requestType, postOveride=null  }) => {
    const [formValues, setFormValues] = useState({ ['user-option']:'fetch-media'});
    const [pageCount, setPageCount] = useState(0);
    const [pageTotalCount, setPageTotalCount] = useState(0);
    const [increment, setIncrement] = useState(0);
    const [dataResponse, setDataResponse] = useState('');
    const [dataSync, setDataSync] = useState('');
    const [dataSyncProgress, setDataSyncProgress] = useState('');
    
    // On page render lets detect if the option field is populated.
    useEffect(()=>{
        if(dataResponse == 'incomplete'){
            setDataResponse('incomplete_half');
        }
    }, [dataResponse, formValues ])


    // On page render lets detect if the option field is populated.
    useEffect(()=>{
        console.log(dataSync);

        if(dataSync){
            console.log('dataSync, dataSyncProgress');

            const f_wpwrap = document.querySelector("#wpwrap");
            if(f_wpwrap){
                const f_wpwrap = document.querySelector("#wpwrap");
                const f_wpcontent = document.querySelector("#wpcontent");
                f_wpwrap.classList.add('loader')
                f_wpcontent.insertAdjacentHTML('beforebegin', '<div class= "centered-blob"><div class= "blob-1"></div><div class= "blob-2"></div></div>');
                if (document.contains(document.querySelector(".page-counter"))) {
                    document.querySelector(".page-counter").remove();
                }
                f_wpcontent.insertAdjacentHTML('beforebegin', '<div class="page-counter"> Please do not refresh the page! </div>');
            }
            console.log('USE-EFFECT');
            if(dataSync == 'DONE'){
                // alert('done');
                handlePostData(formValues['user-option'], 'all', increment, false );
            } else {
                console.log(dataSyncProgress);
                console.log(dataSync);
                // alert('SYNC In Progress');
                handlePostData(formValues['user-option'], 'all', increment, 'inprogress' );
            }
        }
    }, [dataSync, dataSyncProgress])


    // On page render lets detect if the option field is populated.
    useEffect(()=>{
        const f_wpwrap = document.querySelector("#wpwrap");
        if( f_wpwrap.classList.contains('loader')   ){
            const f_wpcontent = document.querySelector(".centered-blob");
            if (document.contains(document.querySelector(".page-counter"))) {
                document.querySelector(".page-counter").remove();
            }
            if(pageTotalCount !== 0){
                f_wpcontent.insertAdjacentHTML('beforebegin', '<div class="page-counter">Page '+pageCount + ' of ' + pageTotalCount+'</div>');
            }
        }
    }, [pageCount, pageTotalCount])


    // Lets handle the input changes and store the changes to form values.
    const handleChange = (e) => {
        setFormValues({ ...formValues, 'user-option': e.target.value });
    }

    // Handlefetch data from server and update option values.
    const handleSubmit = (e) => {
     e.preventDefault();
        const f_wpwrap = document.querySelector("#wpwrap");
        const f_wpcontent = document.querySelector("#wpcontent");
        f_wpwrap.classList.add('loader')
        f_wpcontent.insertAdjacentHTML('beforebegin', '<div class= "centered-blob"><div class= "blob-1"></div><div class= "blob-2"></div></div>');
        handlePostData(formValues['user-option'], 'all', increment, false );
        e.preventDefault();

    };


    const handlePostData = async ( userOptions, mimeType, f_increment, f_sync ) => {
        const data = new FormData();
            data.append( 'action', requestType );
            data.append( 'nonce', wpVars.nonce );
            data.append( 'post_overide',  postOveride );
            data.append( 'user_option',  userOptions );
            data.append( 'mime_type',  mimeType );
            data.append( 'increment',  f_increment );
            data.append( 'sync', f_sync );

        fetch(wpVars.ajaxURL, {
            method: "POST",
            credentials: 'same-origin',
            body: data
        })
        .then((response) => response.json())
        .then((data) => {
            if (data) {
                console.log(data);
                if(data.data['response'] == 'Reload'){
                    setTimeout(function(){
                        alert('Synchronization is complete! Page will auto reload.');
                        location.reload();
                    }, 50);
                }
                if(data.data['response'] == 'Done'){
                    setTimeout(function(){
                        // Lets remove the form
                        location.reload();
                        setIncrement(increment+1);
                    }, 50);
                    setTimeout(function(){
                        setDataResponse('incomplete');
                        setPageCount(increment);
                        setPageTotalCount(data.data['pageTotalCounter']);
                    }, 50);
                }
                console.log(data.data['response']);
                if(data.data == 'Cleaner-Done'){
                    setTimeout(function(){
                        // Lets remove the form
                        alert('Media cleanup complete! Page will auto reload.');
                        location.reload();
                    }, 50);
                }
                if(data.data['response'].includes("Collector-Sync-inprogress")){
                    // alert("Sync is in Progress... Please do not refresh the page!");
                    setTimeout(function(){
                        setDataSyncProgress(data.data['sync']);
                        setDataSync(data.data['response']);
                    }, 5000);
                }
                if(data.data['response'] == 'Collector-Sync-done'){
                    // alert("Sync is completed! Please do not refresh the page!");
                    setTimeout(function(){
                        setDataSync('DONE');
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
        <div className="media-cleaner-block">
            <div className="media-cleaner-block__inner">
                <div className="media-cleaner-item" id={requestType}>
                    <div className="media-cleaner-item__inner">
                        <div className="media-cleaner-item__content">
                            <form className='media-cleaner-item__form' onSubmit={handleSubmit}  >
                                <div className="media-cleaner-item__input-group">
                                    <div className="radio-switch" onChange={handleChange}>
                                        <div className="radio-switch-field" >
                                            <input id="switch-off" type="radio" name="radio-switch" value="fetch-media" defaultChecked />
                                            <label htmlFor="switch-off">Init Unused Media Migration</label>
                                        </div>
                                        <div className="radio-switch-field">
                                            <input id="switch-on" type="radio" name="radio-switch" value="delete-media" />
                                            <label htmlFor="switch-on">Delete Unused Media</label>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" className="submit-btn">Submit</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default FetchAddon;
