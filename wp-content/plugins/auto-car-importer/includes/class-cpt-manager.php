<?php
/**
 * Custom Post Type Manager für Sportwagen
 */
class ACI_CPT_Manager {
    
    /**
     * Konstruktor
     */
    public function __construct() {
        // CPT registrieren
        add_action('init', array($this, 'register_post_type'));
    }
    
    /**
     * Custom Post Type 'Sportwagen' registrieren
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Sportwagen', 'auto-car-importer'),
            'singular_name'      => __('Sportwagen', 'auto-car-importer'),
            'menu_name'          => __('Sportwagen', 'auto-car-importer'),
            'name_admin_bar'     => __('Sportwagen', 'auto-car-importer'),
            'add_new'            => __('Neu hinzufügen', 'auto-car-importer'),
            'add_new_item'       => __('Neuen Sportwagen hinzufügen', 'auto-car-importer'),
            'new_item'           => __('Neuer Sportwagen', 'auto-car-importer'),
            'edit_item'          => __('Sportwagen bearbeiten', 'auto-car-importer'),
            'view_item'          => __('Sportwagen ansehen', 'auto-car-importer'),
            'all_items'          => __('Alle Sportwagen', 'auto-car-importer'),
            'search_items'       => __('Sportwagen suchen', 'auto-car-importer'),
            'parent_item_colon'  => __('Übergeordneter Sportwagen:', 'auto-car-importer'),
            'not_found'          => __('Keine Sportwagen gefunden.', 'auto-car-importer'),
            'not_found_in_trash' => __('Keine Sportwagen im Papierkorb gefunden.', 'auto-car-importer')
        );
        
        $args = array(
            'labels'              => $labels,
            'description'         => __('Sportwagen für Autohändler', 'auto-car-importer'),
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array('slug' => 'sportwagen'),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-car',
            'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
        );
        
        register_post_type('sportwagen', $args);
        
        // Taxonomien registrieren (optional)
        $this->register_taxonomies();
    }
    
    /**
     * Taxonomien für Sportwagen registrieren
     */
    private function register_taxonomies() {
        // Marke
        $labels = array(
            'name'              => __('Marken', 'auto-car-importer'),
            'singular_name'     => __('Marke', 'auto-car-importer'),
            'search_items'      => __('Marken suchen', 'auto-car-importer'),
            'all_items'         => __('Alle Marken', 'auto-car-importer'),
            'parent_item'       => __('Übergeordnete Marke', 'auto-car-importer'),
            'parent_item_colon' => __('Übergeordnete Marke:', 'auto-car-importer'),
            'edit_item'         => __('Marke bearbeiten', 'auto-car-importer'),
            'update_item'       => __('Marke aktualisieren', 'auto-car-importer'),
            'add_new_item'      => __('Neue Marke hinzufügen', 'auto-car-importer'),
            'new_item_name'     => __('Neuer Markenname', 'auto-car-importer'),
            'menu_name'         => __('Marken', 'auto-car-importer'),
        );
        
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'marke'),
        );
        
        register_taxonomy('marke', array('sportwagen'), $args);
        
        // Modell
        $labels = array(
            'name'              => __('Modelle', 'auto-car-importer'),
            'singular_name'     => __('Modell', 'auto-car-importer'),
            'search_items'      => __('Modelle suchen', 'auto-car-importer'),
            'all_items'         => __('Alle Modelle', 'auto-car-importer'),
            'parent_item'       => __('Übergeordnetes Modell', 'auto-car-importer'),
            'parent_item_colon' => __('Übergeordnetes Modell:', 'auto-car-importer'),
            'edit_item'         => __('Modell bearbeiten', 'auto-car-importer'),
            'update_item'       => __('Modell aktualisieren', 'auto-car-importer'),
            'add_new_item'      => __('Neues Modell hinzufügen', 'auto-car-importer'),
            'new_item_name'     => __('Neuer Modellname', 'auto-car-importer'),
            'menu_name'         => __('Modelle', 'auto-car-importer'),
        );
        
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'modell'),
        );
        
        register_taxonomy('modell', array('sportwagen'), $args);
    }
    
    /**
     * Prüfen, ob ein Sportwagen mit einer bestimmten internen Nummer existiert
     * 
     * @param string $interne_nummer Die interne Nummer des Fahrzeugs
     * @return int|false Die Post-ID, wenn gefunden, ansonsten false
     */
    public function get_car_by_interne_nummer($interne_nummer) {
        $args = array(
            'post_type'      => 'sportwagen',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'   => 'interne_nummer',
                    'value' => $interne_nummer,
                )
            )
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            return $query->posts[0]->ID;
        }
        
        return false;
    }
    
    /**
     * Prüfen, ob ein Sportwagen mit einer bestimmten Bild-ID existiert
     * 
     * @param string $bild_id Die Bild-ID des Fahrzeugs
     * @return int|false Die Post-ID, wenn gefunden, ansonsten false
     */
    public function get_car_by_bild_id($bild_id) {
        $args = array(
            'post_type'      => 'sportwagen',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'   => 'bild_id',
                    'value' => $bild_id,
                )
            )
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            return $query->posts[0]->ID;
        }
        
        return false;
    }
}