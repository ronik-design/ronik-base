import React, { useState, useEffect, useRef } from 'react';
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
    
    // Get post types from data attributes
    const postTypesJson = getDataAttribute('#ronik-base_settings-media-cleaner', 'data-post-types');
    const selectedPostTypesJson = getDataAttribute('#ronik-base_settings-media-cleaner', 'data-selected-post-types');
    const allPostTypes = postTypesJson ? JSON.parse(postTypesJson) : [];
    const initialSelectedPostTypes = selectedPostTypesJson ? JSON.parse(selectedPostTypesJson) : allPostTypes;

    // State to manage form input values and backup status
    const [formValues, setFormValues] = useState({ 
        'filesize-option': fileSizeDefault,
        'fileimport-option': getDataAttribute('#ronik-base_settings-media-cleaner', 'data-file-backup') || 'off'
    });
    const [selectedPostTypes, setSelectedPostTypes] = useState(initialSelectedPostTypes);
    const [dataResponse, setDataResponse] = useState('');
    const [backupEnabled, setBackupEnabled] = useState('off');

    // Effect hook to initialize backup settings and handle form changes
    useEffect(() => {
        const fileBackupEnabled = getDataAttribute('#ronik-base_settings-media-cleaner', 'data-file-backup');
        setBackupEnabled(fileBackupEnabled === 'on' ? 'valid' : 'invalid');
    }, []);

    // Track if this is the initial mount to prevent unnecessary API calls
    const isInitialMount = useRef(true);

    useEffect(() => {
        // Skip API calls on initial mount
        if (isInitialMount.current) {
            isInitialMount.current = false;
            return;
        }

        // Handle file size changes
        if (formValues['filesize-option'] !== undefined && formValues['filesize-option'] !== null) {
            handlePostData({
                fileSizeSelector: formValues['filesize-option'],
                fileSizeSelectorChanged: 'changed',
                fileImportSelector: 'invalid',
                fileImportSelectorChanged: 'invalid',
            });
        }
    }, [formValues['filesize-option']]);

    useEffect(() => {
        // Skip API calls on initial mount
        if (isInitialMount.current) {
            return;
        }

        // Handle file import/backup changes - send immediately when changed
        if (formValues['fileimport-option'] !== undefined) {
            console.log('File import option changed, sending to server:', formValues['fileimport-option']);
            handlePostData({
                fileSizeSelector: 'invalid',
                fileSizeSelectorChanged: 'invalid',
                fileImportSelector: formValues['fileimport-option'],
                fileImportSelectorChanged: 'changed',
            });
        }
    }, [formValues['fileimport-option']]);

    useEffect(() => {
        // Skip API calls on initial mount
        if (isInitialMount.current) {
            return;
        }

        // Handle post types changes - send immediately when changed
        if (selectedPostTypes !== undefined && Array.isArray(selectedPostTypes)) {
            console.log('Post types changed, sending to server:', selectedPostTypes);
            handlePostData({
                fileSizeSelector: 'invalid',
                fileSizeSelectorChanged: 'invalid',
                fileImportSelector: 'invalid',
                fileImportSelectorChanged: 'invalid',
                postTypesSelector: selectedPostTypes,
                postTypesSelectorChanged: 'changed',
            });
        }
    }, [selectedPostTypes]);

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
        const newValue = isChecked ? 'on' : 'off';
        console.log('File import checkbox changed:', isChecked, 'New value:', newValue);
        
        setFormValues((prevValues) => {
            console.log('Previous formValues:', prevValues);
            const updated = {
                ...prevValues,
                'fileimport-option': newValue,
            };
            console.log('Updated formValues:', updated);
            return updated;
        });
        setBackupEnabled(isChecked ? 'valid' : 'invalid');

        if (isChecked) {
            alert('Your files will be backed up within the file: /ronik-base/admin/media-cleaner/ronikdetached');
        }
    };

    // Handle changes to post type checkboxes
    const handlePostTypeChange = (postType, isChecked) => {
        setSelectedPostTypes((prevSelected) => {
            if (isChecked) {
                // Add post type if not already selected
                return prevSelected.includes(postType) ? prevSelected : [...prevSelected, postType];
            } else {
                // Remove post type
                return prevSelected.filter(type => type !== postType);
            }
        });
    };

    // Handle select all / deselect all for post types
    const handleSelectAllPostTypes = (selectAll) => {
        if (selectAll) {
            setSelectedPostTypes([...allPostTypes]);
        } else {
            setSelectedPostTypes([]);
        }
    };

    // Post data to the server
    const handlePostData = async ({ fileSizeSelector, fileSizeSelectorChanged, fileImportSelector, fileImportSelectorChanged, postTypesSelector, postTypesSelectorChanged }) => {
        const data = new FormData();
        data.append('action', 'rmc_ajax_media_cleaner_settings');
        data.append('nonce', wpVars.nonce);
        data.append('file_size_selector', fileSizeSelectorChanged);
        data.append('file_size_selection', fileSizeSelector);
        data.append('file_import_selector', fileImportSelectorChanged);
        data.append('file_import_selection', fileImportSelector);
        
        // Add post types data if provided (allow empty arrays)
        if (postTypesSelectorChanged === 'changed' && Array.isArray(postTypesSelector)) {
            data.append('post_types_selector', postTypesSelectorChanged);
            data.append('post_types_selection', JSON.stringify(postTypesSelector));
        }

        console.log('Sending to server:', {
            file_size_selector: fileSizeSelectorChanged,
            file_size_selection: fileSizeSelector,
            file_import_selector: fileImportSelectorChanged,
            file_import_selection: fileImportSelector,
            post_types_selector: postTypesSelectorChanged,
            post_types_selection: postTypesSelector,
        });

        try {
            const response = await fetch(wpVars.ajaxURL, {
                method: 'POST',
                credentials: 'same-origin',
                body: data,
            });
            const result = await response.json();
            console.log('Server response:', result);

            if (result?.success) {
                setDataResponse('complete');
                console.log('Settings updated successfully');
            } else {
                console.error('Settings update failed:', result);
            }
        } catch (error) {
            console.error('[Media Cleaner Settings] Error:', error);
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

                <div className="section">
                    <h2>Backup Settings</h2>
                    <div className="section-content">
                        <p>Enable file backup to save deleted media files to a backup folder before they are permanently removed. This provides an extra layer of safety in case you need to restore files later.</p>
                        
                        {/* File import/backup settings */}
                        <div className='media-cleaner-item-settings__file-import'>
                            <label htmlFor="file-import-selector" style={{ display: 'flex', alignItems: 'center', gap: '10px', cursor: 'pointer' }}>
                                <input
                                    type="checkbox"
                                    id="file-import-selector"
                                    name="fileimport-option"
                                    checked={formValues['fileimport-option'] === 'on'}
                                    onChange={handleImportChange}
                                />
                                <span>Enable File Backup Before Deletion</span>
                            </label>
                            {formValues['fileimport-option'] === 'on' && (
                                <p style={{ marginTop: '10px', padding: '10px', backgroundColor: '#f0f0f0', borderRadius: '4px' }}>
                                    <strong>Backup Location:</strong> Files will be backed up to <code>/ronik-base/admin/media-cleaner/ronikdetached/</code> before deletion.
                                </p>
                            )}
                        </div>
                    </div>
                </div>

                <div className="section">
                    <h2>Post Type Settings</h2>
                    <div className="section-content">
                        <p>Select which post types should be included when scanning for media usage. Only selected post types will be checked for media references. Uncheck post types you want to exclude from the scan.</p>
                        <p><strong>Please note:</strong> If you adjust your settings, a preloaded scan of your site will be discarded and a new scan will need to be initiated either manually or automatically in order for you to review your files.</p>
                        
                        {/* Post types selection */}
                        <div className='media-cleaner-item-settings__post-types' style={{ marginTop: '20px' }}>
                            <div style={{ marginBottom: '15px', display: 'flex', gap: '10px' }}>
                                <button
                                    type="button"
                                    onClick={() => handleSelectAllPostTypes(true)}
                                    style={{ padding: '8px 16px', cursor: 'pointer' }}
                                >
                                    Select All
                                </button>
                                <button
                                    type="button"
                                    onClick={() => handleSelectAllPostTypes(false)}
                                    style={{ padding: '8px 16px', cursor: 'pointer' }}
                                >
                                    Deselect All
                                </button>
                            </div>
                            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: '10px', marginTop: '10px' }}>
                                {allPostTypes.map((postType) => (
                                    <label
                                        key={postType}
                                        htmlFor={`post-type-${postType}`}
                                        style={{ display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                    >
                                        <input
                                            type="checkbox"
                                            id={`post-type-${postType}`}
                                            checked={selectedPostTypes.includes(postType)}
                                            onChange={(e) => handlePostTypeChange(postType, e.target.checked)}
                                        />
                                        <span>{postType}</span>
                                    </label>
                                ))}
                            </div>
                            {selectedPostTypes.length === 0 && (
                                <p style={{ marginTop: '10px', padding: '10px', backgroundColor: '#fff3cd', borderRadius: '4px', color: '#856404' }}>
                                    <strong>Warning:</strong> No post types selected. Please select at least one post type to enable media scanning.
                                </p>
                            )}
                            <p style={{ marginTop: '15px', fontSize: '14px', color: '#666' }}>
                                <strong>Selected:</strong> {selectedPostTypes.length} of {allPostTypes.length} post types
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default MediaCleanerSettings;
