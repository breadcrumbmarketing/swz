<div class="wrap">
    <h1><?php _e('Auto Import Dashboard', 'auto-car-importer'); ?></h1>
    
    <div class="aci-dashboard">
        <!-- Import-Karte -->
        <div class="aci-card">
            <div class="aci-card-header">
                <h2><?php _e('Fahrzeugdaten importieren', 'auto-car-importer'); ?></h2>
            </div>
            <div class="aci-card-content">
                <p><?php _e('Wählen Sie eine ZIP-Datei mit CSV-Daten und Bildern zum Importieren aus oder führen Sie einen Import über FTP durch.', 'auto-car-importer'); ?></p>

                <!-- Für das Upload-Tab einfügen, nach den Einstellungen für CSV-Trennzeichen und Textbegrenzungszeichen -->
<div class="aci-form-row">
    <label for="upload_csv_filename"><?php _e('CSV-Dateiname (optional):', 'auto-car-importer'); ?></label>
    <input type="text" name="csv_filename" id="upload_csv_filename" placeholder="<?php _e('Automatisch erkennen', 'auto-car-importer'); ?>">
    <p class="description"><?php _e('Wenn die ZIP-Datei mehrere CSV-Dateien enthält, können Sie den genauen Dateinamen angeben.', 'auto-car-importer'); ?></p>
</div>


                
                <div class="aci-tabs">
                    <div class="aci-tab-headers">
                        <button class="aci-tab-header active" data-tab="upload"><?php _e('Upload', 'auto-car-importer'); ?></button>
                        <button class="aci-tab-header" data-tab="ftp"><?php _e('FTP', 'auto-car-importer'); ?></button>
                    </div>
                    
                    <div class="aci-tab-content active" data-tab="upload">
                        <form id="aci-upload-form" method="post" enctype="multipart/form-data">
                            <div class="aci-form-row">
                                <label for="import_file"><?php _e('ZIP-Datei auswählen:', 'auto-car-importer'); ?></label>
                                <input type="file" name="import_file" id="import_file" accept=".zip" required>
                            </div>
                            
                            <div class="aci-form-row">
                                <label for="upload_delimiter"><?php _e('CSV-Trennzeichen:', 'auto-car-importer'); ?></label>
                                <select name="delimiter" id="upload_delimiter">
    <option value=";"><?php _e('Semikolon (;)', 'auto-car-importer'); ?></option>
    <option value=","><?php _e('Komma (,)', 'auto-car-importer'); ?></option>
    <option value="\t"><?php _e('Tabulator (Tab)', 'auto-car-importer'); ?></option>
