<?php
/**
 * Handles Abilities Registration
 */
class MaaXII_Etch_Abilities {

    protected $callbacks;

    public function __construct() {
        $this->callbacks = new MaaXII_Etch_Callbacks();
    }

    public function register_categories() {
        if ( function_exists( 'wp_register_ability_category' ) ) {
            wp_register_ability_category( 'maaxii', [ 
                'label'       => 'MaaXII Etch Tools', 
                'description' => 'MaaXII Custom Tools and Automation for Etch.' 
            ] );
        }
    }

    public function register_abilities() {
        if ( ! function_exists( 'wp_register_ability' ) ) {
            return;
        }

        // Ability: Build Etch Page
        wp_register_ability( 'maaxii/build-etch-page', [
            'label'               => 'Build Etch Page',
            'description'         => 'Build fully editable Etch pages programmatically using JSON blueprints.',
            'category'            => 'maaxii',
            'execute_callback'    => [ $this->callbacks, 'build_etch_page' ],
            'permission_callback' => [ $this->callbacks, 'check_admin_permission' ],
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
            'execute_callback'    => [ $this->callbacks, 'append_etch_section' ],
            'permission_callback' => [ $this->callbacks, 'check_admin_permission' ],
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
            'execute_callback'    => [ $this->callbacks, 'get_page_blocks' ],
            'permission_callback' => [ $this->callbacks, 'check_admin_permission' ],
            'input_schema'        => [
                'type' => 'object',
                'properties' => [ 'title' => [ 'type' => 'string' ] ],
                'required' => [ 'title' ]
            ],
            'meta'                => [ 'show_in_rest' => true, 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
        ] );

        // Ability: Delete Pages (Updated with search_columns)
        wp_register_ability( 'maaxii/delete-pages', [
            'label'               => 'Delete Pages',
            'description'         => 'Remotely delete pages using keyword search with context control.',
            'category'            => 'maaxii',
            'execute_callback'    => [ $this->callbacks, 'delete_pages' ],
            'permission_callback' => [ $this->callbacks, 'check_admin_permission' ],
            'input_schema'        => [
                'type' => 'object',
                'properties' => [ 
                    'search'         => [ 'type' => 'string' ],
                    'search_columns' => [ 
                        'type' => 'array', 
                        'items' => [ 'type' => 'string', 'enum' => ['post_title', 'post_content', 'post_excerpt'] ],
                        'description' => 'Target columns for search. Default: post_title'
                    ] 
                ],
                'required' => [ 'search' ]
            ],
            'meta'                => [ 'show_in_rest' => true, 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
        ] );

        // Ping
        wp_register_ability( 'maaxii/ping', [
            'label'            => 'MaaXII Ping',
            'description'      => 'Connectivity check.',
            'category'         => 'maaxii',
            'execute_callback' => [ $this->callbacks, 'ping' ],
            'permission_callback' => '__return_true',
            'meta'             => [ 'show_in_rest' => true, 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
        ] );
    }
}
