<?php
/**
 * Plugin Name:       LC Tracking Codes
 * Description:       LC Tracking Codes make easier put tracking codes in your site.
 * Version:           1.0.3
 * Author:            Luciano Closs
 * Author URI:        https://lucianocloss.com
 * Text Domain:       lcmd-tracking-codes
 * License:           AGPL-3.0+
 * License URI:       https://www.gnu.org/licenses/agpl-3.0.en.html
 * Domain Path:       lcmd-tracking-codes
 * GitHub Plugin URI: https://github.com/lcloss/lcmd-tracking-codes
 */

 /*
   Copyright (C) 2018  Luciano Closs

   This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as
   published by the Free Software Foundation, either version 3 of the
   License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace LCMD;

// Some security
if ( ! defined('ABSPATH') ) {
   die(__('You should not be here. Stay in peace') );
}

require_once( dirname(__FILE__) . '/lib/lcmd_template_class.php' );
require_once( dirname(__FILE__) . '/includes/functions.php' );

use LCMD\Template;

class LCMD_Tracking_Codes {
   const TEXT_DOMAIN = 'lcmd-tracking-codes';
   const PLUGIN_NAME = 'LC Tracking Codes';

   private static $instance;
   protected static $plugin_path;

   /**
    * Handle plugin instance
    */
   public static function getInstance() 
   {
       if (self::$instance == NULL) {
           self::$instance = new self();
       }

       return self::$instance;
   }

   /**
    * The getter for text domain
    */
   public static function get_text_domain()
   {
      return self::TEXT_DOMAIN;
   }

   /**
    * The getter for plugin name
    */
   public static function get_plugin_name()
   {
      return self::PLUGIN_NAME;
   }

   /**
    * The getter for plugin path
    */
   public static function get_plugin_path( $path_to = '' )
   {
      return self::$plugin_path . $path_to;
   }

   /**
    * Constructor
    */
   public function __construct()
   {
      self::$plugin_path = dirname( __FILE__ );

      // Handle internacionalization
      add_action( 'init', array( $this, 'load_text_domain' ) );

      // Add scripts
      add_action( 'wp_head', array( $this, 'add_google_analytics' ) );
      // Add Meta Search Console
      add_action( 'wp_head', array( $this, 'add_google_search_console' ) , 2 );
      // Add Meta Bing Webmaster
      add_action( 'wp_head', array( $this, 'add_bing_webmaster' ) , 2 );

      // Add Contact Form 7 Google Analytics
      add_action( 'wp_footer', array( $this, 'add_contact_form_7_google_analytics' ) , 2 );
      
      // Add General Tracking Code
      add_action( 'wp_footer', array( $this, 'add_general_code' ) );

      // Add admin page
      add_action( 'admin_menu', array( $this, 'add_admin_page' ) );

      // Register admin style
      add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );
      add_action( 'admin_footer', array($this, 'add_admin_inline_scripts') );

      // Extends API to delete option
      add_action( 'rest_api_init', function() {
         register_rest_route( 'lcmd-tracking-codes/v1', '/option/delete/(?P<field>[\w_]+)', array(
            'methods'   => 'GET',
            'callback'  => array($this, 'delete_option')
         ));
      });

      // Register settings
      add_action( 'admin_init', array( $this, 'register_settings' ) );
   }

   /**
    * Load language file
    */
   public function load_text_domain()
   {
      $language_path = basename( self::get_plugin_path() ) . '/languages';
      load_plugin_textdomain( self::get_text_domain(), false, $language_path );
   }

   /**
    * Add admin manage page
    */
   public function add_admin_page() 
   {
      $page_title = self::get_plugin_name();
      $menu_title = $page_title;
      $capability = 'manage_options';
      $menu_slug = self::get_text_domain();
      $callback_function = 'LCMD\LCMD_Tracking_Codes::show_admin_page';
      $icon = 'dashicons-editor-code';
      $position = 25;

      add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback_function, $icon, $position);
   }

   /**
    * Add admin scripts
    * Only on this page.
    */
   public function add_admin_scripts( $hook )
   {
      if ( 'toplevel_page_lcmd-tracking-codes' != $hook ) {
         return;
      }
      wp_enqueue_style( 'lcmd-tracking-codes-style', \plugins_url( 'includes/css/admin_options.css', __FILE__ ) );
   }

   public function add_admin_inline_scripts()
   {
      echo '
      <script type="text/javascript">
      jQuery(document).ready(function($) {
         $(\'#file_link_delete\').click(function(e) {
             let filename = $(\'#file_link_delete\').attr(\'file\');
             $.ajax({
                 type: \'get\',
                 contentType: \'application/json\',
                 url:  \'' . \site_url( '/wp-json/lcmd-tracking-codes/v1/option/delete/') . '\' + filename,
     
             }).done(function(data) {
               $(\'#file_link\').text(data);
     
             }).fail(function(data) {
                 if (data.response !== \'\') {
                     $(\'#file_link\').text(data.response);
                 } else {
                     $(\'#file_link\').text(\'Something going wrong.\');
                 }
             });
         });
      });
      </script>
      ';
   }

   /**
   * Delete one item from the options
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
   public function delete_option( $data ) 
   {
      $file = get_option( $data['field'] );
      if ( empty( $file ) ) {
         return new \WP_Error( 'no_option', __('File not found.', self::get_text_domain() ), array( 'status' => 404 ) );
      }
 
      if ( unlink( $file ) ) {
         delete_option( $data['field'] );
         $response = __('File deleted successfully.', self::get_text_domain() );
         return new \WP_REST_Response( $response, 200 );
      } else {
         return new \WP_Error( 'bad_request', __( 'Failed on file delete', 'lcmd-tracking-codes' ), array( 'status' => 500 ) );
      }

   }

   public function register_settings()
   {
      $settings_group = 'lcmd_tracking_codes_settings';
      register_setting(  $settings_group . '_settings', 'lcmd_hide_when_auth' );   // Settings: Hide when user is authenticated
      register_setting(  $settings_group . '_google', 'lcmd_gscvc' );   // Google Search Console Verification Code
      register_setting(  $settings_group . '_google', 'lcmd_gscvf' );   // Google Search Console Verification File
      register_setting(  $settings_group . '_google', 'lcmd_gau' );     // Google Analitics Userid
      register_setting(  $settings_group . '_google', 'lcmd_gtm' );     // Google Tag Manager
      register_setting(  $settings_group . '_google', 'lcmd_gad' );     // Google Ads
      // register_setting(  $settings_group . '_google', 'lcmd_grc' );     // Google Remarketing Code
      register_setting(  $settings_group . '_bing', 'lcmd_bc' );        // Bing Code
      register_setting(  $settings_group . '_bing', 'lcmd_bcf' );       // Bing Verification File
      register_setting(  $settings_group . '_wpcf7', 'lcmd_cf7ga' );    // Contact Form 7 - Google Analytics
      register_setting(  $settings_group . '_general', 'lcmd_general' );   // General Code
   }

   /**
    * Show admin page options
    */
   public function show_admin_page()
   {
      /**
       * Check permission to do that
       */
      if ( ! current_user_can('manage_options') ) {
         return;
      }

      $views_path = self::get_plugin_path('/includes/views/');
      if ( !isset($_GET['tab']) ) {
         $tab = 'google';
      } else {
         $tab = $_GET['tab'];
      }

      $settings_fields = array(
         /* Hide on Admin */
         array(
            'name'   => 'lcmd_hide_when_auth',
            'id'     => 'lcmd_hide_when_auth',
            'label'  => __('Hide when a logged user is navigating', self::get_text_domain() ),
            'help'   => __('Set to true to hide all codes when a logged user is navigating through the site.', self::get_text_domain() ),
         ),
      );

      $google_fields = array(
         /* Google Search Console Verification Code */
         array(
            'name'   => 'lcmd_gsvc',
            'id'     => 'lcmd_gsvc',
            'label'  => __('Google Search Console Verification Code', self::get_text_domain() ),
            'placeholder'  => __( 'Verification code', self::get_text_domain() ),
            'help'   => __('Enter your Google Site Verification Code. Please, refer to <a href="https://search.google.com/search-console/about" target="_blank">Google Search Console</a> for more information.</a>', self::get_text_domain() ),
            'validate'  => 'is_google_verification_code',
            'error_msg' => __( 'Invalid format for Google Verification Code. Please, enter only letters, numbers and/or underscore.', self::get_text_domain() )
         ),
         /* Google Search Console Verification File */
         array(
            'name'   => 'lcmd_gsvf',
            'id'     => 'lcmd_gsvf',
            'label'  => __('...or Google Search Console Verification File', self::get_text_domain() ),
            'placeholder'  => '',
            'help'   => __('Upload your verification file. Please, refer to <a href="https://search.google.com/search-console/about" target="_blank">Google Search Console</a> for more information.</a>', self::get_text_domain() ),
            'validate'  => 'is_google_search_console_file_ok',
            'error_msg' => __( 'This is not seem to be a valid Google Search Console file. Please, verify the file and resubmit.', self::get_text_domain() ),
         ),
         /* Google Analytics Userid */
         array(
            'name'   => 'lcmd_gau',
            'id'     => 'lcmd_gau',
            'label'  => __('Google Analytics User', self::get_text_domain() ),
            'placeholder'  => __( 'UA-XXXXXX-X', self::get_text_domain() ),
            'help'   => __('Enter your ID Tracking Code for Google Analytics. Please, refer to <a href="https://analytics.google.com/analytics/web/" target="_blank">Google Analytics</a> for more information.', self::get_text_domain() ),
            'validate'  => 'is_google_analytics_id',
            'error_msg' => __( 'Invalid format for Google Analytics. Please, enter a UA-XXXX code.', self::get_text_domain() )
         ),
         /* Google Tag Manager */
         array(
            'name'   => 'lcmd_gtm',
            'id'     => 'lcmd_gtm',
            'label'  => __('Google Tag Manager', self::get_text_domain() ),
            'placeholder'  => __( 'GTM-XXXXXX', self::get_text_domain() ),
            'help'   => __('Enter your ID Google Tag Manager. Please, refer to <a href="https://tagmanager.google.com" target="_blank">Google Tag Manager</a> for more information.', self::get_text_domain() ),
            'validate'  => 'is_google_tag_manager_id',
            'error_msg' => __( 'Invalid format for Google Tag Manager Id. Please, enter a GTM-XXXX code.', self::get_text_domain() )
         ),
         /* Google Ads */
         array(
            'name'   => 'lcmd_gad',
            'id'     => 'lcmd_gad',
            'label'  => __('Google Ads', self::get_text_domain() ),
            'placeholder'  => __( 'GA-XXXXXX', self::get_text_domain() ),
            'help'   => __('Enter your ID Google Ads. Please, refer to <a href="https://ads.google.com" target="_blank">Google Ads</a> for more information.', self::get_text_domain() )
         ),
         /* Google Remarketing Code */
         /*
         array(
            'name'   => 'lcmd_grc',
            'id'     => 'lcmd_grc',
            'label'  => __('Google Remarketing', self::get_text_domain() ),
            'placeholder'  => __( 'XXXXXXXX', self::get_text_domain() ),
            'help'   => __('Enter your ID Google Remarketing Code. Please, refer to <a href="https://support.google.com/google-ads/answer/2476688?co=ADWORDS.IsAWNCustomer%3Dtrue&hl=pt-BR&oco=0" target="_blank">Google Remarketing</a> for more information.', self::get_text_domain() )
         ),
         */
      );
      $bing_fields = array(
         /* Bing Code */
         array(
            'name'   => 'lcmd_bc',
            'id'     => 'lcmd_bc',
            'label'  => __('Bing Code', self::get_text_domain() ),
            'help'   => __('Enter your Bing Code. Please, refer to <a href="https://www.bing.com/toolbox/webmaster" target="_blank">Bing Webmaster</a> for more information.', self::get_text_domain() ),
            'validate'  => 'is_bing_code',
            'error_msg' => __( 'Invalid format for your Bing Code. Please, enter only numbers and letters.', self::get_text_domain() )
         ),
         /* Bing Code File */
         array(
            'name'   => 'lcmd_bcf',
            'id'     => 'lcmd_bcf',
            'label'  => __('...or Bing Code File', self::get_text_domain() ),
            'help'   => __('Upload your Bing File. Please, refer to <a href="https://www.bing.com/toolbox/webmaster" target="_blank">Bing Webmaster</a> for more information.', self::get_text_domain() ),
            'validate'  => 'is_bing_webmaster_file_ok',
            'error_msg' => __( 'This is not seem to be a valid Bing Webmaster file. Please, verify the file and resubmit.', self::get_text_domain() ),
         )
      );

      $wpcf7_fields = array(
         /* Contact Form 7 - Google Analytics */
         array(
            'name'   => 'lcmd_cf7ga',
            'id'     => 'lcmd_cf7ga',
            'label'  => __('Do you want track Contact Form 7 submission no Google Analytics?', self::get_text_domain()),
            'help'   => __('Set this to true to start tag forms submission.', self::get_text_domain()),
         )
      );

      $general_fields = array(
         /* General fields */
         array(
            'name'   => 'lcmd_general',
            'id'     => 'lcmd_general',
            'label'  => __('General Tracking Code', self::get_text_domain() ),
            'help'   => __('Enter a custom tracking code.', self::get_text_domain() )
         )
      );

      /**
       * Get field values
       */
      /* Settings Fields */
      foreach( $settings_fields as $i => $field ) {
         $settings_fields[$i]['value'] = get_option($field['name']);
      }

      /* Google Fields */
      foreach( $google_fields as $i => $field ) {
         if ( 'lcmd_gsvf' != $field['name'] ) {
            $google_fields[$i]['value'] = get_option($field['name']);
         } else {
            $file_name = get_option( $field['name'] . '_name' );
            if ( ! empty($file_name) ) {
               $google_fields[$i]['value'] = home_url( get_option($field['name'] . '_name' ) );
            } else {
               $google_fields[$i]['value'] = '';
            }
         }
      }
      /* Bing Fields */
      foreach( $bing_fields as $i => $field ) {
         if ( 'lcmd_bcf' != $field['name'] ) {
            $bing_fields[$i]['value'] = get_option($field['name']);
         } else {
            $file_name = get_option( $field['name'] . '_name' );
            if ( ! empty($file_name) ) {
               $bing_fields[$i]['value'] = home_url( get_option($field['name'] . '_name' ) );
            } else {
               $bing_fields[$i]['value'] = '';
            }
         }
      }
      /* Contact Form 7 Fields */
      foreach( $wpcf7_fields as $i => $field ) {
         $wpcf7_fields[$i]['value'] = get_option($field['name']);
      }
      /* General field */
      foreach( $general_fields as $i => $field ) {
         $general_fields[$i]['value'] = get_option($field['name']);
      }

      $errors = array();

      /**
       * Validate and sanitize and update fields
       */
      if ( isset($_POST['tab']) ) {
         switch( $_POST['tab'] ) {

            /* Settings Fields Validation */
            case 'settings':
               foreach( $settings_fields as $i => $field ) {
                  $has_error = false;
                  $p_field = sanitize_text_field( $_POST[$field['name']] );

                  if ( isset($field['validate']) ) {
                     $e = call_user_func( __NAMESPACE__ . '\\' . $field['validate'], $p_field);
                     if ( ! $e ) {
                        $errors[] = array(
                           'id'  => $field['id'],
                           'msg' => $field['error_msg']
                        );
                        $has_error = true;
                     }
                  }
                  
                  if ( ! $has_error ) {
                     update_option($field['name'], $p_field);
                     $settings_fields[$i]['value'] = $p_field;
                  }
               }
               break;

            /* Google Fields Validation*/
            case 'google':
               foreach( $google_fields as $i => $field ) {
                  $has_error = false;

                  if ( 'lcmd_gsvf' == $field['name'] ) {
                     // Prepare the upload
                     $dir = get_home_path();
                     $file_name = $_FILES[$field['name']]['name'];

                     if ( '' != $file_name ) {
                        $file_name = sanitize_file_name( $file_name );

                        if ( ! preg_match('/^google\w+\.html$/', $file_name) ) {
                           $errors[] = array(
                              'id'  => $field['id'],
                              'msg' => __( 'Invalid file name.', self::get_text_domain() )
                           );
                           $has_error = true;
                        }

                        if ( ! $has_error ) {
                           $target_file = $dir . '/' . basename( $file_name );
   
                           // Check if name is correct
                           move_uploaded_file($_FILES[$field['name']]['tmp_name'], $target_file);
                           update_option($field['name'] . '_name', $file_name);
                           $google_fields[$i]['value'] = home_url( $file_name );;
                        }
                     }
                  } else {
                     $p_field = sanitize_text_field( $_POST[$field['name']] );

                     if ( isset($field['validate']) ) {
                        $e = call_user_func( __NAMESPACE__ . '\\' . $field['validate'], $p_field);
                        if ( ! $e ) {
                           $errors[] = array(
                              'id'  => $field['id'],
                              'msg' => $field['error_msg']
                           );
                           $has_error = true;
                        }
                     }
                     
                     if ( ! $has_error ) {
                        update_option($field['name'], $p_field);
                        $google_fields[$i]['value'] = $p_field;
                     }
                  }
               }
               break;

            /* Bing Fields Validation */
            case 'bing':
               foreach( $bing_fields as $i => $field ) {
                  $has_error = false;

                  if ( 'lcmd_bcf' == $field['name'] ) {
                     // Prepare the upload
                     $dir = get_home_path();
                     $file_name = $_FILES[$field['name']]['name'];

                     $file_name = sanitize_file_name( $file_name );
                     
                     if ( 'BingSiteAuth.xml' != $file_name ) {
                        $errors[] = array(
                           'id'  => $field['id'],
                           'msg' => __( 'Invalid file name.', self::get_text_domain() )
                        );
                        $has_error = true;
                     }
                     if ( '' != $file_name && ! $has_error ) {
                        $target_file = $dir . '/' . basename( $file_name );

                        // Check if name is correct
                        move_uploaded_file($_FILES[$field['name']]['tmp_name'], $target_file);
                        update_option($field['name'] . '_name', $file_name);
                        $bing_fields[$i]['value'] = home_url( $file_name );;
                     }
                  } else {
                     $p_field = sanitize_text_field( $_POST[$field['name']] );

                     if ( isset($field['validate']) ) {
                        $e = call_user_func( __NAMESPACE__ . '\\' . $field['validate'], $p_field);
                        if ( ! $e ) {
                           $errors[] = array(
                              'id'  => $field['id'],
                              'msg' => $field['error_msg']
                           );
                           $has_error = true;
                        }
                     }
                     
                     if ( ! $has_error ) {
                        update_option($field['name'], $p_field);
                        $bing_fields[$i]['value'] = $p_field;
                     }
                  }
               }
               break;

            /* Contact Form 7 Fields Validation */
            case 'wpcf7':
               foreach( $wpcf7_fields as $i => $field ) {
                  $has_error = false;
                  $p_field = sanitize_text_field( $_POST[$field['name']] );

                  if ( isset($field['validate']) ) {
                     $e = call_user_func( __NAMESPACE__ . '\\' . $field['validate'], $p_field);
                     if ( ! $e ) {
                        $errors[] = array(
                           'id'  => $field['id'],
                           'msg' => $field['error_msg']
                        );
                        $has_error = true;
                     }
                  }
                  
                  if ( ! $has_error ) {
                     update_option($field['name'], $p_field);
                     $wpcf7_fields[$i]['value'] = $p_field;
                  }
               }
               break;

            /* General Fields Validation */
            case 'general':
               foreach( $general_fields as $i => $field ) {
                  $has_error = false;
                  $p_field = sanitize_text_field( $_POST[$field['name']] );

                  if ( isset($field['validate']) ) {
                     $e = call_user_func( __NAMESPACE__ . '\\' . $field['validate'], $p_field);
                     if ( ! $e ) {
                        $errors[] = array(
                           'id'  => $field['id'],
                           'msg' => $field['error_msg']
                        );
                        $has_error = true;
                     }
                  }
                  
                  if ( ! $has_error ) {
                     update_option($field['name'], $p_field);
                     $general_fields[$i]['value'] = $p_field;
                  }
               }
               break;
         }
      }

      if ( isset( $_POST['tab'] ) ) {
         if ( count($errors) == 0 ) {
            add_settings_error( 'lcmd_tracking_codes_messages', 'lcmd_tracking_code_message', __('Settings saved!', self::get_text_domain() ), 'updated' );
         } else {
            foreach($errors as $error) {
               add_settings_error( 'lcmd_tracking_codes_messages', 'setting-error-' . $error['id'], $error['msg'], 'error' );            
            }
         }
         
      }
      include self::get_plugin_path() . '/includes/views/admin_options.php';
   }

   public function is_hidden() 
   {

      $hide_when = ( get_option('lcmd_hide_when_auth', "0") );
      
      if ( empty( $hide_when ) ) {
         $hide = false;
      } else {
         $hide_when = intval($hide_when);

         if ( $hide_when && is_user_logged_in() ) {
            $hide = true;
         } else {
            $hide = false;
         }
      }

      return $hide;
   }

   /**
    * Add Google Search Console Meta Tag
    */
   public function add_google_search_console() {
      $gsc_code = get_option('lcmd_gsvc');
      if ( ! empty($gsc_code) && ! $this->is_hidden() ) {
         echo '<meta name="google-site-verification" content="' . $gsc_code . '">';
      }
   }

   /**
    * Add Google Analytics Code
    */
   public function add_google_analytics() {
      $ga_uid = get_option('lcmd_gau');
      if ( ! empty($ga_uid) && ! $this->is_hidden() ) {
        echo '
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=' . $ga_uid . '"></script>
        <script>
         window.dataLayer = window.dataLayer || [];
         function gtag(){dataLayer.push(arguments);}
         gtag(\'js\', new Date());

         gtag(\'config\', \''. $ga_uid . '\');
        </script>
        ';
      }
   }

   /**
    * Add Bing Webmaster Meta Tag
    */
    public function add_bing_webmaster() {
      $bw_code = get_option('lcmd_bc');
      if ( ! empty($bw_code) && ! $this->is_hidden() ) {
         echo '<meta name="msvalidate.01" content="' . $bw_code . '">';
      }
   }

   /**
    * Add Contact Form 7 Google Analytics
    */
    public function add_contact_form_7_google_analytics() {
      $cf7_ga = get_option('lcmd_cf7ga');
      if ( ! empty($cf7_ga) && ! $this->is_hidden()  ) {
        echo '
        <script>
         document.addEventListener( \'wpcf7mailsent\', function( event ) {
            ga(\'send\', \'event\', \'Contact Form\', \'submit\');
         }, false );
         </script>
        ';
      }
   }

   /**
    * Custom Tracking Code
    */
   public function add_general_code() 
   {
      $general = get_option('lcmd_general');
      // Allway publish general code.
      echo '<script>' . esc_js( $general ) . '</script>';
   }
}

/**
 * Initialize and run
 */
LCMD_Tracking_Codes::getInstance();

?>