</select>
                            </div>
                            
                            <div class="aci-form-row">
                                <label for="upload_enclosure"><?php _e('Textbegrenzungszeichen:', 'auto-car-importer'); ?></label>
                                <select name="enclosure" id="upload_enclosure">
                                    <option value='"'><?php _e('Anführungszeichen (")', 'auto-car-importer'); ?></option>
                                    <option value="'"><?php _e('Apostroph (\')', 'auto-car-importer'); ?></option>
                                </select>
                            </div>
                            
                            <div class="aci-form-row">
                                <label>
                                    <input type="checkbox" name="update_existing" value="yes" checked>
                                    <?php _e('Vorhandene Fahrzeuge aktualisieren', 'auto-car-importer'); ?>
                                </label>
                            </div>
                            
                            <div class="aci-form-row">
                                <label>
                                    <input type="checkbox" name="skip_without_images" value="yes">
                                    <?php _e('Fahrzeuge ohne Bilder überspringen', 'auto-car-importer'); ?>
                                </label>
                            </div>
                            
                            <div class="aci-form-row aci-buttons">
                                <button type="submit" class="button button-primary">
                                    <?php _e('Importieren', 'auto-car-importer'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="aci-tab-content" data-tab="ftp">
                        <?php
                        $ftp_host = get_option('aci_ftp_host', '');
                        $ftp_username = get_option('aci_ftp_username', '');
                        $ftp_password = get_option('aci_ftp_password', '');
                        $ftp_path = get_option('aci_ftp_path', '/');
                        
                        $ftp_configured = !empty($ftp_host) && !empty($ftp_username) && !empty($ftp_password);
                        ?>
                        
                        <?php if (!$ftp_configured): ?>
                        <div class="notice notice-warning inline">
                            <p><?php _e('FTP-Einstellungen sind nicht vollständig konfiguriert. Bitte gehen Sie zur Einstellungsseite, um die FTP-Verbindung zu konfigurieren.', 'auto-car-importer'); ?></p>
                            <p><a href="<?php echo admin_url('admin.php?page=auto-car-importer-settings'); ?>" class="button"><?php _e('Zur Einstellungsseite', 'auto-car-importer'); ?></a></p>
                        </div>
                        
                        <?php else: ?>
                        <form id="aci-ftp-form" method="post">
                            <div class="aci-form-row">
                                <label><?php _e('FTP-Host:', 'auto-car-importer'); ?></label>
                                <strong><?php echo esc_html($ftp_host); ?></strong>
                            </div>
                            
                            <div class="aci-form-row">
                                <label><?php _e('FTP-Benutzer:', 'auto-car-importer'); ?></label>
                                <strong><?php echo esc_html($ftp_username); ?></strong>
                            </div>
                            
                            <div class="aci-form-row">
                                <label><?php _e('FTP-Pfad:', 'auto-car-importer'); ?></label>
                                <strong><?php echo esc_html($ftp_path); ?></strong>
                            </div>
                            
                            <div class="aci-form-row">
                                <label for="ftp_delimiter"><?php _e('CSV-Trennzeichen:', 'auto-car-importer'); ?></label>
                                <select name="delimiter" id="ftp_delimiter">
                                    <option value=","><?php _e('Komma (,)', 'auto-car-importer'); ?></option>
                                    <option value=";"><?php _e('Semikolon (;)', 'auto-car-importer'); ?></option>
                                    <option value="\t"><?php _e('Tabulator (Tab)', 'auto-car-importer'); ?></option>
                                </select>
                            </div>
                            
                            <div class="aci-form-row">
                                <label for="ftp_enclosure"><?php _e('Textbegrenzungszeichen:', 'auto-car-importer'); ?></label>
                                <select name="enclosure" id="ftp_enclosure">
                                    <option value='"'><?php _e('Anführungszeichen (")', 'auto-car-importer'); ?></option>
                                    <option value="'"><?php _e('Apostroph (\')', 'auto-car-importer'); ?></option>
                                </select>
                            </div>
                            
                            <div class="aci-form-row">
                                <label>
                                    <input type="checkbox" name="update_existing" value="yes" checked>
                                    <?php _e('Vorhandene Fahrzeuge aktualisieren', 'auto-car-importer'); ?>
                                </label>
                            </div>
                            
                            <div class="aci-form-row">
                                <label>
                                    <input type="checkbox" name="skip_without_images" value="yes">
                                    <?php _e('Fahrzeuge ohne Bilder überspringen', 'auto-car-importer'); ?>
                                </label>
                            </div>

                            <!-- Für das FTP-Tab einfügen, nach den Einstellungen für CSV-Trennzeichen und Textbegrenzungszeichen -->
<div class="aci-form-row">
    <label for="ftp_csv_filename"><?php _e('CSV-Dateiname (optional):', 'auto-car-importer'); ?></label>
    <input type="text" name="csv_filename" id="ftp_csv_filename" placeholder="<?php _e('Automatisch erkennen', 'auto-car-importer'); ?>" value="<?php echo esc_attr(get_option('aci_csv_filename', '')); ?>">
    <p class="description"><?php _e('Wenn die ZIP-Datei mehrere CSV-Dateien enthält, können Sie den genauen Dateinamen angeben.', 'auto-car-importer'); ?></p>
</div>
                            
                            <div class="aci-form-row aci-buttons">
                                <button type="submit" class="button button-primary">
                                    <?php _e('Von FTP importieren', 'auto-car-importer'); ?>
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Status-Karte -->
        <div class="aci-card">
            <div class="aci-card-header">
                <h2><?php _e('Import-Status', 'auto-car-importer'); ?></h2>
            </div>
            <div class="aci-card-content">
                <div id="aci-import-progress" style="display: none;">
                    <div class="aci-progress">
                        <div class="aci-progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <p class="aci-progress-text"><?php _e('Import läuft...', 'auto-car-importer'); ?></p>
                </div>
                
                <div id="aci-import-results">
                    <?php if (!empty($last_import)): ?>
                    <p><?php _e('Letzter Import:', 'auto-car-importer'); ?> <strong><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_import)); ?></strong></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($import_status)): ?>
                    <table class="aci-stats-table">
                        <tr>
                            <th><?php _e('Gesamt:', 'auto-car-importer'); ?></th>
                            <td><?php echo intval($import_status['total']); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Neu erstellt:', 'auto-car-importer'); ?></th>
                            <td><?php echo intval($import_status['created']); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Aktualisiert:', 'auto-car-importer'); ?></th>
                            <td><?php echo intval($import_status['updated']); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Übersprungen:', 'auto-car-importer'); ?></th>
                            <td><?php echo intval($import_status['skipped']); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Fehler:', 'auto-car-importer'); ?></th>
                            <td><?php echo intval($import_status['errors']); ?></td>
                        </tr>
                        <?php if (isset($import_status['images_total'])): ?>
                        <tr>
                            <th><?php _e('Bilder gesamt:', 'auto-car-importer'); ?></th>
                            <td><?php echo intval($import_status['images_total']); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Neue Bilder:', 'auto-car-importer'); ?></th>
                            <td><?php echo intval($import_status['images_new']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    <?php else: ?>
                    <p><?php _e('Noch keine Importe durchgeführt.', 'auto-car-importer'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab-Funktionalität
    $('.aci-tab-header').on('click', function() {
        var tab = $(this).data('tab');
        
        // Aktiven Tab setzen
        $('.aci-tab-header').removeClass('active');
        $(this).addClass('active');
        
        // Tab-Inhalt anzeigen
        $('.aci-tab-content').removeClass('active');
        $('.aci-tab-content[data-tab="' + tab + '"]').addClass('active');
    });
    
    // Upload-Formular
    $('#aci-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'aci_import_from_upload');
        formData.append('nonce', aciData.nonce);
        
        // Import-Fortschritt anzeigen
        $('#aci-import-results').hide();
        $('#aci-import-progress').show();
        
        $.ajax({
            url: aciData.ajaxUrl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#aci-import-progress').hide();
                $('#aci-import-results').html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                $('#aci-import-results').show();
                
                // Statistiken anzeigen
                updateStats(response.data.stats);
            },
            error: function(xhr, status, error) {
                $('#aci-import-progress').hide();
                $('#aci-import-results').html('<div class="notice notice-error inline"><p>' + error + '</p></div>');
                $('#aci-import-results').show();
            }
        });
    });
    
    // FTP-Formular
    $('#aci-ftp-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData();
        formData.append('action', 'aci_import_from_ftp');
        formData.append('nonce', aciData.nonce);
        
        // Formularfelder hinzufügen
        $(this).find('select, input').each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            
            if ($(this).attr('type') === 'checkbox') {
                value = $(this).is(':checked') ? $(this).val() : 'no';
            }
            
            formData.append(name, value);
        });
        
        // Import-Fortschritt anzeigen
        $('#aci-import-results').hide();
        $('#aci-import-progress').show();
        
        $.ajax({
            url: aciData.ajaxUrl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#aci-import-progress').hide();
                $('#aci-import-results').html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                $('#aci-import-results').show();
                
                // Statistiken anzeigen
                updateStats(response.data.stats);
            },
            error: function(xhr, status, error) {
                $('#aci-import-progress').hide();
                $('#aci-import-results').html('<div class="notice notice-error inline"><p>' + error + '</p></div>');
                $('#aci-import-results').show();
            }
        });
    });
    
    // Funktion zum Aktualisieren der Statistiken
    function updateStats(stats) {
        var html = '<table class="aci-stats-table">';
        html += '<tr><th><?php _e('Gesamt:', 'auto-car-importer'); ?></th><td>' + stats.total + '</td></tr>';
        html += '<tr><th><?php _e('Neu erstellt:', 'auto-car-importer'); ?></th><td>' + stats.created + '</td></tr>';
        html += '<tr><th><?php _e('Aktualisiert:', 'auto-car-importer'); ?></th><td>' + stats.updated + '</td></tr>';
        html += '<tr><th><?php _e('Übersprungen:', 'auto-car-importer'); ?></th><td>' + stats.skipped + '</td></tr>';
        html += '<tr><th><?php _e('Fehler:', 'auto-car-importer'); ?></th><td>' + stats.errors + '</td></tr>';
        
        if (stats.images_total !== undefined) {
            html += '<tr><th><?php _e('Bilder gesamt:', 'auto-car-importer'); ?></th><td>' + stats.images_total + '</td></tr>';
            html += '<tr><th><?php _e('Neue Bilder:', 'auto-car-importer'); ?></th><td>' + stats.images_new + '</td></tr>';
        }
        
        html += '</table>';
        
        $('#aci-import-results').append(html);
    }
});
</script>