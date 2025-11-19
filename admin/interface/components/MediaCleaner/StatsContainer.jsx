import React, { useState, useEffect, useCallback } from "react";
import useMediaCleanerStore from "./stores/mediaCleanerStore";

function StatsContainer() {
  const { isScanning, scanInitiated, setScanInitiated, syncStatus, scanInitiatedType, setScanInitiatedType } = useMediaCleanerStore();
  const [statsUnlinked, setStatsUnlinked] = useState([]);
  const [statsPreserved, setStatsPreserved] = useState([]);
  const [breakdown, setBreakdown] = useState([]);
  const [lastUpdateTime, setLastUpdateTime] = useState(null);
  const [localScanInitiated, setLocalScanInitiated] = useState(false);

  // Use both global scanInitiated and local scanInitiated for immediate feedback
  const showLoading = isScanning || scanInitiated || localScanInitiated;

  useEffect(() => {
    fetchStats();
    // SyncStatus component will handle all sync state management
    // No need to check initial scan status here
  }, []);

  // Watch for sync status changes to update lastUpdateTime
  useEffect(() => {
    if (syncStatus.lastUpdate) {
      setLastUpdateTime(syncStatus.lastUpdate);
    }
  }, [syncStatus.lastUpdate]);

  // Watch for scan completion to reset local state and refresh stats
  useEffect(() => {
    if (!isScanning && !scanInitiated && localScanInitiated) {
      setLocalScanInitiated(false);
      fetchStats(); // Refresh stats when scan completes
    }
  }, [isScanning, scanInitiated, localScanInitiated, fetchStats]);





  const fetchStats = useCallback(() => {
    fetch("/wp-json/mediacleaner/v1/mediacollector/stats")
      .then((response) => response.json())
      .then((response) => {
        if (response && response.success && response.data) {
          const data = response.data;

          const statsUnlinked = [
            {
              label: "Number of unlinked files",
              value: data.unlinked,
              unit: "Files",
            },
            {
              label: "Total unlinked media file size",
              value: data.unlinked_size_formatted,
            },
          ];
          const statsPreserved = [
            {
              label: "Number of preserved files",
              value: data.preserved,
              unit: "Files",
            },
            {
              label: "Total preserved media file size",
              value: data.preserved_size_formatted,
            },
          ];

          setBreakdown(data.breakdown);
          setStatsUnlinked(statsUnlinked);
          setStatsPreserved(statsPreserved);
        } else {
          console.log("Data structure not as expected:", response);
          setStatsUnlinked([]);
          setStatsPreserved([]);
          setBreakdown([]);
        }
      })
      .catch((error) => {
        console.error("Error fetching stats:", error);
        setStatsUnlinked([]);
        setStatsPreserved([]);
        setBreakdown([]);
      });
  }, []);

  const handleSubmitWithAction = (action) => (e) => {
    e.preventDefault();
    console.log("Action:", action);

    // Start the scan INSTANTLY - no delay
    setLocalScanInitiated(true); // Set local state to true
    setScanInitiated(true);

    // Trigger the media scan
    handlePostData("fetch-media", "all", 0, "inprogress")
      .then((result) => {
        console.log("Scan initiated:", result);
        // The scan will now be managed by the SyncStatus component
        // which will automatically update isScanning based on real API status
      })
      .catch((error) => {
        console.error("Error initiating scan:", error);
        setScanInitiated(false);
        setLocalScanInitiated(false); // Reset local state on error
      });
  };

  // Perform the POST request
  const handlePostData = async (userOptions, mimeType, postOveride = null) => {
    const data = new FormData();
    data.append("action", "rmc_ajax_media_cleaner");
    data.append("nonce", wpVars.nonce);
    data.append("post_overide", postOveride);
    data.append("user_option", userOptions);
    data.append("mime_type", mimeType);

    try {
      const response = await fetch(wpVars.ajaxURL, {
        method: "POST",
        credentials: "same-origin",
        body: data,
      });

      const result = await response.json();
      return result;
    } catch (error) {
      console.error("Error:", error);
      throw error;
    }
  };

  // Format the last update time
  const formatLastUpdate = (timestamp) => {
    if (!timestamp) return "Never";

    // alert(timestamp);
    let date;

    // Handle WordPress date format "m/d/Y h:ia" (e.g., "08/20/2025 01:54am")
    if (typeof timestamp === "string" && timestamp.includes("/")) {
      const parts = timestamp.match(/(\d+)\/(\d+)\/(\d+)\s+(\d+):(\d+)(am|pm)/);
      if (parts) {
        const [, month, day, year, hour, minute, ampm] = parts;
        let hour24 = parseInt(hour);
        if (ampm === "pm" && hour24 !== 12) hour24 += 12;
        if (ampm === "am" && hour24 === 12) hour24 = 0;

        // Create date string in ISO format to avoid timezone issues
        // Format: YYYY-MM-DDTHH:mm:ss (treat as local time)
        const dateString = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}T${hour24.toString().padStart(2, '0')}:${minute.padStart(2, '0')}:00`;
        date = new Date(dateString);
      } else {
        // Fallback to direct parsing
        date = new Date(timestamp);
      }
    } else {
      // Handle other date formats
      date = new Date(timestamp);
    }

    // Check if date is valid
    if (isNaN(date.getTime())) {
      return timestamp; // Return original if parsing failed
    }

    const now = new Date();
    const diffMs = now - date;

    // Handle future dates (likely due to timezone differences)
    // If the date is less than 24 hours in the future, treat it as "just now"
    if (diffMs < 0 && Math.abs(diffMs) < 24 * 60 * 60 * 1000) {
      return "Just now";
    }
    
    // If it's more than 24 hours in the future, something is wrong - return original
    if (diffMs < 0) {
      return timestamp;
    }

    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    const diffSecs = Math.floor((diffMs % 60000) / 1000);

    if (diffDays > 0) {
      return `${diffDays}d ${diffHours % 24}h ago`;
    } else if (diffHours > 0) {
      return `${diffHours}h ${diffMins % 60}m ago`;
    } else if (diffMins > 0) {
      return `${diffMins}m ${diffSecs}s ago`;
    } else {
      return `${diffSecs}s ago`;
    }
  };

  return (
    <div className="stats-container">
      {/* SyncStatus is now managed at the top level to prevent conflicts */}

      <div className="stats-container-info">
        <div className="stats-container-info-items">
          {statsUnlinked.map((stat, index) => (
            <div
              key={`unlinked-${index}`}
              className="stats-container-info-item"
            >
              <div
                className={`stats-container-info-item-label ${
                  showLoading ? "loading" : ""
                }`}
              >
                {stat.label}
              </div>
              <div
                className={`stats-container-info-item-value ${
                  showLoading ? "loading" : ""
                }`}
              >
                {stat.value} {stat.unit}
              </div>
            </div>
          ))}
        </div>
        <div className="stats-container-info-items">
          {statsPreserved.map((stat, index) => (
            <div
              key={`preserved-${index}`}
              className="stats-container-info-item"
            >
              <div
                className={`stats-container-info-item-label ${
                  showLoading ? "loading" : ""
                }`}
              >
                {stat.label}
              </div>
              <div
                className={`stats-container-info-item-value ${
                  showLoading ? "loading" : ""
                }`}
              >
                {stat.value} {stat.unit}
              </div>
            </div>
          ))}
        </div>

        {showLoading ? (
          <div className="stats-container-info-sync-progress">
            <div className="stats-container-info-sync-progress-header">
              <span className="stats-container-info-sync-progress-status">
                {/* Scan in Progress */}
                {scanInitiatedType}
              </span>
              <span className="stats-container-info-sync-progress-message">
                {syncStatus.progress === "100%" ? "Complete!" : "Processing..."}
              </span>
              {/* <button
                className="stats-container-info-sync-progress-stop"
                onClick={() => setScanning(false)}
              >
                Stop Scan
              </button> */}
            </div>
            <div className="stats-container-info-sync-progress-bar">
              <div className="stats-container-info-sync-progress-track">
                <div
                  className="stats-container-info-sync-progress-fill"
                  style={{ width: syncStatus.progress || "0%" }}
                ></div>
              </div>
              <span className="stats-container-info-sync-progress-percentage">
                {syncStatus.progress || "0%"}
              </span>
            </div>
          </div>
        ) : (
          <div className="stats-container-info-sync">
            <div className="stats-container-info-sync-action">
              <button
                className="stats-container-info-sync-action-button"
                onClick={handleSubmitWithAction("scan-media")}
                disabled={showLoading}
              >
                <img
                  src="/wp-content/plugins/ronik-base/assets/images/scan.svg"
                  alt="Ronik Base Logo"
                />
                Scan Media Library
              </button>
            </div>
            <div className="stats-container-info-sync-status">
              <div className="stats-container-info-sync-status-label">
                <img
                  src="/wp-content/plugins/ronik-base/assets/images/reload.svg"
                  alt="Ronik Base Logo"
                />
                Last Updated &nbsp;{formatLastUpdate(lastUpdateTime)}
              </div>
            </div>
          </div>
        )}
      </div>
      <div className="stats-container-breakdown">
        <span>File Type Breakdown</span>
        <div className="stats-container-breakdown-graph">
          <div
            className={`stats-container-breakdown-graph-bar ${
              showLoading ? "loading" : ""
            }`}
          >
            {breakdown.map((item) => (
              <div
                key={`breakdown-${item.type}`}
                className="stats-container-breakdown-graph-item"
                style={{ width: `${item.percentage}%` }}
              ></div>
            ))}
          </div>
          <div className="stats-container-breakdown-graph-list">
            {breakdown.map((item) => (
              <div
                key={`breakdown-label-${item.type}`}
                className="stats-container-breakdown-graph-item-label"
              >
                {item.type}
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

export default StatsContainer;
