<?php
/**
 * Plugin Name: MaaXII Etch Control
 * Description: Universal remote management and programmatic page builder for Etch (Original Logic).
 * Version: 1.0.16
 * Author: MaaXII Solutions and Services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Autoload PUC
define( 'MAAXII_ETCH_CONTROL_PATH', plugin_dir_path( __FILE__ ) );
if ( file_exists( MAAXII_ETCH_CONTROL_PATH . 'libs/plugin-update-checker/plugin-update-checker.php' ) ) {
    require_once MAAXII_ETCH_CONTROL_PATH . 'libs/plugin-update-checker/plugin-update-checker.php';
    $updateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/kodeexii/maaxii-etch-control/',
        __FILE__,
        'maaxii-etch-control'
    );
}

/**
 * ─── 1. Kategori ────────────────────────────────────────────────
 */
function maaxii_reg_categories_v1016() {
    if ( function_exists( 'wp_register_ability_category' ) ) {
        wp_register_ability_category( 'maaxii', [ 
            'label'       => 'MaaXII Etch Tools', 
            'description' => 'MaaXII Custom Tools and Automation for Etch.' 
        ] );
    }
}
add_action( 'wp_abilities_api_categories_init', 'maaxii_reg_categories_v1016' );

/**
 * ─── 2. Abilities ───────────────────────────────────────────────
 */
