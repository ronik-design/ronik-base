import React, { useState } from "react";
import useMediaCleanerStore from "./stores/mediaCleanerStore";
import ActionButtons from "./ActionButtons.jsx";

const MediaFilter = ({ type }) => {
  const { selectedFilters, setSelectedFilters } = useMediaCleanerStore();
  const [isOpen, setIsOpen] = useState(false);

  const filterOptions = [
    { value: "jpg", label: "JPG" },
    { value: "png", label: "PNG" },
    { value: "video", label: "Video" },
    { value: "gif", label: "GIF" },
    { value: "audio", label: "Audio" },
    { value: "misc", label: "Misc" },
  ];

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
            src="/wp-content/plugins/ronik-base/assets/images/filter.svg"
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
