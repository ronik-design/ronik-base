import React, { useState, useEffect, useLayoutEffect, useCallback, useRef } from "react";
import MediaTable from "./MediaTable";
import useMediaCleanerStore from "./stores/mediaCleanerStore";


const { MediaCollectorTable, PreservedMediaCollectorTable } = MediaTable;

const MediaCollector = ({ type }) => {
  const fileSize = "large";
  const [hasLoaded, setHasLoaded] = useState(false);
  const [mediaCollector, setMediaCollector] = useState(null);
  const [filterPager, setFilterPager] = useState(
    parseInt(getQueryParam("page_number", 0))
  );
  const [filterMode, setFilterMode] = useState(
    getQueryParam("filter_size", fileSize)
  );
  const [mediaCollectorLow, setMediaCollectorLow] = useState(null);
  const [mediaCollectorHigh, setMediaCollectorHigh] = useState(null);
  const [unPreserveImageId, setUnPreserveImageId] = useState([]);
  const [preserveImageId, setPreserveImageId] = useState([]);
  const [deleteImageId, setDeleteImageId] = useState(null);
  const [mediaCollectorPreserved, setMediaCollectorPreserved] = useState(null);
  const filtersInitializedRef = useRef(false);

  // Use Zustand store for filters
  // const { selectedFilters } = useMediaCleanerStore();
  const { selectedFilters, setSelectedFilters, isScanning, scanInitiated, setScanInitiated, syncStatus, scanInitiatedType, setScanInitiatedType } = useMediaCleanerStore();

  // Initialize filters from URL on mount if not already set
  // Use useLayoutEffect to ensure this runs synchronously before other effects
  useLayoutEffect(() => {
    // Only initialize if filters are empty and URL has filter params
    if (!filtersInitializedRef.current && selectedFilters.length === 0) {
      const urlParams = new URLSearchParams(window.location.search);
      const filterParams = urlParams.getAll("filter");
      
      if (filterParams.length > 0) {
        // Valid filter options
        const validFilterOptions = ["jpg", "png", "video", "gif", "audio", "misc"];
        const validFilters = filterParams.filter((filter) =>
          validFilterOptions.includes(filter)
        );
        if (validFilters.length > 0) {
          setSelectedFilters(validFilters);
          filtersInitializedRef.current = true;
        }
      }
    }
    // Mark as initialized even if no URL params (to prevent re-checking)
    if (!filtersInitializedRef.current) {
      filtersInitializedRef.current = true;
    }
  }, [selectedFilters.length, setSelectedFilters]);

  // Convert store filters to the format expected by the component
  const selectedDataFormValues =
    selectedFilters.length > 0 ? selectedFilters : ["all"];

  // Utility function to get query parameters
  function getQueryParam(param, defaultValue) {
    const params = new URLSearchParams(window.location.search);
    return params.get(param) || defaultValue;
  }

  // Lazy load images as well as image compression
  function lazyLoader() {
    const imageObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const lazyImage = entry.target;

          // Skip if already processed
          if (lazyImage.classList.contains("reveal-enabled")) {
            return;
          }

          // console.log("Processing lazy image:", lazyImage.dataset.src);

          fetch(lazyImage.dataset.src)
            .then(() => {
              const imageSelector = document.querySelector(
                `[data-id="${lazyImage.getAttribute("data-id")}"]`
              );

              // Update class to indicate it's been processed
              lazyImage.className = lazyImage.className.replace(
                "reveal-disabled",
                "reveal-enabled"
              );

              const canvas = document.createElement("canvas");
              const ctx = canvas.getContext("2d");
              const img = new Image();
              img.crossOrigin = "";
              img.src = lazyImage.getAttribute("data-src");

              img.onload = function () {
                canvas.width = this.naturalWidth;
                canvas.height = this.naturalHeight;
                ctx.drawImage(this, 0, 0);
                canvas.toBlob(
                  function (blob) {
                    if (imageSelector) {
                      imageSelector.src = URL.createObjectURL(blob);
                      // console.log(
                      //   "✅ Lazy loaded image:",
                      //   lazyImage.dataset.src
                      // );
                    }
                  },
                  lazyImage.getAttribute("data-type"),
                  0.5
                );
              };

              img.onerror = function () {
                console.warn(
                  "❌ Failed to load lazy image:",
                  lazyImage.dataset.src
                );
                // Set corrupt thumbnail as fallback
                if (imageSelector) {
                  imageSelector.src = "/wp-content/plugins/ronik-base/admin/media-cleaner/image/thumb-corrupt-file.svg";
                  imageSelector.classList.add("reveal-enabled");
                }
              };
            })
            .catch((error) => {
              console.warn("❌ Fetch failed for lazy image:", error);
              // Set corrupt thumbnail as fallback on fetch error
              const imageSelector = document.querySelector(
                `[data-id="${lazyImage.getAttribute("data-id")}"]`
              );
              if (imageSelector) {
                imageSelector.src = "/wp-content/plugins/ronik-base/admin/media-cleaner/image/thumb-corrupt-file.svg";
                imageSelector.classList.add("reveal-enabled");
              }
            });
        }
      });
    });

    // Look for images with lzy_img class, including those with reveal-disabled
    const images = document.querySelectorAll(
      "img.lzy_img:not(.reveal-enabled)"
    );
    // console.log(`🔍 Found ${images.length} lazy images to observe`);

    // Debug: Let's see what images are actually in the DOM
    const allImages = document.querySelectorAll("img");
    const lzyImages = document.querySelectorAll("img.lzy_img");
    const revealDisabled = document.querySelectorAll("img.reveal-disabled");
    const revealEnabled = document.querySelectorAll("img.reveal-enabled");

    // console.log(`📊 Image Debug:
    // - Total images in DOM: ${allImages.length}
    // - Images with .lzy_img: ${lzyImages.length}
    // - Images with .reveal-disabled: ${revealDisabled.length}
    // - Images with .reveal-enabled: ${revealEnabled.length}`);

    // Show the first few images for debugging
    // if (allImages.length > 0) {
    //   console.log(
    //     "First 3 images in DOM:",
    //     Array.from(allImages)
    //       .slice(0, 3)
    //       .map((img) => ({
    //         src: img.src,
    //         dataSrc: img.dataset.src,
    //         className: img.className,
    //         id: img.dataset.id,
    //       }))
    //   );
    // }

    images.forEach((img) => imageObserver.observe(img));
  }

  // Run lazyLoader when scan is complete (scanInitiated becomes false)
  useEffect(() => {
    // Only trigger when scanInitiated is false (scan complete)
    if (!scanInitiated) {
      // Multiple attempts with increasing delays to catch images that load later
      const timer1 = setTimeout(() => {
        lazyLoader();
      }, 100);

      const timer2 = setTimeout(() => {
        lazyLoader();
      }, 500);

      const timer3 = setTimeout(() => {
        lazyLoader();
      }, 1000);

      const timer4 = setTimeout(() => {
        lazyLoader();
      }, 2000);

      const timer5 = setTimeout(() => {
        lazyLoader();
      }, 4000);

      const timer6 = setTimeout(() => {
        lazyLoader();
      }, 6000);

      const timer7 = setTimeout(() => {
        lazyLoader();
      }, 8000);

      const timer8 = setTimeout(() => {
        lazyLoader();
      }, 10000);

      const timer9 = setTimeout(() => {
        lazyLoader();
      }, 50000);

      return () => {
        clearTimeout(timer1);
        clearTimeout(timer2);
        clearTimeout(timer3);
        clearTimeout(timer4);
        clearTimeout(timer5);
        clearTimeout(timer6);
        clearTimeout(timer7);
        clearTimeout(timer8);
        clearTimeout(timer9);
      };
    }
  }, [scanInitiated, filterPager]);

  // Effect to handle image deletion
  useEffect(() => {
    if (deleteImageId) {
      handlePostDataDelete(deleteImageId);
    }
  }, [deleteImageId]);

  // Effect to handle preserving images
  useEffect(() => {
    if (preserveImageId.length > 0) {
      handlePostDataPreserve(preserveImageId, "invalid");
    }
  }, [preserveImageId]);

  // Effect to handle un-preserving images and fetching preserved media
  useEffect(() => {
    if (unPreserveImageId.length > 0) {
      handlePostDataPreserve("invalid", unPreserveImageId);
    }
    fetchPreservedMedia();
  }, [unPreserveImageId]);

  // Fetch preserved media and update the loading state
  const fetchPreservedMedia = useCallback(() => {
    setHasLoaded(false);
    fetch("/wp-json/mediacleaner/v1/mediacollector/tempsaved")
      .then((response) => response.json())
      .then((data) => {
        // Set data only if there is data
        if (data.length) {
          setMediaCollectorPreserved(data);
        }
        // ALWAYS set hasLoaded to true after fetch completes
        setHasLoaded(true);
        removeLoader();
      })
      .catch((error) => {
        console.error("Error fetching preserved media:", error);
        setHasLoaded(true);
        removeLoader();
      });
  }, []);

  // Effect to fetch media collector data based on filters
  useEffect(() => {
    // If filters are empty, check URL for filter params before fetching
    let filtersToUse = selectedFilters;
    if (filtersToUse.length === 0) {
      const urlParams = new URLSearchParams(window.location.search);
      const filterParams = urlParams.getAll("filter");
      if (filterParams.length > 0) {
        const validFilterOptions = ["jpg", "png", "video", "gif", "audio", "misc"];
        const validFilters = filterParams.filter((filter) =>
          validFilterOptions.includes(filter)
        );
        if (validFilters.length > 0) {
          filtersToUse = validFilters;
          // Update store for future renders
          setSelectedFilters(validFilters);
        }
      }
    }

    setScanInitiated(true);
    setScanInitiatedType("Loading Media in Progress");

    setHasLoaded(false);
    const route = filtersToUse.length > 0 && !filtersToUse.includes("all")
      ? filtersToUse.join("?")
      : "all";
    const endpoint = filterMode
      ? `${filterMode}?filter=${route}`
      : `all?filter=${route}`;

    fetch(`/wp-json/mediacleaner/v1/mediacollector/${endpoint}`)
      .then((response) => response.json())
      .then((data) => {
        // Set data only if there is data, but always set hasLoaded
        if (data.length) {
          setMediaCollectorPreserved(data);
          setMediaCollector(data);
        } else {
          // Set empty state to indicate no data found
          setMediaCollector("no-images");
        }

        // ALWAYS set hasLoaded to true after fetch completes
        setTimeout(() => {
          setScanInitiated(false);

          setHasLoaded(true);
          removeLoader();
        }, 0);
      })
      .catch((error) => {
        console.error("Error fetching media collector data:", error);
        setHasLoaded(true);
        removeLoader();
      });
  }, [selectedFilters, filterMode]);

  // Effect to update URL based on filter mode
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    params.set("filter_size", filterMode);
    params.set("page_number", getQueryParam("page_number", 0));

    const newURL = new URL(window.location.href);
    newURL.search = params.toString();
    window.history.pushState({ path: newURL.href }, "", newURL.href);
  }, [filterMode]);

  // Effect to update URL based on filter pager
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    params.set("page_number", filterPager);

    const newURL = new URL(window.location.href);
    newURL.search = params.toString();
    window.history.pushState({ path: newURL.href }, "", newURL.href);
  }, [filterPager]);

  // Function to handle filter size changes
  const filter_size = useCallback(
    async (e) => {
      setHasLoaded(false);
      const filter = e.target.getAttribute("data-filter");
      if (filter) {
        setFilterMode(filter);
        const route = filter === "large" ? "large" : "small";
        const endpoint = `/wp-json/mediacleaner/v1/mediacollector/${route}?filter=${selectedFilters.join(
          "?"
        )}`;

        // alert(endpoint);

        fetch(endpoint)
          .then((response) => response.json())
          .then((data) => {
            // Set data only if there is data
            if (data.length) {
              filter === "large"
                ? setMediaCollectorHigh(data)
                : setMediaCollectorLow(data);
            }
            // Note: hasLoaded is set in .finally() so it always triggers
          })
          .catch((error) =>
            console.error(`Error fetching ${filter} media:`, error)
          )
          .finally(() => {
            // ALWAYS set hasLoaded to true after fetch completes
            setHasLoaded(true);
            removeLoader();
          });
      }
    },
    [selectedFilters]
  );

  // Function to handle post data deletion
  const handlePostDataDelete = async (imageId) => {
    const data = new FormData();
    data.append("action", "rmc_ajax_media_cleaner");
    data.append("nonce", wpVars.nonce);
    data.append("post_overide", "media-delete-indiv");
    data.append("imageId", imageId);

    try {
      const response = await fetch(wpVars.ajaxURL, {
        method: "POST",
        credentials: "same-origin",
        body: data,
      });
      const result = await response.json();
      if (result?.data === "Reload") {
        setTimeout(() => location.reload(), 1000);
      }
    } catch (error) {
      console.error("[WP Pageviews Plugin]", error);
    }
  };

  // Function to handle post data preservation
  const handlePostDataPreserve = async (preserveImageId, unPreserveImageId) => {
    const data = new FormData();
    data.append("action", "rmc_ajax_media_cleaner");
    data.append("nonce", wpVars.nonce);
    data.append("post_overide", "media-preserve");
    data.append("preserveImageId", preserveImageId);
    data.append("unPreserveImageId", unPreserveImageId);

    try {
      const response = await fetch(wpVars.ajaxURL, {
        method: "POST",
        credentials: "same-origin",
        body: data,
      });
      const result = await response.json();
      if (result?.data === "Reload") {
        let resMessage, resUrl;

        if (preserveImageId !== "invalid") {
          resMessage =
            "Media is preserved. Would you like to view the preserved content?";
          resUrl = `/wp-admin/admin.php?page=options-ronik-base_preserved&filter_size=large&page_number=0&media_id=${preserveImageId}`;
        }
        if (unPreserveImageId !== "invalid") {
          resMessage =
            "Media is unpreserved. Would you like to view the unpreserved content?";
          resUrl = `/wp-admin/admin.php?page=options-ronik-base_media_cleaner&filter_size=large&page_number=0&media_id=${unPreserveImageId}`;
        }

        if (confirm(resMessage)) {
          setTimeout(() => {
            window.location.href = resUrl;
          }, 50);
        } else {
          setTimeout(() => location.reload(), 50);
        }
      }
    } catch (error) {
      console.error("[WP Pageviews Plugin handlePostDataPreserve]", error);
    }
  };

  // Helper function to remove loader class and HTML
  const removeLoader = () => {
    const wpwrap = document.querySelector("#wpwrap");
    const centeredBlob = document.querySelector(".centered-blob");

    // For ping validator we need to add a class to the wpwrap to ensure user can't click call.
    if (!wpwrap?.classList.contains("active-loader")) {
      wpwrap?.classList.remove("loader");
      centeredBlob?.remove();
    }
  };

  // Define activation functions
  const activatePreserve = (e) => {
    e.stopPropagation();

    const target = e.target;
    const mediaId = target.getAttribute("data-preserve-media");
    if (mediaId) {
      setPreserveImageId([mediaId]);
    } else {
      setUnPreserveImageId([target.getAttribute("data-unpreserve-media")]);
    }

    // Find the closest <tr> element and remove it
    const row = target.closest("tr");
    if (row) {
      row.remove();
    } else {
      console.error("No <tr> ancestor found.");
    }
  };

  const activateDelete = (e) => {
    const target = e.target;
    const mediaId =
      target.getAttribute("data-delete-media") ||
      target.closest("tr").getAttribute("data-media-id");
    if (mediaId && confirm("Are you sure you want to continue?")) {
      setDeleteImageId(mediaId);
    }
  };

  // Function to get the value of a query parameter from the URL
  function getQueryParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
  }

  // Function to scroll to the element with matching data-media-id
  function scrollToMediaItem(mediaId, offset = 0) {
    if (!mediaId) return;

    const element = document.querySelector(`tr[data-media-id="${mediaId}"]`);
    if (element) {
      element.classList.add("highlighted");

      const elementPosition =
        element.getBoundingClientRect().top + window.scrollY;
      const scrollToPosition = elementPosition - offset;

      window.scrollTo({
        top: scrollToPosition,
        behavior: "smooth",
      });
    } else {
      console.log(`Element with data-media-id="${mediaId}" not found.`);
    }
  }

  // Scroll to media item if media_id is in URL
  useEffect(() => {
    const mediaId = getQueryParameter("media_id");
    const offsetValue = 100;

    if (mediaId) {
      const timer = setTimeout(() => {
        scrollToMediaItem(mediaId, offsetValue);
      }, 500);

      return () => clearTimeout(timer);
    }
  }, []);

  // Common props for both table components
  const commonTableProps = {
    type,
    filterMode,
    setFilterPager,
    filter_size,
    filterPager,
    mediaCollectorHigh,
    mediaCollectorLow,
    activateDelete,
    activatePreserve,
  };

  // if(scanInitiatedType == "Loading Media in Progress") {

  // } else {

  //   if (mediaCollector === "no-images") {
  //     return <p style={{ color: "#fff" }}>No Media Found!</p>;
  //   }
  // }

  return (
    <>
      <MediaCollectorTable
        {...commonTableProps}
        mediaCollector={mediaCollector}
      />
      <PreservedMediaCollectorTable
        {...commonTableProps}
        mediaCollector={mediaCollectorPreserved}
      />
    </>
  );
};

export default MediaCollector;
