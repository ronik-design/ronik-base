import React, { useState, useEffect } from 'react';
import parse from 'html-react-parser';

const FetchAddon = ({requestType, postOveride=null  }) => {
    const [formValues, setFormValues] = useState({});
    const [formCheckValues, setFormCheckValues] = useState([]);

    const [increment, setIncrement] = useState(1);
    const [dataResponse, setDataResponse] = useState('');
    const f_increment = document.querySelector(".ronik-user-exporter_increment").value;


    // On page render lets detect if the option field is populated. 
    useEffect(()=>{
        if(dataResponse == 'incomplete'){
            setIncrement(increment+1);
            handlePostData( 'fetch-media', formCheckValues, increment );
            setDataResponse('incomplete_half');
        }


        // const f_increment = document.querySelector(".ronik-user-exporter_increment").value;
        // if(f_increment > 5 && f_increment !== '0' ){
        //     // initAjaxUserData( $f_selected_email, $f_target_email_domain, $f_target_sso, $f_increment );
        //     handlePostData('fetch-media', formCheckValues, f_increment );
        //     alert('Synchronization is complete! Please click the "Download" button.');
        // } else{
        //     if(f_increment !== '0'){
        //         setTimeout(() => { }, 500);

        //         console.log(formCheckValues);
        //         console.log(f_increment);

        //         // initAjaxUserData( $f_selected_email, $f_target_email_domain, $f_target_sso, $f_increment );
        //         handlePostData( 'fetch-media', formCheckValues, f_increment );

        //         // $('#wpcontent').css(
        //         //     {
        //         //         "cursor":"wait", 
        //         //         "opacity":"0.3"
        //         //     }
        //         // );
        //     } else {
        //         // alert($f_increment);
        //         // handlePostData(e, formValues['user-option'], formCheckValues, f_increment );
        //     }
        // }

    }, [dataResponse])


    // Lets handle the input changes and store the changes to form values.
    const handleChange = (e) => {
        console.log(e.target.value);
        setFormValues({ ...formValues, [e.target.id]: e.target.value });
    };

    const handleChangeRadio = (e) => {
        console.log(e.target.value);
        // setFormCheckValues({ ...formCheckValues, [e.target.id]: e.target.value });
        setFormCheckValues([...formCheckValues, e.target.value ]);
    };
    
    // Handlefetch data from server and update option values.
    const handleSubmit = (e) => {
        console.log(formCheckValues);
        e.preventDefault();
        handlePostData(formValues['user-option'], formCheckValues, f_increment );
    };





    const handlePostData = async ( userOptions, mimeType, f_increment ) => {
        const data = new FormData();
            data.append( 'action', requestType );
            data.append( 'nonce', wpVars.nonce );
            data.append( 'post_overide',  postOveride );
            data.append( 'user_option',  userOptions );
            data.append( 'mime_type',  mimeType );
            data.append( 'increment',  f_increment );

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
                        alert('Synchronization is complete! Page will auto reload.');
                        location.reload();
                    }, 50);
                }
                if(data.data == 'Done'){
                    setTimeout(function(){
                        // Lets remove the form
                        // location.reload();
                        setDataResponse('incomplete');
                    }, 50);
                }
            }
        })
        .catch((error) => {
            console.log('[WP Pageviews Plugin]');
            console.error(error);
        });
    }










    
    // const validResponse = () => {
    //     if(dataResponse.responseResults == 'valid') {
    //         return (
    //             <div className='tile-item__text tile-item__text--message tile-item__text--valid'>{dataResponse.response}</div>
    //         )
    //     }
    // }
    // const invalidResponse = () => {
    //     if(dataResponse.responseResults == 'invalid') {
    //         return (
    //             <div className='tile-item__text tile-item__text--message tile-item__text--invalid'>{dataResponse.response}</div>
    //         )
    //     }
    // }
    


    return (
        <div className="tile-item" id={requestType}>
             <div className="tile-item__inner">
                <div className="tile-item__content">
                    <form className='tile-item__form' onSubmit={handleSubmit}  >

                        <div onChange={handleChangeRadio}>
                            <input type="checkbox" id="checkbox" value="jpg" />
                            <label htmlFor="checkbox">JPG</label>

                            <input type="checkbox" id="checkbox2" value="gif" />
                            <label htmlFor="checkbox2">GIF </label>

                            <input type="checkbox" id="checkbox3"  value="png" />
                            <label htmlFor="checkbox3">PNG </label>

                            <input type="checkbox" id="checkbox4"  value="video" />
                            <label htmlFor="checkbox4">Video </label>

                            <input type="checkbox" id="checkbox5"  value="misc" />
                            <label htmlFor="checkbox5">Misc </label>
                        </div>


                        <div className="tile-item__input-group" onChange={handleChange}>
                            <label htmlFor="name">User Media Option</label>
                            <input type="radio" value="fetch-media" id="user-option"  name="user-option" /> Init Unused Media Migration
                            <input type="radio" value="delete-media" id="user-option" name="user-option" /> Delete Unused Media
                        </div>
                        {(Object.keys(formValues).length == 0) ? <button type="submit" className="submit-btn" >Submit Disabled</button> : <button type="submit" className="submit-btn">Submit</button>}
                    </form>

                </div>
             </div>
        </div>
    );
  };
  
  export default FetchAddon;