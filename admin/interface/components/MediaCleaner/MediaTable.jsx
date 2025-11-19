import React, { useMemo, useCallback, useState } from "react";
import parse from "html-react-parser";
import useMediaCleanerStore from "./stores/mediaCleanerStore";

// Function to get the value of a query parameter from the URL
function getQueryParameter(name) {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get(name);
}

// Optimized pagination logic
const getPaginatedData = (data, page, itemsPerPage) => {
  if (!data || data.length === 0) return [];
  const startIndex = page * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  return data.slice(startIndex, endIndex);
};

// Optimized file size color function with lookup table
const SIZE_COLORS = {
  small: "#1E513F",
  medium: "#5C5A46",
  large: "#66424E",
  default: "gray",
};

function getFileSizeColor(mediaSize) {
  if (!mediaSize) return SIZE_COLORS.default;

  const size = parseFloat(mediaSize);
  const unit = mediaSize.replace(/[^a-zA-Z]/g, "").toLowerCase();

  // Convert to KB using a more efficient approach
  let sizeInKB = size;
  switch (unit) {
    case "mb":
      sizeInKB = size * 1024;
      break;
    case "gb":
      sizeInKB = size * 1024 * 1024;
      break;
    case "bytes":
      sizeInKB = size / 1024;
      break;
    default: // already in KB
      break;
  }

  if (sizeInKB < 500) return SIZE_COLORS.small;
  if (sizeInKB < 1024) return SIZE_COLORS.medium;
  return SIZE_COLORS.large;
}

// Memoized FilterNav component
const FilterNav = React.memo(({ mediaCollector, filterMode, filter_size }) => {
  return (
    <div className="filter-nav">
      <button
        type="button"
        title="Sort Smallest to Largest File Size"
        onClick={filter_size}
        data-filter="small"
        className={`filter-nav__button filter-nav__button-sort filter-nav__button--${
          filterMode === "small" ? "active" : "inactive"
        }`}
      >
        Sort Smallest to Largest File Size
      </button>

      <button
        type="button"
        title="Sort Largest to Smallest File Size"
        onClick={filter_size}
        data-filter="large"
        className={`filter-nav__button filter-nav__button-sort filter-nav__button--${
          filterMode === "large" || filterMode === "all" ? "active" : "inactive"
        }`}
      >
        Sort Largest to Smallest File Size
      </button>
    </div>
  );
});

