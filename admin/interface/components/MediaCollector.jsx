import React, { useState, useEffect } from 'react';
import parse from 'html-react-parser';
const MediaCollector = ({ items }) => {
    const [unPreserveImageId, setUnImageId] = useState([]);
    const [preserveImageId, setImageId] = useState([]);
    const targets = document.querySelectorAll("[data-media-id]");

    for (var i = 0, len = targets.length; i < len; i++) {
        targets[i].querySelector("button").addEventListener('click', (e) => {
            const f_wpwrap = document.querySelector("#wpwrap");
            const f_wpcontent = document.querySelector("#wpcontent");
            f_wpwrap.classList.add('loader')
            f_wpcontent.insertAdjacentHTML('beforebegin', '<div class= "centered-blob"><div class= "blob-1"></div><div class= "blob-2"></div></div>');

            if(e.target.getAttribute("data-unpreserve-media")){
                e.target.textContent = 'Row is Removed!';
                setUnImageId([e.target.getAttribute("data-unpreserve-media")]);
                // Lets remove the row.
                e.currentTarget.parentNode.parentNode.remove();
                return;
            }
            else if(e.target.getAttribute("data-preserve-media")) {
                e.target.textContent = 'Row is Removed!';
                setImageId([e.target.getAttribute("data-preserve-media")]);
                // Lets remove the row.
                e.currentTarget.parentNode.parentNode.remove();
                return;
            }
        });
    }

    useEffect(() => {
        console.log(preserveImageId);
        handlePostDataPreserve( preserveImageId, 'invalid');
    }, [preserveImageId]);
    useEffect(() => {
        handlePostDataPreserve( 'invalid' , unPreserveImageId);
    }, [unPreserveImageId]);


    const handlePostDataPreserve = async ( preserveImageId,  unPreserveImageId) => {
        const data = new FormData();
            data.append( 'action', 'rmc_ajax_media_cleaner' );
            data.append( 'nonce', wpVars.nonce );
            data.append( 'post_overide',  "media-preserve" );

            data.append( 'preserveImageId',  preserveImageId );
            data.append( 'unPreserveImageId',  unPreserveImageId );

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