import React, { useState, useEffect } from "react";
import useMediaCleanerStore from "./stores/mediaCleanerStore";
import ActionButtons from "./ActionButtons.jsx";

// Function to update URL query parameters
function updateURLQueryParams(filters) {
  const url = new URL(window.location.href);
  
  // Remove existing filter parameters
  url.searchParams.delete("filter");
  
  // Add new filter parameters
  if (filters.length > 0) {
    filters.forEach((filter) => {
      url.searchParams.append("filter", filter);
    });
  }
  
  // Update URL without page reload
  window.history.pushState({}, "", url.toString());
}

const filterOptions = [
  { value: "jpg", label: "JPG" },
  { value: "png", label: "PNG" },
  { value: "video", label: "Video" },
  { value: "gif", label: "GIF" },
  { value: "audio", label: "Audio" },
  { value: "misc", label: "Misc" },
];

const MediaFilter = ({ type }) => {
  const { selectedFilters, setSelectedFilters } = useMediaCleanerStore();
  const [isOpen, setIsOpen] = useState(false);
  const [isInitialized, setIsInitialized] = useState(false);

  // Get pluginName from wpVars (localized by WordPress)
  const pluginName = typeof window !== 'undefined' && window.wpVars ? (window.wpVars.pluginName || 'ronik-base') : 'ronik-base';

  // Initialize filters from URL on component mount
  // This will automatically trigger the data fetch in MediaCollector component
  // because its useEffect depends on selectedFilters
  useEffect(() => {
    if (!isInitialized) {
      const urlParams = new URLSearchParams(window.location.search);
      const filterParams = urlParams.getAll("filter");
      
      if (filterParams.length > 0) {
        // Validate that all filter params are valid options
        const validFilters = filterParams.filter((filter) =>
          filterOptions.some((option) => option.value === filter)
        );
        if (validFilters.length > 0) {
          // Setting filters here will trigger the useEffect in MediaCollector
          // that fetches filtered media data (it depends on selectedFilters)
          setSelectedFilters(validFilters);
        }
      }
      setIsInitialized(true);
    }
  }, [isInitialized, setSelectedFilters]);

  // Update URL when filters change (but not on initial load)
  useEffect(() => {
    if (isInitialized) {
      updateURLQueryParams(selectedFilters);
    }
  }, [selectedFilters, isInitialized]);

  const toggleFilter = (value) => {
    if (selectedFilters.includes(value)) {
      setSelectedFilters(selectedFilters.filter((filter) => filter !== value));
    } else {
      setSelectedFilters([...selectedFilters, value]);
    }
  };

  const getDisplayText = () => {
    if (selectedFilters.length === 0) return "All File Types";
    if (selectedFilters.length === filterOptions.length)
      return "All File Types";

    const selectedLabels = selectedFilters
      .map(
        (filter) =>
          filterOptions.find((option) => option.value === filter)?.label
      )
      .filter(Boolean);

    return selectedLabels.join(", ");
  };

  const handleSelectAll = () => {
    setSelectedFilters(filterOptions.map((option) => option.value));
  };

  const handleClearAll = () => {
    setSelectedFilters([]);
  };

  return (
    <div className="media-filter-container">
      <ActionButtons type={type} />

      {/* Main Filter Button */}
      <button
        className="media-filter-button"
        onClick={() => setIsOpen(!isOpen)}
      >
        <span className="media-filter-icon">
          <img
            src={`/wp-content/plugins/${pluginName}/assets/images/filter.svg`}
            alt="Ronik Base Logo"
          />
        </span>
        <span className="media-filter-text">Showing: {getDisplayText()}</span>
      </button>

      {/* Dropdown Menu */}
      {isOpen && (
        <div className="media-filter-dropdown">
          <div className="media-filter-header">Select one or more options:</div>

          <div className="media-filter-options">
            {filterOptions.map((option) => (
              <label key={option.value} className="media-filter-option">
                <input
                  type="checkbox"
                  checked={selectedFilters.includes(option.value)}
                  onChange={() => toggleFilter(option.value)}
                />
                <span className="media-filter-option-label">
                  {option.label}
                </span>
              </label>
            ))}
          </div>

          <div className="media-filter-actions">
            <button
              className="media-filter-action-btn media-filter-select-all"
              onClick={handleSelectAll}
            >
              Select All
            </button>
            <button
              className="media-filter-action-btn media-filter-clear-all"
              onClick={handleClearAll}
            >
              Clear All
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

export default MediaFilter;
