import React, { useState, useEffect, useRef } from 'react';
import parse from 'html-react-parser';
import Select from 'react-select';

const MediaCollector = ({ items }) => {
    const [hasLoaded, setHasLoaded] = useState(false);
    const [mediaCollector, setMediaCollector] = useState(null);
    const urlParams = new URLSearchParams(window.location.search);
    const rbp_media_cleaner_media_data_page = urlParams.get('page_number') ? urlParams.get('page_number') : 0;
    const [filterPager, setFilterPager] = useState(rbp_media_cleaner_media_data_page);
    const rbp_media_cleaner_media_data_filter = urlParams.get('filter_size') ? urlParams.get('filter_size') : 'all';
    const [filterMode, setFilterMode] = useState(rbp_media_cleaner_media_data_filter);    
    const [filterType, setFilterType] = useState(urlParams.get('filter_type') ? urlParams.get('filter_type') : 'all');    
    const [mediaCollectorLow, setMediaCollectorLow] = useState(null);
    const [mediaCollectorHigh, setMediaCollectorHigh] = useState(null);
    const [unPreserveImageId, setUnImageId] = useState([]);
    const [preserveImageId, setImageId] = useState([]);
    const [deleteImageId, setDeleteImageId] = useState();
    const [selectedDataFormValues, setSelecDatatedFormValues] = useState(['all']);
    const [selectedFormValues, setSelectedFormValues] = useState([{ value: 'all', label: 'All' }]);

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


    const activateDelete = (e) => {
        const f_wpwrap = document.querySelector("#wpwrap");
        const f_wpcontent = document.querySelector("#wpcontent");
        f_wpwrap.classList.add('loader')
        f_wpcontent.insertAdjacentHTML('beforebegin', '<div class= "centered-blob"><div class= "blob-1"></div><div class= "blob-2"></div></div>');
        console.log(e.target.getAttribute("data-delete-media"));
        console.log(e.target.parentNode.parentNode.parentNode.getAttribute("data-media-id"));
        if(e.target.getAttribute("data-delete-media")){
            var MediaId = e.target.getAttribute("data-delete-media");
        } else if(e.target.parentNode.parentNode.parentNode.getAttribute("data-media-id")){
            var MediaId = e.target.parentNode.parentNode.parentNode.getAttribute("data-media-id");
        } else {
            f_wpwrap.classList.remove('loader')
            const element = document.getElementsByClassName("centered-blob");
            element[0].remove(); // Removes the div with the 'div-02' id
        }
        var userPreference;
        if (confirm("Are you sure you want to continue?") == true) {
            userPreference = "Data saved successfully!";
            setDeleteImageId(MediaId);
        } else {
            userPreference = "Save Canceled!";
            f_wpwrap.classList.remove('loader')
            const element = document.getElementsByClassName("centered-blob");
            element[0].remove(); // Removes the div with the 'div-02' id
        }
        return;  
    }



    const handlePostDataDelete = async ( imageId ) => {
        const data = new FormData();
            data.append( 'action', 'rmc_ajax_media_cleaner' );
            data.append( 'nonce', wpVars.nonce );
            data.append( 'post_overide',  "media-delete-indiv" );
            data.append( 'imageId',  imageId );
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

 
    
    useEffect(() => {
        if(deleteImageId){
            console.log('deleteImageId');
            console.log(deleteImageId);
            handlePostDataDelete( deleteImageId );
        }
    }, deleteImageId);

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
            if(data.length !== 0){
                setMediaCollectorPreserved(data);
            }
        }).catch((error) => console.log(error));
        setHasLoaded(true);
    }, [unPreserveImageId]);


    useEffect(() => {  
        setHasLoaded(false);
        var route = 'all';
        var $endpoint = "all?filter=all";

        if(!selectedDataFormValues.includes("all")){
            console.log('selectedDataFormValues');
            console.log(selectedDataFormValues);
            route = selectedDataFormValues.join('?');

            if(!filterMode){
                $endpoint = "all?filter="+route;

            } else {
                $endpoint = filterMode+"?filter="+route;                
            }
        }

        if(selectedDataFormValues.length == 0){
            $endpoint = "all?filter=all";
        }

        // fetch("/wp-json/mediacleaner/v1/mediacollector/all?filter="+route, {
        fetch("/wp-json/mediacleaner/v1/mediacollector/"+$endpoint, {
            method: "GET",
        }).then((response) => response.json()).then((data) => {
            if(data.length !== 0){
                setMediaCollector(data);

                setTimeout(() => {
                    console.log("Delayed for 1 second.");
                    setHasLoaded(true);
                }, 1000);
            }
        }).catch((error) => console.log(error));
    }, [selectedDataFormValues, filterMode]);


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
        // setHasLoaded(true);
        if(e){
            setFilterMode(e.target.getAttribute("data-filter"));
            if(e.target.getAttribute("data-filter")){
                setHasLoaded(false);

                if(e.target.getAttribute("data-filter") == 'high'){
                    var route = 'large';
                    if(!selectedDataFormValues.includes("large")){
                        route = selectedDataFormValues.join('?');
                    }
                    fetch("/wp-json/mediacleaner/v1/mediacollector/large?filter="+route, {
                    // fetch("/wp-json/mediacleaner/v1/mediacollector/large", {
                        method: "GET",
                    }).then((response) => response.json()).then((data) => {
                        if(data.length !== 0){                                                        
                            setMediaCollectorHigh(data);
                        }
                    }).catch((error) => console.log(error));
                } else {
                    var route = 'small';
                    if(!selectedDataFormValues.includes("small")){
                        route = selectedDataFormValues.join('?');
                    }
                    fetch("/wp-json/mediacleaner/v1/mediacollector/small?filter="+route, {
                    // fetch("/wp-json/mediacleaner/v1/mediacollector/small", {
                        method: "GET",
                    }).then((response) => response.json()).then((data) => {
                        if(data.length !== 0){                                    
                            setMediaCollectorLow(data);                
                        }
                    }).catch((error) => console.log(error));
                }

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
        console.log(mediaCollector);
        if(mediaCollector){
            const urlParams = new URLSearchParams(window.location.search);
            const rbp_media_cleaner_media_data_page = parseInt(filterPager);
            let page_counter = 20;
            let page_counter_offset = page_counter*rbp_media_cleaner_media_data_page;

            if(mediaCollector !== 'no-images'){
                // items per chunk 
                const perChunk = page_counter;   
                const inputArray = mediaCollector;
                const result = inputArray.reduce((resultArray, item, index) => { 
                const chunkIndex = Math.floor(index/perChunk)
                    if(!resultArray[chunkIndex]) {
                        resultArray[chunkIndex] = [] // start a new chunk
                    }
                    resultArray[chunkIndex].push(item)
                    return resultArray
                }, [])

                var output;
                output = result[rbp_media_cleaner_media_data_page];



                if(filterMode == 'high'){
                    if(mediaCollectorHigh){
                        const resultHigh = mediaCollectorHigh.reduce((resultArray, item, index) => { 
                        const chunkIndex = Math.floor(index/page_counter)
                            if(!resultArray[chunkIndex]) {
                                resultArray[chunkIndex] = [] // start a new chunk
                            }
                            resultArray[chunkIndex].push(item)
                            return resultArray
                        }, [])
                        output = resultHigh[rbp_media_cleaner_media_data_page];
                    }
                } else if(filterMode == 'low'){
                    if(mediaCollectorLow){
                        const resultLow = mediaCollectorLow.reduce((resultArray, item, index) => { 
                        const chunkIndex = Math.floor(index/page_counter)
                            if(!resultArray[chunkIndex]) {
                                resultArray[chunkIndex] = [] // start a new chunk
                            }
                            resultArray[chunkIndex].push(item)
                            return resultArray
                        }, [])
                        output = resultLow[rbp_media_cleaner_media_data_page];
                    }
                }
            }


            const options = [
                { value: 'all', label: 'All' },
                { value: 'jpg', label: 'JPG' },
                { value: 'gif', label: 'GIF' },
                { value: 'png', label: 'PNG' },
                { value: 'video', label: 'Video' },
                { value: 'misc', label: 'Misc' }
              ]

            const FilterType = ( props ) => {                                
                return (
                    <div className="select select-multiple">
                        <Select 
                            closeMenuOnSelect={false}
                            // components={animatedComponents}
                            defaultValue={selectedFormValues}
                            isMulti
                            options={options}   
                            onChange={e => {
                                setFilterMode('all');
                                const params = new URLSearchParams(window.location.search);
                                const paramsObj = Array.from(params.keys()).reduce(
                                  (acc, val) => ({ ...acc, [val]: params.get(val) }),
                                  {}
                                );
                                if (window.history.pushState) {       
                                    const newURL = new URL(window.location.href);       
                                    newURL.search = '?page=options-ronik-base_media_cleaner&page_number='+paramsObj['page_number'];        
                                    window.history.pushState({ path: newURL.href }, '', newURL.href); 
                                }




                                const newArr = e.map(myFunction);
                                function myFunction(num) {
                                    return num;
                                }
                                setSelectedFormValues(newArr);
                                const newDataArr = e.map(myDataFunction);
                                function myDataFunction(num) {
                                    return num['value'];
                                }
                                setSelecDatatedFormValues(newDataArr);
                            }}
                        />
                    </div>

        
                )
            }

            const FilterNav = ( props ) => {
                var mediaCollectorItemsCounterOverall = 0;
                for (let i = 0; i < mediaCollector.length; i++) {
                    var number = 0;
                    if( mediaCollector[i]['media_size'].includes("MB") ){
                        number = Number(mediaCollector[i]['media_size'].replace(" MB", ""));
                    } else if( mediaCollector[i]['media_size'].includes("KB") ){
                        number = Number(mediaCollector[i]['media_size'].replace(" KB", ""));
                        number = number / 1024;
                    }  else if( mediaCollector[i]['media_size'].includes("GB") ){
                        number = Number(mediaCollector[i]['media_size'].replace(" GB", ""));
                        number = number * 1000;
                    }  else if( mediaCollector[i]['media_size'].includes("bytes") ){
                        number = Number(mediaCollector[i]['media_size'].replace(" bytes", ""));
                        number = number * 1e+6;
                    } 

                    if( !isNaN(number)){

                        console.log(number);
                        mediaCollectorItemsCounterOverall += number; 
                    }                
                }




                return (
                    <>
                        <div className='filter-nav'>
                            <button type="button" title="Filter All" onClick={filter_size} data-filter="all" className={'filter-nav__button filter-nav__button--' + (filterMode == 'all' ? 'active' : 'inactive')}>Filter All</button>
                            <button type="button" title="Filter High" onClick={filter_size} data-filter="high" className={'filter-nav__button filter-nav__button--' + (filterMode == 'high' ? 'active' : 'inactive')}>Filter High</button>
                            <button type="button" title="Filter Low" onClick={filter_size} data-filter="low" className={'filter-nav__button filter-nav__button--' + (filterMode == 'low' ? 'active' : 'inactive')}>Filter Low</button> 
                        </div>
                        <span className="overall-number">Overall Unattached Media Found: {mediaCollector.length}</span>
                        <span className="overall-number">Overall Unattached Media File Size: { Math.round(mediaCollectorItemsCounterOverall * 100) / 100 } MB</span>

                        
                    </>
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
                    if(mediaCollector.length > 20){
                        return (
                            <div className='filter-nav filter-nav--no-space-top'>
                                <button className='filter-nav__button' onClick={pager} data-pager="next">Next Page</button>
                            </div>
                        );  
                    }               
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
            

            if(mediaCollector !== 'no-images'){
                var dataPluginName = document.querySelector('#ronik-base_media_cleaner').getAttribute("data-plugin-name");                
                const mediaCollectorItems = output.map((collector) =>
                    <>                    
                        <tr className='media-collector-table__tr' data-media-id={collector['id']} key={collector['id']}>
                            <td className='media-collector-table__td'>
                                <button onClick={activateDelete} data-delete-media={collector['id']}>
                                    <img src={`/wp-content/plugins/${dataPluginName}/admin/media-cleaner/image/big-trash-can.svg`}></img>
                                </button>
                            </td>
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
                    </>
                );

            return (
                <> 
                    <FilterType filterType={filterType} />
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
            } else {
                return(
                    <>                    
                        <FilterType filterType={filterType} />
                        <p>No Media Found!</p>
                    </>

                )
            }
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
    
    const f_wpwrap = document.querySelector("#wpwrap");
    const f_wpcontent = document.querySelector("#wpcontent");
    
    if(hasLoaded){
        f_wpwrap.classList.remove('loader')
        const element = document.getElementsByClassName("centered-blob");
        element[0].remove(); // Removes the div with the 'div-02' id
        
        return (
            <>
                <div className="message"> </div>
                <MediaCollectorTable filter={filterMode} />
                <h1>Preserved Images</h1>
                <PreservedMediaCollectorTable filter={filterMode} />
            </>
        );
    } else {
        f_wpwrap.classList.add('loader')
        f_wpcontent.insertAdjacentHTML('beforebegin', '<div class= "centered-blob"><div class= "blob-1"></div><div class= "blob-2"></div></div>');

        
        return 'Loading...';
    }
};
  
export default MediaCollector;