function maaxii_reg_abilities_v1016() {
    if ( ! function_exists( 'wp_register_ability' ) ) {
        return;
    }

    // Ability: Build Etch Page
    wp_register_ability( 'maaxii/build-etch-page', [
        'label'               => 'Build Etch Page',
        'description'         => 'Build fully editable Etch pages programmatically using JSON blueprints.',
        'category'            => 'maaxii',
        'execute_callback'    => 'maaxii_ability_build_etch_page',
        'permission_callback' => 'maaxii_check_admin_permission',
        'input_schema'        => [
            'type' => 'object',
            'properties' => [
                'title'  => [ 'type' => 'string' ],
                'layout' => [ 'type' => 'array' ],
                'styles' => [ 'type' => 'object' ],
            ],
            'required' => [ 'title', 'layout' ]
        ],
        'meta'                => [ 'show_in_rest' => true, 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
    ] );

    // Ability: Append Etch Section
    wp_register_ability( 'maaxii/append-etch-section', [
        'label'               => 'Append Etch Section',
        'description'         => 'Add a new section to the end of an existing Etch page.',
        'category'            => 'maaxii',
        'execute_callback'    => 'maaxii_ability_append_etch_section',
        'permission_callback' => 'maaxii_check_admin_permission',
        'input_schema'        => [
            'type' => 'object',
            'properties' => [
                'title'  => [ 'type' => 'string' ],
                'blocks' => [ 'type' => 'array' ],
                'styles' => [ 'type' => 'object' ],
            ],
            'required' => [ 'title', 'blocks' ]
        ],
        'meta'                => [ 'show_in_rest' => true, 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
    ] );

    // Ability: Get Page Blocks
    wp_register_ability( 'maaxii/get-page-blocks', [
        'label'               => 'Get Page Blocks',
        'description'         => 'Retrieve the current block structure.',
        'category'            => 'maaxii',
        'execute_callback'    => 'maaxii_ability_get_page_blocks',
        'permission_callback' => 'maaxii_check_admin_permission',
        'input_schema'        => [
            'type' => 'object',
            'properties' => [ 'title' => [ 'type' => 'string' ] ],
            'required' => [ 'title' ]
        ],
        'meta'                => [ 'show_in_rest' => true, 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
    ] );

    // Ability: Delete Pages
    wp_register_ability( 'maaxii/delete-pages', [
        'label'               => 'Delete Pages',
        'description'         => 'Remotely delete pages.',
        'category'            => 'maaxii',
        'execute_callback'    => 'maaxii_ability_delete_pages',
        'permission_callback' => 'maaxii_check_admin_permission',
        'input_schema'        => [
            'type' => 'object',
            'properties' => [ 'search' => [ 'type' => 'string' ] ],
            'required' => [ 'search' ]
        ],
        'meta'                => [ 'show_in_rest' => true, 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
    ] );

    // Ping
    wp_register_ability( 'maaxii/ping', [
        'label'            => 'MaaXII Ping',
        'description'      => 'Connectivity check.',
        'category'         => 'maaxii',
        'execute_callback' => 'maaxii_ping_callback',
        'permission_callback' => '__return_true',
        'meta'             => [ 'show_in_rest' => true, 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
    ] );
}
add_action( 'wp_abilities_api_init', 'maaxii_reg_abilities_v1016' );

/**
 * ─── 3. Callbacks & Core Logic ────────────────────────────────────
 */

function maaxii_check_admin_permission() {
    return current_user_can( 'manage_options' );
}

function maaxii_ping_callback() {
    return 'pong';
}

function maaxii_ability_build_etch_page( $params ) {
    $title  = $params['title'];
    $layout = $params['layout'] ?? [];
    $styles = $params['styles'] ?? [];

    $blocks = maaxii_process_blueprint_recursive( $layout );

    if ( ! function_exists( 'serialize_blocks' ) ) require_once ABSPATH . WPINC . '/blocks.php';

    $existing = get_page_by_title( $title, OBJECT, 'page' );
    $page_id  = $existing ? $existing->ID : wp_insert_post(['post_title' => $title, 'post_status' => 'publish', 'post_type' => 'page']);
    
    wp_update_post([ 'ID' => $page_id, 'post_content' => wp_slash( serialize_blocks($blocks) ) ], true);
    
    maaxii_auto_register_styles($layout, $styles);

    return [ 'success' => true, 'url' => get_permalink($page_id) ];
}

function maaxii_ability_append_etch_section( $params ) {
    $title = $params['title'] ?? '';
    $new_blocks_raw = $params['blocks'] ?? [];
    if ( ! function_exists( 'serialize_blocks' ) ) require_once ABSPATH . WPINC . '/blocks.php';

    $existing = get_page_by_title( $title, OBJECT, 'page' );
    if ( ! $existing ) return new \WP_Error( 'not_found', "Halaman '$title' tidak dijumpai." );

    $existing_blocks = parse_blocks( $existing->post_content );
    $clean_new_blocks = maaxii_process_blueprint_recursive( $new_blocks_raw );
    
    $merged = array_merge( $existing_blocks, $clean_new_blocks );
    $merged = array_values( array_filter( $merged, function($b) { return ! empty($b['blockName']); } ) );

    wp_update_post([ 'ID' => $existing->ID, 'post_content' => wp_slash( serialize_blocks($merged) ) ], true);
    maaxii_auto_register_styles($new_blocks_raw, $params['styles'] ?? []);

    return [ 'success' => true, 'message' => 'Seksyen berjaya ditambah.' ];
}

function maaxii_ability_get_page_blocks( $params ) {
    $page = get_page_by_title( $params['title'], OBJECT, 'page' );
    if ( ! $page ) return new \WP_Error( 'not_found', 'Page not found.' );
    if ( ! function_exists( 'parse_blocks' ) ) require_once ABSPATH . WPINC . '/blocks.php';
    $blocks = parse_blocks( $page->post_content );
    return [ 'success' => true, 'blocks' => array_values( array_filter( $blocks, function($b) { return ! empty($b['blockName']); } ) ) ];
}

function maaxii_ability_delete_pages( $params ) {
    $query = new \WP_Query( [ 'post_type' => 'page', 's' => $params['search'], 'posts_per_page' => -1 ] );
    $count = 0;
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) { $query->the_post(); wp_delete_post( get_the_ID(), false ); $count++; }
    }
    wp_reset_postdata();
    return [ 'success' => true, 'deleted_count' => $count ];
}

/**
 * Ensures all classes in blueprint are registered in Etch Style Manager.
 */
function maaxii_auto_register_styles($layout, $additional_styles = []) {
    $existing = (array)get_option( 'etch_styles', [] );
    $new_styles = (array)$additional_styles;

    $extract_classes = function($items) use (&$extract_classes, &$new_styles, $existing) {
        foreach ((array)$items as $item) {
            $classes = $item['attrs']['class'] ?? $item['attributes']['class'] ?? '';
            if ($classes) {
                $class_list = explode(' ', $classes);
                foreach ($class_list as $cls) {
                    $cls = trim( $cls );
                    if ($cls && !isset($existing[$cls]) && !isset($new_styles[$cls])) {
                        $new_styles[$cls] = [
                            'type' => 'class',
                            'selector' => '.' . $cls,
                            'collection' => 'default',
                            'css' => '',
                            'readonly' => false
                        ];
                    }
                }
            }
            if (isset($item['children'])) $extract_classes($item['children']);
        }
    };
    $extract_classes($layout);

    if (!empty($new_styles)) {
        update_option( 'etch_styles', array_merge($existing, $new_styles) );
    }
}

function maaxii_process_blueprint_recursive( $layout ) {
    $blocks = [];
    foreach ( (array)$layout as $item ) {
        if ( isset($item['text']) ) {
            $blocks[] = [ 'blockName' => 'etch/text', 'attrs' => [ 'metadata' => [ 'name' => 'Text' ], 'content' => $item['text'] ], 'innerBlocks' => [], 'innerHTML' => '', 'innerContent' => [] ];
        } else {
            $tag = $item['tag'] ?? 'div';
            $name = $item['name'] ?? 'Element';
            $styles = (array)($item['styles'] ?? []);
            $html_attrs = (array)($item['attrs'] ?? $item['attributes'] ?? []);
            
            if (isset($html_attrs['class'])) {
                $cls_array = explode(' ', $html_attrs['class']);
                foreach ($cls_array as $c) if (!in_array($c, $styles)) $styles[] = $c;
            }

            if ( in_array( 'etch-section-style', $styles, true ) ) $html_attrs['data-etch-element'] = 'section';
            elseif ( in_array( 'etch-container-style', $styles, true ) ) $html_attrs['data-etch-element'] = 'container';
            elseif ( in_array( 'etch-flex-div-style', $styles, true ) ) $html_attrs['data-etch-element'] = 'flex-div';

            $children = isset($item['children']) ? maaxii_process_blueprint_recursive($item['children']) : [];
            $n = count($children);
            $inner_c = ($n === 0) ? ["\n\n"] : ["\n"];
            if ($n > 0) {
                for ($i = 0; $i < $n; $i++) {
                    $inner_c[] = null;
                    if ($i < $n - 1) $inner_c[] = "\n\n";
                }
                $inner_c[] = "\n";
            }

            $blocks[] = [ 'blockName' => 'etch/element', 'attrs' => [ 'metadata' => [ 'name' => $name ], 'tag' => $tag, 'attributes' => $html_attrs, 'styles' => $styles ], 'innerBlocks' => $children, 'innerHTML' => "\n\n", 'innerContent' => $inner_c ];
        }
    }
    return $blocks;
}
