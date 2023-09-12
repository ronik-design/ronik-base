import React, { useState, useEffect } from 'react';
import parse from 'html-react-parser';
const MediaCollector = ({ items }) => {
    const [isRemovedRow, setRemovedRow] = useState([]);
    const [dataResponse, setDataResponse] = useState({});
    const targets = document.querySelectorAll("[data-media-id]");

    for (var i = 0, len = targets.length; i < len; i++) {
        targets[i].querySelector("button").addEventListener('click', (e) => {
            e.target.textContent = 'Row is Removed!';
            setRemovedRow([e.target.getAttribute("data-media-row")]);
            // Lets remove the row.
            e.currentTarget.parentNode.parentNode.remove();
            return;
          });
    }

    useEffect(() => {
        console.log(isRemovedRow);
        handlePostData(isRemovedRow);
    }, [isRemovedRow]);


    const handlePostData = async (isRemovedRow ) => {
        const data = new FormData();
            data.append( 'action', 'rmc_ajax_media_cleaner' );
            data.append( 'nonce', wpVars.nonce );
            data.append( 'post_overide',  "media-row-removal" );
            data.append( 'row-id',  isRemovedRow );

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


    return (
        <div className="message"> </div>
    );
  };
  
  export default MediaCollector;