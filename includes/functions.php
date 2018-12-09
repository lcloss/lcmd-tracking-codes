<?php
namespace LCMD;

if ( ! function_exists( 'is_google_analytics_id' ) ) {
    function is_google_analytics_id( $ga_code ) {
        if ( '' == $ga_code ) {
            return true;
        }

        if ( preg_match('/^UA-\d{4-10}-\d{1,4}$/i', $ga_code) ) {
            return true;
        } else {
            return false;
        }
    }
}
if ( ! function_exists( 'is_google_search_console_file_ok' ) ) {
    function is_google_search_console_file_ok( $gsc_filename ) {
        if ( '' == $gsc_filename ) {
            return true;
        }

        if ( preg_match('/^google[\w]+\.html$/i', $gsc_filename) ) {
            return true;
        } else {
            return false;
        }
    }
}
if ( ! function_exists( 'is_google_verification_code' ) ) {
    function is_google_verification_code( $gv_code ) {
        if ( '' == $gv_code ) {
            return true;
        }
        if ( preg_match('/^[\w_]{5-40}$/i', $gv_code) ) {
            return true;
        } else {
            return false;
        }
    }
}
if ( ! function_exists( 'is_google_tag_manager_id' ) ) {
    function is_google_tag_manager_id( $gtm_id ) {
        if ( '' == $gtm_id ) {
            return true;
        }
        if ( preg_match('/^GTM-\d{1, 9}$/i', $gtm_id) ) {
            return true;
        } else {
            return false;
        }
    }
}
if ( ! function_exists( 'is_bing_code' ) ) {
    function is_bing_code( $ms_bc ) {
        if ( '' == $ms_bc ) {
            return true;
        }
        if ( preg_match('/^[\w]{5, 30}$/i', $ms_bc) ) {
            return true;
        } else {
            return false;
        }
    }
}
if ( ! function_exists( 'is_bing_webmaster_file_ok' ) ) {
    function is_bing_webmaster_file_ok( $bw_filename ) {
        if ( '' == $bw_filename ) {
            return true;
        }

        if ( 'BingSiteAuth.xml' == $bw_filename ) {
            return true;
        } else {
            return false;
        }
    }
}


?>