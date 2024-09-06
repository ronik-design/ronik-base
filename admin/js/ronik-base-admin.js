(function($) {
    'use strict';

    // Function to initialize media progress
    function initRonikMediaProgress() {
        $.ajax({
            type: 'post',
            url: wpVars.ajaxURL,
            data: {
                action: 'api_checkpoint',
                nonce: wpVars.nonce,
                media_progress: 'checker_run'
            },
            dataType: 'json',
            success: function(data) {
                const loaderElement = document.querySelector('.progress-bar');
                if (loaderElement) {
                    console.log('loaderElement');
                    console.log(data);

                    if ( data.data === 'COMPLETED' ) {
                        setTimeout(() => {
                            console.log(data);
                            window.location.reload(true);
                        }, 500);
                    } else {
                        if(data.data === 'SEMI_SUCCESS'){
                            loaderElement.innerHTML = `Progress Status: Done`;
                        } else {
                            loaderElement.innerHTML = data.data ? `Progress Status: ${data.data}` : 'Progress Status: 0%';
                        }
                    }
                }
            },
            error: function(err) {
                console.error('Error fetching media progress:', err);
            }
        });
    }

    // Function to determine API key validity
    async function initApiKeyDeterminism(pluginSlug, key) {
        const endpoint = window.location.href.includes(".local/") 
            ? "https://ronik-marketing.local"
            : "https://ronikmarketstg.wpenginepowered.com";

        const websiteID = JSON.stringify(window.location.hostname);
        try {
            const response = await fetch(`${endpoint}/wp-json/apikey/v1/data/apikey?pluginSlug=${pluginSlug}&key=${key}&websiteID=${websiteID}`);
            const data = await response.json();
            if (data === 'Success') {
                console.log('Correct API Key');
            } else {
                initRonikDeterminism(pluginSlug, 'invalidate');
            }
        } catch (err) {
            console.error('Error validating API key:', err);
        }
    }

    // Function to initialize Ronik determinism
    function initRonikDeterminism(pluginSlug, validation, checkerRun = false) {
        $.ajax({
            type: 'post',
            url: wpVars.ajaxURL,
            data: {
                action: 'api_checkpoint',
                nonce: wpVars.nonce,
                plugin_slug: pluginSlug,
                api_key: pluginSlug,
                api_validation: validation,
                checker_run: checkerRun
            },
            dataType: 'json',
            success: function(data) {
                if (data.data === 'Reload') {
                    alert('Yikes, looks like your API key has expired!');
                    setTimeout(() => window.location.reload(true), 500);
                } else if (data.data !== 'noreload') {
                    console.log('Success:', data);
                    initApiKeyDeterminism(pluginSlug, data.data);
                }
            },
            error: function(err) {
                console.error('Error initializing Ronik determinism:', err);
            }
        });
    }

    // Function to start validation and progress checking
    function ronikbasePingValidator() {
        console.log('Ping validator started');
        function pingValidator() {
            initRonikDeterminism('ronik_media_cleaner', 'valid');
        }
        setInterval(pingValidator, 10000);

        if (window.location.href.includes("options-ronik-base_media_cleaner")) {
            function pingMediaProgressValidator() {
                initRonikMediaProgress();
            }
            setInterval(pingMediaProgressValidator, 3000);
        }
    }

    // Function to initialize unused media migration
    function initUnusedMedia() {
        if ($("#page_media_cleaner_field").length === 0) {
            return;
        }

        $('<span>', {
            class: 'page_unused_media__link',
            text: 'Init Unused Media Migration',
            css: {
                cursor: 'pointer',
                background: '#7210d4',
                border: 'none',
                padding: '10px',
                color: '#fff',
                borderRadius: '5px'
            }
        }).appendTo($('#page_media_cleaner_field')).on('click', function() {
            alert('Data is processing. Please do not reload!');
            $('.wp-core-ui').css({ 'pointer-events': 'none', 'opacity': 0.5 });

            $.ajax({
                type: 'post',
                url: wpVars.ajaxURL,
                data: {
                    action: 'do_init_unused_media_migration',
                    nonce: wpVars.nonce
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        alert('Data processing. Page will reload after processing! Please do not reload!');
                        setTimeout(() => window.location.reload(true), 500);
                    } else {
                        alert(data.data === 'No rows found!' 
                            ? 'Great News! No un-detached images were found. Please try again later!' 
                            : 'Whoops! Something went wrong! Please try again later!');
                        setTimeout(() => window.location.reload(true), 500);
                    }
                    $('.wp-core-ui').css({ 'pointer-events': '', 'opacity': '' });
                },
                error: function(err) {
                    console.error('Error initializing unused media migration:', err);
                    $('.wp-core-ui').css({ 'pointer-events': '', 'opacity': '' });
                    alert('Whoops! Something went wrong! Please try again later!');
                    setTimeout(() => window.location.reload(true), 500);
                }
            });
        });
    }

    // Function to delete unused media
    function deleteUnusedMedia() {
        if ($("#page_media_cleaner_field").length === 0) {
            return;
        }

        $('<span>', {
            class: 'page_delete_media__link',
            text: 'Delete Unused Media',
            css: {
                marginLeft: '10px',
                cursor: 'pointer',
                background: '#d4104e',
                border: 'none',
                padding: '10px',
                color: '#fff',
                borderRadius: '5px'
            }
        }).appendTo($('#page_media_cleaner_field')).on('click', function() {
            $('.wp-core-ui').css({ 'pointer-events': 'none', 'opacity': 0.5 });

            $.ajax({
                type: 'post',
                url: wpVars.ajaxURL,
                data: {
                    action: 'do_init_remove_unused_media',
                    nonce: wpVars.nonce
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        alert('Data processing. Page will reload after processing! Please do not reload!');
                        setTimeout(() => window.location.reload(true), 500);
                    } else {
                        alert('Whoops! Something went wrong! Please try again later!');
                        setTimeout(() => window.location.reload(true), 500);
                    }
                    $('.wp-core-ui').css({ 'pointer-events': '', 'opacity': '' });
                },
                error: function(err) {
                    console.error('Error deleting unused media:', err);
                    $('.wp-core-ui').css({ 'pointer-events': '', 'opacity': '' });
                    alert('Whoops! Something went wrong! Please try again later!');
                    setTimeout(() => window.location.reload(true), 500);
                }
            });
        });
    }

    // Initialize functions once the window has loaded
    $(window).on('load', function() {
        setTimeout(() => {
            initUnusedMedia();
            deleteUnusedMedia();
            ronikbasePingValidator();
        }, 250);
    });

})(jQuery);
