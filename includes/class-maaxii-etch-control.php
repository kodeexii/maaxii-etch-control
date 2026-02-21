<?php
/**
 * Main Plugin Class
 */
class MaaXII_Etch_Control {

    public function run() {
        $this->define_hooks();
    }

    private function define_hooks() {
        $abilities = new MaaXII_Etch_Abilities();
        
        // Register Categories
        add_action( 'wp_abilities_api_categories_init', [ $abilities, 'register_categories' ] );
        
        // Register Abilities
        add_action( 'wp_abilities_api_init', [ $abilities, 'register_abilities' ] );
    }
}