// Memoized PagerNav component
const PagerNav = React.memo(
  ({ pager, setFilterPager, mediaCollector = [], itemsPerPage }) => {
    const { totalItems, totalPages, visiblePages } = useMemo(() => {
      const totalItems = (mediaCollector && mediaCollector.length) || 0;
      const totalPages = Math.ceil(totalItems / itemsPerPage);
      
      // Smart pagination logic
      const getVisiblePages = (currentPage, totalPages) => {
        if (totalPages <= 10) {
          // Show all pages if 10 or fewer
          return Array.from({ length: totalPages }, (_, i) => i);
        }
        
        const pages = [];
        const current = currentPage;
        
        // Always show first page
        pages.push(0);
        
        // Determine the range around current page
        let startRange = Math.max(1, current - 2);
        let endRange = Math.min(totalPages - 2, current + 2);
        
        // Adjust range if we're near the beginning
        if (current <= 3) {
          startRange = 1;
          endRange = Math.min(5, totalPages - 2);
        }
        
        // Adjust range if we're near the end
        if (current >= totalPages - 4) {
          startRange = Math.max(1, totalPages - 6);
          endRange = totalPages - 2;
        }
        
        // Add ellipsis before middle section if needed
        if (startRange > 1) {
          pages.push('ellipsis-start');
        }
        
        // Add middle section pages
        for (let i = startRange; i <= endRange; i++) {
          if (i !== 0 && i !== totalPages - 1) {
            pages.push(i);
          }
        }
        
        // Add ellipsis after middle section if needed
        if (endRange < totalPages - 2) {
          pages.push('ellipsis-end');
        }
        
        // Always show last page (if more than 1 page)
        if (totalPages > 1) {
          pages.push(totalPages - 1);
        }
        
        return pages;
      };
      
      const visiblePages = getVisiblePages(pager, totalPages);

      return { totalItems, totalPages, visiblePages };
    }, [mediaCollector, itemsPerPage, pager]);

    const handlePageClick = useCallback(
      (pageNumber) => {
        setFilterPager(pageNumber);
      },
      [setFilterPager]
    );

    const handlePrevClick = useCallback(() => {
      if (pager > 0) {
        setFilterPager(pager - 1);
      }
    }, [pager, setFilterPager]);

    const handleNextClick = useCallback(() => {
      if (pager < totalPages - 1) {
        setFilterPager(pager + 1);
      }
    }, [pager, totalPages, setFilterPager]);

    return (
      <div className="filter-pagination">
        <p>
          Showing {pager * itemsPerPage + 1}-
          {Math.min((pager + 1) * itemsPerPage, totalItems)} of {totalItems}
        </p>
        <div className="pagination">
          <button
            className={`filter-pagination__button filter-pagination__button--arrow filter-pagination__button--arrow-prev ${
              pager === 0 ? "filter-pagination__button--disabled" : ""
            }`}
            onClick={handlePrevClick}
            disabled={pager === 0}
            aria-label="Previous page"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="28"
              height="28"
              viewBox="0 0 28 28"
              fill="none"
            >
              <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M17.493 9.09308C17.8747 8.71027 17.8747 8.08903 17.493 7.70621C17.1102 7.32449 16.489 7.32449 16.1061 7.70621L10.5061 13.3062C10.1244 13.689 10.1244 14.3103 10.5061 14.6931L16.1061 20.2931C16.489 20.6748 17.1102 20.6748 17.493 20.2931C17.8747 19.9103 17.8747 19.289 17.493 18.9062L12.5832 13.9975L17.493 9.09308Z"
                fill="white"
              />
            </svg>{" "}
          </button>

          {visiblePages.map((pageNumber, index) => {
            // Handle ellipsis
            if (typeof pageNumber === 'string' && pageNumber.startsWith('ellipsis')) {
              return (
                <span 
                  key={pageNumber}
                  className="filter-pagination__ellipsis"
                  aria-label="More pages"
                >
                  ...
                </span>
              );
            }
            
            // Handle regular page numbers
            return (
              <button
                key={pageNumber}
                className={`filter-pagination__button ${
                  pageNumber === pager ? "filter-pagination__button--active" : ""
                }`}
                onClick={() => handlePageClick(pageNumber)}
              >
                {pageNumber + 1}
              </button>
            );
          })}

          <button
            className={`filter-pagination__button filter-pagination__button--arrow filter-pagination__button--arrow-next ${
              pager === totalPages - 1
                ? "filter-pagination__button--disabled"
                : ""
            }`}
            onClick={handleNextClick}
            disabled={pager === totalPages - 1}
            aria-label="Next page"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="28"
              height="28"
              viewBox="0 0 28 28"
              fill="none"
            >
              <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M17.493 9.09308C17.8747 8.71027 17.8747 8.08903 17.493 7.70621C17.1102 7.32449 16.489 7.32449 16.1061 7.70621L10.5061 13.3062C10.1244 13.689 10.1244 14.3103 10.5061 14.6931L16.1061 20.2931C16.489 20.6748 17.1102 20.6748 17.493 20.2931C17.8747 19.9103 17.8747 19.289 17.493 18.9062L12.5832 13.9975L17.493 9.09308Z"
                fill="white"
              />
            </svg>
          </button>
        </div>
      </div>
    );
  }
);

// Memoized table row component
const MediaTableRow = React.memo(
  ({ collector, selectedIds, onSelect, onImageClick }) => {
    const handleSelect = useCallback(() => {
      onSelect(collector["id"]);
    }, [collector["id"], onSelect]);

    const mediaFileType = () => {
      const fileType = collector["media_file_type"];
      return (
        fileType.includes("image") ||
        fileType.includes("png") ||
        fileType.includes("jpg") ||
        fileType.includes("jpeg")
      );
    };

    const { isScanning, setScanInitiated, syncStatus, scanInitiatedType } = useMediaCleanerStore();
    // Use local scanInitiated for immediate feedback, combined with global isScanning
    const showLoading = isScanning ;

    return (
      <tr
        className="media-collector-table__tr"
        data-media-id={collector["id"]}
        key={collector["id"]}
      >
        <td className="media-collector-table__td media-collector-table__td--select">
          <input
            type="checkbox"
            checked={selectedIds.includes(collector["id"])}
            onChange={handleSelect}
            aria-label={`Select media ${collector["id"]}`}
          />
        </td>
        <td className={`media-collector-table__td media-collector-table__td--img-thumb ${
            showLoading ? "loading" : ""
          }`}>
          {mediaFileType() ? (
            <div
              className="thumbnail-clickable"
              onClick={() => {
                if (onImageClick) {
                  onImageClick(collector["media_file"], collector["id"]);
                }
              }}
              style={{ cursor: "pointer" }}
            >
              {parse(collector["img-thumb"])}
            </div>
          ) : (
            <div className="media-collector-table__td--img-thumb-clickable">
              {parse(collector["img-thumb"])}
            </div>
          )}
        </td>
        <td
          className={`media-collector-table__td media-collector-table__td--file-type ${
            showLoading ? "loading" : ""
          }`}
        >
          <div
            className="media-collector-table__td--file-type-pill">
            {collector["media_file_type"]}
          </div>
        </td>
        <td className={`media-collector-table__td media-collector-table__td--file-size ${
            showLoading ? "loading" : ""
          }`}>
          <div
            className="media-collector-table__td--file-size-pill"
            style={{
              backgroundColor: getFileSizeColor(collector["media_size"]),
            }}
          >
            {collector["media_size"]}
          </div>
        </td>
        <td className={`media-collector-table__td media-collector-table__td--id ${
            showLoading ? "loading" : ""
          }`}>
          {collector["id"]}
        </td>
        <td className={`media-collector-table__td media-collector-table__td--media-link ${
            showLoading ? "loading" : ""
          }`}>
          <a
            target="_blank"
            rel="noopener noreferrer"
            href={`/wp-admin/upload.php?item=${collector["id"]}&mode=grid`}
          >
            Go to media
          </a>
        </td>
        <td className={`media-collector-table__td media-collector-table__td--img-url ${
            showLoading ? "loading" : ""
          }`}>
          <span className="media-collector-table__td--img-url-text">
            {collector["media_file"]}
          </span>
        </td>
      </tr>
    );
  }
);

