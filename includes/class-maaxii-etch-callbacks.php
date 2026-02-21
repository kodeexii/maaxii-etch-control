<?php
/**
 * Handles Ability Execution Callbacks
 */
class MaaXII_Etch_Callbacks {

    public function check_admin_permission() {
        return current_user_can( 'manage_options' );
    }

    public function ping() {
        return 'pong';
    }

    public function build_etch_page( $params ) {
        $title  = $params['title'] ?? '';
        $layout = $params['layout'] ?? [];
        $styles = $params['styles'] ?? [];

        $blocks = $this->process_blueprint_recursive( $layout );

        if ( ! function_exists( 'serialize_blocks' ) ) {
            require_once ABSPATH . WPINC . '/blocks.php';
        }

        $existing = get_page_by_title( $title, OBJECT, 'page' );
        $page_id  = $existing ? $existing->ID : wp_insert_post([
            'post_title'  => $title,
            'post_status' => 'publish',
            'post_type'   => 'page'
        ]);
        
        wp_update_post([ 
            'ID'           => $page_id, 
            'post_content' => wp_slash( serialize_blocks( $blocks ) ) 
        ], true);
        
        $this->auto_register_styles( $layout, $styles );

        return [ 'success' => true, 'url' => get_permalink( $page_id ) ];
    }

    public function append_etch_section( $params ) {
        $title = $params['title'] ?? '';
        $new_blocks_raw = $params['blocks'] ?? [];

        if ( ! function_exists( 'serialize_blocks' ) ) {
            require_once ABSPATH . WPINC . '/blocks.php';
        }

        $existing = get_page_by_title( $title, OBJECT, 'page' );
        if ( ! $existing ) {
            return new \WP_Error( 'not_found', "Halaman '$title' tidak dijumpai." );
        }

        $existing_blocks = parse_blocks( $existing->post_content );
        $clean_new_blocks = $this->process_blueprint_recursive( $new_blocks_raw );
        
        $merged = array_merge( $existing_blocks, $clean_new_blocks );
        $merged = array_values( array_filter( $merged, function($b) { 
            return ! empty( $b['blockName'] ); 
        } ) );

        wp_update_post([ 
            'ID'           => $existing->ID, 
            'post_content' => wp_slash( serialize_blocks( $merged ) ) 
        ], true);

        $this->auto_register_styles( $new_blocks_raw, $params['styles'] ?? [] );

        return [ 'success' => true, 'message' => 'Seksyen berjaya ditambah.' ];
    }

    public function get_page_blocks( $params ) {
        $page = get_page_by_title( $params['title'] ?? '', OBJECT, 'page' );
        if ( ! $page ) {
            return new \WP_Error( 'not_found', 'Page not found.' );
        }

        if ( ! function_exists( 'parse_blocks' ) ) {
            require_once ABSPATH . WPINC . '/blocks.php';
        }

        $blocks = parse_blocks( $page->post_content );
        return [ 
            'success' => true, 
            'blocks'  => array_values( array_filter( $blocks, function($b) { 
                return ! empty( $b['blockName'] ); 
            } ) ) 
        ];
    }

    public function delete_pages( $params ) {
        $query = new \WP_Query( [ 
            'post_type'      => 'page', 
            's'              => $params['search'] ?? '', 
            'posts_per_page' => -1 
        ] );
        
        $count = 0;
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) { 
                $query->the_post(); 
                wp_delete_post( get_the_ID(), false ); 
                $count++; 
            }
        }
        wp_reset_postdata();
        return [ 'success' => true, 'deleted_count' => $count ];
    }

    private function auto_register_styles( $layout, $additional_styles = [] ) {
        $existing = (array)get_option( 'etch_styles', [] );
        $new_styles = (array)$additional_styles;

        $extract_classes = function( $items ) use ( &$extract_classes, &$new_styles, $existing ) {
            foreach ( (array)$items as $item ) {
                $attrs = $item['attrs'] ?? $item['attributes'] ?? [];
                $classes = $attrs['class'] ?? '';
                if ( $classes ) {
                    $class_list = explode( ' ', $classes );
                    foreach ( $class_list as $cls ) {
                        $cls = trim( $cls );
                        if ( $cls && ! isset( $existing[$cls] ) && ! isset( $new_styles[$cls] ) ) {
                            $new_styles[$cls] = [
                                'type'       => 'class',
                                'selector'   => '.' . $cls,
                                'collection' => 'default',
                                'css'        => '',
                                'readonly'   => false
                            ];
                        }
                    }
                }
                if ( isset( $item['children'] ) ) {
                    $extract_classes( $item['children'] );
                }
            }
        };
        $extract_classes( $layout );

        if ( ! empty( $new_styles ) ) {
            update_option( 'etch_styles', array_merge( $existing, $new_styles ) );
        }
    }

    private function process_blueprint_recursive( $layout ) {
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

                $children = isset($item['children']) ? $this->process_blueprint_recursive($item['children']) : [];
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
}
