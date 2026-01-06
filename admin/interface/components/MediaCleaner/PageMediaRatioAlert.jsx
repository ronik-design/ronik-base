import React, { useState, useEffect } from 'react';

// Component to show file size recommendations
const FileSizeRecommendation = ({ distribution, currentSetting, totalMedia }) => {
    // Toggle this to show only 1MB recommendation (for testing)
    // const SHOW_ONLY_1MB = false; // Set to true to show only 1MB
    const SHOW_ONLY_1MB = true; // Set to true to show only 1MB

    
    console.log('[FileSizeRecommendation] Props:', { distribution, currentSetting, totalMedia });
    
    if (!distribution || typeof distribution !== 'object') {
        console.log('[FileSizeRecommendation] No distribution data available');
        return null;
    }
    
    // Validate distribution - backend should always return correct data now
    // If there's a mismatch, log a warning but don't scale (backend should handle this)
    if (distribution[0] !== undefined && distribution[0] !== totalMedia && distribution[0] > 0) {
        console.warn('[FileSizeRecommendation] Distribution mismatch detected! Distribution[0] =', distribution[0], 'but totalMedia =', totalMedia, '- This should not happen with the updated backend.');
        // Use the backend's distribution[0] value as the source of truth
        const actualTotal = distribution[0];
        // Recalculate reductions based on actual total from backend
        // (This is just a fallback - backend should always be correct now)
    }
    
    // Find the best recommendation (threshold that reduces files by at least 15%)
    const recommendations = [];
    // Use distribution[0] as the actual total (backend provides this correctly now)
    const actualTotal = distribution[0] !== undefined ? distribution[0] : totalMedia;
    const currentCount = distribution[currentSetting] !== undefined ? distribution[currentSetting] : actualTotal;
    
    // Common thresholds to suggest (show all, even if one is current setting)
    // If SHOW_ONLY_1MB is true, only show 1MB
    const suggestedThresholds = SHOW_ONLY_1MB ? [1] : [1, 5, 10];
    
    suggestedThresholds.forEach(threshold => {
        const countAtThreshold = distribution[threshold] !== undefined ? distribution[threshold] : 0;
        
        // Skip if count is 0 or invalid
        if (countAtThreshold === 0 && threshold > 0) {
            console.log(`[FileSizeRecommendation] Skipping ${threshold}MB - no files found`);
            return;
        }
        
        // Calculate reduction based on actual total from backend
        const reduction = actualTotal - countAtThreshold;
        // Calculate percentage with one decimal place for accuracy
        // Use Math.round to nearest 0.1% to avoid showing 100% when it's actually 99.5%
        const reductionPercent = actualTotal > 0 ? Math.round((reduction / actualTotal) * 1000) / 10 : 0;
        
        console.log(`[FileSizeRecommendation] Threshold ${threshold}MB: ${countAtThreshold} files, reduction: ${reduction} (${reductionPercent}%)`);
        
        // Show recommendation if it reduces by at least 15%
        // Show all thresholds, even if one matches current setting (so user can see all options)
        if (reductionPercent >= 15) {
            recommendations.push({
                threshold,
                count: countAtThreshold,
                reduction,
                reductionPercent
            });
        }
    });
    
    console.log('[FileSizeRecommendation] Recommendations found:', recommendations);
    
    // Sort recommendations with smart logic:
    // 1. Prefer reductions between 50-85% (sweet spot) - balanced and practical
    // 2. If multiple in sweet spot, prefer lower threshold (more files scanned)
    // 3. If none in sweet spot, prefer highest reduction
    // TEMPORARY FOR TESTING: Prefer lower thresholds first
    recommendations.sort((a, b) => {
        // For testing: prefer lower threshold first
        if (a.threshold !== b.threshold) {
            return a.threshold - b.threshold; // Lower threshold comes first
        }
        
        // If same threshold, prefer higher reduction
        return b.reductionPercent - a.reductionPercent;
        
        /* ORIGINAL LOGIC (commented out for testing):
        const aInSweetSpot = a.reductionPercent >= 50 && a.reductionPercent <= 85;
        const bInSweetSpot = b.reductionPercent >= 50 && b.reductionPercent <= 85;
        
        // If both in sweet spot, prefer lower threshold (more practical)
        if (aInSweetSpot && bInSweetSpot) {
            return a.threshold - b.threshold;
        }
        
        // If only one in sweet spot, prefer that one
        if (aInSweetSpot && !bInSweetSpot) {
            return -1; // a comes first
        }
        if (!aInSweetSpot && bInSweetSpot) {
            return 1; // b comes first
        }
        
        // If neither in sweet spot, prefer higher reduction
        return b.reductionPercent - a.reductionPercent;
        */
    });
    
    console.log('[FileSizeRecommendation] Sorted recommendations:', recommendations);
    
    if (recommendations.length === 0) {
        console.log('[FileSizeRecommendation] No valid recommendations');
        return null;
    }
    
    return (
        <div style={{
            marginTop: '0',
            padding: '10px',
            backgroundColor: 'rgba(255, 255, 255, 0.5)',
            borderRadius: '4px',
            fontSize: '13px'
        }}>
            <strong>💡 Quick Tip:</strong> Consider adjusting your minimum file size setting:
            <ul style={{ margin: '8px 0 0 0', paddingLeft: '20px' }}>
                {recommendations.map((rec, index) => (
                    <li key={index} style={{ marginBottom: '6px' }}>
                        Setting to <strong>{rec.threshold} MB</strong> would reduce scanning by{' '}
                        <strong>{rec.reduction.toLocaleString()} files</strong> ({rec.reductionPercent.toFixed(1)}% fewer files to scan).
                    </li>
                ))}
            </ul>
        </div>
    );
};

