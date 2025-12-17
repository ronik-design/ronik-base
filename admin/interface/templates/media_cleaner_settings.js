import React, { useState, useEffect } from 'react';
import ContentBlock from '../components/ContentBlock.jsx';
import TopNav from '../components/MediaCleaner/TopNav.jsx';
import SyncStatus from '../components/MediaCleaner/SyncStatus.jsx';

// Helper function to get data attribute from an element
const getDataAttribute = (selector, attribute) => {
    const element = document.querySelector(selector);
    return element ? element.getAttribute(attribute) : null;
};

const MediaCleanerSettings = () => {
    // Default value for file size from the data attribute
    const fileSizeDefault = getDataAttribute('#ronik-base_settings-media-cleaner', 'data-file-size');

    // State to manage form input values and backup status
    const [formValues, setFormValues] = useState({ 'filesize-option': fileSizeDefault });
    const [dataResponse, setDataResponse] = useState('');
    const [backupEnabled, setBackupEnabled] = useState('off');

    // Effect hook to initialize backup settings and handle form changes
    useEffect(() => {
        const fileBackupEnabled = getDataAttribute('#ronik-base_settings-media-cleaner', 'data-file-backup');
        setBackupEnabled(fileBackupEnabled === 'on' ? 'valid' : 'invalid');
    }, []);

    useEffect(() => {
        if (formValues['filesize-option'] > 0) {
            handlePostData({
                fileSizeSelector: formValues['filesize-option'],
                fileSizeSelectorChanged: 'changed',
                fileImportSelector: 'invalid',
                fileImportSelectorChanged: 'invalid',
            });
        }

        if (formValues['fileimport-option']) {
            handlePostData({
                fileSizeSelector: 'invalid',
                fileSizeSelectorChanged: 'invalid',
                fileImportSelector: formValues['fileimport-option'],
                fileImportSelectorChanged: 'changed',
            });
        }
    }, [formValues]);

    // Handle changes to the file size input
    const handleChange = (e) => {
        setFormValues((prevValues) => ({
            ...prevValues,
            'filesize-option': e.target.value,
        }));
    };

    // Handle changes to the file import checkbox
    const handleImportChange = (e) => {
        const isChecked = e.target.checked;
        setFormValues((prevValues) => ({
            ...prevValues,
            'fileimport-option': isChecked ? 'on' : 'off',
        }));
        setBackupEnabled(isChecked ? 'valid' : 'invalid');

        if (isChecked) {
            alert('Your files will be backed up within the file: /ronik-base/admin/media-cleaner/ronikdetached');
        }
    };

    // Post data to the server
    const handlePostData = async ({ fileSizeSelector, fileSizeSelectorChanged, fileImportSelector, fileImportSelectorChanged }) => {
        const data = new FormData();
        data.append('action', 'rmc_ajax_media_cleaner_settings');
        data.append('nonce', wpVars.nonce);
        data.append('file_size_selector', fileSizeSelectorChanged);
        data.append('file_size_selection', fileSizeSelector);
        data.append('file_import_selector', fileImportSelectorChanged);
        data.append('file_import_selection', fileImportSelector);

        try {
            const response = await fetch(wpVars.ajaxURL, {
                method: 'POST',
                credentials: 'same-origin',
                body: data,
            });
            const result = await response.json();

            if (result?.data === 'Done') {
                setDataResponse('complete');
                // Optionally reload or handle success state
                // setTimeout(() => location.reload(), 500);
            }
        } catch (error) {
            console.error('[WP Pageviews Plugin]', error);
        }
    };

    return (
        <div className='general-container mediacleaner-container'>
            {/* SyncStatus manages global state independently */}
            <SyncStatus />

            <TopNav mode="dark" />

            <ContentBlock
                mode="dark"
                title="Media Cleaner Settings"
                description="Configure your media cleaner preferences below. Adjust the minimum file size limit to control which files are targeted for review."
            />

            <div className="container">
                <div className="section">
                    <h2>File Size Settings</h2>
                    <div className="section-content">
                        <p>Minimum File Size Limit: Only files above the number entered below will be targeted for review. Anything less will be ignored. We recommend 750KB to target files with higher impact; or, you can start with a higher limit first, and try a lower limit afterwards.</p>
                        <p><strong>Please note:</strong> If you adjust your settings, a preloaded scan of your site will be discarded and a new scan will need to be initiated either manually or automatically in order for you to review your files.</p>
                        
                        {/* File size settings */}
                        <div className='media-cleaner-item-settings__file-size'>
                            <label htmlFor="file-size-selector">Minimum File Size (MB):</label>
                            <input
                                type="number"
                                id="file-size-selector"
                                name="filesize-option"
                                min="0"
                                max="1000"
                                step=".01"
                                value={formValues['filesize-option']}
                                onChange={handleChange}
                            />
                            <p id="file-size-selector_val">
                                {formValues['filesize-option'] == 0
                                    ? '0 MB (all files)'
                                    : formValues['filesize-option'] < 1
                                    ? `${(formValues['filesize-option'] * 1024).toFixed(2)} KB`
                                    : `${formValues['filesize-option']} MB`}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default MediaCleanerSettings;
