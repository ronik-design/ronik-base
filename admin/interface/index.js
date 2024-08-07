import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';

// Load Support Screen.
import LoadSupportScreen from './templates/support.js';
// Load Integrations Screen.
import LoadIntegrationsScreen from './templates/integrations.js';
// Load Settings Screen.
import LoadSettingsScreen from './templates/settings.js';
// Load Settings Screen.
import LoadSettingsScreenMediaCleaner from './templates/media_cleaner_settings.js';
// Load General Screen.
import LoadGeneralScreen from './templates/general.js';
// Load General Screen.
import LoadMediaCleanerScreen from './templates/media_cleaner.js';

// Due to the nature of WP we need to wait for the readystate.
document.addEventListener('readystatechange', event => { 
    // When HTML/DOM elements are ready:
    if (event.target.readyState === "interactive") {
        // Finally we check to see if the query selector is present.
            // Support screen
            if(document.querySelector("#ronik-base_support") !== null){
                const domNode = document.getElementById('ronik-base_support');
                const root = createRoot(domNode);
                root.render(<LoadSupportScreen />);
            }
            // Integrations screen
            if(document.querySelector("#ronik-base_integrations") !== null){
                const domNode = document.getElementById('ronik-base_integrations');
                const root = createRoot(domNode);
                root.render(<LoadIntegrationsScreen />);
            }
            // Settings screen
            if(document.querySelector("#ronik-base_settings") !== null){
                const domNode = document.getElementById('ronik-base_settings');
                const root = createRoot(domNode);
                root.render(<LoadSettingsScreen />);
            }

            // Settings screen
            if(document.querySelector("#ronik-base_settings-media-cleaner") !== null){
                const domNode = document.getElementById('ronik-base_settings-media-cleaner');
                const root = createRoot(domNode);
                root.render(<LoadSettingsScreenMediaCleaner />);
            }



            // General screen
            if(document.querySelector("#ronik-base_general") !== null){
                const domNode = document.getElementById('ronik-base_general');
                const root = createRoot(domNode);
                root.render(<LoadGeneralScreen />);
            }
       
            // General screen
            if(document.querySelector("#ronik-base_media_cleaner") !== null){
                const domNode = document.getElementById('ronik-base_media_cleaner');
                const root = createRoot(domNode);
                root.render(<LoadMediaCleanerScreen />);
            }

            

        
    }
});