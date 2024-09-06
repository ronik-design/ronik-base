import React, { useState, useEffect } from 'react';

const FetchAddon = ({ requestType, postOveride = null }) => {
    const [formValues, setFormValues] = useState({ 'user-option': 'fetch-media' });
    const [increment, setIncrement] = useState(0);
    const [dataSync, setDataSync] = useState('');
    // const [dataSyncProgress, setDataSyncProgress] = useState('');
    // Check if there is an element with data-sync="valid"
    const syncIsRunning = document.querySelector('[data-sync="valid"]');
    // Determine if the button should be disabled
    const isButtonDisabled = syncIsRunning !== null;



    // Handle the state of the loader and initiate the sync process
    useEffect(() => {
        // if (dataSync) {
        //     handleLoaderState(dataSync, dataSyncProgress);
        // }
    }, [dataSync ]);

    // Toggle the loader and initiate data post
    const handleLoaderState = (syncStatus, syncProgress) => {
        const f_wpwrap = document.querySelector("#wpwrap");
        const f_wpcontent = document.querySelector("#wpcontent");

        if (f_wpwrap) {
            f_wpwrap.classList.add('loader');
            f_wpcontent.insertAdjacentHTML('beforebegin', `
                <div class="progress-bar"></div>
                <div class="centered-blob">
                    <div class="blob-1"></div>
                    <div class="blob-2"></div>
                </div>
                <div class="page-counter">Please do not refresh the page!</div>
            `);
            handlePostData(formValues['user-option'], 'all', increment, 'inprogress');
        }
    };
    
    // Handle form changes
    const handleChange = (e) => {
        setFormValues({ 'user-option': e.target.value });
    };

    // Handle form submission
    const handleSubmit = (e) => {
        e.preventDefault();
        console.log('clicked submit');

        handleLoaderState(dataSync);
    };

    // Perform the POST request
    const handlePostData = async (userOptions, mimeType, increment, sync) => {
        const data = new FormData();
        data.append('action', requestType);
        data.append('nonce', wpVars.nonce);
        data.append('post_overide', postOveride);
        data.append('user_option', userOptions);
        data.append('mime_type', mimeType);
        data.append('increment', increment);
        data.append('sync', sync);

        try {
            const response = await fetch(wpVars.ajaxURL, {
                method: "POST",
                credentials: 'same-origin',
                body: data,
            });
            const result = await response.json();
            handleResponse(result);
        } catch (error) {
            console.error('Error:', error);
            // location.reload(); // Consider handling errors more gracefully
        }
    };

    // Handle the server response
    const handleResponse = (data) => {
        console.log('handleResponse');
        console.log(data);

        if (!data || !data.data) return;

        const response = data.data['response'];
        switch (response) {
            case 'Reload':
                setTimeout(() => {
                    alert('Synchronization is complete! Page will auto reload.');
                    location.reload();
                }, 50);
                break;
            case 'Done':
                setTimeout(() => {
                    location.reload();
                    setIncrement(prev => prev + 1);
                }, 50);
                break;
            case 'Cleaner-Done':
                setTimeout(() => {
                    alert('Media cleanup complete! Page will auto reload.');
                    location.reload();
                }, 50);
                break;
            default:
                if (response.includes("Collector-Sync-inprogress")) {
                    // setDataSyncProgress(data.data['sync']);
                    setDataSync(response);
                } else if (response === 'Collector-Sync-done') {
                    alert("Sync is completed! Please do not refresh the page!");
                    setTimeout(() => setDataSync('DONE'), 500);
                }
        }
    };

    return (
        <div className="media-cleaner-block">
            <div className="media-cleaner-block__inner">
                <div className="media-cleaner-item" id={requestType}>
                    <div className="media-cleaner-item__inner">
                        <div className="media-cleaner-item__content">
                            <form className='media-cleaner-item__form' onSubmit={handleSubmit}>
                                <div className="media-cleaner-item__input-group">
                                    <div className="radio-switch">
                                        <div className="radio-switch-field">
                                            <input
                                                id="switch-off"
                                                type="radio"
                                                name="radio-switch"
                                                value="fetch-media"
                                                checked={formValues['user-option'] === 'fetch-media'}
                                                onChange={handleChange}
                                            />
                                            <label htmlFor="switch-off">Init Unused Media Migration</label>
                                        </div>
                                        <div className="radio-switch-field">
                                            <input
                                                id="switch-on"
                                                type="radio"
                                                name="radio-switch"
                                                value="delete-media"
                                                checked={formValues['user-option'] === 'delete-media'}
                                                onChange={handleChange}
                                            />
                                            <label htmlFor="switch-on">Delete Unused Media</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <button 
                                    type="submit" 
                                    className={isButtonDisabled ? 'submit-btn submit-btn-disabled' : 'submit-btn'}
                                    disabled={isButtonDisabled}  // Disable the button if syncIsRunning is valid
                                >
                                    {formValues['user-option'] === 'fetch-media' ? isButtonDisabled ? 'Sync is inprogress' : 'Sync Media' : 'Delete Media'}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default FetchAddon;
