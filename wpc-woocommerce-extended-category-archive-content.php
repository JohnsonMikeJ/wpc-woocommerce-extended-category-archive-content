<?php
/*
 * WPComplete Woocommerce Extended Category Archive Content
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
 * Version:     0.1.5
 * Author:      JohnsonMikej
 * Author URI:  https://profiles.wordpress.org/johnsonmikej
 * Text Domain: wpc-wecac
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

$wpc_wecac_mu_plugins = get_mu_plugins();

if ( ! isset( $wpc_wecac_mu_plugins['wpc-mu-autoloader.php']  ) ) {
    add_action('admin_notices', function() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong><?php _e('WPComplete Woocommerce extended category descriptions not loaded.', 'wpc-wecac'); ?></strong>
                <?php _e('WPComplete Must-Use Auto Loader not detected.', 'wpc-wecac'); ?>
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
                <strong><?php _e('WPComplete Woocommerce extended category descriptions not loaded.', 'wpc-wecac'); ?></strong>
                <?php _e('Woocommerce not detected.', 'wpc-wecac'); ?>
            </p>
        </div>
        <?php
    });
}
else
{
    // Edit term page
    add_action( 'product_cat_edit_form_fields', 'wpc_wecac_action_product_cat_edit_form_fields', 10, 2 );

    function wpc_wecac_action_product_cat_edit_form_fields($wpc_wecac_term) {

        // put the term ID into a variable
        $wpc_wecac_t_id = $wpc_wecac_term->term_id;

        // retrieve the existing value(s) for this meta field. This returns an array
        $wpc_wecac_term_meta = get_option( "taxonomy_{$wpc_wecac_t_id}" );
        $wpc_wecac_content = $wpc_wecac_term_meta['wpc_wecac_custom_term_meta_extended_description'] ? wp_kses_post( $wpc_wecac_term_meta['wpc_wecac_custom_term_meta_extended_description'] ) : '';
        $wpc_wecac_settings = array( 'textarea_name' => 'term_meta[wpc_wecac_custom_term_meta_extended_description]' );
        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
            <label for="term_meta[wpc_wecac_custom_term_meta_extended_description]">
                <?php _e( 'Extended Description', 'wpc-wecac' ); ?>
            </label>
        </th>
        <td>
            <?php wp_editor( $wpc_wecac_content, 'product_cat_details', $wpc_wecac_settings ); ?>
            <p class="description">
                <?php _e( 'Detailed category info to appear below the product list','wpc-wecac' ); ?>
            </p>
        </td>
        </tr>
    <?php
    }

    // Save extra taxonomy fields callback function - Edit term page
    add_action( 'edited_product_cat', 'wpc_wecac_action_edited_product_cat', 10, 2 );
    add_action( 'create_product_cat', 'wpc_wecac_action_edited_product_cat', 10, 2 );

    function wpc_wecac_action_edited_product_cat( $wpc_wecac_term_id ) {
        if ( isset( $_POST['term_meta'] ) ) {
            $wpc_wecac_t_id = $wpc_wecac_term_id;
            $wpc_wecac_term_meta = get_option( "taxonomy_{$wpc_wecac_t_id}" );
            $wpc_wecac_cat_keys = array_keys( $_POST['term_meta'] );
            foreach ( $wpc_wecac_cat_keys as $wpc_wecac_key ) {
                if ( isset ( $_POST['term_meta'][$wpc_wecac_key] ) && strpos($wpc_wecac_key, 'wpc_wecac_') === 0 ) {
                    $wpc_wecac_term_meta[$wpc_wecac_key] = wp_kses_post( stripslashes($_POST['term_meta'][$wpc_wecac_key]) );
                }
            }
            // Save the option array.
            update_option( "taxonomy_{$wpc_wecac_t_id}", $wpc_wecac_term_meta );
        }
    }

    // Display details on product category archive pages
    add_action( 'woocommerce_after_shop_loop', 'wpc_wecac_action_woocommerce_after_shop_loop' );

    function wpc_wecac_action_woocommerce_after_shop_loop() {
        $wpc_wecac_t_id = get_queried_object()->term_id;
        $wpc_wecac_term_meta = get_option( "taxonomy_{$wpc_wecac_t_id}" );
        $wpc_wecac_term_meta_content = $wpc_wecac_term_meta['wpc_wecac_custom_term_meta_extended_description'];
        if ( $wpc_wecac_term_meta_content != '' ) {
            echo '<div class="woo-sc-box normal rounded full">';
            echo apply_filters( 'the_content', $wpc_wecac_term_meta_content );
            echo '</div>';
        }
    }
}
