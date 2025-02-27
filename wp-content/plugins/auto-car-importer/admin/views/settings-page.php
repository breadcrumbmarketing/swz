<div class="wrap">
    <h1><?php _e('Auto Import Einstellungen', 'auto-car-importer'); ?></h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('aci_settings'); ?>
        <?php do_settings_sections('aci_settings'); ?>
        
        <div class="aci-settings-container">
            <div class="aci-card">
                <div class="aci-card-header">
                    <h2><?php _e('FTP-Einstellungen', 'auto-car-importer'); ?></h2>
                </div>
                <div class="aci-card-content">
                    <div class="aci-form-row">
                        <label for="aci_ftp_host"><?php _e('FTP-Host:', 'auto-car-importer'); ?></label>
                        <input type="text" id="aci_ftp_host" name="aci_ftp_host" value="<?php echo esc_attr(get_option('aci_ftp_host', '')); ?>" class="regular-text">
                        <p class="description"><?php _e('z.B. ftp.example.com oder 123.456.789.10', 'auto-car-importer'); ?></p>
                    </div>
                    
                    <div class="aci-form-row">
                        <label for="aci_ftp_username"><?php _e('FTP-Benutzername:', 'auto-car-importer'); ?></label>
                        <input type="text" id="aci_ftp_username" name="aci_ftp_username" value="<?php echo esc_attr(get_option('aci_ftp_username', '')); ?>" class="regular-text">
                    </div>
                    
                    <div class="aci-form-row">
                        <label for="aci_ftp_password"><?php _e('FTP-Passwort:', 'auto-car-importer'); ?></label>
                        <input type="password" id="aci_ftp_password" name="aci_ftp_password" value="<?php echo esc_attr(get_option('aci_ftp_password', '')); ?>" class="regular-text">
                    </div>
                    
                    <div class="aci-form-row">
                        <label for="aci_ftp_path"><?php _e('FTP-Pfad:', 'auto-car-importer'); ?></label>
                        <input type="text" id="aci_ftp_path" name="aci_ftp_path" value="<?php echo esc_attr(get_option('aci_ftp_path', '/')); ?>" class="regular-text">
                        <p class="description"><?php _e('Das Verzeichnis, in dem die ZIP-Dateien liegen. Standard: /', 'auto-car-importer'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="aci-card">
                <div class="aci-card-header">
                    <h2><?php _e('CSV-Einstellungen', 'auto-car-importer'); ?></h2>
                </div>
                <div class="aci-card-content">
                    <div class="aci-form-row">
                        <label for="aci_csv_delimiter"><?php _e('CSV-Trennzeichen:', 'auto-car-importer'); ?></label>
                        <select id="aci_csv_delimiter" name="aci_csv_delimiter">
                            <option value="," <?php selected(get_option('aci_csv_delimiter', ','), ','); ?>><?php _e('Komma (,)', 'auto-car-importer'); ?></option>
                            <option value=";" <?php selected(get_option('aci_csv_delimiter', ','), ';'); ?>><?php _e('Semikolon (;)', 'auto-car-importer'); ?></option>
                            <option value="\t" <?php selected(get_option('aci_csv_delimiter', ','), '\t'); ?>><?php _e('Tabulator (Tab)', 'auto-car-importer'); ?></option>
                        </select>
                    </div>
                    
                    <div class="aci-form-row">
                        <label for="aci_csv_enclosure"><?php _e('Textbegrenzungszeichen:', 'auto-car-importer'); ?></label>
                        <select id="aci_csv_enclosure" name="aci_csv_enclosure">
                            <option value='"' <?php selected(get_option('aci_csv_enclosure', '"'), '"'); ?>><?php _e('Anführungszeichen (")', 'auto-car-importer'); ?></option>
                            <option value="'" <?php selected(get_option('aci_csv_enclosure', '"'), "'"); ?>><?php _e('Apostroph (\')', 'auto-car-importer'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="aci-form-row">
    <label for="aci_csv_filename"><?php _e('CSV-Dateiname:', 'auto-car-importer'); ?></label>
    <input type="text" id="aci_csv_filename" name="aci_csv_filename" value="<?php echo esc_attr(get_option('aci_csv_filename', '')); ?>" class="regular-text">
    <p class="description"><?php _e('Optional: Geben Sie den Namen der CSV-Datei im ZIP-Archiv an. Lassen Sie das Feld leer, um automatisch nach CSV-Dateien zu suchen.', 'auto-car-importer'); ?></p>
</div>
            <div class="aci-card">
                <div class="aci-card-header">
                    <h2><?php _e('Import-Einstellungen', 'auto-car-importer'); ?></h2>
                </div>
                <div class="aci-card-content">
                    <div class="aci-form-row">
                        <label>
                            <input type="checkbox" id="aci_update_existing" name="aci_update_existing" value="yes" <?php checked(get_option('aci_update_existing', 'yes'), 'yes'); ?>>
                            <?php _e('Vorhandene Fahrzeuge aktualisieren', 'auto-car-importer'); ?>
                        </label>
                        <p class="description"><?php _e('Wenn aktiviert, werden bestehende Fahrzeuge aktualisiert. Wenn deaktiviert, werden bestehende Fahrzeuge übersprungen.', 'auto-car-importer'); ?></p>
                    </div>
                    
                    <div class="aci-form-row">
                        <label>
                            <input type="checkbox" id="aci_skip_without_images" name="aci_skip_without_images" value="yes" <?php checked(get_option('aci_skip_without_images', 'no'), 'yes'); ?>>
                            <?php _e('Fahrzeuge ohne Bilder überspringen', 'auto-car-importer'); ?>
                        </label>
                        <p class="description"><?php _e('Wenn aktiviert, werden Fahrzeuge ohne zugeordnete Bilder übersprungen.', 'auto-car-importer'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php submit_button(__('Einstellungen speichern', 'auto-car-importer')); ?>
    </form>
    
    <div class="aci-card">
        <div class="aci-card-header">
            <h2><?php _e('FTP-Verbindung testen', 'auto-car-importer'); ?></h2>
        </div>
        <div class="aci-card-content">
            <p><?php _e('Klicken Sie auf die Schaltfläche, um die FTP-Verbindung zu testen.', 'auto-car-importer'); ?></p>
            <button id="aci-test-ftp" class="button">
                <?php _e('FTP-Verbindung testen', 'auto-car-importer'); ?>
            </button>
            <div id="aci-test-result" style="margin-top: 10px;"></div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // FTP-Verbindung testen
    $('#aci-test-ftp').on('click', function(e) {
        e.preventDefault();
        
        var data = {
            action: 'aci_test_ftp',
            nonce: aciData.nonce,
            host: $('#aci_ftp_host').val(),
            username: $('#aci_ftp_username').val(),
            password: $('#aci_ftp_password').val(),
            path: $('#aci_ftp_path').val()
        };
        
        $('#aci-test-result').html('<div class="notice notice-info inline"><p><?php _e('Verbindung wird getestet...', 'auto-car-importer'); ?></p></div>');
        
        $.ajax({
            url: aciData.ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $('#aci-test-result').html('<div class="notice notice-success inline"><p>' + response.data + '</p></div>');
                } else {
                    $('#aci-test-result').html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>');
                }
            },
            error: function() {
                $('#aci-test-result').html('<div class="notice notice-error inline"><p><?php _e('Fehler beim Testen der Verbindung.', 'auto-car-importer'); ?></p></div>');
            }
        });
    });
});
</script>