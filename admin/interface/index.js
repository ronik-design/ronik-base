import React from 'react';
import { createRoot } from 'react-dom/client';

// Import screen components
import LoadSupportScreen from './templates/support.js';
import LoadIntegrationsScreen from './templates/integrations.js';
import LoadSettingsScreen from './templates/settings.js';
import LoadSettingsScreenMediaCleaner from './templates/media_cleaner_settings.js';
import LoadGeneralScreen from './templates/general.js';
import LoadMediaCleanerScreen from './templates/media_cleaner.js';
import LoadMediaCleanerSupportScreen from './templates/support.js';

// Function to render a component into a DOM node
const renderComponent = (selector, Component) => {
    const domNode = document.querySelector(selector);
    if (domNode) {
        const root = createRoot(domNode);
        root.render(<Component />);
    }
};

// Wait for the DOM to be fully loaded
document.addEventListener('readystatechange', (event) => {
    if (event.target.readyState === "interactive") {
        // Render components based on the presence of specific selectors
        renderComponent('#ronik-base_support', LoadSupportScreen);
        renderComponent('#ronik-base_integrations', LoadIntegrationsScreen);
        renderComponent('#ronik-base_settings', LoadSettingsScreen);
        renderComponent('#ronik-base_settings-media-cleaner', LoadSettingsScreenMediaCleaner);
        renderComponent('#ronik-base_support-media-cleaner', LoadMediaCleanerSupportScreen);
        renderComponent('#ronik-base_general', LoadGeneralScreen);
        renderComponent('#ronik-base_media_cleaner', LoadMediaCleanerScreen);
    }
});
