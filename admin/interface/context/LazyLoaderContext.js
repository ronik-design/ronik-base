import React, { createContext, useContext, useEffect } from 'react';

// Create the context
const LazyLoaderContext = createContext();

// Create a provider component
export const LazyLoaderProvider = ({ children }) => {
    // Define the lazyLoader function
    const lazyLoader = () => {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const lazyImage = entry.target;
                    const { src, type } = lazyImage.dataset;
                    const imageSelector = document.querySelector(`[data-id="${lazyImage.getAttribute('data-id')}"]`);

                    // Load image and process it
                    fetch(src)
                        .then(response => response.blob())
                        .then(blob => new Promise((resolve, reject) => {
                            const img = new Image();
                            img.crossOrigin = ''; // Handle CORS if from different origin
                            img.src = src;
                            img.onload = () => {
                                const canvas = document.createElement('canvas');
                                canvas.width = img.naturalWidth;
                                canvas.height = img.naturalHeight;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0);
                                canvas.toBlob(resolve, type, 0.5);
                            };
                            img.onerror = reject;
                        }))
                        .then(blob => {
                            if (imageSelector) {
                                imageSelector.src = URL.createObjectURL(blob);
                            }
                            lazyImage.classList.add('reveal-enabled');
                        })
                        .catch(error => console.error('Image processing error:', error));
                }
            });
        });

        document.querySelectorAll('img.lzy_img').forEach(img => imageObserver.observe(img));
    };

    // Optionally, run the lazyLoader function when the provider mounts
    useEffect(() => {
        lazyLoader();
    }, []);

    return (
        <LazyLoaderContext.Provider value={{ lazyLoader }}>
            {children}
        </LazyLoaderContext.Provider>
    );
};

// Custom hook for using the context
export const useLazyLoader = () => useContext(LazyLoaderContext);
