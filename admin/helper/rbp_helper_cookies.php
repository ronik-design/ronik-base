<?php
    // Prevent logging outside admin interface.
    if (!is_admin()) {
        return;
    }

    // &rbp_debug=valid&rbp_severity=severity_low
    // &rbp_debug=rbp_media_cleaner&rbp_severity=severity_low

    // ?rbp_debug=valid
    // &rbp_debug=valid
    // LOG all general errors Logs.
    if( isset($_GET['rbp_debug']) && $_GET['rbp_debug'] == 'valid' ){
        setcookie("RbpDebug", 'valid', time()+1500);  /* expire in 25 min */
    }
    // ?rbp_severity=severity_low
    // &rbp_severity=severity_low&rbp_severity=severity_low
    if( isset($_GET['rbp_severity']) && $_GET['rbp_severity'] == 'severity_low' ){
        setcookie("RbpSeverity", 'low', time()+1500);  /* expire in 25 min */
    }
    // ?rbp_debug=rbp_media_cleaner
    if( isset($_GET['rbp_debug']) && $_GET['rbp_debug'] == 'rbp_media_cleaner' ){
        setcookie("RbpDebug", 'rbp_media_cleaner', time()+1500);  /* expire in 25 min */
    }


