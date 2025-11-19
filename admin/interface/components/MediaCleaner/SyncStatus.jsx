import React, { useState, useEffect, useCallback, useRef } from "react";
import useMediaCleanerStore from "./stores/mediaCleanerStore";

function SyncStatus() {
  const { setScanning, syncStatus, setSyncStatus, setScanInitiated, scanInitiated } = useMediaCleanerStore();
  const [previousStatus, setPreviousStatus] = useState("idle");
  const intervalRef = useRef(null);
  const scanInitiatedTimeRef = useRef(null);

  // Track when scan was initiated to prevent premature state changes
  useEffect(() => {
    if (scanInitiated) {
      scanInitiatedTimeRef.current = Date.now();
    }
  }, [scanInitiated]);

  // Fetch sync status data
  const fetchSyncStatus = async () => {
    try {
      // console.log("Fetching sync status..."); // Debug log
      const response = await fetch(
        "/wp-json/mediacleaner/v1/mediacollector/sync_status"
      );
      const data = await response.json();

      if (data.success && data.data) {
        const syncData = data.data;
        const isServerRunning = syncData.syncRunning === "running";

        // Update sync status based on sync_status data
        const newStatus = {
          isRunning: isServerRunning,
          progress: syncData.progress || "0%",
          status: isServerRunning ? "running" : "idle",
          lastUpdate: syncData.syncTime || new Date().toISOString(),
          totalFiles: 0, // Not provided by sync_status
          processedFiles: 0, // Not provided by sync_status
        };

        setSyncStatus(newStatus);
        
        // Improved logic to prevent flicker
        if (scanInitiated) {
          // If user initiated a scan, keep loading state until:
          // 1. Server reports running, OR
          // 2. Enough time has passed (5 seconds grace period)
          const timeSinceInitiated = scanInitiatedTimeRef.current ? 
            (Date.now() - scanInitiatedTimeRef.current) : 0;
          
          if (isServerRunning) {
            // Server confirmed scan is running - safe to rely on server state
            setScanning(true);
          } else if (timeSinceInitiated > 100000) {
            // Extended grace period (100 seconds) for large operations like bulk deletes
            // This gives the server more time to start processing
            alert("Grace period reached. Time since initiated: " + timeSinceInitiated + " seconds.");
            setScanInitiated(false);
            setScanning(false);
            scanInitiatedTimeRef.current = null;
          } else {
            // Still within grace period - keep showing loading
            setScanning(true);
          }
        } else {
          // No user-initiated scan - follow server state directly
          setScanning(isServerRunning);
        }
        
        // If scan stopped running, reset initiated state
        if (previousStatus === "running" && !isServerRunning && scanInitiated) {
          setScanInitiated(false);
          setScanning(false);
          scanInitiatedTimeRef.current = null;
        }

        setPreviousStatus(newStatus.status);
      }
    } catch (error) {
      console.error("Error fetching sync status:", error);
      
      // On error, if scan was initiated, give it a bit more time
      if (scanInitiated) {
        const timeSinceInitiated = scanInitiatedTimeRef.current ? 
          (Date.now() - scanInitiatedTimeRef.current) : 0;
        
        if (timeSinceInitiated > 10000) { // 10 second timeout on error

          setScanInitiated(false);
          setScanning(false);
          scanInitiatedTimeRef.current = null;
        }
      }
    }
  };

  // Start polling when component mounts
  useEffect(() => {
    // Initial poll
    fetchSyncStatus();

    // Dynamic polling: faster when scan is initiated, slower when idle
    const getPollingInterval = () => {
      if (scanInitiated) {
        const timeSinceInitiated = scanInitiatedTimeRef.current ? 
          (Date.now() - scanInitiatedTimeRef.current) : 0;
        
        // Poll every 250ms for first 5 seconds, then every 500ms
        return timeSinceInitiated < 5000 ? 250 : 500;
      }
      
      // Normal polling when no scan initiated
      return 1000;
    };

    const setupPolling = () => {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
      }
      
      intervalRef.current = setInterval(fetchSyncStatus, getPollingInterval());
    };

    setupPolling();

    // Cleanup on unmount
    return () => {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
        intervalRef.current = null;
      }
    };
  }, [scanInitiated]); // Re-setup polling when scanInitiated changes

  return null; // This component only manages state
}

export default SyncStatus;