// Lightbox component
const LightboxModal = React.memo(
  ({ lightboxOpen, lightboxImage, closeLightbox }) => {
    if (!lightboxOpen || !lightboxImage) return null;

    return (
      <div className="lightbox-overlay" onClick={closeLightbox}>
        <div className="lightbox-content" onClick={(e) => e.stopPropagation()}>
          <div className="lightbox-content-header">
            <div className="lightbox-content-header-left">
              <h1>File ID: {lightboxImage.id}</h1>
              <a
                href={`/wp-admin/upload.php?item=${lightboxImage.id}&mode=grid`}
              >
                View in Library
              </a>
            </div>
            <div className="lightbox-content-header-right">
              <button className="lightbox-close" onClick={closeLightbox}>
                ×
              </button>
            </div>
          </div>

          <div
            className="lightbox-content-lightbox-image"
            style={{
              backgroundImage: `url(/wp-content${lightboxImage.url})`,
            }}
          />
        </div>
      </div>
    );
  }
);

// Separate function to render just the table
const renderTable = ({
  mediaCollectorItems,
  allSelected,
  handleSelectAll,
  mediaCollector,
  filterMode,
  filter_size,
  showSelect = true,
  showPreserveButton = false,
}) => {
  return (
    <table className="media-collector-table">
      <tbody className="media-collector-table__tbody">
        <tr className="media-collector-table__tr media-collector-table__tr--header">
          {showSelect && (
            <th className="media-collector-table__th media-collector-table__th--select">
              <input
                type="checkbox"
                checked={allSelected}
                onChange={handleSelectAll}
                aria-label="Select all"
              />
            </th>
          )}
          <th className="media-collector-table__th media-collector-table__th--img-thumb">
            Thumbnail
          </th>
          <th className="media-collector-table__th media-collector-table__th--file-type">
            File Type
          </th>
          <th className="media-collector-table__th media-collector-table__th--file-size">
            Size
            <FilterNav
              mediaCollector={mediaCollector}
              filterMode={filterMode}
              filter_size={filter_size}
            />
          </th>
          <th className="media-collector-table__th media-collector-table__th--id">
            ID
          </th>
          <th className="media-collector-table__th media-collector-table__th--media-link">
            Media Link
          </th>
          <th className="media-collector-table__th media-collector-table__th--img-url">
            File Path
          </th>
        </tr>
        {mediaCollectorItems}
      </tbody>
    </table>
  );
};

