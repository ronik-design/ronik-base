import React, { useState, useEffect, useRef } from 'react';
import parse from 'html-react-parser';

import { DndProvider } from 'react-dnd'
import { HTML5Backend } from 'react-dnd-html5-backend'

import { useDrag, useDrop } from 'react-dnd'


const MediaCollector = ({ items }) => {
    const [hasLoaded, setHasLoaded] = useState(false);
    const [mediaCollector, setMediaCollector] = useState(null);
    
    const urlParams = new URLSearchParams(window.location.search);
    const rbp_media_cleaner_media_data_page = urlParams.get('page_number') ? urlParams.get('page_number') : 0;
    const [filterPager, setFilterPager] = useState(rbp_media_cleaner_media_data_page);

    const rbp_media_cleaner_media_data_filter = urlParams.get('filter_size') ? urlParams.get('filter_size') : 'all';
    const [filterMode, setFilterMode] = useState(rbp_media_cleaner_media_data_filter);    

    const [mediaCollectorLow, setMediaCollectorLow] = useState(null);
    const [mediaCollectorHigh, setMediaCollectorHigh] = useState(null);

    const [unPreserveImageId, setUnImageId] = useState([]);
    const [preserveImageId, setImageId] = useState([]);


    const [mediaCollectorPreserved, setMediaCollectorPreserved] = useState(null);


    // Lazy load images in aswell as image compression.
    function lazyLoader() {
        const imageObserver = new IntersectionObserver((entries, imgObserver) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                const lazyImage = entry.target
                fetch(lazyImage.dataset.src)
                    .then(res => res.blob()) // Gets the response and returns it as a blob
                    .then(blob => {
        
                        var imageSelector = document.querySelector('[data-id="'+lazyImage.getAttribute('data-id')+'"]');
                        lazyImage.className = " reveal-enabled";

                        var c = document.createElement("canvas");
                        var ctx = c.getContext("2d");

                        var img = new Image;
                            img.crossOrigin = ""; // if from different origin
                            img.src = lazyImage.getAttribute('data-src');                                        
                        
                            img.onload = function() {
                                c.width = this.naturalWidth;     // update canvas size to match image
                                c.height = this.naturalHeight;
                                ctx.drawImage(this, 0, 0);       // draw in image
                                c.toBlob(function(blob) {        // get content as JPEG blob
                                    // here the image is a blob
                                    imageSelector.src = URL.createObjectURL(blob)    
                                }, lazyImage.getAttribute('data-type'), 0.5);
                            };

                });
            }
        })
        });
        const arr = document.querySelectorAll('img.lzy_img');
        console.log(arr.length);
        arr.forEach((v) => {
            imageObserver.observe(v);
        });
    }
    setTimeout(() => {
        lazyLoader();
    }, 50);


                




    const activatePreserve = (e) => {
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
    }











    useEffect(() => {
        if(preserveImageId){
            console.log('preserveImageId');
            console.log(preserveImageId);
            handlePostDataPreserve( preserveImageId, 'invalid');
        }

    }, [preserveImageId]);

    useEffect(() => {
        if(unPreserveImageId){
            console.log('unpreserveImageId');
            console.log(unPreserveImageId);
            handlePostDataPreserve( 'invalid' , unPreserveImageId);
        }

        setHasLoaded(false);
        fetch("/wp-json/mediacleaner/v1/mediacollector/tempsaved", {
            method: "GET",
        }).then((response) => response.json()).then((data) => {
            setMediaCollectorPreserved(data);
        }).catch((error) => console.log(error));
        setHasLoaded(true);

    }, [unPreserveImageId]);





    useEffect(() => {        
        fetch("/wp-json/mediacleaner/v1/mediacollector/all", {
            method: "GET",
        }).then((response) => response.json()).then((data) => {
            setMediaCollector(data);
            setHasLoaded(true);
        }).catch((error) => console.log(error));
    }, []);


    useEffect(() => {
        filter_size();    
        const params = new URLSearchParams(window.location.search);
        const paramsObj = Array.from(params.keys()).reduce(
          (acc, val) => ({ ...acc, [val]: params.get(val) }),
          {}
        );

        if (window.history.pushState) {       
            const newURL = new URL(window.location.href);       
            newURL.search = '?page=options-ronik-base_media_cleaner&filter_size='+filterMode+'&page_number='+paramsObj['page_number'];        
            window.history.pushState({ path: newURL.href }, '', newURL.href); 
        }

    }, [filterMode]);

    
    useEffect(() => {
        console.log(filterPager);

        const params = new URLSearchParams(window.location.search);
        const paramsObj = Array.from(params.keys()).reduce(
          (acc, val) => ({ ...acc, [val]: params.get(val) }),
          {}
        );
        if (window.history.pushState) {       
            const newURL = new URL(window.location.href);       
            newURL.search = '?page=options-ronik-base_media_cleaner&filter_size='+paramsObj['filter_size']+'&page_number='+filterPager;        
            window.history.pushState({ path: newURL.href }, '', newURL.href); 
        }
      }, [filterPager]);

  




    const filter_size = async (e) => {
        setHasLoaded(false);
        // setHasFilterLoaded(true);

        fetch("/wp-json/mediacleaner/v1/mediacollector/small", {
            method: "GET",
        }).then((response) => response.json()).then((data) => {
            setMediaCollectorLow(data);                
        }).catch((error) => console.log(error));

        fetch("/wp-json/mediacleaner/v1/mediacollector/large", {
            method: "GET",
        }).then((response) => response.json()).then((data) => {
            setMediaCollectorHigh(data);
        }).catch((error) => console.log(error));
        setHasLoaded(true);

        if(e){
            setFilterMode(e.target.getAttribute("data-filter"));
            if(e.target.getAttribute("data-filter")){
                setHasLoaded(false);
                // setHasFilterLoaded(true);

                fetch("/wp-json/mediacleaner/v1/mediacollector/small", {
                    method: "GET",
                }).then((response) => response.json()).then((data) => {
                    setMediaCollectorLow(data);                
                }).catch((error) => console.log(error));
    
                fetch("/wp-json/mediacleaner/v1/mediacollector/large", {
                    method: "GET",
                }).then((response) => response.json()).then((data) => {
                    setMediaCollectorHigh(data);
                }).catch((error) => console.log(error));
    
                setHasLoaded(true);
            }
        }
    }





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



    const MediaCollectorTable = ( props ) => {

        if(mediaCollector){
            const urlParams = new URLSearchParams(window.location.search);
            const rbp_media_cleaner_media_data_page = filterPager;
            let page_counter = 20;
            let page_counter_offset = page_counter*rbp_media_cleaner_media_data_page;
            let output = mediaCollector.slice(page_counter_offset, -(mediaCollector.length - (page_counter_offset + 20)));


            console.log('filterMode');
            console.log(filterMode);
            console.log(mediaCollectorHigh);
            if(filterMode == 'high'){
                if(mediaCollectorHigh){
                    console.log(filterMode);

                    output = mediaCollectorHigh.slice(page_counter_offset, -(mediaCollectorHigh.length - (page_counter_offset + 20)));
                }
            } else if(filterMode == 'low'){
                if(mediaCollectorLow){
                    output = mediaCollectorLow.slice(page_counter_offset, -(mediaCollectorLow.length - (page_counter_offset + 20)));
                }
            }

            const FilterNav = ( props ) => {
                return (
                    <div className='filter-nav'>
                        <button type="button" title="Filter All" onClick={filter_size} data-filter="all" className={'filter-nav__button filter-nav__button--' + (filterMode == 'all' ? 'active' : 'inactive')}>Filter All</button>
                        <button type="button" title="Filter High" onClick={filter_size} data-filter="high" className={'filter-nav__button filter-nav__button--' + (filterMode == 'high' ? 'active' : 'inactive')}>Filter High</button>
                        <button type="button" title="Filter Low" onClick={filter_size} data-filter="low" className={'filter-nav__button filter-nav__button--' + (filterMode == 'low' ? 'active' : 'inactive')}>Filter Low</button> 
                    </div>
                )
            }
            const PagerNav = ( props ) => {
                const pager = (e) => {
                    if(e.target.getAttribute("data-pager") == 'next'){
                        setFilterPager(Number(filterPager)+1)
                    }
                    if(e.target.getAttribute("data-pager") == 'previous'){
                        setFilterPager(Number(filterPager)-1)
                    }
                }
                if(props.pager == '0'){
                    return (
                        <div className='filter-nav filter-nav--no-space-top'>
                            <button className='filter-nav__button' onClick={pager} data-pager="next">Next Page</button>
                        </div>
                    );  
                } else if(Number(props.pager)+2 <= Math.floor(mediaCollector.length/page_counter)){
                    return (
                        <div className='filter-nav filter-nav--no-space-top'>
                            <button className='filter-nav__button' onClick={pager} data-pager="previous">Previous Page</button>
                            <button className='filter-nav__button' onClick={pager} data-pager="next">Next Page</button>
                        </div>
                    );  
                } else {
                    return (
                        <> 
                            <button onClick={pager} data-pager="previous">Previous Page</button>
                        </>
                    ); 
                }
            }
            const mediaCollectorItems = output.map((collector) =>
                <tr className='media-collector-table__tr' data-media-id={collector['id']} key={collector['id']}>
                    <td className='media-collector-table__td'>Trash</td>
                    <td className="media-collector-table__td media-collector-table__td--img-thumb">{parse(collector['img-thumb'])}</td>
                    <td className='media-collector-table__td file-type'>{collector['media_file_type']}</td>
                    <td className='media-collector-table__td file-size'>{collector['media_size']}</td>
                    <td className='media-collector-table__td'>{collector['id']}</td>
                    <td className='media-collector-table__td'> 
                        <a target="_blank" href={`/wp-admin/post.php?post=${collector['id']}&action=edit`}>Edit</a>
                    </td>
                    <td className='media-collector-table__td media-collector-table__td--img-url'>{collector['media_file']}</td>
                    <td className='media-collector-table__td media-collector-table__td--preserve'>
                        <button onClick={activatePreserve} data-preserve-media={collector['id']}>Preserve Row</button>
                    </td>
                </tr>
            );
            return (
                <>
                    <FilterNav filterType={filterMode} />
                    <PagerNav pager={rbp_media_cleaner_media_data_page} />

                    <table className='media-collector-table'>
                        <tbody className='media-collector-table__tbody'>
                            <tr className='media-collector-table__tr'>
                                <th className='media-collector-table__th'>Trash</th>
                                <th className='media-collector-table__th media-collector-table__th--img-thumb'>Thumbnail Image</th>
                                <th className='media-collector-table__th'>File Type</th>
                                <th className='media-collector-table__th'>File Size</th>
                                <th className='media-collector-table__th'>Image ID</th>
                                <th className='media-collector-table__th'>Image Edit</th>
                                <th className='media-collector-table__th media-collector-table__th--img-url'>Image Url</th>
                                <th className='media-collector-table__th media-collector-table__th--preserve'>Temporarily Preserve Image <br></br> <sup>Clicking the button will not delete the image it will just exclude the selected image from the media list temporarily.</sup></th>
                            </tr>
                            {mediaCollectorItems}
                        </tbody>
                    </table>
                </>
            )
        }
    }




    const PreservedMediaCollectorTable = ( props ) => {

        if(mediaCollectorPreserved){
            // We might make pagination for the 
            // const urlParams = new URLSearchParams(window.location.search);
            // const rbp_media_cleaner_media_data_page = filterPager;
            // let page_counter = 20;
            // let page_counter_offset = page_counter*rbp_media_cleaner_media_data_page;
            // let output = mediaCollectorPreserved.slice(page_counter_offset, -(mediaCollectorPreserved.length - (page_counter_offset + 20)));

            let output = mediaCollectorPreserved;
            console.log(mediaCollectorPreserved);

            const mediaCollectorItems = output.map((collector) =>
                <tr className='media-collector-table__tr' data-media-id={collector['id']} key={collector['id']}>
                    <td className='media-collector-table__td'>Trash</td>
                    <td  className="media-collector-table__td media-collector-table__td--img-thumb">{ (collector['img-thumb']) ? parse(collector['img-thumb']) : 'No Image Found'}</td>
                    <td className='media-collector-table__td file-type'>{collector['media_file_type']}</td>
                    <td className='media-collector-table__td file-size'>{collector['media_size']}</td>
                    <td className='media-collector-table__td'>{collector['id']}</td>
                    <td className='media-collector-table__td'> 
                        <a target="_blank" href={`/wp-admin/post.php?post=${collector['id']}&action=edit`}>Edit</a>
                    </td>
                    <td className='media-collector-table__td media-collector-table__td--img-url'>{collector['media_file']}</td>
                    <td className='media-collector-table__td media-collector-table__td--preserve'>
                        <button onClick={activatePreserve} data-unpreserve-media={collector['id']}>Unpreserve Row</button>
                    </td>
                </tr>
            );
            return (
                <>
                    <table className='media-collector-table'>
                        <tbody className='media-collector-table__tbody'>
                            <tr className='media-collector-table__tr'>
                                <th className='media-collector-table__th'>Trash</th>
                                <th className='media-collector-table__th media-collector-table__th--img-thumb'>Thumbnail Image</th>
                                <th className='media-collector-table__th'>File Type</th>
                                <th className='media-collector-table__th'>File Size</th>
                                <th className='media-collector-table__th'>Image ID</th>
                                <th className='media-collector-table__th'>Image Edit</th>
                                <th className='media-collector-table__th media-collector-table__th--img-url'>Image Url</th>
                                <th className='media-collector-table__th media-collector-table__th--preserve'>Temporarily Preserve Image <br></br> <sup>Clicking the button will not delete the image it will just exclude the selected image from the media list temporarily.</sup></th>
                            </tr>
                            {mediaCollectorItems}
                        </tbody>
                    </table>
                </>
            )
        }

    }
    
    if(hasLoaded){
        return (
            <>
                <div className="message"> </div>
                <MediaCollectorTable filter={filterMode} />
                <h1>Preserved Images</h1>
                <PreservedMediaCollectorTable filter={filterMode} />
            </>
        );
    } else {
        return 'Loading...';
    }
  };
  
  export default MediaCollector;