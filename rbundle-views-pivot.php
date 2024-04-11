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
define('RBUNDLE_VIEWS_PIVOT_TABLE_NAME', 'frm_views_pivot');

register_activation_hook(__FILE__, function () {
    global $wpdb;
    $table_name = $wpdb->prefix . RBUNDLE_VIEWS_PIVOT_TABLE_NAME;
    $wpdb->query("
        CREATE TABLE `{$table_name}` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `form_id` bigint(20) DEFAULT NULL,
            `column_name` varchar(255) DEFAULT NULL,
            `entry_id` bigint(20) DEFAULT NULL,
            `user_id` bigint(20) DEFAULT NULL,
            `meta_value` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `{$table_name}_form_id_idx` (`form_id`) USING BTREE,
            KEY `{$table_name}_column_name_idx` (`column_name`) USING BTREE,
            KEY `{$table_name}_entry_id_idx` (`entry_id`) USING BTREE,
            KEY `{$table_name}_user_id_idx` (`user_id`) USING BTREE
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ");

    $user_ids = array_map(function ($record) {
        return $record->ID;
    }, $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}users"));
    foreach (RBUNDLE_VIEWS_PIVOT_CONFIG as $form_id => $functions) {
        $entry_ids = array_map(function ($record) {
            return $record->id;
        }, $wpdb->get_results("SELECT id FROM {$wpdb->prefix}frm_items WHERE form_id = {$form_id}"));

        foreach ($user_ids as $user_id) {
            foreach ($entry_ids as $entry_id) {
                // rvp_clean_up($entry_id, $user_id);
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
    $table_name = $wpdb->prefix . RBUNDLE_VIEWS_PIVOT_TABLE_NAME;
    $wpdb->query("DROP TABLE `{$table_name}`");
});

add_action('frm_after_create_entry', function ($entry_id, $form_id) {
}, 30, 2);

add_action('frm_after_update_entry', function ($entry_id, $form_id) {
}, 10, 2);

add_action('frm_before_destroy_entry', function ($entry_id) {
});

add_filter('frm_view_order', function ($query, $args) {
    if (isset($args['order_by_array'][0])) {
        $column_names = [];
        foreach (RBUNDLE_VIEWS_PIVOT_CONFIG as $form_id => $col_names) $column_names = array_merge($column_names, $col_names);
        $order_field = $args['order_by_array'][0];
        if (in_array($order_field, $column_names)) {
            global $wpdb;
            $logged_in = get_current_user_id();
            $table_name = $wpdb->prefix . RBUNDLE_VIEWS_PIVOT_TABLE_NAME;
            $order_type = $args['order_array'][0];
            $query['select'] = "SELECT it.id FROM {$wpdb->prefix}frm_items it LEFT JOIN {$table_name} em0 ON em0.entry_id=it.id AND em0.user_id={$logged_in} AND em0.column_name='{$order_field}' ";
            $query['order'] = "GROUP BY it.id ORDER BY CASE WHEN em0.meta_value IS NULL THEN 1 ELSE 0 END, em0.meta_value {$order_type}, ";
        }
    }

    return $query;
}, 10, 2);

add_shortcode('rbundle-pivot-value', function ($atts) {
    $user_id = get_current_user_id();
});

function rvp_debug($string)
{
    global $wpdb;
    $wpdb->insert("{$wpdb->prefix}options", [
        'option_name' => 'rvp' . rand(),
        'option_value' => $string
    ]);
}

function rvp_clean_up($entry_id, $user_id)
{
    global $wpdb;
    $wpdb->delete($wpdb->prefix . RBUNDLE_VIEWS_PIVOT_TABLE_NAME, [
        'entry_id' => $entry_id,
        'user_id' => $user_id
    ]);
}

function rvp_58_star_shortlist($entry_id, $user_id)
{
    global $wpdb;
    $starred = ($wpdb->get_row("
        SELECT
            answer_5261.item_id
        FROM {$wpdb->prefix}frm_item_metas answer_5261
        LEFT JOIN {$wpdb->prefix}frm_item_metas answer_5262 ON answer_5261.item_id = answer_5262.item_id
        LEFT JOIN {$wpdb->prefix}frm_item_metas answer_5263 ON answer_5261.item_id = answer_5263.item_id
        WHERE answer_5261.meta_value = {$user_id}
        AND answer_5262.meta_value = 1
        AND answer_5263.meta_value = {$entry_id}
    ")) ? 1 : 0;

    $wpdb->insert($wpdb->prefix . RBUNDLE_VIEWS_PIVOT_TABLE_NAME, [
        'form_id' => 58,
        'column_name' => 'star_shortlist',
        'entry_id' => $entry_id,
        'user_id' => $user_id,
        'meta_value' => $starred
    ]);
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
