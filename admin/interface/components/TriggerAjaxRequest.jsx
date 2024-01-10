import React, { useState, useEffect } from 'react';
import parse from 'html-react-parser';

const FetchAddon = ({requestType, postOveride=null  }) => {
    const [formValues, setFormValues] = useState({ ['user-option']:'fetch-media'});
    const [formCheckValues, setFormCheckValues] = useState([]);

    const [pageCount, setPageCount] = useState(0);
    const [pageTotalCount, setPageTotalCount] = useState(0);

    const [increment, setIncrement] = useState(0);
    const [dataResponse, setDataResponse] = useState('');
    // const f_increment = document.querySelector(".ronik-user-exporter_increment").value;

    // On page render lets detect if the option field is populated.
    useEffect(()=>{
        console.log(formCheckValues);
        if( formCheckValues.length > 0  ){
            if(!formCheckValues.includes('all') ){
                document.getElementById("checkbox_all").checked = false;
            } else {
                document.getElementById("checkbox").checked = true;
                document.getElementById("checkbox2").checked = true;
                document.getElementById("checkbox3").checked = true;
                document.getElementById("checkbox4").checked = true;
                document.getElementById("checkbox5").checked = true;

                setFormCheckValues(['jpg', 'gif', 'png', 'video', 'misc']);
            }
        }

        if(dataResponse == 'incomplete'){
            console.log(increment);
            if(formCheckValues.length == 0){
                console.log(increment);
                handlePostData(formValues['user-option'], ['all'], increment );
            } else {
                console.log(increment);
                handlePostData(formValues['user-option'], formCheckValues, increment );
            }
            setDataResponse('incomplete_half');
        }
    }, [dataResponse, formValues, formCheckValues])


    // On page render lets detect if the option field is populated.

    useEffect(()=>{
        const f_wpwrap = document.querySelector("#wpwrap");

        if( f_wpwrap.classList.contains('loader')   ){
            const f_wpcontent = document.querySelector(".centered-blob");

            if (document.contains(document.querySelector(".page-counter"))) {
                document.querySelector(".page-counter").remove();
            }
            f_wpcontent.insertAdjacentHTML('beforebegin', '<div class="page-counter">Page '+pageCount + ' of ' + pageTotalCount+'</div>');
        }
    }, [pageCount, pageTotalCount])


    // Lets handle the input changes and store the changes to form values.
    const handleChange = (e) => {
        setFormValues({ ...formValues, 'user-option': e.target.value });
        // setFormValues( 'e.target.value' );
        console.log(formValues);
    }

    const handleChangeRadio = (e) => {
        if(e.target.checked){
            setFormCheckValues([...formCheckValues, e.target.value ]);
        } else {
            let index = formCheckValues.indexOf(e.target.value);
            formCheckValues.splice(index, 1);
            setFormCheckValues(formCheckValues);
        }
    };

    // Handlefetch data from server and update option values.
    const handleSubmit = (e) => {
     e.preventDefault();
        const f_wpwrap = document.querySelector("#wpwrap");
        const f_wpcontent = document.querySelector("#wpcontent");
        f_wpwrap.classList.add('loader')
        f_wpcontent.insertAdjacentHTML('beforebegin', '<div class= "centered-blob"><div class= "blob-1"></div><div class= "blob-2"></div></div>');


        console.log('formCheckValues');
        console.log(formCheckValues);

        if(formCheckValues.length == 0){
            console.log(increment);
            console.log('formCheckValues1');
            console.log(formCheckValues);
            console.log("formValues['user-option']");
            console.log(formValues['user-option']);
            handlePostData(formValues['user-option'], ['all'], increment );
        } else {
            console.log(increment);
            console.log('formCheckValues2');
            console.log(formCheckValues);
            console.log(formValues['user-option']);
            handlePostData(formValues['user-option'], formCheckValues, increment );
        }
        e.preventDefault();

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
                if(data.data['response'] == 'Reload'){
                    setTimeout(function(){
                        alert('Synchronization is complete! Page will auto reload.');
                        location.reload();
                    }, 50);
                }
                if(data.data['response'] == 'Done'){
                    setTimeout(function(){
                        // Lets remove the form
                        // location.reload();
                        setIncrement(increment+1);
                    }, 50);
                    setTimeout(function(){
                        setDataResponse('incomplete');
                        setPageCount(increment);
                        setPageTotalCount(data.data['pageTotalCounter']);
                    }, 50);
                }
                if(data.data == 'Cleaner-Done'){
                    alert('Media cleanup complete! Page will auto reload.');
                    location.reload();
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
        <div className="media-cleaner-block">
            <div className="media-cleaner-block__inner">
                <div className="media-cleaner-item" id={requestType}>
                    <div className="media-cleaner-item__inner">
                        <div className="media-cleaner-item__content">
                            <form className='media-cleaner-item__form' onSubmit={handleSubmit}  >

                                <div className='media-cleaner-item__checkboxes' onChange={handleChangeRadio}>
                                    <span className="switch colored">
                                        <h3>ALL</h3>
                                        <input type="checkbox" id="checkbox_all" value="all" defaultChecked />
                                        <label htmlFor="checkbox_all">ALL</label>
                                    </span>
                                    <span className="switch colored hidden">
                                        <h3>JPG</h3>
                                        <input type="checkbox" id="checkbox" value="jpg" />
                                        <label htmlFor="checkbox">JPG</label>
                                    </span>
                                    <span className="switch colored hidden">
                                        <h3>GIF</h3>
                                        <input type="checkbox" id="checkbox2" value="gif" />
                                        <label htmlFor="checkbox2">GIF </label>
                                    </span>
                                    <span className="switch colored hidden">
                                        <h3>PNG</h3>
                                        <input type="checkbox" id="checkbox3"  value="png" />
                                        <label htmlFor="checkbox3">PNG </label>
                                    </span>
                                    <span className="switch colored hidden">
                                        <h3>Video</h3>
                                        <input type="checkbox" id="checkbox4"  value="video" />
                                        <label htmlFor="checkbox4">Video </label>
                                    </span>
                                    <span className="switch colored hidden">
                                        <h3>MISC</h3>
                                        <input type="checkbox" id="checkbox5"  value="misc" />
                                        <label htmlFor="checkbox5">Misc </label>
                                    </span>
                                </div>


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
