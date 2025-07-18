<?php
/*
Plugin Name: Custom Post Type Manager
Description: A plugin to create and manage custom post types from the admin panel.
Version: 1.2
Author: Vedant Thakare
*/

// Add menu in admin
function cptm_add_admin_menu() {
    add_menu_page(
        'CPT Manager',
        'CPT Manager',
        'manage_options',
        'cpt-manager',
        'cptm_admin_page',
        'dashicons-admin-tools',
        100
    );
}
add_action('admin_menu', 'cptm_add_admin_menu');

// Admin page
function cptm_admin_page() {
    ?>
    <div class="wrap">
        <h1>Custom Post Type Manager</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="post_type_name">Post Type Slug:</label></th>
                    <td><input type="text" name="post_type_name" id="post_type_name" required /></td>
                </tr>
                <tr>
                    <th><label for="post_type_label">Plural Label:</label></th>
                    <td><input type="text" name="post_type_label" id="post_type_label" required /></td>
                </tr>
                <tr>
                    <th><label for="post_type_singular">Singular Label:</label></th>
                    <td><input type="text" name="post_type_singular" id="post_type_singular" required /></td>
                </tr>
            </table>
            <p><input type="submit" name="submit_cpt" class="button button-primary" value="Create Post Type"></p>
        </form>
    </div>
    <?php

    // Save form data
    if (isset($_POST['submit_cpt'])) {
        $name     = sanitize_key($_POST['post_type_name']);
        $label    = sanitize_text_field($_POST['post_type_label']);
        $singular = sanitize_text_field($_POST['post_type_singular']);

        if (!empty($name) && !empty($label) && !empty($singular)) {
            cptm_save_post_type([
                'name'     => $name,
                'label'    => $label,
                'singular' => $singular
            ]);
            echo '<div class="notice notice-success is-dismissible"><p>Post Type Created!</p></div>';
        }
    }
}

// Save post type data
function cptm_save_post_type($data) {
    $existing = get_option('cptm_post_types', []);

    // Ensure $existing is always an array
    if (!is_array($existing)) {
        $existing = [];
    }

    // Avoid duplicates
    foreach ($existing as $pt) {
        if (is_array($pt) && isset($pt['name']) && $pt['name'] === $data['name']) {
            return;
        }
    }

    $existing[] = $data;
    update_option('cptm_post_types', $existing);
}

// Register custom post types
function cptm_register_post_types() {
    $post_types = get_option('cptm_post_types', []);

    // Ensure it's a list of arrays
    if (!is_array($post_types)) return;

    foreach ($post_types as $pt) {
        if (!is_array($pt) || !isset($pt['name'], $pt['label'], $pt['singular'])) {
            continue;
        }

        register_post_type($pt['name'], [
            'labels' => [
                'name'          => $pt['label'],
                'singular_name' => $pt['singular']
            ],
            'public'       => true,
            'has_archive'  => true,
            'menu_icon'    => 'dashicons-admin-post',
            'supports'     => ['title', 'editor', 'thumbnail']
        ]);
    }
}
add_action('init', 'cptm_register_post_types');
