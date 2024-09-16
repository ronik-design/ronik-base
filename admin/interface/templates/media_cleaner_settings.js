import React, { useState, useEffect } from 'react';
import ContentBlock from '../components/ContentBlock.jsx';

// Helper function to get data attribute from an element
const getDataAttribute = (selector, attribute) => {
    const element = document.querySelector(selector);
    return element ? element.getAttribute(attribute) : null;
};

const MediaCleanerSettings = () => {
    // Default value for file size from the data attribute
    const fileSizeDefault = getDataAttribute('#ronik-base_settings-media-cleaner', 'data-file-size');

    function isScientificNotationNumber(value) {
        if (typeof value === 'number' && isFinite(value)) {
            const stringValue = value.toExponential();
            return stringValue.includes('e') || stringValue.includes('E');
        }
        return false;
    }
    // if(isScientificNotationNumber(fileSizeDefault)){

    // }
    

    // State to manage form input values
    const [formValues, setFormValues] = useState({ ['filesize-option']: fileSizeDefault });
    // State to manage the response status
    const [dataResponse, setDataResponse] = useState('');
    // State to manage backup status
    const [backupEnabled, setBackupEnabled] = useState('off');

    // Effect hook to handle form data changes and perform updates
    useEffect(() => {
        // Handle file size change if it is valid
        if (formValues['filesize-option'] > 0) {
            handlePostData(formValues['filesize-option'], 'changed', 'invalid', 'invalid');
        }

        // Handle file import option change if it is set
        if (formValues['fileimport-option']) {
            handlePostData('invalid', 'invalid', formValues['fileimport-option'], 'changed');
        }
    }, [formValues]);

    // Effect hook to initialize backup settings
    useEffect(() => {
        const fileBackupEnabled = getDataAttribute('#ronik-base_settings-media-cleaner', 'data-file-backup');
        setBackupEnabled(fileBackupEnabled === 'on' ? 'valid' : 'invalid');
    }, []);

    // Handle changes to the file size input
    const handleChange = (e) => {
        setFormValues(prevValues => ({
            ...prevValues,
            'filesize-option': e.target.value
        }));
    };

    // Handle changes to the file import checkbox
    const handleImportChange = (e) => {
        const isChecked = e.target.checked;
        setFormValues(prevValues => ({
            ...prevValues,
            'fileimport-option': isChecked ? 'on' : 'off'
        }));
        setBackupEnabled(isChecked ? 'valid' : 'invalid');
        
        if (isChecked) {
            alert('Your files will be backed up within the file: /ronik-base/admin/media-cleaner/ronikdetached');
        }
    };

    // Post data to the server
    const handlePostData = async (fileSizeSelector, fileSizeSelectorChanged, fileImportSelector, fileImportSelectorChanged) => {
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
                body: data
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
        <div className='settings-container'>
            {/* Display general settings message */}
            <ContentBlock
                title="Media Cleaner Settings:"
                description="Configure file size minimum and backup settings below. <br><br>Minimum File Size Limit: Only files above the number entered below will be targeted for review. Anything less will be ignored. We recommend 750KB to target files with higher impact; or, you can start with a higher limit first, and try a lower limit afterwards."
            />
            <br />
            {/* File size settings */}
            <div className='media-cleaner-item-settings__file-size'>
                <input
                    type="number"
                    id="file-size-selector"
                    name="file-size-selector"
                    min="0.1"
                    max="1000"
                    step=".01"
                    value={formValues['filesize-option']}
                    onChange={handleChange}
                />
                <p id="file-size-selector_val">{formValues['filesize-option']} MB</p>
            </div>

            {/* Backup settings */}
            <ContentBlock
                title="Backup files:"
                description="Turn on to automatically backup files. Please note that this may take an additional 1-2 minutes.  "
            />
            <br />
            <div className='media-cleaner-item-settings__file-size'>
                <label className="switch">
                    <input
                        type="checkbox"
                        checked={backupEnabled === 'valid'}
                        className={backupEnabled}
                        onChange={handleImportChange}
                    />
                    <span className="slider round"></span>
                </label>
            </div>
        </div>
    );
};

export default MediaCleanerSettings;
