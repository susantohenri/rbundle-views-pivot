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
            `entry_id` bigint(20) DEFAULT NULL,
            `user_id` bigint(20) DEFAULT NULL,
            `meta_value` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `{$wpdb->prefix}frm_views_pivot_views_id_idx` (`views_id`) USING BTREE,
            KEY `{$wpdb->prefix}frm_views_pivot_form_id_idx` (`form_id`) USING BTREE,
            KEY `{$wpdb->prefix}frm_views_pivot_column_name_idx` (`column_name`) USING BTREE,
            KEY `{$wpdb->prefix}frm_views_pivot_entry_id_idx` (`entry_id`) USING BTREE,
            KEY `{$wpdb->prefix}frm_views_pivot_user_id_idx` (`user_id`) USING BTREE
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ");

    $user_ids = array_map(function ($record) {
        return $record->ID;
    }, $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}users"));
    foreach (RBUNDLE_VIEWS_PIVOT_CONFIG as $form_id => $functions) {
        $entry_ids = array_map(function ($record) {
            return $record->id;
        }, $wpdb->get_results("SELECT id FROM {$wpdb->prefix}frm_items"));

        foreach ($user_ids as $user_id) {
            foreach ($entry_ids as $entry_id) {
                foreach ($functions  as $function) {
                    $function_name = "rvp_{$form_id}_{$function}";
                    $function_name($entry_id, $user_id);
                }
            }
        }
    }
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
    return $query;
}, 10, 2);

add_shortcode('rbundle-pivot-value', function ($atts) {
    $user_id = get_current_user_id();
});

function rvp_58_star_shortlist($entry_id, $user_id)
{
}

function rvp_58_your_proposal($entry_id, $user_id)
{
}

function rvp_58_compatibility($entry_id, $user_id)
{
}

function rvp_58_user_rating($entry_id, $user_id)
{
}