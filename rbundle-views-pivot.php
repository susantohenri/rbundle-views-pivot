<?php

/**
 * RBundle Views Pivot
 *
 * @package     RBundleViewsPivot
 * @author      Henri Susanto
 * @copyright   2022 Henri Susanto
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: RBundle Views Pivot
 * Plugin URI:  https://github.com/susantohenri/rbundle-views-pivot
 * Description: When you need to show data in views base on logged in user (with faster loading & sortable)
 * Version:     1.0.0
 * Author:      Henri Susanto
 * Author URI:  https://github.com/susantohenri/
 * Text Domain: RBundleViewsPivot
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

define('RBUNDLE_VIEWS_PIVOT_CONFIG', [
    58 => [
        'star_shortlist',
        'your_proposal',
        'compatibility',
        'star_shortlist',
        'user_rating',
    ]
]);

register_activation_hook(__FILE__, function () {
    global $wpdb;
    $wpdb->query("
        CREATE TABLE `{$wpdb->prefix}frm_views_pivot` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `views_id` bigint(20) DEFAULT NULL,
            `form_id` bigint(20) DEFAULT NULL,
            `column_name` varchar(255) DEFAULT NULL,
            `user_id` bigint(20) DEFAULT NULL,
            `meta_value` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ");
});

register_deactivation_hook(__FILE__, function () {
    global $wpdb;
    $wpdb->query("DROP TABLE `{$wpdb->prefix}frm_views_pivot`");
});

add_action('frm_after_create_entry', function ($entry_id, $form_id) {
}, 30, 2);

add_action('frm_after_update_entry', function ($entry_id, $form_id) {
}, 10, 2);

add_action('frm_before_destroy_entry', function ($entry_id) {
});

add_filter('frm_view_order', function ($query, $args) {
}, 10, 2);

add_shortcode('rbundle-pivot-value', function ($atts) {
    $user_id = get_current_user_id();
});
