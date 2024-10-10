import React, { useState, useEffect, useCallback } from 'react';
import MediaTable from './MediaTable';

const { FilterMedia, MediaCollectorTable, PreservedMediaCollectorTable } = MediaTable;

const MediaCollector = ({ type }) => {
    const [pageDetector, setPageDetector] = useState(getQueryParam('page', 'options-ronik-base_media_cleaner') == 'options-ronik-base_media_cleaner' ? 'mediacollector' : 'mediacollectorpreserved');
    let fileSize = 'large';
    // if(pageDetector == 'mediacollectorpreserved'){
    //     fileSize = 'all';
    // } 

    const [hasLoaded, setHasLoaded] = useState(false);
    const [mediaCollector, setMediaCollector] = useState(null);
    const [filterPager, setFilterPager] = useState(parseInt(getQueryParam('page_number', 0)));
    const [filterMode, setFilterMode] = useState(getQueryParam('filter_size', fileSize));

    const [filterType, setFilterType] = useState(getQueryParam('filter_type', 'all'));
    const [mediaCollectorLow, setMediaCollectorLow] = useState(null);
    const [mediaCollectorHigh, setMediaCollectorHigh] = useState(null);
    const [unPreserveImageId, setUnPreserveImageId] = useState([]);
    const [preserveImageId, setPreserveImageId] = useState([]);
    const [deleteImageId, setDeleteImageId] = useState(null);
    const [selectedDataFormValues, setSelectedDataFormValues] = useState(['all']);
    const [selectedFormValues, setSelectedFormValues] = useState([{ value: 'all', label: 'All' }]);
    const [mediaCollectorPreserved, setMediaCollectorPreserved] = useState(null);
    // const { lazyLoader } = useLazyLoader();

    // Utility function to get query parameters
    function getQueryParam(param, defaultValue) {
        const params = new URLSearchParams(window.location.search);
        return params.get(param) || defaultValue;
    }


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


    // Effect to handle image deletion
    useEffect(() => {
        if (deleteImageId) {
            handlePostDataDelete(deleteImageId);
        }
    }, [deleteImageId]);

    // Effect to handle preserving images
    useEffect(() => {
        if (preserveImageId.length > 0) {
            handlePostDataPreserve(preserveImageId, 'invalid');
        }
    }, [preserveImageId]);

    // Effect to handle un-preserving images and fetching preserved media
    useEffect(() => {
        if (unPreserveImageId.length > 0) {
            handlePostDataPreserve('invalid', unPreserveImageId);
        }
        fetchPreservedMedia();
        lazyLoader();
    }, [unPreserveImageId]);

    // Fetch preserved media and update the loading state
    const fetchPreservedMedia = useCallback(() => {
        setHasLoaded(false);
        fetch("/wp-json/mediacleaner/v1/mediacollector/tempsaved")
            .then(response => response.json())
            .then(data => {
                if (data.length) {
                    setMediaCollectorPreserved(data);
                }
                // Ensure loader is removed after data is fetched
                setHasLoaded(true);
                removeLoader();
            })
            .catch(error => {
                console.error('Error fetching preserved media:', error);
                setHasLoaded(true);
                removeLoader();
            });
    }, []);

    // Effect to fetch media collector data based on filters
    useEffect(() => {
        setHasLoaded(false);
        const route = selectedDataFormValues.includes("all") ? 'all' : selectedDataFormValues.join('?');
        const endpoint = filterMode ? `${filterMode}?filter=${route}` : `all?filter=${route}`;
        // alert(pageDetector);
        fetch(`/wp-json/mediacleaner/v1/mediacollector/${endpoint}`)
            .then(response => response.json())
            .then(data => {
                if (data.length) {

                    setMediaCollectorPreserved(data)
                    setMediaCollector(data);
                    setTimeout(() => {
                        setHasLoaded(true); // Simulate delay
                        removeLoader();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error fetching media collector data:', error);
                setHasLoaded(true);
                removeLoader();
            });
    }, [selectedDataFormValues, filterMode]);

    // Effect to update URL based on filter mode
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        params.set('filter_size', filterMode);
        params.set('page_number', getQueryParam('page_number', 0));

        const newURL = new URL(window.location.href);
        newURL.search = params.toString();
        window.history.pushState({ path: newURL.href }, '', newURL.href);
    }, [filterMode]);

    // Effect to update URL based on filter pager
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        params.set('page_number', filterPager);

        const newURL = new URL(window.location.href);
        newURL.search = params.toString();
        window.history.pushState({ path: newURL.href }, '', newURL.href);
    }, [filterPager]);

    // Function to handle filter size changes
    const filter_size = useCallback(async (e) => {
        setHasLoaded(false);        
        const filter = e.target.getAttribute("data-filter");
        if (filter) {
            setFilterMode(filter);
            const route = filter === 'high' ? 'large' : 'small';
            const endpoint = `/wp-json/mediacleaner/v1/mediacollector/${route}?filter=${selectedDataFormValues.join('?')}`;

            fetch(endpoint)
                .then(response => response.json())
                .then(data => {
                    if (data.length) {
                        filter === 'high' ? setMediaCollectorHigh(data) : setMediaCollectorLow(data);
                    }
                })
                .catch(error => console.error(`Error fetching ${filter} media:`, error))
                .finally(() => {
                    setHasLoaded(true);
                    removeLoader();
                });
        }
    }, [selectedDataFormValues]);

    // Function to handle post data deletion
    const handlePostDataDelete = async (imageId) => {
        const data = new FormData();
        data.append('action', 'rmc_ajax_media_cleaner');
        data.append('nonce', wpVars.nonce);
        data.append('post_overide', "media-delete-indiv");
        data.append('imageId', imageId);

        try {
            const response = await fetch(wpVars.ajaxURL, {
                method: "POST",
                credentials: 'same-origin',
                body: data
            });
            const result = await response.json();
            if (result?.data === 'Reload') {
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            console.error('[WP Pageviews Plugin]', error);
        }
    };

    // Function to handle post data preservation
    const handlePostDataPreserve = async (preserveImageId, unPreserveImageId) => {
        const data = new FormData();
        data.append('action', 'rmc_ajax_media_cleaner');
        data.append('nonce', wpVars.nonce);
        data.append('post_overide', "media-preserve");
        data.append('preserveImageId', preserveImageId);
        data.append('unPreserveImageId', unPreserveImageId);

        try {
            const response = await fetch(wpVars.ajaxURL, {
                method: "POST",
                credentials: 'same-origin',
                body: data
            });
            const result = await response.json();
            if (result?.data === 'Reload') {

                // alert('preserveImageId' + preserveImageId);
                // alert('unPreserveImageId' + unPreserveImageId);
                let $res_message,$res_url;

                if(preserveImageId !== 'invalid'){
                    $res_message = "Media is preserved. Would you like to view the preserved content?";
                    $res_url = '/wp-admin/admin.php?page=options-ronik-base_preserved&filter_size=large&page_number=0&media_id='+preserveImageId;
                }
                if(unPreserveImageId !== 'invalid'){
                    $res_message =  "Media is unpreserved. Would you like to view the unpreserved content?";
                    $res_url = '/wp-admin/admin.php?page=options-ronik-base_media_cleaner&filter_size=large&page_number=0&media_id='+unPreserveImageId;
                }




                if (confirm($res_message)) {
                    // User clicked OK
                    // Redirect to the specified URL
                    setTimeout(() => {
                        window.location.href = $res_url;
                    }, 50); // Add a slight delay (100ms)

                } else {
                    // User clicked Cancel
                    // Do nothing or perform an alternative action
                    setTimeout(() => location.reload(), 50);
                }




                
            }
        } catch (error) {
            console.error('[WP Pageviews Plugin handlePostDataPreserve]', error);
        }
    };

    // Helper function to remove loader class and HTML
    const removeLoader = () => {
        const wpwrap = document.querySelector("#wpwrap");
        const centeredBlob = document.querySelector(".centered-blob");

        // For ping validator we need to add a class to the wpwrap to ensure user cant click call.
        if(!wpwrap.classList.contains('active-loader')){
            if (wpwrap) {
                wpwrap.classList.remove('loader');
            }
            if (centeredBlob) {
                centeredBlob.remove();
            }
        }
    };

    // Define activation functions
    const activatePreserve = (e) => {     
        e.stopPropagation(); // Prevents event bubbling, if necessary
   
        const target = e.target;
        const mediaId = target.getAttribute("data-preserve-media");
        if (mediaId) {
            alert('Media is preserved!');
            setPreserveImageId([mediaId]);
        } else {
            alert('Media is unpreserved!');
            setUnPreserveImageId([target.getAttribute("data-unpreserve-media")]);
        }
        // Find the closest <tr> element
        const row = target.closest('tr');

        if (row) {
            // Remove the row if found
            row.remove();
        } else {
            console.error('No <tr> ancestor found.');
        }
    };


    const activateDelete = (e) => {
        const target = e.target;
        const mediaId = target.getAttribute("data-delete-media") || target.closest('tr').getAttribute("data-media-id");
        if (mediaId) {
            if (confirm("Are you sure you want to continue?")) {
                setDeleteImageId(mediaId);
            }
        }
    };

    // Render component
    if (!hasLoaded) {
        return 'Loading...';
    }

// Function to get the value of a query parameter from the URL
function getQueryParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
  }
  

    // Function to scroll to the element with matching data-media-id
    function scrollToMediaItem(mediaId, offset = 0) {
        if (!mediaId) return; // Exit if no mediaId is provided
    
        // Find the element with the matching data-media-id attribute
        const element = document.querySelector(`tr[data-media-id="${mediaId}"]`);
    
        if (element) {
            element.classList.add('highlighted'); // Replace 'highlighted' with your desired class name

            // Calculate the position to scroll to, accounting for the offset
            const elementPosition = element.getBoundingClientRect().top + window.scrollY;
            const scrollToPosition = elementPosition - offset;
    
            // Smoothly scroll to the calculated position
            window.scrollTo({
                top: scrollToPosition,
                behavior: 'smooth'
            });
        } else {
            console.log(`Element with data-media-id="${mediaId}" not found.`);
        }
    }
        // Specify an offset value (e.g., 100 pixels)
        const offsetValue = 100;
        // Get the 'media_id' parameter value from the URL
        const mediaId = getQueryParameter('media_id');




        // Set a delay (e.g., 500 milliseconds) before calling the scrollToMediaItem function
        setTimeout(() => {
            scrollToMediaItem(mediaId, offsetValue);
        }, 500);



    return (
        <>
            <div className="message"> </div>
            {/* <FilterMedia
                selectedFormValues={selectedFormValues}
                setFilterMode={setFilterMode}
                setSelectedFormValues={setSelectedFormValues}
                setSelectedDataFormValues={setSelectedDataFormValues}
            /> */}
            <MediaCollectorTable
                type={type}
                mediaCollector={mediaCollector}
                selectedFormValues={selectedFormValues}
                filterMode={filterMode}
                setFilterMode={setFilterMode}
                setSelectedFormValues={setSelectedFormValues}
                setSelectedDataFormValues={setSelectedDataFormValues}
                setFilterPager={setFilterPager}
                setFilterType={setFilterType}
                filter_size={filter_size}
                filterPager={filterPager}
                filter={filterMode}
                filterType={filterType}
                mediaCollectorHigh={mediaCollectorHigh}
                mediaCollectorLow={mediaCollectorLow}
                activateDelete={activateDelete}
                activatePreserve={activatePreserve}
            />
            <PreservedMediaCollectorTable
                type={type}
                mediaCollectorPreserved={mediaCollectorPreserved}
                activatePreserve={activatePreserve}
                setFilterMode={setFilterMode}
                selectedFormValues={selectedFormValues}
                filterMode={filterMode}
                setSelectedFormValues={setSelectedFormValues}
                setSelectedDataFormValues={setSelectedDataFormValues}
                setFilterPager={setFilterPager}
                setFilterType={setFilterType}
                filter_size={filter_size}
                filterPager={filterPager}
                filter={filterMode}
                filterType={filterType}
                mediaCollectorHigh={mediaCollectorHigh}
                mediaCollectorLow={mediaCollectorLow}
                activateDelete={activateDelete}

            />
        </>
    );
};
export default MediaCollector;
