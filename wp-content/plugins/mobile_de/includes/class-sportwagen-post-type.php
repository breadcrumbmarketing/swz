<?php
/**
 * Plugin Name: Sportwagen Post Type
 * Description: Registriert den Sportwagen Custom Post Type und ACF-Felder
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Sportwagen_Post_Type {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register post type
        add_action('init', array($this, 'register_post_type'));
        
        // Register ACF fields if ACF is active
        if (function_exists('acf_add_local_field_group')) {
            add_action('acf/init', array($this, 'register_acf_fields'));
        }
    }
    
    /**
     * Register the Sportwagen Custom Post Type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => 'Sportwagen',
            'singular_name'      => 'Sportwagen',
            'menu_name'          => 'Sportwagen',
            'name_admin_bar'     => 'Sportwagen',
            'add_new'            => 'Neu hinzufügen',
            'add_new_item'       => 'Neuen Sportwagen hinzufügen',
            'new_item'           => 'Neuer Sportwagen',
            'edit_item'          => 'Sportwagen bearbeiten',
            'view_item'          => 'Sportwagen ansehen',
            'all_items'          => 'Alle Sportwagen',
            'search_items'       => 'Sportwagen suchen',
            'parent_item_colon'  => 'Übergeordneter Sportwagen:',
            'not_found'          => 'Keine Sportwagen gefunden.',
            'not_found_in_trash' => 'Keine Sportwagen im Papierkorb gefunden.'
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'sportwagen'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'menu_icon'          => 'dashicons-car'
        );
        
        register_post_type('sportwagen', $args);
    }
    
    /**
     * Register ACF fields
     */
    public function register_acf_fields() {
        // Create field groups
        $this->create_basic_info_fields();
        $this->create_technical_fields();
        $this->create_equipment_fields();
        $this->create_image_gallery_fields();
    }
    
    /**
     * Create basic information field group
     */
    private function create_basic_info_fields() {
        acf_add_local_field_group(array(
            'key' => 'group_sportwagen_basis',
            'title' => 'Fahrzeug-Basisinformationen',
            'fields' => array(
                array(
                    'key' => 'field_interne_nummer',
                    'label' => 'Interne Nummer',
                    'name' => 'interne_nummer',
                    'type' => 'text',
                    'instructions' => 'Interne Identifikationsnummer des Fahrzeugs',
                    'required' => 1,
                    'maxlength' => 40,
                ),
                array(
                    'key' => 'field_kategorie',
                    'label' => 'Kategorie',
                    'name' => 'kategorie',
                    'type' => 'text',
                    'instructions' => 'Fahrzeugkategorie',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_marke',
                    'label' => 'Marke',
                    'name' => 'marke',
                    'type' => 'text',
                    'instructions' => 'Fahrzeugmarke',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_modell',
                    'label' => 'Modell',
                    'name' => 'modell',
                    'type' => 'text',
                    'instructions' => 'Fahrzeugmodell',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_preis',
                    'label' => 'Preis',
                    'name' => 'preis',
                    'type' => 'number',
                    'instructions' => 'Preis des Fahrzeugs',
                ),
                array(
                    'key' => 'field_mwst',
                    'label' => 'MwSt ausweisbar',
                    'name' => 'mwst',
                    'type' => 'select',
                    'instructions' => 'Ist die MwSt ausweisbar?',
                    'required' => 1,
                    'choices' => array(
                        '0' => 'Ja',
                        '1' => 'Nein',
                    ),
                    'default_value' => '0',
                ),
                // Weitere Basis-Felder hier
                array(
                    'key' => 'field_kilometer',
                    'label' => 'Kilometer',
                    'name' => 'kilometer',
                    'type' => 'number',
                    'instructions' => 'Gefahrene Kilometer',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_ez',
                    'label' => 'Erstzulassung',
                    'name' => 'ez',
                    'type' => 'date_picker',
                    'instructions' => 'Datum der Erstzulassung',
                    'display_format' => 'd.m.Y',
                    'return_format' => 'd.m.Y',
                ),
                array(
                    'key' => 'field_hu',
                    'label' => 'Hauptuntersuchung',
                    'name' => 'hu',
                    'type' => 'date_picker',
                    'instructions' => 'Datum der nächsten Hauptuntersuchung',
                    'display_format' => 'd.m.Y',
                    'return_format' => 'd.m.Y',
                ),
                array(
                    'key' => 'field_bild_id',
                    'label' => 'Bild-ID',
                    'name' => 'bild_id',
                    'type' => 'text',
                    'instructions' => 'ID zur Zuordnung der Bilder',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'sportwagen',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
        ));
    }
    
    /**
     * Create technical data field group
     */
    private function create_technical_fields() {
        acf_add_local_field_group(array(
            'key' => 'group_sportwagen_technisch',
            'title' => 'Technische Daten',
            'fields' => array(
                // Technische Felder hier
                array(
                    'key' => 'field_leistung',
                    'label' => 'Leistung (kW)',
                    'name' => 'leistung',
                    'type' => 'number',
                    'instructions' => 'Leistung in kW',
                ),
                array(
                    'key' => 'field_ccm',
                    'label' => 'Hubraum (ccm)',
                    'name' => 'ccm',
                    'type' => 'number',
                    'instructions' => 'Hubraum in ccm',
                ),
                array(
                    'key' => 'field_kraftstoffart',
                    'label' => 'Kraftstoffart',
                    'name' => 'kraftstoffart',
                    'type' => 'select',
                    'instructions' => 'Art des Kraftstoffs',
                    'choices' => array(
                        '1' => 'Benzin',
                        '2' => 'Diesel',
                        '3' => 'Autogas',
                        '4' => 'Erdgas',
                        '6' => 'Elektro',
                        '7' => 'Hybrid',
                        '8' => 'Wasserstoff',
                        '9' => 'Ethanol',
                        '10' => 'Hybrid-Diesel',
                        '0' => 'Andere',
                    ),
                ),
                array(
                    'key' => 'field_getriebeart',
                    'label' => 'Getriebeart',
                    'name' => 'getriebeart',
                    'type' => 'select',
                    'instructions' => 'Art des Getriebes',
                    'choices' => array(
                        '0' => 'Keine Angabe',
                        '1' => 'Schaltgetriebe',
                        '2' => 'Halbautomatik',
                        '3' => 'Automatik',
                    ),
                ),
                // Weitere technische Felder
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'sportwagen',
                    ),
                ),
            ),
            'menu_order' => 1,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
        ));
    }
    
    /**
     * Create equipment field group
     */
    private function create_equipment_fields() {
        acf_add_local_field_group(array(
            'key' => 'group_sportwagen_ausstattung',
            'title' => 'Ausstattung',
            'fields' => array(
                // Tabs und Ausrüstungsfelder hier
                array(
                    'key' => 'field_tab_grundausstattung',
                    'label' => 'Grundausstattung',
                    'name' => '',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_abs',
                    'label' => 'ABS',
                    'name' => 'abs',
                    'type' => 'true_false',
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_esp',
                    'label' => 'ESP',
                    'name' => 'esp',
                    'type' => 'true_false',
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_klimaanlage',
                    'label' => 'Klimaanlage',
                    'name' => 'klima',
                    'type' => 'select',
                    'choices' => array(
                        '0' => 'Keine',
                        '1' => 'Klimaanlage',
                        '2' => 'Klimaautomatik',
                    ),
                ),
                // Weitere Ausrüstungsfelder
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'sportwagen',
                    ),
                ),
            ),
            'menu_order' => 2,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
        ));
    }
    
    /**
     * Create image gallery field group
     */
    private function create_image_gallery_fields() {
        acf_add_local_field_group(array(
            'key' => 'group_sportwagen_bilder',
            'title' => 'Fahrzeugbilder',
            'fields' => array(
                array(
                    'key' => 'field_fahrzeug_bilder',
                    'label' => 'Bilder',
                    'name' => 'fahrzeug_bilder',
                    'type' => 'gallery',
                    'instructions' => 'Bilder des Fahrzeugs',
                    'required' => 0,
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'sportwagen',
                    ),
                ),
            ),
            'menu_order' => 3,
            'position' => 'side',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
        ));
    }
}

// Initialize Post Type
new Sportwagen_Post_Type();