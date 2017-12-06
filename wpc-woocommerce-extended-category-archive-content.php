<?php
/*
 * WP Complete Must-Use Plugin Autoloader
 *
 * @package     wpc-woocommerce-extended-category-archive-content
 * @author      Johnson, Mike J.
 * @copyright   2017 Mike J Johnson.
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: WPComplete Woocommerce extended category archive content
 * Plugin URI:  https://github.com/JohnsonMikeJ/wpc-woocommerce-extended-category-archive-content/
 * Description: Adds content below the thumbnails on Woocommerce category archive pages.
 * Version:     0.1.0
 * Author:      JohnsonMikej
 * Author URI:  https://profiles.wordpress.org/johnsonmikej
 * Text Domain: wpc-woocommerce-extended-category-archive-content
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Adds content below the thumbnails on Woocommerce category archive pages.
 *
*/

// fly fly away little script kiddies
defined( 'ABSPATH' ) || die( '-1' );


/*
 * Check if WooCommerce is active
*/

$wpc_mu_plugins = get_mu_plugins();

if ( ! isset( $wpc_mu_plugins['wpc-mu-autoloader.php']  ) ) {
    add_action('admin_notices', function() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong><?php _e('WPComplete Woocommerce extended category descriptions not loaded.', 'wpc-woocommerce-extended-category-archive-content'); ?></strong>
                <?php _e('WPComplete Must-Use Auto Loader not detected.', 'wpc-woocommerce-extended-category-archive-content'); ?>
            </p>
        </div>
        <?php
    });
}
elseif ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action('admin_notices', function() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong><?php _e('WPComplete Woocommerce extended category descriptions not loaded.', 'wpc-woocommerce-extended-category-archive-content'); ?></strong>
                <?php _e('Woocommerce not detected.', 'wpc-woocommerce-extended-category-archive-content'); ?>
            </p>
        </div>
        <?php
    });
}
else
{
    // Edit term page
    add_action( 'product_cat_edit_form_fields', 'wpc_action_product_cat_edit_form_fields', 10, 2 );
    function wpc_action_product_cat_edit_form_fields($wpc_term) {

      // put the term ID into a variable
      $wpc_t_id = $wpc_term->term_id;

      // retrieve the existing value(s) for this meta field. This returns an array
      $wpc_term_meta = get_option( "taxonomy_$wpc_t_id" );
      $wpc_content = $wpc_term_meta['custom_term_meta'] ? wp_kses_post( $wpc_term_meta['custom_term_meta'] ) : '';
      $wpc_settings = array( 'textarea_name' => 'term_meta[custom_term_meta]' );
      ?>
      <tr class="form-field">
      <th scope="row" valign="top"><label for="term_meta[custom_term_meta]"><?php _e( 'Details', 'wpc-woocommerce-extended-category-archive-content' ); ?></label></th>
        <td>
          <?php wp_editor( $wpc_content, 'product_cat_details', $wpc_settings ); ?>
          <p class="description"><?php _e( 'Detailed category info to appear below the product list','wpc-woocommerce-extended-category-archive-content' ); ?></p>
        </td>
      </tr>
    <?php
    }


    // Save extra taxonomy fields callback function
    add_action( 'edited_product_cat', 'wpc_action_edited_product_cat', 10, 2 );
    add_action( 'create_product_cat', 'wpc_action_edited_product_cat', 10, 2 );
    function wpc_action_edited_product_cat( $wpc_term_id ) {
      if ( isset( $wpc__POST['term_meta'] ) ) {
        $wpc_t_id = $wpc_term_id;
        $wpc_term_meta = get_option( "taxonomy_$wpc_t_id" );
        $wpc_cat_keys = array_keys( $wpc__POST['term_meta'] );
        foreach ( $wpc_cat_keys as $wpc_key ) {
          if ( isset ( $wpc__POST['term_meta'][$wpc_key] ) ) {
            $wpc_term_meta[$wpc_key] = wp_kses_post( stripslashes($wpc__POST['term_meta'][$wpc_key]) );
          }
        }
        // Save the option array.
        update_option( "taxonomy_$wpc_t_id", $wpc_term_meta );
      }
    }

    // Display details on product category archive pages
    add_action( 'woocommerce_after_shop_loop', 'wpc_action_woocommerce_after_shop_loop' );
    function wpc_action_woocommerce_after_shop_loop() {
      $wpc_t_id = get_queried_object()->term_id;
      $wpc_term_meta = get_option( "taxonomy_$wpc_t_id" );
      $wpc_term_meta_content = $wpc_term_meta['custom_term_meta'];
      if ( $wpc_term_meta_content != '' ) {
        echo '<div class="woo-sc-box normal rounded full">';
          echo apply_filters( 'the_content', $wpc_term_meta_content );
        echo '</div>';
      }
    }
}

