<?php
/**
 * ACF Manager برای Auto Car Importer
 * مدیریت فیلدهای ACF و نگاشت به ستون‌های CSV
 */
class ACI_ACF_Manager {
    
    /**
     * نمونه Logger
     */
    private $logger;
    
    /**
     * سازنده
     * 
     * @param ACI_Logger $logger نمونه Logger
     */
    public function __construct($logger) {
        $this->logger = $logger;
        
        // فیلدهای ACF را ثبت کنید، اگر ACF فعال است
        add_action('acf/init', array($this, 'register_acf_fields'));
    }
    
    /**
     * فیلدهای ACF را برای نوع پست سفارشی 'Sportwagen' ثبت کنید
     */
    public function register_acf_fields() {
        // بررسی کنید که آیا ACF فعال است
        if (!function_exists('acf_add_local_field_group')) {
            $this->logger->log('ACF فعال نیست، ثبت فیلد را رد می‌کنیم', 'warning');
            return;
        }
        
        // Feldgruppe für Fahrzeugdaten
        acf_add_local_field_group(array(
            'key' => 'group_sportwagen_details',
            'title' => 'Fahrzeugdetails',
            'fields' => $this->get_car_fields(),
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
 * Liefert die Definition der ACF-Felder für Sportwagen
 * 
 * @return array Definition der ACF-Felder
 */
private function get_car_fields() {
    return array(
        // Kundennummer
        array(
            'key' => 'field_kundennummer',
            'label' => 'Kundennummer',
            'name' => 'kundennummer',
            'type' => 'text',
            'required' => 0,
        ),
        // Interne Nummer (Pflichtfeld)
        array(
            'key' => 'field_interne_nummer',
            'label' => 'Interne Nummer',
            'name' => 'interne_nummer',
            'type' => 'text',
            'required' => 1,
        ),
        // Kategorie (Pflichtfeld)
        array(
            'key' => 'field_kategorie',
            'label' => 'Kategorie',
            'name' => 'kategorie',
            'type' => 'text',
            'required' => 1,
        ),
        // Marke (Pflichtfeld)
        array(
            'key' => 'field_marke',
            'label' => 'Marke',
            'name' => 'marke',
            'type' => 'text',
            'required' => 1,
        ),
        // Modell (Pflichtfeld)
        array(
            'key' => 'field_modell',
            'label' => 'Modell',
            'name' => 'modell',
            'type' => 'text',
            'required' => 1,
        ),
        // Leistung (KW)
        array(
            'key' => 'field_leistung',
            'label' => 'Leistung (KW)',
            'name' => 'leistung',
            'type' => 'number',
            'required' => 0,
        ),
        // Hauptuntersuchung (HU)
        array(
            'key' => 'field_hu',
            'label' => 'Hauptuntersuchung',
            'name' => 'hu',
            'type' => 'text',
            'required' => 0,
        ),
        // Erstzulassung (EZ)
        array(
            'key' => 'field_ez',
            'label' => 'Erstzulassung',
            'name' => 'ez',
            'type' => 'text',
            'required' => 0,
        ),
        // Kilometer
        array(
            'key' => 'field_kilometer',
            'label' => 'Kilometer',
            'name' => 'kilometer',
            'type' => 'number',
            'required' => 0,
        ),
        // Preis
        array(
            'key' => 'field_preis',
            'label' => 'Preis',
            'name' => 'preis',
            'type' => 'number',
            'required' => 0,
        ),
        // MwSt. ausweisbar
        array(
            'key' => 'field_mwst',
            'label' => 'MwSt. ausweisbar',
            'name' => 'mwst',
            'type' => 'true_false',
            'message' => 'MwSt. ausweisbar',
            'required' => 0,
        ),
        // Oldtimer
        array(
            'key' => 'field_oldtimer',
            'label' => 'Oldtimer',
            'name' => 'oldtimer',
            'type' => 'true_false',
            'message' => 'Ist ein Oldtimer',
            'required' => 0,
        ),
        // FIN/VIN
        array(
            'key' => 'field_vin',
            'label' => 'Fahrzeug-Identifizierungsnummer (VIN)',
            'name' => 'vin',
            'type' => 'text',
            'required' => 0,
        ),
        // Beschädigtes Fahrzeug
        array(
            'key' => 'field_beschaedigtes_fahrzeug',
            'label' => 'Beschädigtes Fahrzeug',
            'name' => 'beschaedigtes_fahrzeug',
            'type' => 'true_false',
            'message' => 'Ist beschädigt',
            'required' => 0,
        ),
        // Farbe
        array(
            'key' => 'field_farbe',
            'label' => 'Farbe',
            'name' => 'farbe',
            'type' => 'text',
            'required' => 0,
        ),
        // Klimaanlage
        array(
            'key' => 'field_klima',
            'label' => 'Klimaanlage',
            'name' => 'klima',
            'type' => 'select',
            'choices' => array(
                '0' => 'Keine',
                '1' => 'Klimaanlage',
                '2' => 'Klimaautomatik',
            ),
            'required' => 0,
        ),
        // Taxi
        array(
            'key' => 'field_taxi',
            'label' => 'Taxi',
            'name' => 'taxi',
            'type' => 'true_false',
            'message' => 'Ist ein Taxi',
            'required' => 0,
        ),
        // Behindertengerecht
        array(
            'key' => 'field_behindertengerecht',
            'label' => 'Behindertengerecht',
            'name' => 'behindertengerecht',
            'type' => 'true_false',
            'message' => 'Ist behindertengerecht',
            'required' => 0,
        ),
        // Jahreswagen
        array(
            'key' => 'field_jahreswagen',
            'label' => 'Jahreswagen',
            'name' => 'jahreswagen',
            'type' => 'true_false',
            'message' => 'Ist ein Jahreswagen',
            'required' => 0,
        ),
        // Neufahrzeug
        array(
            'key' => 'field_neufahrzeug',
            'label' => 'Neufahrzeug',
            'name' => 'neufahrzeug',
            'type' => 'true_false',
            'message' => 'Ist ein Neufahrzeug',
            'required' => 0,
        ),
        // Unsere Empfehlung
        array(
            'key' => 'field_unsere_empfehlung',
            'label' => 'Unsere Empfehlung',
            'name' => 'unsere_empfehlung',
            'type' => 'true_false',
            'message' => 'Als Empfehlung markieren',
            'required' => 0,
        ),
        // Händlerpreis
        array(
            'key' => 'field_haendlerpreis',
            'label' => 'Händlerpreis',
            'name' => 'haendlerpreis',
            'type' => 'number',
            'required' => 0,
        ),
        // Bemerkung
        array(
            'key' => 'field_bemerkung',
            'label' => 'Bemerkung',
            'name' => 'bemerkung',
            'type' => 'textarea',
            'required' => 0,
        ),
        // Bild-ID
        array(
            'key' => 'field_bild_id',
            'label' => 'Bild-ID',
            'name' => 'bild_id',
            'type' => 'text',
            'required' => 0,
        ),
        // Metallic
        array(
            'key' => 'field_metallic',
            'label' => 'Metallic',
            'name' => 'metallic',
            'type' => 'true_false',
            'message' => 'Metallic-Lackierung',
            'required' => 0,
        ),
        // Währung
        array(
            'key' => 'field_waehrung',
            'label' => 'Währung',
            'name' => 'waehrung',
            'type' => 'text',
            'required' => 0,
        ),
        // MwSt-Satz
        array(
            'key' => 'field_mwstsatz',
            'label' => 'MwSt-Satz',
            'name' => 'mwstsatz',
            'type' => 'number',
            'required' => 0,
        ),
        // Garantie
        array(
            'key' => 'field_garantie',
            'label' => 'Garantie',
            'name' => 'garantie',
            'type' => 'true_false',
            'message' => 'Garantie inkl.',
            'required' => 0,
        ),
        // Leichtmetallfelgen
        array(
            'key' => 'field_leichtmetallfelgen',
            'label' => 'Leichtmetallfelgen',
            'name' => 'leichtmetallfelgen',
            'type' => 'true_false',
            'message' => 'Hat Leichtmetallfelgen',
            'required' => 0,
        ),
        // ESP
        array(
            'key' => 'field_esp',
            'label' => 'ESP',
            'name' => 'esp',
            'type' => 'true_false',
            'message' => 'Hat ESP',
            'required' => 0,
        ),
        // ABS
        array(
            'key' => 'field_abs',
            'label' => 'ABS',
            'name' => 'abs',
            'type' => 'true_false',
            'message' => 'Hat ABS',
            'required' => 0,
        ),
        // Anhängerkupplung
        array(
            'key' => 'field_anhaengerkupplung',
            'label' => 'Anhängerkupplung',
            'name' => 'anhaengerkupplung',
            'type' => 'true_false',
            'message' => 'Hat Anhängerkupplung',
            'required' => 0,
        ),
        // Wegfahrsperre
        array(
            'key' => 'field_wegfahrsperre',
            'label' => 'Wegfahrsperre',
            'name' => 'wegfahrsperre',
            'type' => 'true_false',
            'message' => 'Hat Wegfahrsperre',
            'required' => 0,
        ),
        // Navigationssystem
        array(
            'key' => 'field_navigationssystem',
            'label' => 'Navigationssystem',
            'name' => 'navigationssystem',
            'type' => 'true_false',
            'message' => 'Hat Navigationssystem',
            'required' => 0,
        ),
        // Schiebedach
        array(
            'key' => 'field_schiebedach',
            'label' => 'Schiebedach',
            'name' => 'schiebedach',
            'type' => 'true_false',
            'message' => 'Hat Schiebedach',
            'required' => 0,
        ),
        // Zentralverriegelung
        array(
            'key' => 'field_zentralverriegelung',
            'label' => 'Zentralverriegelung',
            'name' => 'zentralverriegelung',
            'type' => 'true_false',
            'message' => 'Hat Zentralverriegelung',
            'required' => 0,
        ),
        // Fensterheber
        array(
            'key' => 'field_fensterheber',
            'label' => 'Fensterheber',
            'name' => 'fensterheber',
            'type' => 'true_false',
            'message' => 'Hat elektrische Fensterheber',
            'required' => 0,
        ),
        // Allradantrieb
        array(
            'key' => 'field_allradantrieb',
            'label' => 'Allradantrieb',
            'name' => 'allradantrieb',
            'type' => 'true_false',
            'message' => 'Hat Allradantrieb',
            'required' => 0,
        ),
        // Türen
        array(
            'key' => 'field_tueren',
            'label' => 'Türen',
            'name' => 'tueren',
            'type' => 'number',
            'required' => 0,
        ),
        // Umweltplakette
        array(
            'key' => 'field_umweltplakette',
            'label' => 'Umweltplakette',
            'name' => 'umweltplakette',
            'type' => 'select',
            'choices' => array(
                '1' => 'Keine Plakette',
                '2' => 'Rot',
                '3' => 'Gelb',
                '4' => 'Grün',
            ),
            'required' => 0,
        ),
        // Servolenkung
        array(
            'key' => 'field_servolenkung',
            'label' => 'Servolenkung',
            'name' => 'servolenkung',
            'type' => 'true_false',
            'message' => 'Hat Servolenkung',
            'required' => 0,
        ),
        // Biodiesel
        array(
            'key' => 'field_biodiesel',
            'label' => 'Biodiesel',
            'name' => 'biodiesel',
            'type' => 'true_false',
            'message' => 'Biodiesel geeignet',
            'required' => 0,
        ),
        // Scheckheftgepflegt
        array(
            'key' => 'field_scheckheftgepflegt',
            'label' => 'Scheckheftgepflegt',
            'name' => 'scheckheftgepflegt',
            'type' => 'true_false',
            'message' => 'Ist scheckheftgepflegt',
            'required' => 0,
        ),
        // Katalysator
        array(
            'key' => 'field_katalysator',
            'label' => 'Katalysator',
            'name' => 'katalysator',
            'type' => 'true_false',
            'message' => 'Hat Katalysator',
            'required' => 0,
        ),
        // Kickstarter
        array(
            'key' => 'field_kickstarter',
            'label' => 'Kickstarter',
            'name' => 'kickstarter',
            'type' => 'true_false',
            'message' => 'Hat Kickstarter',
            'required' => 0,
        ),
        // E-Starter
        array(
            'key' => 'field_estarter',
            'label' => 'E-Starter',
            'name' => 'estarter',
            'type' => 'true_false',
            'message' => 'Hat E-Starter',
            'required' => 0,
        ),
        // Vorführfahrzeug
        array(
            'key' => 'field_vorfuehrfahrzeug',
            'label' => 'Vorführfahrzeug',
            'name' => 'vorfuehrfahrzeug',
            'type' => 'true_false',
            'message' => 'Ist ein Vorführfahrzeug',
            'required' => 0,
        ),
        // Antrieb
        array(
            'key' => 'field_antrieb',
            'label' => 'Antrieb',
            'name' => 'antrieb',
            'type' => 'select',
            'choices' => array(
                '1' => 'Kette',
                '2' => 'Kardan',
                '3' => 'Riemen',
            ),
            'required' => 0,
        ),
        // Hubraum (ccm)
        array(
            'key' => 'field_ccm',
            'label' => 'Hubraum (ccm)',
            'name' => 'ccm',
            'type' => 'number',
            'required' => 0,
        ),
        // Tragkraft
        array(
            'key' => 'field_tragkraft',
            'label' => 'Tragkraft (kg)',
            'name' => 'tragkraft',
            'type' => 'number',
            'required' => 0,
        ),
        // Nutzlast
        array(
            'key' => 'field_nutzlast',
            'label' => 'Nutzlast (kg)',
            'name' => 'nutzlast',
            'type' => 'number',
            'required' => 0,
        ),
        // Gesamtgewicht
        array(
            'key' => 'field_gesamtgewicht',
            'label' => 'Gesamtgewicht (kg)',
            'name' => 'gesamtgewicht',
            'type' => 'number',
            'required' => 0,
        ),
        // Hubhöhe
        array(
            'key' => 'field_hubhoehe',
            'label' => 'Hubhöhe (mm)',
            'name' => 'hubhoehe',
            'type' => 'number',
            'required' => 0,
        ),
        // Bauhöhe
        array(
            'key' => 'field_bauhoehe',
            'label' => 'Bauhöhe (mm)',
            'name' => 'bauhoehe',
            'type' => 'number',
            'required' => 0,
        ),
        // Baujahr
        array(
            'key' => 'field_baujahr',
            'label' => 'Baujahr',
            'name' => 'baujahr',
            'type' => 'number',
            'required' => 0,
        ),
        // Betriebsstunden
        array(
            'key' => 'field_betriebsstunden',
            'label' => 'Betriebsstunden',
            'name' => 'betriebsstunden',
            'type' => 'number',
            'required' => 0,
        ),
        // Sitze
        array(
            'key' => 'field_sitze',
            'label' => 'Sitze',
            'name' => 'sitze',
            'type' => 'number',
            'required' => 0,
        ),
        // Schadstoffklasse
        array(
            'key' => 'field_schadstoff',
            'label' => 'Schadstoffklasse',
            'name' => 'schadstoff',
            'type' => 'select',
            'choices' => array(
                '1' => 'Euro 1',
                '2' => 'Euro 2',
                '3' => 'Euro 3',
                '4' => 'Euro 4',
                '5' => 'Euro 5',
                '6' => 'Euro 6',
            ),
            'required' => 0,
        ),
        // Kabinenart
        array(
            'key' => 'field_kabinenart',
            'label' => 'Kabinenart',
            'name' => 'kabinenart',
            'type' => 'select',
            'choices' => array(
                '1' => 'Doppelkabine',
                '2' => 'Liegeplatz',
                '3' => 'Nahverkehr',
                '4' => 'Fernverkehr',
            ),
            'required' => 0,
        ),
        // Achsen
        array(
            'key' => 'field_achsen',
            'label' => 'Achsen',
            'name' => 'achsen',
            'type' => 'number',
            'required' => 0,
        ),
        // Tempomat
        array(
            'key' => 'field_tempomat',
            'label' => 'Tempomat',
            'name' => 'tempomat',
            'type' => 'true_false',
            'message' => 'Hat Tempomat',
            'required' => 0,
        ),
        // Standheizung
        array(
            'key' => 'field_standheizung',
            'label' => 'Standheizung',
            'name' => 'standheizung',
            'type' => 'true_false',
            'message' => 'Hat Standheizung',
            'required' => 0,
        ),
        // Kabine
        array(
            'key' => 'field_kabine',
            'label' => 'Kabine',
            'name' => 'kabine',
            'type' => 'true_false',
            'message' => 'Hat Kabine',
            'required' => 0,
        ),
        // Schutzdach
        array(
            'key' => 'field_schutzdach',
            'label' => 'Schutzdach',
            'name' => 'schutzdach',
            'type' => 'true_false',
            'message' => 'Hat Schutzdach',
            'required' => 0,
        ),
        // Vollverkleidung
        array(
            'key' => 'field_vollverkleidung',
            'label' => 'Vollverkleidung',
            'name' => 'vollverkleidung',
            'type' => 'true_false',
            'message' => 'Hat Vollverkleidung',
            'required' => 0,
        ),
        // Kommunal
        array(
            'key' => 'field_komunal',
            'label' => 'Kommunal',
            'name' => 'komunal',
            'type' => 'true_false',
            'message' => 'Ist Kommunalfahrzeug',
            'required' => 0,
        ),
        // Kran
        array(
            'key' => 'field_kran',
            'label' => 'Kran',
            'name' => 'kran',
            'type' => 'true_false',
            'message' => 'Hat Kran',
            'required' => 0,
        ),
        // Retarder/Intarder
        array(
            'key' => 'field_retarder_intarder',
            'label' => 'Retarder/Intarder',
            'name' => 'retarder_intarder',
            'type' => 'true_false',
            'message' => 'Hat Retarder/Intarder',
            'required' => 0,
        ),
        // Schlafplatz
        array(
            'key' => 'field_schlafplatz',
            'label' => 'Schlafplatz',
            'name' => 'schlafplatz',
            'type' => 'true_false',
            'message' => 'Hat Schlafplatz',
            'required' => 0,
        ),
        // TV
        array(
            'key' => 'field_tv',
            'label' => 'TV',
            'name' => 'tv',
            'type' => 'true_false',
            'message' => 'Hat TV',
            'required' => 0,
        ),
        // WC
        array(
            'key' => 'field_wc',
            'label' => 'WC',
            'name' => 'wc',
            'type' => 'true_false',
            'message' => 'Hat WC',
            'required' => 0,
        ),
        // Ladebordwand
        array(
            'key' => 'field_ladebordwand',
            'label' => 'Ladebordwand',
            'name' => 'ladebordwand',
            'type' => 'true_false',
            'message' => 'Hat Ladebordwand',
            'required' => 0,
        ),
        // Hydraulikanlage
        array(
            'key' => 'field_hydraulikanlage',
            'label' => 'Hydraulikanlage',
            'name' => 'hydraulikanlage',
            'type' => 'select',
            'choices' => array(
                '1' => 'Andere Hydraulik',
                '2' => 'Kipphydraulik',
                '3' => 'Schubbodenhydraulik',
                '4' => 'Tankwagenhydraulik',
            ),
            'required' => 0,
        ),
        // Schiebetür
        array(
            'key' => 'field_schiebetuer',
            'label' => 'Schiebetür',
            'name' => 'schiebetuer',
            'type' => 'true_false',
            'message' => 'Hat Schiebetür',
            'required' => 0,
        ),
        // Radformel
        array(
            'key' => 'field_radformel',
            'label' => 'Radformel',
            'name' => 'radformel',
            'type' => 'select',
            'choices' => array(
                '0' => 'Beliebig',
                '1' => '4x2',
                '2' => '4x4',
                '3' => '6x2',
                '4' => '6x4',
                '5' => '6x6',
                '6' => '8x4',
                '7' => '8x6',
                '9' => '8x8',
            ),
            'required' => 0,
        ),
        // Trennwand
        array(
            'key' => 'field_trennwand',
            'label' => 'Trennwand',
            'name' => 'trennwand',
            'type' => 'true_false',
            'message' => 'Hat Trennwand',
            'required' => 0,
        ),
        // EBS
        array(
            'key' => 'field_ebs',
            'label' => 'EBS',
            'name' => 'ebs',
            'type' => 'true_false',
            'message' => 'Hat EBS',
            'required' => 0,
        ),
        // Vermietbar
        array(
            'key' => 'field_vermietbar',
            'label' => 'Vermietbar',
            'name' => 'vermietbar',
            'type' => 'true_false',
            'message' => 'Ist vermietbar',
            'required' => 0,
        ),
        // Kompressor
        array(
            'key' => 'field_kompressor',
            'label' => 'Kompressor',
            'name' => 'kompressor',
            'type' => 'true_false',
            'message' => 'Hat Kompressor',
            'required' => 0,
        ),
        // Luftfederung
        array(
            'key' => 'field_luftfederung',
            'label' => 'Luftfederung',
            'name' => 'luftfederung',
            'type' => 'true_false',
            'message' => 'Hat Luftfederung',
            'required' => 0,
        ),
        // Scheibenbremse
        array(
            'key' => 'field_scheibenbremse',
            'label' => 'Scheibenbremse',
            'name' => 'scheibenbremse',
            'type' => 'true_false',
            'message' => 'Hat Scheibenbremse',
            'required' => 0,
        ),
        // Fronthydraulik
        array(
            'key' => 'field_fronthydraulik',
            'label' => 'Fronthydraulik',
            'name' => 'fronthydraulik',
            'type' => 'true_false',
            'message' => 'Hat Fronthydraulik',
            'required' => 0,
        ),
        // BSS
        array(
            'key' => 'field_bss',
            'label' => 'BSS',
            'name' => 'bss',
            'type' => 'true_false',
            'message' => 'Hat BSS',
            'required' => 0,
        ),
        // Schnellwechsel
        array(
            'key' => 'field_schnellwechsel',
            'label' => 'Schnellwechsel',
            'name' => 'schnellwechsel',
            'type' => 'true_false',
            'message' => 'Hat Schnellwechsel',
            'required' => 0,
        ),
        // ZSA
        array(
            'key' => 'field_zsa',
            'label' => 'ZSA',
            'name' => 'zsa',
            'type' => 'true_false',
            'message' => 'Hat ZSA',
            'required' => 0,
        ),
        // Küche
        array(
            'key' => 'field_kueche',
            'label' => 'Küche',
            'name' => 'kueche',
            'type' => 'true_false',
            'message' => 'Hat Küche',
            'required' => 0,
        ),
        // Kühlbox
        array(
            'key' => 'field_kuehlbox',
            'label' => 'Kühlbox',
            'name' => 'kuehlbox',
            'type' => 'true_false',
            'message' => 'Hat Kühlbox',
            'required' => 0,
        ),
        // Schlafsitze
        array(
            'key' => 'field_schlafsitze',
            'label' => 'Schlafsitze',
            'name' => 'schlafsitze',
            'type' => 'true_false',
            'message' => 'Hat Schlafsitze',
            'required' => 0,
        ),
// Frontheber
array(
    'key' => 'field_frontheber',
    'label' => 'Frontheber',
    'name' => 'frontheber',
    'type' => 'true_false',
    'message' => 'Hat Frontheber',
    'required' => 0,
),
// Sichtbar nur für Händler
array(
    'key' => 'field_sichtbar_nur_fuer_haendler',
    'label' => 'Sichtbar nur für Händler',
    'name' => 'sichtbar_nur_fuer_haendler',
    'type' => 'true_false',
    'message' => 'Nur für Händler sichtbar',
    'required' => 0,
),
// Reserviert
array(
    'key' => 'field_reserviert',
    'label' => 'Reserviert',
    'name' => 'reserviert',
    'type' => 'true_false',
    'message' => 'Ist reserviert',
    'required' => 0,
),
// EnVKV
array(
    'key' => 'field_envkv',
    'label' => 'EnVKV',
    'name' => 'envkv',
    'type' => 'true_false',
    'message' => 'EnVKV',
    'required' => 0,
),
// Verbrauch innerorts
array(
    'key' => 'field_verbrauch_innerorts',
    'label' => 'Verbrauch innerorts',
    'name' => 'verbrauch_innerorts',
    'type' => 'number',
    'step' => 0.1,
    'required' => 0,
),
// Verbrauch außerorts
array(
    'key' => 'field_verbrauch_ausserorts',
    'label' => 'Verbrauch außerorts',
    'name' => 'verbrauch_ausserorts',
    'type' => 'number',
    'step' => 0.1,
    'required' => 0,
),
// Verbrauch kombiniert
array(
    'key' => 'field_verbrauch_kombiniert',
    'label' => 'Verbrauch kombiniert',
    'name' => 'verbrauch_kombiniert',
    'type' => 'number',
    'step' => 0.1,
    'required' => 0,
),
// Emission
array(
    'key' => 'field_emission',
    'label' => 'Emission (g/km)',
    'name' => 'emission',
    'type' => 'number',
    'required' => 0,
),
// Xenonscheinwerfer
array(
    'key' => 'field_xenonscheinwerfer',
    'label' => 'Xenonscheinwerfer',
    'name' => 'xenonscheinwerfer',
    'type' => 'true_false',
    'message' => 'Hat Xenonscheinwerfer',
    'required' => 0,
),
// Sitzheizung
array(
    'key' => 'field_sitzheizung',
    'label' => 'Sitzheizung',
    'name' => 'sitzheizung',
    'type' => 'true_false',
    'message' => 'Hat Sitzheizung',
    'required' => 0,
),
// Partikelfilter
array(
    'key' => 'field_partikelfilter',
    'label' => 'Partikelfilter',
    'name' => 'partikelfilter',
    'type' => 'true_false',
    'message' => 'Hat Partikelfilter',
    'required' => 0,
),
// Einparkhilfe
array(
    'key' => 'field_einparkhilfe',
    'label' => 'Einparkhilfe',
    'name' => 'einparkhilfe',
    'type' => 'true_false',
    'message' => 'Hat Einparkhilfe',
    'required' => 0,
),
// Schwackecode
array(
    'key' => 'field_schwackecode',
    'label' => 'Schwackecode',
    'name' => 'schwackecode',
    'type' => 'number',
    'required' => 0,
),
// Lieferdatum
array(
    'key' => 'field_lieferdatum',
    'label' => 'Lieferdatum',
    'name' => 'lieferdatum',
    'type' => 'text',
    'required' => 0,
),
// Lieferfrist
array(
    'key' => 'field_lieferfrist',
    'label' => 'Lieferfrist',
    'name' => 'lieferfrist',
    'type' => 'select',
    'choices' => array(
        '1' => '1 Tag',
        '2' => '2 Tage',
        '3' => '3 Tage',
        '4' => '4 Tage',
        '5' => '5 Tage',
        '6' => '6 Tage',
        '7' => '7 Tage',
        '14' => '14 Tage',
        '42' => '42 Tage',
        '60' => '60 Tage',
        '90' => '90 Tage',
        '120' => '120 Tage',
        '150' => '150 Tage',
        '180' => '180 Tage',
        '270' => '270 Tage',
        '360' => '360 Tage',
    ),
    'required' => 0,
),
// Überführungskosten
array(
    'key' => 'field_ueberfuehrungskosten',
    'label' => 'Überführungskosten',
    'name' => 'ueberfuehrungskosten',
    'type' => 'number',
    'required' => 0,
),
// HU/AU neu
array(
    'key' => 'field_hu_au_neu',
    'label' => 'HU/AU neu',
    'name' => 'hu_au_neu',
    'type' => 'true_false',
    'message' => 'HU und AU werden mit dem Kauf ausgeführt',
    'required' => 0,
),
// Kraftstoffart
array(
    'key' => 'field_kraftstoffart',
    'label' => 'Kraftstoffart',
    'name' => 'kraftstoffart',
    'type' => 'select',
    'choices' => array(
        '0' => 'Andere',
        '1' => 'Benzin',
        '2' => 'Diesel',
        '3' => 'Autogas',
        '4' => 'Erdgas',
        '6' => 'Elektro',
        '7' => 'Hybrid',
        '8' => 'Wasserstoff',
        '9' => 'Ethanol',
        '10' => 'Hybrid-Diesel',
    ),
    'required' => 0,
),
// Getriebeart
array(
    'key' => 'field_getriebeart',
    'label' => 'Getriebeart',
    'name' => 'getriebeart',
    'type' => 'select',
    'choices' => array(
        '0' => 'Keine Angabe',
        '1' => 'Schaltgetriebe',
        '2' => 'Halbautomatik',
        '3' => 'Automatik',
    ),
    'required' => 0,
),
// Exportfahrzeug
array(
    'key' => 'field_exportfahrzeug',
    'label' => 'Exportfahrzeug',
    'name' => 'exportfahrzeug',
    'type' => 'true_false',
    'message' => 'Ist Exportfahrzeug',
    'required' => 0,
),
// Tageszulassung
array(
    'key' => 'field_tageszulassung',
    'label' => 'Tageszulassung',
    'name' => 'tageszulassung',
    'type' => 'true_false',
    'message' => 'Ist Tageszulassung',
    'required' => 0,
),
// Blickfänger
array(
    'key' => 'field_blickfaenger',
    'label' => 'Blickfänger',
    'name' => 'blickfaenger',
    'type' => 'true_false',
    'message' => 'Ist Blickfänger',
    'required' => 0,
),
// HSN
array(
    'key' => 'field_hsn',
    'label' => 'HSN',
    'name' => 'hsn',
    'type' => 'text',
    'required' => 0,
),
// TSN
array(
    'key' => 'field_tsn',
    'label' => 'TSN',
    'name' => 'tsn',
    'type' => 'text',
    'required' => 0,
),
// Seite 1 Inserat
array(
    'key' => 'field_seite_1_inserat',
    'label' => 'Seite 1 Inserat',
    'name' => 'seite_1_inserat',
    'type' => 'true_false',
    'message' => 'Als Seite 1 Inserat markieren',
    'required' => 0,
),
// E10
array(
    'key' => 'field_e10',
    'label' => 'E10',
    'name' => 'e10',
    'type' => 'true_false',
    'message' => 'E10 geeignet',
    'required' => 0,
),
// Pflanzenöl
array(
    'key' => 'field_pflanzenoel',
    'label' => 'Pflanzenöl',
    'name' => 'pflanzenoel',
    'type' => 'true_false',
    'message' => 'Pflanzenöl geeignet',
    'required' => 0,
),
// SCR
array(
    'key' => 'field_scr',
    'label' => 'SCR',
    'name' => 'scr',
    'type' => 'true_false',
    'message' => 'Hat Harnstofftank',
    'required' => 0,
),
// Koffer
array(
    'key' => 'field_koffer',
    'label' => 'Koffer',
    'name' => 'koffer',
    'type' => 'true_false',
    'message' => 'Hat Koffer',
    'required' => 0,
),
// Sturzbügel
array(
    'key' => 'field_sturzbuegel',
    'label' => 'Sturzbügel',
    'name' => 'sturzbuegel',
    'type' => 'true_false',
    'message' => 'Hat Sturzbügel',
    'required' => 0,
),
// Scheibe
array(
    'key' => 'field_scheibe',
    'label' => 'Scheibe',
    'name' => 'scheibe',
    'type' => 'true_false',
    'message' => 'Hat Scheibe',
    'required' => 0,
),
// Standklima
array(
    'key' => 'field_standklima',
    'label' => 'Standklima',
    'name' => 'standklima',
    'type' => 'true_false',
    'message' => 'Hat Standklima',
    'required' => 0,
),
// S-S-Bereifung
array(
    'key' => 'field_s_s_bereifung',
    'label' => 'S-S-Bereifung',
    'name' => 's_s_bereifung',
    'type' => 'true_false',
    'message' => 'Hat S-S-Bereifung',
    'required' => 0,
),
// Straßenzulassung
array(
    'key' => 'field_strassenzulassung',
    'label' => 'Straßenzulassung',
    'name' => 'strassenzulassung',
    'type' => 'true_false',
    'message' => 'Hat Straßenzulassung',
    'required' => 0,
),
// Etagenbett
array(
    'key' => 'field_etagenbett',
    'label' => 'Etagenbett',
    'name' => 'etagenbett',
    'type' => 'true_false',
    'message' => 'Hat Etagenbett',
    'required' => 0,
),
// Festbett
array(
    'key' => 'field_festbett',
    'label' => 'Festbett',
    'name' => 'festbett',
    'type' => 'true_false',
    'message' => 'Hat Festbett',
    'required' => 0,
),
// Heckgarage
array(
    'key' => 'field_heckgarage',
    'label' => 'Heckgarage',
    'name' => 'heckgarage',
    'type' => 'true_false',
    'message' => 'Hat Heckgarage',
    'required' => 0,
),
// Markise
array(
    'key' => 'field_markise',
    'label' => 'Markise',
    'name' => 'markise',
    'type' => 'true_false',
    'message' => 'Hat Markise',
    'required' => 0,
),
// Separate Dusche
array(
    'key' => 'field_sep_dusche',
    'label' => 'Separate Dusche',
    'name' => 'sep_dusche',
    'type' => 'true_false',
    'message' => 'Hat separate Dusche',
    'required' => 0,
),
// Solaranlage
array(
    'key' => 'field_solaranlage',
    'label' => 'Solaranlage',
    'name' => 'solaranlage',
    'type' => 'true_false',
    'message' => 'Hat Solaranlage',
    'required' => 0,
),
// Mittelsitzgruppe
array(
    'key' => 'field_mittelsitzgruppe',
    'label' => 'Mittelsitzgruppe',
    'name' => 'mittelsitzgruppe',
    'type' => 'true_false',
    'message' => 'Hat Mittelsitzgruppe',
    'required' => 0,
),
// Rundsitzgruppe
array(
    'key' => 'field_rundsitzgruppe',
    'label' => 'Rundsitzgruppe',
    'name' => 'rundsitzgruppe',
    'type' => 'true_false',
    'message' => 'Hat Rundsitzgruppe',
    'required' => 0,
),
// Seitensitzgruppe
array(
    'key' => 'field_seitensitzgruppe',
    'label' => 'Seitensitzgruppe',
    'name' => 'seitensitzgruppe',
    'type' => 'true_false',
    'message' => 'Hat Seitensitzgruppe',
    'required' => 0,
),
// Hagelschaden
array(
    'key' => 'field_hagelschaden',
    'label' => 'Hagelschaden',
    'name' => 'hagelschaden',
    'type' => 'true_false',
    'message' => 'Hat Hagelschaden',
    'required' => 0,
),
// Schlafplätze
array(
    'key' => 'field_schlafplaetze',
    'label' => 'Schlafplätze',
    'name' => 'schlafplaetze',
    'type' => 'number',
    'required' => 0,
),
// Fahrzeuglänge
array(
    'key' => 'field_fahrzeuglaenge',
    'label' => 'Fahrzeuglänge (mm)',
    'name' => 'fahrzeuglaenge',
    'type' => 'number',
    'required' => 0,
),
// Fahrzeugbreite
array(
    'key' => 'field_fahrzeugbreite',
    'label' => 'Fahrzeugbreite (mm)',
    'name' => 'fahrzeugbreite',
    'type' => 'number',
    'required' => 0,
),
// Fahrzeughöhe
array(
    'key' => 'field_fahrzeughoehe',
    'label' => 'Fahrzeughöhe (mm)',
    'name' => 'fahrzeughoehe',
    'type' => 'number',
    'required' => 0,
),
// Laderaum Europalette
array(
    'key' => 'field_laderaum_europalette',
    'label' => 'Laderaum Europalette (Stück)',
    'name' => 'laderaum_europalette',
    'type' => 'number',
    'required' => 0,
),
// Laderaum Volumen
array(
    'key' => 'field_laderaum_volumen',
    'label' => 'Laderaum Volumen (m³)',
    'name' => 'laderaum_volumen',
    'type' => 'number',
    'required' => 0,
),
// Laderaum Länge
array(
    'key' => 'field_laderaum_laenge',
    'label' => 'Laderaum Länge (mm)',
    'name' => 'laderaum_laenge',
    'type' => 'number',
    'required' => 0,
),
// Laderaum Breite
array(
    'key' => 'field_laderaum_breite',
    'label' => 'Laderaum Breite (mm)',
    'name' => 'laderaum_breite',
    'type' => 'number',
    'required' => 0,
),
// Laderaum Höhe
array(
    'key' => 'field_laderaum_hoehe',
    'label' => 'Laderaum Höhe (mm)',
    'name' => 'laderaum_hoehe',
    'type' => 'number',
    'required' => 0,
),
// Inserat als 'neu' markieren
array(
    'key' => 'field_inserat_als_neu_markieren',
    'label' => "Inserat als 'neu' markieren",
    'name' => 'inserat_als_neu_markieren',
    'type' => 'true_false',
    'message' => "Als 'neu' markieren",
    'required' => 0,
),
// Effektiver Jahreszins
array(
    'key' => 'field_effektiver_jahreszins',
    'label' => 'Effektiver Jahreszins',
    'name' => 'effektiver_jahreszins',
    'type' => 'number',
    'step' => 0.01,
    'required' => 0,
),
// Monatliche Rate
array(
    'key' => 'field_monatliche_rate',
    'label' => 'Monatliche Rate',
    'name' => 'monatliche_rate',
    'type' => 'number',
    'required' => 0,
),
// Laufzeit
array(
    'key' => 'field_laufzeit',
    'label' => 'Laufzeit (Monate)',
    'name' => 'laufzeit',
    'type' => 'select',
    'choices' => array(
        '12' => '12 Monate',
        '24' => '24 Monate',
        '36' => '36 Monate',
        '48' => '48 Monate',
        '60' => '60 Monate',
        '72' => '72 Monate',
        '84' => '84 Monate',
        '96' => '96 Monate',
    ),
    'required' => 0,
),
// Anzahlung
array(
    'key' => 'field_anzahlung',
    'label' => 'Anzahlung',
    'name' => 'anzahlung',
    'type' => 'number',
    'required' => 0,
),
// Schlussrate
array(
    'key' => 'field_schlussrate',
    'label' => 'Schlussrate',
    'name' => 'schlussrate',
    'type' => 'number',
    'required' => 0,
),
// Finanzierungsfeature
array(
    'key' => 'field_finanzierungsfeature',
    'label' => 'Finanzierungsfeature',
    'name' => 'finanzierungsfeature',
    'type' => 'true_false',
    'message' => 'Finanzierungsfeature aktivieren',
    'required' => 0,
),
// Interieurfarbe
array(
    'key' => 'field_interieurfarbe',
    'label' => 'Interieurfarbe',
    'name' => 'interieurfarbe',
    'type' => 'select',
    'choices' => array(
        '1' => 'Schwarz',
        '2' => 'Grau',
        '3' => 'Beige',
        '4' => 'Braun',
        '5' => 'Andere',
    ),
    'required' => 0,
),
// Interieurtyp
array(
    'key' => 'field_interieurtyp',
    'label' => 'Interieurtyp',
    'name' => 'interieurtyp',
    'type' => 'select',
    'choices' => array(
        '1' => 'Leder',
        '2' => 'Teilleder',
        '3' => 'Stoff',
        '4' => 'Velour',
        '5' => 'Alcantara',
        '6' => 'Andere',
    ),
    'required' => 0,
),
// Airbag
array(
    'key' => 'field_airbag',
    'label' => 'Airbag',
    'name' => 'airbag',
    'type' => 'select',
    'choices' => array(
        '2' => 'Fahrer Airbag',
        '3' => 'Vordere Airbags',
        '4' => 'Vorder und Seiten Airbags',
        '5' => 'Vorder und Seiten und weitere Airbags',
    ),
    'required' => 0,
),
// Vorbesitzer
array(
    'key' => 'field_vorbesitzer',
    'label' => 'Vorbesitzer',
    'name' => 'vorbesitzer',
    'type' => 'number',
    'required' => 0,
),
// Top Inserat
array(
    'key' => 'field_top_inserat',
    'label' => 'Top Inserat',
    'name' => 'top_inserat',
    'type' => 'true_false',
    'message' => 'Als Top Inserat markieren',
    'required' => 0,
),
// Bruttokreditbetrag
array(
    'key' => 'field_bruttokreditbetrag',
    'label' => 'Bruttokreditbetrag',
    'name' => 'bruttokreditbetrag',
    'type' => 'number',
    'step' => 0.01,
    'required' => 0,
),
// Abschlussgebühren
array(
    'key' => 'field_abschlussgebuehren',
    'label' => 'Abschlussgebühren',
    'name' => 'abschlussgebuehren',
    'type' => 'number',
    'step' => 0.01,
    'required' => 0,
),
// Ratenabsicherung
array(
    'key' => 'field_ratenabsicherung',
    'label' => 'Ratenabsicherung',
    'name' => 'ratenabsicherung',
    'type' => 'number',
    'step' => 0.01,
    'required' => 0,
),
// Nettokreditbetrag
array(
    'key' => 'field_nettokreditbetrag',
    'label' => 'Nettokreditbetrag',
    'name' => 'nettokreditbetrag',
    'type' => 'number',
    'step' => 0.01,
    'required' => 0,
),
// Anbieterbank
array(
    'key' => 'field_anbieterbank',
    'label' => 'Anbieterbank',
    'name' => 'anbieterbank',
    'type' => 'text',
    'required' => 0,
),
// Soll-Zinssatz
array(
    'key' => 'field_soll_zinssatz',
    'label' => 'Soll-Zinssatz',
    'name' => 'soll_zinssatz',
    'type' => 'number',
    'step' => 0.01,
    'required' => 0,
),
// Art des Soll-Zinssatzes
array(
    'key' => 'field_art_des_soll_zinssatzes',
    'label' => 'Art des Soll-Zinssatzes',
    'name' => 'art_des_soll_zinssatzes',
    'type' => 'select',
    'choices' => array(
        '1' => 'Gebunden',
        '2' => 'Veränderlich',
        '3' => 'Kombiniert',
    ),
    'required' => 0,
),
// Landesversion
array(
    'key' => 'field_landesversion',
    'label' => 'Landesversion',
    'name' => 'landesversion',
    'type' => 'text',
    'required' => 0,
),
// Video-URL
array(
    'key' => 'field_video_url',
    'label' => 'Video-URL',
    'name' => 'video_url',
    'type' => 'url',
    'required' => 0,
),
// Energieeffizienzklasse
array(
    'key' => 'field_energieeffizienzklasse',
    'label' => 'Energieeffizienzklasse',
    'name' => 'energieeffizienzklasse',
    'type' => 'select',
    'choices' => array(
        'A+' => 'A+',
        'A' => 'A',
        'B' => 'B',
        'C' => 'C',
        'D' => 'D',
        'E' => 'E',
        'F' => 'F',
        'G' => 'G',
    ),
    'required' => 0,
),
// EnVKV Benzin Sorte
array(
    'key' => 'field_envkv_benzin_sorte',
    'label' => 'EnVKV Benzin Sorte',
    'name' => 'envkv_benzin_sorte',
    'type' => 'select',
    'choices' => array(
        'NORMAL' => 'Normal',
        'SUPER' => 'Super',
        'SUPER_PLUS' => 'Super Plus',
    ),
    'required' => 0,
),
// Elektrische Seitenspiegel
array(
    'key' => 'field_elektrische_seitenspiegel',
    'label' => 'Elektrische Seitenspiegel',
    'name' => 'elektrische_seitenspiegel',
    'type' => 'true_false',
    'message' => 'Hat elektrische Seitenspiegel',
    'required' => 0,
),
// Sportfahrwerk
array(
    'key' => 'field_sportfahrwerk',
    'label' => 'Sportfahrwerk',
    'name' => 'sportfahrwerk',
    'type' => 'true_false',
    'message' => 'Hat Sportfahrwerk',
    'required' => 0,
),
// Sportpaket
array(
    'key' => 'field_sportpaket',
    'label' => 'Sportpaket',
    'name' => 'sportpaket',
    'type' => 'true_false',
    'message' => 'Hat Sportpaket',
    'required' => 0,
),
// Bluetooth
array(
    'key' => 'field_bluetooth',
    'label' => 'Bluetooth',
    'name' => 'bluetooth',
    'type' => 'true_false',
    'message' => 'Hat Bluetooth',
    'required' => 0,
),
// Bordcomputer
array(
    'key' => 'field_bordcomputer',
    'label' => 'Bordcomputer',
    'name' => 'bordcomputer',
    'type' => 'true_false',
    'message' => 'Hat Bordcomputer',
    'required' => 0,
),
// CD-Spieler
array(
    'key' => 'field_cd_spieler',
    'label' => 'CD-Spieler',
    'name' => 'cd_spieler',
    'type' => 'true_false',
    'message' => 'Hat CD-Spieler',
    'required' => 0,
),
// Elektrische Sitzeinstellung
array(
    'key' => 'field_elektrische_sitzeinstellung',
    'label' => 'Elektrische Sitzeinstellung',
    'name' => 'elektrische_sitzeinstellung',
    'type' => 'true_false',
    'message' => 'Hat elektrische Sitzeinstellung',
    'required' => 0,
),
// Head-Up Display
array(
    'key' => 'field_head_up_display',
    'label' => 'Head-Up Display',
    'name' => 'head_up_display',
    'type' => 'true_false',
    'message' => 'Hat Head-Up Display',
    'required' => 0,
),
// Freisprecheinrichtung
array(
    'key' => 'field_freisprecheinrichtung',
    'label' => 'Freisprecheinrichtung',
    'name' => 'freisprecheinrichtung',
    'type' => 'true_false',
    'message' => 'Hat Freisprecheinrichtung',
    'required' => 0,
),
// MP3-Schnittstelle
array(
    'key' => 'field_mp3_schnittstelle',
    'label' => 'MP3-Schnittstelle',
    'name' => 'mp3_schnittstelle',
    'type' => 'true_false',
    'message' => 'Hat MP3-Schnittstelle',
    'required' => 0,
),
// Multifunktionslenkrad
array(
    'key' => 'field_multifunktionslenkrad',
    'label' => 'Multifunktionslenkrad',
    'name' => 'multifunktionslenkrad',
    'type' => 'true_false',
    'message' => 'Hat Multifunktionslenkrad',
    'required' => 0,
),
// Skisack
array(
    'key' => 'field_skisack',
    'label' => 'Skisack',
    'name' => 'skisack',
    'type' => 'true_false',
    'message' => 'Hat Skisack',
    'required' => 0,
),
// Tuner oder Radio
array(
    'key' => 'field_tuner_oder_radio',
    'label' => 'Tuner oder Radio',
    'name' => 'tuner_oder_radio',
    'type' => 'true_false',
    'message' => 'Hat Tuner oder Radio',
    'required' => 0,
),
// Sportsitze
array(
    'key' => 'field_sportsitze',
    'label' => 'Sportsitze',
    'name' => 'sportsitze',
    'type' => 'true_false',
    'message' => 'Hat Sportsitze',
    'required' => 0,
),
// Panorama-Dach
array(
    'key' => 'field_panorama_dach',
    'label' => 'Panorama-Dach',
    'name' => 'panorama_dach',
    'type' => 'true_false',
    'message' => 'Hat Panorama-Dach',
    'required' => 0,
),
// Kindersitzbefestigung
array(
    'key' => 'field_kindersitzbefestigung',
    'label' => 'Kindersitzbefestigung',
    'name' => 'kindersitzbefestigung',
    'type' => 'true_false',
    'message' => 'Hat Kindersitzbefestigung',
    'required' => 0,
),
// Kurvenlicht
array(
    'key' => 'field_kurvenlicht',
    'label' => 'Kurvenlicht',
    'name' => 'kurvenlicht',
    'type' => 'true_false',
    'message' => 'Hat Kurvenlicht',
    'required' => 0,
),
// Lichtsensor
array(
    'key' => 'field_lichtsensor',
    'label' => 'Lichtsensor',
    'name' => 'lichtsensor',
    'type' => 'true_false',
    'message' => 'Hat Lichtsensor',
    'required' => 0,
),
// Nebelscheinwerfer
array(
    'key' => 'field_nebelscheinwerfer',
    'label' => 'Nebelscheinwerfer',
    'name' => 'nebelscheinwerfer',
    'type' => 'true_false',
    'message' => 'Hat Nebelscheinwerfer',
    'required' => 0,
),
// Tagfahrlicht
array(
    'key' => 'field_tagfahrlicht',
    'label' => 'Tagfahrlicht',
    'name' => 'tagfahrlicht',
    'type' => 'true_false',
    'message' => 'Hat Tagfahrlicht',
    'required' => 0,
),
// Traktionskontrolle
array(
    'key' => 'field_traktionskontrolle',
    'label' => 'Traktionskontrolle',
    'name' => 'traktionskontrolle',
    'type' => 'true_false',
    'message' => 'Hat Traktionskontrolle',
    'required' => 0,
),
// Start/Stop-Automatik
array(
    'key' => 'field_start_stop_automatik',
    'label' => 'Start/Stop-Automatik',
    'name' => 'start_stop_automatik',
    'type' => 'true_false',
    'message' => 'Hat Start/Stop-Automatik',
    'required' => 0,
),
// Regensensor
array(
    'key' => 'field_regensensor',
    'label' => 'Regensensor',
    'name' => 'regensensor',
    'type' => 'true_false',
    'message' => 'Hat Regensensor',
    'required' => 0,
),
// Nichtraucher-Fahrzeug
array(
    'key' => 'field_nichtraucher_fahrzeug',
    'label' => 'Nichtraucher-Fahrzeug',
    'name' => 'nichtraucher_fahrzeug',
    'type' => 'true_false',
    'message' => 'Ist Nichtraucher-Fahrzeug',
    'required' => 0,
),
// Dachreling
array(
    'key' => 'field_dachreling',
    'label' => 'Dachreling',
    'name' => 'dachreling',
    'type' => 'true_false',
    'message' => 'Hat Dachreling',
    'required' => 0,
),
// Unfallfahrzeug
array(
    'key' => 'field_unfallfahrzeug',
    'label' => 'Unfallfahrzeug',
    'name' => 'unfallfahrzeug',
    'type' => 'true_false',
    'message' => 'Ist Unfallfahrzeug',
    'required' => 0,
),
// Fahrtauglich
array(
    'key' => 'field_fahrtauglich',
    'label' => 'Fahrtauglich',
    'name' => 'fahrtauglich',
    'type' => 'true_false',
    'message' => 'Ist fahrtauglich',
    'required' => 0,
),
// Produktionsdatum
array(
    'key' => 'field_produktionsdatum',
    'label' => 'Produktionsdatum',
    'name' => 'produktionsdatum',
    'type' => 'text',
    'required' => 0,
),
// Einparkhilfe Sensoren vorne
array(
    'key' => 'field_einparkhilfe_sensoren_vorne',
    'label' => 'Einparkhilfe Sensoren vorne',
    'name' => 'einparkhilfe_sensoren_vorne',
    'type' => 'true_false',
    'message' => 'Hat Einparkhilfe Sensoren vorne',
    'required' => 0,
),
// Einparkhilfe Sensoren hinten
array(
    'key' => 'field_einparkhilfe_sensoren_hinten',
    'label' => 'Einparkhilfe Sensoren hinten',
    'name' => 'einparkhilfe_sensoren_hinten',
    'type' => 'true_false',
    'message' => 'Hat Einparkhilfe Sensoren hinten',
    'required' => 0,
),
// Einparkhilfe Kamera
array(
    'key' => 'field_einparkhilfe_kamera',
    'label' => 'Einparkhilfe Kamera',
    'name' => 'einparkhilfe_kamera',
    'type' => 'true_false',
    'message' => 'Hat Einparkhilfe Kamera',
    'required' => 0,
),
// Einparkhilfe selbstlenkendes System
array(
    'key' => 'field_einparkhilfe_selbstlenkendes_system',
    'label' => 'Einparkhilfe selbstlenkendes System',
    'name' => 'einparkhilfe_selbstlenkendes_system',
    'type' => 'true_false',
    'message' => 'Hat Einparkhilfe selbstlenkendes System',
    'required' => 0,
),
// Rotstiftpreis
array(
    'key' => 'field_rotstiftpreis',
    'label' => 'Rotstiftpreis',
    'name' => 'rotstiftpreis',
    'type' => 'true_false',
    'message' => 'Rotstiftpreis gebucht',
    'required' => 0,
),
// Kleinanzeigen Export
array(
    'key' => 'field_kleinanzeigen_export',
    'label' => 'Kleinanzeigen Export',
    'name' => 'kleinanzeigen_export',
    'type' => 'true_false',
    'message' => 'Kleinanzeigen Export gebucht',
    'required' => 0,
),
// Plugin-Hybrid
array(
    'key' => 'field_plugin_hybrid',
    'label' => 'Plugin-Hybrid',
    'name' => 'plugin_hybrid',
    'type' => 'true_false',
    'message' => 'Ist Plugin-Hybrid',
    'required' => 0,
),
// Kombinierter Stromverbrauch
array(
    'key' => 'field_kombinierter_stromverbrauch',
    'label' => 'Kombinierter Stromverbrauch (kWh/100km)',
    'name' => 'kombinierter_stromverbrauch',
    'type' => 'number',
    'step' => 0.1,
    'required' => 0,
),
// Highlight 1
array(
    'key' => 'field_highlight_1',
    'label' => 'Highlight 1',
    'name' => 'highlight_1',
    'type' => 'text',
    'required' => 0,
),
// Highlight 2
array(
    'key' => 'field_highlight_2',
    'label' => 'Highlight 2',
    'name' => 'highlight_2',
    'type' => 'text',
    'required' => 0,
),
// Highlight 3
array(
    'key' => 'field_highlight_3',
    'label' => 'Highlight 3',
    'name' => 'highlight_3',
    'type' => 'text',
    'required' => 0,
),
// Bedingungen Finanzierungsvorschlag
array(
    'key' => 'field_bedingungen_finanzierungsvorschlag',
    'label' => 'Bedingungen Finanzierungsvorschlag',
    'name' => 'bedingungen_finanzierungsvorschlag',
    'type' => 'text',
    'required' => 0,
),
// Bilder-Galerie
array(
    'key' => 'field_bilder_galerie',
    'label' => 'Bilder-Galerie',
    'name' => 'bilder_galerie',
    'type' => 'gallery',
    'required' => 0,
),
);
}

/**
 * Liefert die CSV-Feldmapping-Definition
 * 
 * @return array Mapping von CSV-Spalten zu ACF-Feldnamen
 */
public function get_csv_field_mapping() {
    return array(
        // Grundlegende Fahrzeuginformationen
        'kundennummer' => 'kundennummer',
        'interne_nummer' => 'interne_nummer',
        'kategorie' => 'kategorie',
        'marke' => 'marke',
        'modell' => 'modell',
        'leistung' => 'leistung',
        'hu' => 'hu',
        'ez' => 'ez',
        'kilometer' => 'kilometer',
        'preis' => 'preis',
        'mwst' => 'mwst',
        'oldtimer' => 'oldtimer',
        'vin' => 'vin',
        'beschaedigtes_fahrzeug' => 'beschaedigtes_fahrzeug',
        'farbe' => 'farbe',
        'klima' => 'klima',
        'taxi' => 'taxi',
        'behindertengerecht' => 'behindertengerecht',
        'jahreswagen' => 'jahreswagen',
        'neufahrzeug' => 'neufahrzeug',
        'unsere empfehlung' => 'unsere_empfehlung',
        'haendlerpreis' => 'haendlerpreis',
        'bemerkung' => 'bemerkung',
        'bild_id' => 'bild_id',
        'metallic' => 'metallic',
        'waehrung' => 'waehrung',
        'mwstsatz' => 'mwstsatz',
        'garantie' => 'garantie',
        'leichtmetallfelgen' => 'leichtmetallfelgen',
        'esp' => 'esp',
        'abs' => 'abs',
        'anhaengerkupplung' => 'anhaengerkupplung',
        'wegfahrsperre' => 'wegfahrsperre',
        'navigationssystem' => 'navigationssystem',
        'schiebedach' => 'schiebedach',
        'zentralverriegelung' => 'zentralverriegelung',
        'fensterheber' => 'fensterheber',
        'allradantrieb' => 'allradantrieb',
        'tueren' => 'tueren',
        'umweltplakette' => 'umweltplakette',
        'servolenkung' => 'servolenkung',
        'biodiesel' => 'biodiesel',
        'scheckheftgepflegt' => 'scheckheftgepflegt',
        'katalysator' => 'katalysator',
        'kickstarter' => 'kickstarter',
        'estarter' => 'estarter',
        'vorfuehrfahrzeug' => 'vorfuehrfahrzeug',
        'antrieb' => 'antrieb',
        'ccm' => 'ccm',
        'tragkraft' => 'tragkraft',
        'nutzlast' => 'nutzlast',
        'gesamtgewicht' => 'gesamtgewicht',
        'hubhoehe' => 'hubhoehe',
        'bauhoehe' => 'bauhoehe',
        'baujahr' => 'baujahr',
        'betriebsstunden' => 'betriebsstunden',
        'sitze' => 'sitze',
        'schadstoff' => 'schadstoff',
        'kabinenart' => 'kabinenart',
        'achsen' => 'achsen',
        'tempomat' => 'tempomat',
        'standheizung' => 'standheizung',
        'kabine' => 'kabine',
        'schutzdach' => 'schutzdach',
        'vollverkleidung' => 'vollverkleidung',
        'komunal' => 'komunal',
        'kran' => 'kran',
        'retarder_intarder' => 'retarder_intarder',
        'schlafplatz' => 'schlafplatz',
        'tv' => 'tv',
        'wc' => 'wc',
        'ladebordwand' => 'ladebordwand',
        'hydraulikanlage' => 'hydraulikanlage',
        'schiebetuer' => 'schiebetuer',
        'radformel' => 'radformel',
        'trennwand' => 'trennwand',
        'ebs' => 'ebs',
        'vermietbar' => 'vermietbar',
        'kompressor' => 'kompressor',
        'luftfederung' => 'luftfederung',
        'scheibenbremse' => 'scheibenbremse',
        'fronthydraulik' => 'fronthydraulik',
        'bss' => 'bss',
        'schnellwechsel' => 'schnellwechsel',
        'zsa' => 'zsa',
        'kueche' => 'kueche',
        'kuehlbox' => 'kuehlbox',
        'schlafsitze' => 'schlafsitze',
        'frontheber' => 'frontheber',
        'sichtbar_nur_fuer_Haendler' => 'sichtbar_nur_fuer_haendler',
        'reserviert' => 'reserviert',
        'envkv' => 'envkv',
        'verbrauch_innerorts' => 'verbrauch_innerorts',
        'verbrauch_ausserorts' => 'verbrauch_ausserorts',
        'verbrauch_kombiniert' => 'verbrauch_kombiniert',
        'emission' => 'emission',
        'xenonscheinwerfer' => 'xenonscheinwerfer',
        'sitzheizung' => 'sitzheizung',
        'partikelfilter' => 'partikelfilter',
        'einparkhilfe' => 'einparkhilfe',
        'schwackecode' => 'schwackecode',
        'lieferdatum' => 'lieferdatum',
        'lieferfrist' => 'lieferfrist',
        'ueberfuehrungskosten' => 'ueberfuehrungskosten',
        'hu/au_neu' => 'hu_au_neu',
        'kraftstoffart' => 'kraftstoffart',
        'getriebeart' => 'getriebeart',
        'exportfahrzeug' => 'exportfahrzeug',
        'tageszulassung' => 'tageszulassung',
        'blickfaenger' => 'blickfaenger',
        'hsn' => 'hsn',
        'tsn' => 'tsn',
        'seite_1_inserat' => 'seite_1_inserat',
        'e10' => 'e10',
        'pflanzenoel' => 'pflanzenoel',
        'scr' => 'scr',
        'koffer' => 'koffer',
        'sturzbuegel' => 'sturzbuegel',
        'scheibe' => 'scheibe',
        'standklima' => 'standklima',
        's-s-bereifung' => 's_s_bereifung',
        'strassenzulassung' => 'strassenzulassung',
        'etagenbett' => 'etagenbett',
        'festbett' => 'festbett',
        'heckgarage' => 'heckgarage',
        'markise' => 'markise',
        'sep-dusche' => 'sep_dusche',
        'solaranlage' => 'solaranlage',
        'mittelsitzgruppe' => 'mittelsitzgruppe',
        'rundsitzgruppe' => 'rundsitzgruppe',
        'seitensitzgruppe' => 'seitensitzgruppe',
        'hagelschaden' => 'hagelschaden',
        'schlafplaetze' => 'schlafplaetze',
        'fahrzeuglaenge' => 'fahrzeuglaenge',
        'fahrzeugbreite' => 'fahrzeugbreite',
        'fahrzeughoehe' => 'fahrzeughoehe',
        'laderaum-europalette' => 'laderaum_europalette',
        'laderaum-volumen' => 'laderaum_volumen',
        'laderaum-laenge' => 'laderaum_laenge',
        'laderaum-breite' => 'laderaum_breite',
        'laderaum-hoehe' => 'laderaum_hoehe',
        'Inserat als \'neu\' markieren' => 'inserat_als_neu_markieren',
        'effektiver jahreszins' => 'effektiver_jahreszins',
        'monatliche rate' => 'monatliche_rate',
        'laufzeit' => 'laufzeit',
        'anzahlung' => 'anzahlung',
        'schlussrate' => 'schlussrate',
        'finanzierungsfeature' => 'finanzierungsfeature',
        'interieurfarbe' => 'interieurfarbe',
        'interieurtyp' => 'interieurtyp',
        'airbag' => 'airbag',
        'vorbesitzer' => 'vorbesitzer',
        'top inserat' => 'top_inserat',
        'bruttokreditbetrag' => 'bruttokreditbetrag',
        'abschlussgebuehren' => 'abschlussgebuehren',
        'ratenabsicherung' => 'ratenabsicherung',
        'nettokreditbetrag' => 'nettokreditbetrag',
        'anbieterbank' => 'anbieterbank',
        'soll-zinssatz' => 'soll_zinssatz',
        'art des soll-zinssatzes' => 'art_des_soll_zinssatzes',
        'landesversion' => 'landesversion',
        'video-url' => 'video_url',
        'energieeffizienzklasse' => 'energieeffizienzklasse',
        'envkv_benzin_sorte' => 'envkv_benzin_sorte',
        'elektrische seitenspiegel' => 'elektrische_seitenspiegel',
        'sportfahrwerk' => 'sportfahrwerk',
        'sportpaket' => 'sportpaket',
        'bluetooth' => 'bluetooth',
        'bordcomputer' => 'bordcomputer',
        'cd spieler' => 'cd_spieler',
        'elektrische sitzeinstellung' => 'elektrische_sitzeinstellung',
        'head-up display' => 'head_up_display',
        'freisprecheinrichtung' => 'freisprecheinrichtung',
        'mp3 schnittstelle' => 'mp3_schnittstelle',
        'multifunktionslenkrad' => 'multifunktionslenkrad',
        'skisack' => 'skisack',
        'tuner oder radio' => 'tuner_oder_radio',
        'sportsitze' => 'sportsitze',
        'panorama dach' => 'panorama_dach',
        'kindersitzbefestigung' => 'kindersitzbefestigung',
        'kurvenlicht' => 'kurvenlicht',
        'lichtsensor' => 'lichtsensor',
        'nebelscheinwerfer' => 'nebelscheinwerfer',
        'tagfahrlicht' => 'tagfahrlicht',
        'traktionskontrolle' => 'traktionskontrolle',
        'start stop automatik' => 'start_stop_automatik',
        'regensensor' => 'regensensor',
        'nichtraucher fahrzeug' => 'nichtraucher_fahrzeug',
        'dachreling' => 'dachreling',
        'unfallfahrzeug' => 'unfallfahrzeug',
        'fahrtauglich' => 'fahrtauglich',
        'produktionsdatum' => 'produktionsdatum',
        'einparkhilfe sensoren vorne' => 'einparkhilfe_sensoren_vorne',
        'einparkhilfe sensoren hinten' => 'einparkhilfe_sensoren_hinten',
        'einparkhilfe kamera' => 'einparkhilfe_kamera',
        'einparkhilfe selbstlenkendes system' => 'einparkhilfe_selbstlenkendes_system',
        'rotstiftpreis' => 'rotstiftpreis',
        'Kleinanzeigen export' => 'kleinanzeigen_export',
        'plugin hybrid' => 'plugin_hybrid',
        'kombinierter stromverbrauch' => 'kombinierter_stromverbrauch',
        'highlight 1' => 'highlight_1',
        'highlight 2' => 'highlight_2',
        'highlight 3' => 'highlight_3',
        'bedingungen finanzierungsvorschlag' => 'bedingungen_finanzierungsvorschlag',
        
        // Spezielle Mappings für Felder mit Sonderzeichen oder abweichenden Namen
        'hu/au_neu' => 'hu_au_neu',
        'sep-dusche' => 'sep_dusche',
        's-s-bereifung' => 's_s_bereifung',
        'laderaum-europalette' => 'laderaum_europalette',
        'laderaum-volumen' => 'laderaum_volumen',
        'laderaum-laenge' => 'laderaum_laenge',
        'laderaum-breite' => 'laderaum_breite',
        'laderaum-hoehe' => 'laderaum_hoehe',
        'video-url' => 'video_url',
        'head-up display' => 'head_up_display',
        'soll-zinssatz' => 'soll_zinssatz',
        'art des soll-zinssatzes' => 'art_des_soll_zinssatzes'
    );
}
    
    /**
     * داده‌های CSV را به فرمت مناسب برای فیلدهای ACF تبدیل می‌کند
     * 
     * @param array $csv_data داده‌های خام از CSV
     * @return array داده‌های تبدیل شده برای فیلدهای ACF
     */
    public function convert_csv_data_for_acf($csv_data) {
        $mapping = $this->get_csv_field_mapping();
        $converted_data = array();
        
        foreach ($csv_data as $key => $value) {
            // بررسی کنید که آیا نگاشتی برای این فیلد وجود دارد
            if (isset($mapping[$key])) {
                $acf_field = $mapping[$key];
                
                // تبدیل‌های ویژه برای انواع فیلد خاص
                switch ($acf_field) {
                    // فیلدهای بولی (0/1 به true/false)
                    case 'mwst':
                    case 'oldtimer':
                    case 'beschaedigtes_fahrzeug':
                    case 'metallic':
                    case 'jahreswagen':
                    case 'neufahrzeug':
                    case 'unsere_empfehlung':
                        $converted_data[$acf_field] = ($value == '1');
                        break;
                    
                    // فیلدهای عددی
                    case 'leistung':
                    case 'ccm':
                    case 'kilometer':
                    case 'preis':
                        $converted_data[$acf_field] = is_numeric($value) ? (float)$value : $value;
                        break;
                    
                    // فیلدهای متنی بدون تغییر می‌مانند
                    default:
                        $converted_data[$acf_field] = $value;
                        break;
                }
            }
        }
        
        return $converted_data;
    }
    
    /**
     * فیلدهای ACF را برای یک خودرو به‌روزرسانی می‌کند
     * 
     * @param int $post_id Post-ID خودرو
     * @param array $car_data داده‌های خودرو
     * @return bool|WP_Error True در صورت موفقیت، WP_Error در صورت خطا
     */
    public function update_car_acf_fields($post_id, $car_data) {
        // بررسی کنید که آیا ACF فعال است
        if (!function_exists('update_field')) {
            $this->logger->log('ACF فعال نیست، به‌روزرسانی فیلد را رد می‌کنیم', 'warning');
            return new WP_Error('acf_inactive', __('Advanced Custom Fields فعال نیست.', 'auto-car-importer'));
        }
        
        try {
            // داده‌ها را برای ACF تبدیل کنید
            $acf_data = $this->convert_csv_data_for_acf($car_data);
            
            // تمام فیلدهای ACF را به‌روزرسانی کنید
            foreach ($acf_data as $field_name => $value) {
                update_field($field_name, $value, $post_id);
            }
            
            // همچنین به عنوان Post-Meta ذخیره کنید برای جستجوی سریع‌تر
            if (!empty($car_data['interne_nummer'])) {
                update_post_meta($post_id, 'interne_nummer', $car_data['interne_nummer']);
            }
            
            if (!empty($car_data['bild_id'])) {
                update_post_meta($post_id, 'bild_id', $car_data['bild_id']);
            }
            
            return true;
        } catch (Exception $e) {
            $this->logger->log('خطا در به‌روزرسانی فیلدهای ACF: ' . $e->getMessage(), 'error');
            return new WP_Error('acf_update_error', $e->getMessage());
        }
    }
}