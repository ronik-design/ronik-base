import React, { useState, useEffect, useCallback } from "react";
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
  
  // Use Zustand store for filters
  const { selectedFilters } = useMediaCleanerStore();
  
  // Convert store filters to the format expected by the component
  const selectedDataFormValues = selectedFilters.length > 0 ? selectedFilters : ["all"];

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
          fetch(lazyImage.dataset.src).then(() => {
            const imageSelector = document.querySelector(
              `[data-id="${lazyImage.getAttribute("data-id")}"]`
            );
            lazyImage.className = "lzy_img reveal-enabled";
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
                  }
                },
                lazyImage.getAttribute("data-type"),
                0.5
              );
            };
          });
        }
      });
    });
    
    const images = document.querySelectorAll("img.lzy_img");
    images.forEach((img) => imageObserver.observe(img));
  }

  // Run lazyLoader when component has loaded and data is available
  useEffect(() => {
    if (hasLoaded) {
      const timer = setTimeout(lazyLoader, 500);
      return () => clearTimeout(timer);
    }
  }, [hasLoaded , filterPager]);

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
        if (data.length) {
          setMediaCollectorPreserved(data);
        }
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
    setHasLoaded(false);
    const route = selectedDataFormValues.includes("all")
      ? "all"
      : selectedDataFormValues.join("?");
    const endpoint = filterMode
      ? `${filterMode}?filter=${route}`
      : `all?filter=${route}`;

    fetch(`/wp-json/mediacleaner/v1/mediacollector/${endpoint}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.length) {
          setMediaCollectorPreserved(data);
          setMediaCollector(data);
          setTimeout(() => {
            setHasLoaded(true);
            removeLoader();
          }, 0);
        }
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
        const endpoint = `/wp-json/mediacleaner/v1/mediacollector/${route}?filter=${selectedFilters.join("?")}`;

        alert(endpoint);

        fetch(endpoint)
          .then((response) => response.json())
          .then((data) => {
            if (data.length) {
              filter === "large"
                ? setMediaCollectorHigh(data)
                : setMediaCollectorLow(data);
            }
          })
          .catch((error) =>
            console.error(`Error fetching ${filter} media:`, error)
          )
          .finally(() => {
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
          resMessage = "Media is preserved. Would you like to view the preserved content?";
          resUrl = `/wp-admin/admin.php?page=options-ronik-base_preserved&filter_size=large&page_number=0&media_id=${preserveImageId}`;
        }
        if (unPreserveImageId !== "invalid") {
          resMessage = "Media is unpreserved. Would you like to view the unpreserved content?";
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

      const elementPosition = element.getBoundingClientRect().top + window.scrollY;
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

  if(mediaCollector === "no-images"){
    return <p>No Media Found!</p>;
  }

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