// Custom hook for common table logic
const useMediaTableLogic = ({
  filterPager,
  filterMode,
  mediaCollector,
  mediaCollectorHigh,
  mediaCollectorLow,
}) => {
  const {
    userSelection,
    setUserSelection,
    addToUserSelection,
    removeFromUserSelection,
    isScanning,
  } = useMediaCleanerStore();

  // Lightbox state
  const [lightboxImage, setLightboxImage] = useState(null);
  const [lightboxOpen, setLightboxOpen] = useState(false);

  // Memoized pagination and data processing
  const { output, itemsPerPage, visibleIds, allSelected } = useMemo(() => {
    const page = parseInt(filterPager) || 0;
    let itemsPerPage = 20;
    // const mediaId = getQueryParameter("media_id");
    // if (mediaId) {
    //   itemsPerPage = 200000000;
    // }

    const output = getPaginatedData(
      filterMode === "high"
        ? mediaCollectorHigh
        : filterMode === "low"
        ? mediaCollectorLow
        : mediaCollector,
      page,
      itemsPerPage
    );

    if (output === "no-images") {
      return {
        output: [],
        itemsPerPage: 0,
        visibleIds: [],
        allSelected: false,
      };
    }

    const visibleIds = output.map((collector) => collector["id"]);

    const allSelected =
      visibleIds.length > 0 &&
      visibleIds.every((id) => userSelection.includes(id));

    return { output, itemsPerPage, visibleIds, allSelected };
  }, [
    filterPager,
    filterMode,
    mediaCollector,
    mediaCollectorHigh,
    mediaCollectorLow,
    userSelection,
  ]);

  // Event handlers
  const handleSelectAll = useCallback(
    (e) => {
      if (e.target.checked) {
        setUserSelection(visibleIds);
      } else {
        setUserSelection([]);
      }
    },
    [visibleIds, setUserSelection]
  );

  const handleSelect = useCallback(
    (id) => {
      if (userSelection.includes(id)) {
        removeFromUserSelection(id);
      } else {
        addToUserSelection(id);
      }
    },
    [userSelection, addToUserSelection, removeFromUserSelection]
  );

  // Lightbox handlers
  const openLightbox = useCallback((imageUrl, imageId) => {
    setLightboxImage({ url: imageUrl, id: imageId });
    setLightboxOpen(true);
  }, []);

  const closeLightbox = useCallback(() => {
    setLightboxOpen(false);
    setLightboxImage(null);
  }, []);

  return {
    output,
    itemsPerPage,
    allSelected,
    handleSelectAll,
    handleSelect,
    openLightbox,
    closeLightbox,
    lightboxImage,
    lightboxOpen,
    userSelection,
  };
};

// Unified MediaCollectorTable component
const MediaCollectorTable = ({
  type = "regular", // Default to regular type
  mediaCollector,
  filterMode,
  setFilterPager,
  filter_size,
  filterPager,
  mediaCollectorHigh,
  mediaCollectorLow,
}) => {
  const isPreservedType = type === "preserved";

  const { scanInitiatedType } = useMediaCleanerStore();

  const {
    output,
    itemsPerPage,
    allSelected,
    handleSelectAll,
    handleSelect,
    openLightbox,
    closeLightbox,
    lightboxImage,
    lightboxOpen,
    userSelection,
  } = useMediaTableLogic({
    filterPager,
    filterMode,
    mediaCollector,
    mediaCollectorHigh,
    mediaCollectorLow,
  });

  // Memoized table rows
  const mediaCollectorItems = useMemo(() => {
    return (output || []).map((collector) => (
      <MediaTableRow
        key={collector["id"]}
        collector={collector}
        selectedIds={userSelection}
        onSelect={handleSelect}
        onImageClick={openLightbox}
      />
    ));
  }, [output, userSelection, handleSelect, openLightbox, scanInitiatedType, mediaCollector]);

  // Early return for no media
  if (mediaCollector === "no-images") {
    return <p style={{ color: "#fff" }}>No Media Found!</p>;
  }

  if (!mediaCollector && scanInitiatedType === "Loading Media in Progress") {
    return <p style={{ color: "#fff" }}>Loading Media...</p>;
  }





  return (
    <>
      <br />
      {renderTable({
        mediaCollectorItems,
        allSelected,
        handleSelectAll,
        mediaCollector,
        filterMode,
        filter_size,
        showSelect: true,
        showPreserveButton: isPreservedType,
      })}
      <PagerNav
        pager={filterPager}
        setFilterPager={setFilterPager}
        mediaCollector={mediaCollector || []}
        itemsPerPage={itemsPerPage}
      />
      <LightboxModal
        lightboxOpen={lightboxOpen}
        lightboxImage={lightboxImage}
        closeLightbox={closeLightbox}
      />
    </>
  );
};

// Original MediaCollectorTable component (backward compatibility)
const OriginalMediaCollectorTable = (props) => {
  // Original logic: Early return for preserved type
  if (props.type === "preserved") {
    return null;
  }
  return <MediaCollectorTable {...props} type="regular" />;
};

// PreservedMediaCollectorTable component
const PreservedMediaCollectorTable = (props) => {
  // Original logic: Early return for non-preserved type
  if (props.type !== "preserved") {
    return null;
  }
  return <MediaCollectorTable {...props} type="preserved" />;
};

// Export components - keeping same names as original for backward compatibility
export default {
  MediaCollectorTable: OriginalMediaCollectorTable, // This is the original non-preserved table
  PreservedMediaCollectorTable,
};
