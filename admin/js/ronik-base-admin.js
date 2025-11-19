(function ($) {
    'use strict';
    // Initialize a counter variable
    let progressCounter = 0;
    let progressTimerCounter = 0;
    let intervalTime = 10000;

    // Function to initialize media progress
    function initRonikMediaProgress(destination) {
        // Increment the counter
        progressCounter++;
        progressTimerCounter++;

        let clearOut = 1;
        let safeGuard = 5;
        if (destination !== 'media-cleaner') {
            clearOut = 5;
            safeGuard = 20;
        }


        // Time is lame so we convert milliseconds to seconds.
        // This helps with the intervall counter to make essentially a countdown clock.
        function timeAdjust(counter) {
            return (counter * intervalTime) * 0.001;
        }

        // Depending on the progress, we want to clear the console to free memory up. If end user console log large amount of data.
        // console.log("initRonikMediaProgress count:", progressCounter);
        if (timeAdjust(progressCounter) > 60 * clearOut) {
            // Clear the console
            console.clear();
            // Reset the counter to 0.
            progressCounter = 0;
        }
        // Pretty much this is a safe guard we simply reload the entire page incase things are acting wacky or slow.
        if (timeAdjust(progressTimerCounter) > 60 * safeGuard) {
            // alert('RONIK TEST progressTimerCounter safeGuard');
            // window.location.reload(true);
        }


        $.ajax({
            type: 'post',
            url: wpVars.ajaxURL,
            data: {
                action: 'api_checkpoint',
                nonce: wpVars.nonce,
                media_progress: 'checker_run'
            },
            dataType: 'json',
            success: function (data) {
                const loaderElement = document.querySelector('.progress-bar');
                if (loaderElement) {
                    if (data.data === 'COMPLETED') {
                        setTimeout(() => {
                            // alert('RONIK TEST 2 COMPLETED');

                            // alert("CCCCCC.");
                            window.location.reload(true);
                        }, 500);
                    } else {
                        if (data.data === 'SEMI_SUCCESS') {
                            loaderElement.innerHTML = `Progress Status: Done`;
                            if (destination !== 'media-cleaner') {
                                var element = document.getElementById("wp-admin-bar-rmc");
                                // Change the color
                                element.style.color = "grey"; // Replace "red" with your desired color
                                // Append text
                                element.textContent = "Media Harmony - Progress Status: Done"; // Replace "New Text" with the text you want to add
                            } else {
                                loaderElement.innerHTML = `Progress Status: Done`;
                            }

                        } else {
                            if (data.data === 'NOT_RUNNING') {
                                // alert('Not Running');
                                // alert('RONIK TEST 3 NOT_RUNNING');
                                // window.location.reload(true);
                            } else {
                                if (destination !== 'media-cleaner') {
                                    var element = document.getElementById("wp-admin-bar-rmc");
                                    // Change the color
                                    element.style.color = "grey"; // Replace "red" with your desired color
                                    // Append text
                                    element.textContent = "Media Harmony - Sync In Progress | " + (data.data ? `Progress Status: ${data.data}` : "Progress Status: 0%");
                                } else {
                                    loaderElement.innerHTML = data.data ? `Progress Status: ${data.data}` : 'Progress Status: 0%';
                                }

                            }
                        }
                    }
                } else {
                    if (!data.data) {
                    } else {
                        if (data.data === 'COMPLETED' || data.data === 'SEMI_SUCCESS') {
                            // Update admin bar to show Done only if element is strictly active (not nonactive)
                            var element = document.getElementById("wp-admin-bar-rmc");
                            if (
                                element &&
                                element.className.includes('active') &&
                                !element.className.includes('nonactive')
                            ) {
                                element.style.color = "grey";
                                element.textContent = "Media Harmony - Progress Status: Done";
                                // alert('RONIK TEST 4 wp-admin-bar-rmc');
                                setTimeout(() => window.location.reload(true), 500);
                            }
                        } else if (data.data !== 'NOT_RUNNING') {
                            // console.log('.progress-bar not present');

                            if (destination !== 'media-cleaner') {
                                var element = document.getElementById("wp-admin-bar-rmc");
                                // Change the color
                                element.style.color = "grey";
                                // Append text
                                element.textContent = "Media Harmony - Sync In Progress | " + (data.data ? `Progress Status: ${data.data}` : "Progress Status: 0%");

                                setTimeout(() => {
                                    if (data.data == '99%') {
                                        // alert('RONIK TEST 5 wp-admin-bar-rmc');
                                        location.reload();
                                    }
                                }, 100000);
                            } else {
                                var element = document.getElementById("wpwrap");
                                element.classList.add("active-loader");
                                const f_wpwrap = document.querySelector("#wpwrap");
                                const f_wpcontent = document.querySelector("#wpcontent");
                                if (f_wpwrap) {
                                    f_wpwrap.classList.add('loader');
                                    f_wpcontent.insertAdjacentHTML('beforebegin', `
                                            <div class="progress-bar"></div>
                                            <div class="centered-blob">
                                                <div class="blob-1"></div>
                                                <div class="blob-2"></div>
                                            </div>
                                            <div class="page-counter">Please do not refresh the page!</div>
                                        `);
                                }


                            }
                        }
                    }
                }
            },
            error: function (err) {
                console.error('Error fetching media progress:', err);
            }
        });
    }

    // Function to determine API key validity
    async function initApiKeyDeterminism(pluginSlug, key) {
        if (wpVars.betaMode) {
            // console.log('initApiKeyDeterminism Beta Mode Enabled');
            return;
        }

        const endpoint = "https://ronikmarketstg.wpenginepowered.com";

        const websiteID = JSON.stringify(window.location.hostname);
        try {
            const response = await fetch(`${endpoint}/wp-json/apikey/v1/data/apikey?pluginSlug=${pluginSlug}&key=${key}&websiteID=${websiteID}`);
            const data = await response.json();
            if (data === 'Success') {
                // console.log('Correct API Key');
            } else {
                setTimeout(() => {
                    // console.log("Delayed");
                    initRonikDeterminism(pluginSlug, 'invalidate');
                }, intervalTime * 2);
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
            success: function (data) {
                if (data.data === 'Reload') {
                    alert('Yikes, looks like this license key is invalid and may be expired.');
                    setTimeout(() => window.location.reload(true), 500);
                } else if (data.data !== 'noreload') {
                    // console.log('Success:', data);
                    initApiKeyDeterminism(pluginSlug, data.data);
                }
            },
            error: function (err) {
                console.error('Error initializing Ronik determinism:', err);
            }
        });
    }

    // Function to start validation and progress checking
    function ronikbasePingValidator() {
        // console.log('Ping validator started');
        if (!wpVars.betaMode) {
            function pingValidator() {
                setTimeout(() => {
                    initRonikDeterminism('ronik_media_cleaner', 'valid');
                }, 400);
            }
            setInterval(pingValidator, intervalTime);
        } else {
            // console.log('Beta Mode Enabled');
        }

        function pingMediaProgressValidator(destination) {
            setTimeout(() => {
                initRonikMediaProgress(destination);
                // console.log("Delayed for 1 second.");
            }, 400);
        }
        if (window.location.href.includes("options-ronik-base_media_cleaner")) {
            setInterval(() => pingMediaProgressValidator('media-cleaner'), intervalTime / 2);
        } else if (window.location.href.includes("/wp-admin/")) {
            setInterval(() => pingMediaProgressValidator('wp-admin'), intervalTime / 2);
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
        }).appendTo($('#page_media_cleaner_field')).on('click', function () {
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
                success: function (data) {
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
                error: function (err) {
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
        }).appendTo($('#page_media_cleaner_field')).on('click', function () {
            $('.wp-core-ui').css({ 'pointer-events': 'none', 'opacity': 0.5 });

            $.ajax({
                type: 'post',
                url: wpVars.ajaxURL,
                data: {
                    action: 'do_init_remove_unused_media',
                    nonce: wpVars.nonce
                },
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        alert('Data processing. Page will reload after processing! Please do not reload!');
                        setTimeout(() => window.location.reload(true), 500);
                    } else {
                        alert('Whoops! Something went wrong! Please try again later!');
                        setTimeout(() => window.location.reload(true), 500);
                    }
                    $('.wp-core-ui').css({ 'pointer-events': '', 'opacity': '' });
                },
                error: function (err) {
                    console.error('Error deleting unused media:', err);
                    $('.wp-core-ui').css({ 'pointer-events': '', 'opacity': '' });
                    alert('Whoops! Something went wrong! Please try again later!');
                    setTimeout(() => window.location.reload(true), 500);
                }
            });
        });
    }

    // Initialize functions once the window has loaded
    $(window).on('load', function () {
        setTimeout(() => {
            initUnusedMedia();
            deleteUnusedMedia();
            ronikbasePingValidator();
        }, 250);
    });

})(jQuery);
