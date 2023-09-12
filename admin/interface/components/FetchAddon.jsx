import React, { useState, useEffect } from 'react';
import parse from 'html-react-parser';

const FetchAddon = ({pluginName, pluginSlug, title, description, linkHref, linkName }) => {
    const [formValues, setFormValues] = useState({});
    const [dataResponse, setDataResponse] = useState({});
    const license_key = "license_key_"+pluginSlug;

    // On page render lets detect if the option field is populated. 
    useEffect(()=>{
        if(pluginSlug == 'ronik_media_cleaner'){
            if(document.getElementById('ronik_media_cleaner_api_key').dataset.api){
                setDataResponse({ responseResults : 'valid', response : 'License key is activated.' });            
            }
        }
        if(pluginSlug == 'ronik_optimization'){
            if(document.getElementById('ronik_optimization_api_key').dataset.api){
                setDataResponse({ responseResults : 'valid', response : 'License key is activated.' });            
            }
        }
    }, [])
    // If Deactivated is clicked we send out a ajax request to the server to invalidate the api key in the metadata.
    const dectivateLicense = (e) => {
        e.preventDefault();
        setDataResponse({ responseResults : 'invalid', response : 'License key deactivated.' });
        setFormValues({ });
        handlePostData(e, '', 'invalid');
    }
    // Lets handle the input changes and store the changes to form values.
    const handleChange = (e) => {
      setFormValues({ ...formValues, [e.target.id]: e.target.value });
    };
    // Handlefetch data from server and update option values.
    const handleSubmit = (e) => {
        e.preventDefault();
        handleFetchData(e);
    };
    // This is critical this will send a request to the plugin owners server. And send out a response of success or failure.
    const handleFetchData = async (e) => {
        const endpoint = "https://ronikmarketstg.wpenginepowered.com";
        const key = formValues[license_key];
        const websiteID = JSON.stringify(window.location.hostname);
        const response = await fetch(`${endpoint}/wp-json/apikey/v1/data/apikey?pluginSlug=${pluginSlug}&key=${key}&websiteID=${websiteID}`);
        const data = await response.json();
        if(data == 'Success') {
            handlePostData(e, e.target[0].value, 'valid');
            // acf js update field
            setDataResponse({ responseResults : 'valid', response : 'License key activated.' });
        } else {
            // alert(data);
            setDataResponse({ responseResults : 'invalid', response : 'License key is invalid.' });
        }
    }




    const handlePostData = async (e, val, validation) => {
        const data = new FormData();
            data.append( 'action', 'api_checkpoint' );
            data.append( 'nonce', wpVars.nonce );
            data.append( 'apikey',  val );
            data.append( 'apikeyValidation',  validation );
            data.append( 'plugin_slug',  pluginSlug );

        fetch(wpVars.ajaxURL, {
            method: "POST",
            credentials: 'same-origin',
            body: data
        })
        .then((response) => response.json())
        .then((data) => {
            if (data) {
                console.log(data);
                if(data.data == 'Reload'){
                    setTimeout(function(){
                        // Lets remove the form
                        location.reload();
                    }, 1000);
                }
            }
        })
        .catch((error) => {
            console.log('[WP Pageviews Plugin]');
            console.error(error);
        });
    }


    
    const validResponse = () => {
        if(dataResponse.responseResults == 'valid') {
            return (
                <div className='tile-item__text tile-item__text--message tile-item__text--valid'>{dataResponse.response}</div>
            )
        }
    }
    const invalidResponse = () => {
        if(dataResponse.responseResults == 'invalid') {
            return (
                <div className='tile-item__text tile-item__text--message tile-item__text--invalid'>{dataResponse.response}</div>
            )
        }
    }
    
    return (
        <div className="tile-item" id={pluginSlug}>
             <div className="tile-item__inner">
                <div className="tile-item__content">
                    {(dataResponse.responseResults == 'valid') ? validResponse() : invalidResponse()}
                    <div className='tile-item__text tile-item__text--name'>{parse(pluginName)}</div>
                    <div className='tile-item__text tile-item__text--title'>{parse(title)}</div>
                    <div className='tile-item__text tile-item__text--desc'>{parse(description)}</div>
                    <a href={parse(linkHref)} className='tile-item__text tile-item__text--link'>{parse(linkName)}</a>

                    <form className='tile-item__form' onSubmit={handleSubmit}>
                        <div className="tile-item__input-group">
                            <label htmlFor="name">License Key</label>
                            {(dataResponse.responseResults == 'valid') ?  
                                <input
                                    autoComplete="on"
                                    disabled
                                    type="password"
                                    id={license_key}
                                    value={formValues.license_key || "xxxxxxxxxxxxxxxxxxxxxxxx"}
                                /> :  <input
                                    autoComplete="on"
                                    type="text"
                                    id={license_key}
                                    value={formValues[license_key] || ""}
                                    onChange={handleChange}
                                />
                            }
                        </div>
                        {(dataResponse.responseResults == 'valid') ? <button onClick={dectivateLicense} className="submit-btn">deactivate License</button> : <button type="submit" className="submit-btn">Activate License</button>}
                    </form>

                </div>
             </div>
        </div>
    );
  };
  
  export default FetchAddon;