const PageMediaRatioAlert = () => {
    const [showAlert, setShowAlert] = useState(false);
    const [stats, setStats] = useState(null);

    useEffect(() => {
        fetchStats();
    }, []);

    const fetchStats = () => {
        fetch("/wp-json/mediacleaner/v1/mediacollector/stats")
            .then((response) => response.json())
            .then((response) => {
                if (response && response.success && response.data) {
                    const data = response.data;
                    
                    // Debug logging
                    console.log('[PageMediaRatioAlert] Full stats data:', data);
                    console.log('[PageMediaRatioAlert] File size distribution:', data.file_size_distribution);
                    console.log('[PageMediaRatioAlert] Current file size setting:', data.current_file_size_setting);
                    
                    setStats(data);
                    
                    // Thresholds for showing the alert
                    const MAX_RATIO = 10; // Alert if more than 10 pages per media file
                    const MIN_PAGES_FOR_LARGE_SITE = 1000; // Alert if more than this many pages
                    const MAX_MEDIA_FOR_LARGE_SITE = 100; // AND less than this many media files
                    const MIN_MEDIA_FOR_LARGE_LIBRARY = 50000; // Alert if more than this many media files
                    const MIN_MEDIA_FOR_MEDIUM_LIBRARY = 20000; // Alert if more than this many media files (with pages context)
                    
                    const ratio = data.pages_to_media_ratio || 0;
                    const totalPages = data.total_pages || 0;
                    const totalMedia = data.total || 0;
                    
                    // Check conditions for high pages-to-media ratio
                    const ratioTooHigh = ratio > MAX_RATIO;
                    const largeSiteLowMedia = totalPages > MIN_PAGES_FOR_LARGE_SITE && totalMedia < MAX_MEDIA_FOR_LARGE_SITE;
                    
                    // Check conditions for large media library
                    const veryLargeMediaLibrary = totalMedia > MIN_MEDIA_FOR_LARGE_LIBRARY;
                    const largeMediaLibraryWithPages = totalMedia > MIN_MEDIA_FOR_MEDIUM_LIBRARY && totalPages > 0;
                    
                    // Determine alert type
                    let alertType = null;
                    if (ratioTooHigh || largeSiteLowMedia) {
                        alertType = 'high_pages_ratio';
                    } else if (veryLargeMediaLibrary || largeMediaLibraryWithPages) {
                        alertType = 'large_media_library';
                    }
                    
                    console.log('[PageMediaRatioAlert] Stats:', {
                        totalPages,
                        totalMedia,
                        ratio,
                        ratioTooHigh,
                        largeSiteLowMedia,
                        veryLargeMediaLibrary,
                        largeMediaLibraryWithPages,
                        alertType,
                        shouldShowAlert: !!alertType
                    });
                    
                    if (alertType) {
                        console.log('[PageMediaRatioAlert] Alert triggered:', {
                            type: alertType,
                            reason: ratioTooHigh 
                                ? `Ratio ${ratio} > ${MAX_RATIO}` 
                                : largeSiteLowMedia
                                ? `Large site (${totalPages} pages, ${totalMedia} media)`
                                : veryLargeMediaLibrary
                                ? `Very large media library (${totalMedia} files)`
                                : `Large media library (${totalMedia} files, ${totalPages} pages)`,
                        });
                        setShowAlert(true);
                        setStats({ ...data, alertType });
                    }
                }
            })
            .catch((error) => {
                console.error("Error fetching stats for ratio alert:", error);
            });
    };

    const handleGoToSettings = () => {
        window.location.href = '/wp-admin/admin.php?page=options-ronik-base_settings_media_cleaner';
    };

    const handleDismiss = () => {
        setShowAlert(false);
    };

    if (!showAlert || !stats) {
        return null;
    }

    const isHighPagesRatio = stats.alertType === 'high_pages_ratio';
    const isLargeMediaLibrary = stats.alertType === 'large_media_library';

    return (
        <div style={{
            margin: '20px 0',
            padding: '15px 20px',
            backgroundColor: isHighPagesRatio ? '#fff3cd' : '#d1ecf1',
            border: `1px solid ${isHighPagesRatio ? '#ffc107' : '#0c5460'}`,
            borderRadius: '4px',
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            flexWrap: 'wrap',
            gap: '15px'
        }}>
            <div style={{ flex: '1', minWidth: '250px' }}>
                <strong style={{ 
                    display: 'block', 
                    marginBottom: '8px', 
                    color: isHighPagesRatio ? '#856404' : '#0c5460' 
                }}>
                    {isHighPagesRatio ? '⚠️ High Pages to Media Ratio Detected' : '📦 Large Media Library Detected'}
                </strong>
                <div>
                    <p style={{ 
                        margin: 0, 
                        marginBottom: '12px',
                        color: isHighPagesRatio ? '#856404' : '#0c5460', 
                        fontSize: '14px' 
                    }}>
                        {isHighPagesRatio ? (
                            <>
                                Your site has {stats.total_pages?.toLocaleString()} pages/posts but only {stats.total?.toLocaleString()} media files 
                                (ratio: {stats.pages_to_media_ratio}:1). This may cause slow scanning. 
                                Consider reducing the number of post types scanned in Settings to improve performance.
                            </>
                        ) : (
                            <>
                                Your site has a large media library with {stats.total?.toLocaleString()} media files 
                                {stats.total_pages > 0 && ` and ${stats.total_pages?.toLocaleString()} pages/posts`}. 
                                Scanning this many files may take a long time. Consider adjusting your file size settings 
                                in Settings to focus on larger files first, or reduce the number of post types scanned.
                            </>
                        )}
                    </p>
                    {stats.file_size_distribution && stats.current_file_size_setting !== undefined && (
                        <FileSizeRecommendation 
                            distribution={stats.file_size_distribution}
                            currentSetting={stats.current_file_size_setting}
                            totalMedia={stats.total}
                        />
                    )}
                </div>
            </div>
            <div style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                <button
                    onClick={handleGoToSettings}
                    style={{
                        padding: '8px 16px',
                        backgroundColor: '#0073aa',
                        color: 'white',
                        border: 'none',
                        borderRadius: '4px',
                        cursor: 'pointer',
                        fontSize: '14px',
                        fontWeight: '500'
                    }}
                    onMouseOver={(e) => e.target.style.backgroundColor = '#005a87'}
                    onMouseOut={(e) => e.target.style.backgroundColor = '#0073aa'}
                >
                    Go to Settings
                </button>
                <button
                    onClick={handleDismiss}
                    style={{
                        padding: '8px 16px',
                        backgroundColor: 'transparent',
                        color: isHighPagesRatio ? '#856404' : '#0c5460',
                        border: `1px solid ${isHighPagesRatio ? '#856404' : '#0c5460'}`,
                        borderRadius: '4px',
                        cursor: 'pointer',
                        fontSize: '14px'
                    }}
                    onMouseOver={(e) => {
                        e.target.style.backgroundColor = isHighPagesRatio ? '#856404' : '#0c5460';
                        e.target.style.color = 'white';
                    }}
                    onMouseOut={(e) => {
                        e.target.style.backgroundColor = 'transparent';
                        e.target.style.color = isHighPagesRatio ? '#856404' : '#0c5460';
                    }}
                >
                    Dismiss
                </button>
            </div>
        </div>
    );
};

export default PageMediaRatioAlert;

