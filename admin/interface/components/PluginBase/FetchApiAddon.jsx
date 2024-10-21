import React, { useState, useEffect } from 'react';
import parse from 'html-react-parser';

// Helper function to determine the API endpoint based on the environment
// const getApiEndpoint = () => {
//     // Return the local or staging API endpoint based on the current URL
//     return window.location.href.includes(".local/") 
//         ? "https://ronik-marketing.local" 
//         : "https://ronikmarketstg.wpenginepowered.com";
// };
const getApiEndpoint = () => {
    // Return the local or staging API endpoint based on the current URL
    return "https://ronikmarketstg.wpenginepowered.com";;
};

const FetchAddon = ({ pluginName, pluginSlug, title, description, linkHref, linkName }) => {
    // State to hold form input values
    const [formValues, setFormValues] = useState({});
    // State to hold the response from the server
    const [dataResponse, setDataResponse] = useState({});
    // Generate the license key ID based on the plugin slug
    const licenseKeyId = `license_key_${pluginSlug}`;

    // Effect hook to check the API key status when the component mounts
    useEffect(() => {
        // Get the element containing the API key data
        const apiKeyElement = document.getElementById(`${pluginSlug}_api_key`);
        
        // If the API key data attribute is present, update the state with a valid response
        if (apiKeyElement?.dataset.api) {
            setDataResponse({ responseResults: 'valid', response: 'License key is activated.' });
        }
    }, [pluginSlug]);

    // Function to handle license deactivation
    const deactivateLicense = async (e) => {
        e.preventDefault(); // Prevent the default form submission behavior

        // Update the state to indicate that the license key is deactivated
        setDataResponse({ responseResults: 'invalid', response: 'License key deactivated.' });
        setFormValues({}); // Clear the form values

        // Post data to the server indicating that the license key is invalid
        await handlePostData('', 'invalid');
    };

    // Function to handle input changes and update form values
    const handleChange = (e) => {
        const { id, value } = e.target; // Get input field ID and value
        setFormValues((prevValues) => ({ ...prevValues, [id]: value })); // Update form values in state
    };

    // Function to handle form submission
    const handleSubmit = async (e) => {
        e.preventDefault(); // Prevent the default form submission behavior

        // If license is valid, deactivate it; otherwise, activate it
        if (dataResponse.responseResults === 'valid') {
            await deactivateLicense(e); // Call deactivateLicense function
        } else {
            await handleFetchData(); // Fetch data from the server to activate the license
        }
    };

    // Function to fetch data from the server and update the response
    const handleFetchData = async () => {
        const endpoint = getApiEndpoint(); // Get the appropriate API endpoint
        const key = formValues[licenseKeyId]; // Get the license key from form values
        const websiteID = JSON.stringify(window.location.hostname); // Get the hostname of the current website

        try {
            // Make a request to the server to validate the license key
            const response = await fetch(`${endpoint}/wp-json/apikey/v1/data/apikey?pluginSlug=${pluginSlug}&key=${key}&websiteID=${websiteID}`);
            const data = await response.json(); // Parse the JSON response

            // Check if the response indicates success
            if (data === 'Success') {
                // Post data indicating the key is valid
                await handlePostData(key, 'valid');
                // Update state to indicate the license key is activated
                setDataResponse({ responseResults: 'valid', response: 'License key activated.' });
            } else {
                // Handle the case where the license key is invalid
                setDataResponse({ responseResults: 'invalid', response: 'License key is invalid.' });
            }
        } catch (error) {
            console.error('[WP ERROR Plugin]', error); // Log any errors that occur during the fetch
        }
    };

    // Function to post data to the server
    const handlePostData = async (val, validation) => {
        const data = new FormData();
        data.append('action', 'api_checkpoint'); // Action for the server to perform
        data.append('nonce', wpVars.nonce); // Security nonce for validation
        data.append('apikey', val); // License key to be posted
        data.append('apikeyValidation', validation); // Validation status of the license key
        data.append('plugin_slug', pluginSlug); // Plugin slug

        try {
            // Send a POST request to the server with the form data
            const response = await fetch(wpVars.ajaxURL, {
                method: "POST",
                credentials: 'same-origin',
                body: data
            });
            const result = await response.json(); // Parse the JSON response

            // Check if the server response indicates a need to reload
            if (result?.data === 'Reload') {
                // Reload the page after 1 second
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            console.error('[WP ERROR Plugin]', error); // Log any errors that occur during the fetch
        }
    };

    // Function to render response messages based on validation status
    const renderResponseMessage = () => {
        if (dataResponse.responseResults === 'valid') {
            return (
                <div className='tile-item__text tile-item__text--message tile-item__text--valid'>
                    {dataResponse.response}
                </div>
            );
        } else if (dataResponse.responseResults === 'invalid') {
            return (
                <div className='tile-item__text tile-item__text--message tile-item__text--invalid'>
                    {dataResponse.response}
                </div>
            );
        }
    };

    return (
        <div className="tile-item" id={pluginSlug}>
            <div className="tile-item__inner">
                <div className="tile-item__content">
                    {/* Render the response message based on validation status */}
                    {renderResponseMessage()}

                    {/* Render plugin details */}
                    <div className='tile-item__text tile-item__text--name'>{parse(pluginName)}</div>
                    <div className='tile-item__text tile-item__text--title'>{parse(title)}</div>
                    <div className='tile-item__text tile-item__text--desc'>{parse(description)}</div>
                    <a href={parse(linkHref)} className='tile-item__text tile-item__text--link'>{parse(linkName)}</a>

                    {/* Render the form for license key activation/deactivation */}
                    <form className='tile-item__form' onSubmit={handleSubmit}>
                        <div className="tile-item__input-group">
                            <label htmlFor={licenseKeyId}>License Key</label>
                            <input
                                autoComplete="on"
                                type={dataResponse.responseResults === 'valid' ? "password" : "text"}
                                id={licenseKeyId}
                                value={formValues[licenseKeyId] || (dataResponse.responseResults === 'valid' ? "xxxxxxxxxxxxxxxxxxxxxxxx" : "")}
                                onChange={handleChange}
                                disabled={dataResponse.responseResults === 'valid'}
                            />
                        </div>
                        {/* Render the appropriate button based on validation status */}
                        <button
                            type="submit"
                            className="submit-btn"
                            onClick={(e) => {
                                if (dataResponse.responseResults === 'valid') {
                                    // Call deactivateLicense if the license is valid
                                    deactivateLicense(e);
                                }
                            }}
                        >
                            {dataResponse.responseResults === 'valid' ? 'Deactivate License' : 'Activate License'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default FetchAddon;
