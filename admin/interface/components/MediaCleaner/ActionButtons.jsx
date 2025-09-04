import React from "react";
import useMediaCleanerStore from "./stores/mediaCleanerStore";

const ActionButtons = ({ type }) => {
  const { isScanning, syncStatus, setScanInitiated, userSelection } =
    useMediaCleanerStore();

  // Perform the POST request
  const handlePostData = async (action, preserveType = null) => {
    const data = new FormData();
    data.append("action", "rmc_ajax_media_cleaner");
    data.append("nonce", wpVars.nonce);
    data.append("post_overide", action);
    data.append("imageId", userSelection);
    data.append("preserveType", preserveType);

    try {
      // Only set scanInitiated - let SyncStatus handle all scanning state
      setScanInitiated(true);

      const response = await fetch(wpVars.ajaxURL, {
        method: "POST",
        credentials: "same-origin",
        body: data,
      });
      const result = await response.json();
      console.log("Action result:", result);

      // The SyncStatus component will take over from here and update the progress
      // It will set isScanning based on the actual API sync status
    } catch (error) {
      console.error("Error:", error);
      // If there's an error, reset the scan initiated state
      setScanInitiated(false);
    }
  };

  const handleSubmitWithAction = (action) => (e) => {
    e.preventDefault();

    if (action === "media-delete-indiv") {
      if (!window.confirm("Are you sure you want to bulk delete media?")) {
        return;
      }
    }

    // For preserve-mediamedia-preserve action, we need to ensure StatsContainer also shows loading
    if (action === "media-preserve" || action === "media-unpreserve") {
      // This will trigger the loading state in both ActionButtons and StatsContainer
      handlePostData(
        type === "preserved" ? "media-unpreserve" : "media-preserve",
        type === "preserved" ? "unpreserve" : "preserve"
      );
    } else {
      // For other actions like media-delete-indiv
      handlePostData(action, false);
    }
  };

  return (
    <div className="media-filter-action-buttons">
      <div className="media-filter-action-buttons__inner">
        <button
          onClick={handleSubmitWithAction("media-delete-indiv")}
          className={
            isScanning || userSelection.length === 0
              ? "submit-btn submit-btn-disabled delete-btn"
              : "submit-btn delete-btn"
          }
          disabled={isScanning || userSelection.length === 0}
        >
          <img
            src="/wp-content/plugins/ronik-base/assets/images/delete.svg"
            alt="Ronik Base Logo"
          />

          {userSelection.length === 0
            ? "No media selected"
            : isScanning || userSelection.length === 0
            ? "Sync in progress — delete unavailable"
            : "Delete Media"}
        </button>

        <button
          onClick={handleSubmitWithAction("media-preserve")}
          className={
            isScanning || userSelection.length === 0
              ? "submit-btn submit-btn-disabled"
              : "submit-btn"
          }
          disabled={isScanning || userSelection.length === 0}
        >
          <img
            src="/wp-content/plugins/ronik-base/assets/images/preserve.svg"
            alt="Ronik Base Logo"
          />

          {type === "preserved"
            ? userSelection.length === 0
              ? "No media selected"
              : isScanning || userSelection.length === 0
              ? "Unpreserve in progress — unpreserve unavailable"
              : "Unpreserve Media"
            : userSelection.length === 0
            ? "No media selected"
            : isScanning || userSelection.length === 0
            ? "Preserve in progress — preserve unavailable"
            : userSelection.length === 0
            ? "No media selected"
            : isScanning || userSelection.length === 0
            ? "Preserve in progress — preserve unavailable"
            : "Preserve Media"}
        </button>
      </div>
    </div>
  );
};

export default ActionButtons;
