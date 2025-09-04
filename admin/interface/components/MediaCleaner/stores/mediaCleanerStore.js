import { create } from 'zustand';

const useMediaCleanerStore = create((set) => ({
  // Basic state
  isScanning: false,
  scanInitiated: false,
  
  // Filter state
  selectedFilters: [],
  
  // User selection state
  userSelection: [],
  
  // Sync status state
  syncStatus: {
    isRunning: false,
    progress: "0%",
    status: "idle",
    lastUpdate: null,
    totalFiles: 0,
    processedFiles: 0,
  },
  
  // Actions
  setScanning: (isScanning) => set({ isScanning }),
  setScanInitiated: (scanInitiated) => set({ scanInitiated }),
  setSelectedFilters: (selectedFilters) => set({ selectedFilters }),
  setUserSelection: (userSelection) => set({ userSelection }),
  addToUserSelection: (id) => set((state) => ({
    userSelection: [...state.userSelection, id]
  })),
  removeFromUserSelection: (id) => set((state) => ({
    userSelection: state.userSelection.filter(itemId => itemId !== id)
  })),
  clearUserSelection: () => set({ userSelection: [] }),
  setSyncStatus: (syncStatus) => set({ syncStatus }),
  updateSyncStatus: (updates) => set((state) => ({
    syncStatus: { ...state.syncStatus, ...updates }
  })),
}));

export default useMediaCleanerStore;
