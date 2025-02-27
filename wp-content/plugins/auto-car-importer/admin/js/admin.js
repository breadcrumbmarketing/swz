/**
 * Auto Car Importer Admin JavaScript
 */
jQuery(document).ready(function($) {
    
    /**
     * AJAX zum Testen der FTP-Verbindung registrieren
     */
// Debug-Version des FTP-Verbindungstest-Handlers
$(document).on('click', '#aci-test-ftp', function(e) {
    e.preventDefault();
    
    // Konsolen-Log zum Debugging
    console.log('FTP-Test Button wurde geklickt');
    
    // Werte aus den Feldern auslesen
    var host = $('#aci_ftp_host').val();
    var username = $('#aci_ftp_username').val();
    var password = $('#aci_ftp_password').val();
    var path = $('#aci_ftp_path').val();
    
    // Debug-Log für die Werte
    console.log('FTP-Daten:', {
        host: host,
        username: username,
        path: path
    });
    
    // Prüfen, ob alle erforderlichen Felder ausgefüllt sind
    if (!host || !username || !password) {
        $('#aci-test-result').html('<div class="notice notice-error inline"><p>Bitte füllen Sie alle erforderlichen Felder aus.</p></div>');
        return;
    }
    
    // AJAX-Anfrage vorbereiten
    var data = {
        action: 'aci_test_ftp',
        nonce: aciData.nonce,
        host: host,
        username: username,
        password: password,
        path: path
    };
    
    // Ladezustand anzeigen
    $('#aci-test-result').html('<div class="notice notice-info inline"><p>Verbindung wird getestet...</p></div>');
    
    // Debug-Log für AJAX-Anfrage
    console.log('AJAX-Anfrage wird gesendet:', data);
    
    // AJAX-Anfrage senden mit detaillierter Fehlerbehandlung
    $.ajax({
        url: aciData.ajaxUrl,
        type: 'POST',
        data: data,
        success: function(response) {
            console.log('AJAX-Antwort erhalten:', response);
            
            if (response.success) {
                $('#aci-test-result').html('<div class="notice notice-success inline"><p>' + response.data + '</p></div>');
            } else {
                $('#aci-test-result').html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX-Fehler:', {
                xhr: xhr,
                status: status,
                error: error
            });
            $('#aci-test-result').html('<div class="notice notice-error inline"><p>Fehler beim Testen der Verbindung: ' + error + '</p></div>');
        }
    });
});
    /**
     * Tab-Funktionalität
     */
    $('.aci-tab-header').on('click', function() {
        var tab = $(this).data('tab');
        
        // Aktiven Tab setzen
        $('.aci-tab-header').removeClass('active');
        $(this).addClass('active');
        
        // Tab-Inhalt anzeigen
        $('.aci-tab-content').removeClass('active');
        $('.aci-tab-content[data-tab="' + tab + '"]').addClass('active');
    });
    
    /**
     * Upload-Formular absenden
     */
    $('#aci-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        // FormData-Objekt erstellen
        var formData = new FormData(this);
        formData.append('action', 'aci_import_from_upload');
        formData.append('nonce', aciData.nonce);
        
        // Import-Fortschritt anzeigen
        $('#aci-import-results').hide();
        $('#aci-import-progress').show();
        $('.aci-progress-bar').css('width', '0%');
        
        // AJAX-Anfrage senden
        $.ajax({
            url: aciData.ajaxUrl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total * 100;
                        $('.aci-progress-bar').css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                // Import abgeschlossen
                $('#aci-import-progress').hide();
                
                if (response.success) {
                    $('#aci-import-results').html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                    
                    // Statistiken anzeigen
                    updateStats(response.data.stats);
                } else {
                    $('#aci-import-results').html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>');
                }
                
                $('#aci-import-results').show();
            },
            error: function(xhr, status, error) {
                // Fehler beim Import
                $('#aci-import-progress').hide();
                $('#aci-import-results').html('<div class="notice notice-error inline"><p>Fehler beim Import: ' + error + '</p></div>');
                $('#aci-import-results').show();
            }
        });
    });
    
    /**
     * FTP-Formular absenden
     */
    $('#aci-ftp-form').on('submit', function(e) {
        e.preventDefault();
        
        // Daten sammeln
        var formData = new FormData();
        formData.append('action', 'aci_import_from_ftp');
        formData.append('nonce', aciData.nonce);
        
        // Formularfelder hinzufügen
        $(this).find('select, input').each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            
            // Checkboxen speziell behandeln
            if ($(this).attr('type') === 'checkbox') {
                value = $(this).is(':checked') ? $(this).val() : 'no';
            }
            
            formData.append(name, value);
        });
        
        // Import-Fortschritt anzeigen
        $('#aci-import-results').hide();
        $('#aci-import-progress').show();
        $('.aci-progress-bar').css('width', '0%');
        $('.aci-progress-text').text('FTP-Verbindung wird hergestellt...');
        
        // AJAX-Anfrage senden
        $.ajax({
            url: aciData.ajaxUrl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // Import abgeschlossen
                $('#aci-import-progress').hide();
                
                if (response.success) {
                    $('#aci-import-results').html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                    
                    // Statistiken anzeigen
                    updateStats(response.data.stats);
                } else {
                    $('#aci-import-results').html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>');
                }
                
                $('#aci-import-results').show();
            },
            error: function(xhr, status, error) {
                // Fehler beim Import
                $('#aci-import-progress').hide();
                $('#aci-import-results').html('<div class="notice notice-error inline"><p>Fehler beim Import: ' + error + '</p></div>');
                $('#aci-import-results').show();
            }
        });
    });
    
    /**
     * Statistiken aktualisieren
     */
    function updateStats(stats) {
        var html = '<table class="aci-stats-table">';
        html += '<tr><th>Gesamt:</th><td>' + stats.total + '</td></tr>';
        html += '<tr><th>Neu erstellt:</th><td>' + stats.created + '</td></tr>';
        html += '<tr><th>Aktualisiert:</th><td>' + stats.updated + '</td></tr>';
        html += '<tr><th>Übersprungen:</th><td>' + stats.skipped + '</td></tr>';
        html += '<tr><th>Fehler:</th><td>' + stats.errors + '</td></tr>';
        
        if (stats.images_total !== undefined) {
            html += '<tr><th>Bilder gesamt:</th><td>' + stats.images_total + '</td></tr>';
            html += '<tr><th>Neue Bilder:</th><td>' + stats.images_new + '</td></tr>';
        }
        
        if (stats.processed_files !== undefined && stats.processed_files.length > 0) {
            html += '<tr><th>Verarbeitete Dateien:</th><td>' + stats.processed_files.join(', ') + '</td></tr>';
        }
        
        html += '</table>';
        
        $('#aci-import-results').append(html);
    }
    
    /**
     * Protokolle löschen
     */
    $('#aci-clear-logs').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Sind Sie sicher, dass Sie alle Protokolleinträge löschen möchten?')) {
            $.ajax({
                url: aciData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aci_clear_logs',
                    nonce: aciData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Fehler beim Löschen der Protokolle.');
                    }
                },
                error: function() {
                    alert('Fehler beim Löschen der Protokolle.');
                }
            });
        }
    });

    /**
     * Periodisch den Fortschritt des Imports aktualisieren
     */
    var progressTimer;

    function startProgressMonitoring() {
        progressTimer = setInterval(function() {
            if ($('#aci-import-progress').is(':visible')) {
                $.ajax({
                    url: aciData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'aci_get_import_progress',
                        nonce: aciData.nonce
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            var progress = response.data;
                            if (progress.status === 'completed') {
                                clearInterval(progressTimer);
                                $('#aci-import-progress').hide();
                                $('#aci-import-results').show();
                            } else {
                                $('.aci-progress-bar').css('width', progress.percent + '%');
                                $('.aci-progress-text').text(progress.message);
                            }
                        }
                    }
                });
            } else {
                clearInterval(progressTimer);
            }
        }, 2000); // Alle 2 Sekunden aktualisieren
    }

    // Beim Starten eines Imports den Fortschritt überwachen
    $('#aci-upload-form, #aci-ftp-form').on('submit', function() {
        startProgressMonitoring();
    });

    /**
     * FTP-Dateiliste aktualisieren
     */
    $('#aci-refresh-ftp-files').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var originalText = $button.text();
        $button.text('Wird geladen...').prop('disabled', true);
        
        $.ajax({
            url: aciData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'aci_refresh_ftp_files',
                nonce: aciData.nonce
            },
            success: function(response) {
                $button.text(originalText).prop('disabled', false);
                
                if (response.success) {
                    var files = response.data;
                    var $fileList = $('#aci-ftp-file-list');
                    $fileList.empty();
                    
                    if (files.length === 0) {
                        $fileList.html('<p>Keine ZIP-Dateien gefunden.</p>');
                    } else {
                        var html = '<ul class="aci-file-list">';
                        $.each(files, function(index, file) {
                            html += '<li>';
                            html += '<input type="checkbox" name="ftp_files[]" value="' + file.name + '" id="file-' + index + '">';
                            html += '<label for="file-' + index + '">' + file.name + ' (' + formatFileSize(file.size) + ')</label>';
                            html += '</li>';
                        });
                        html += '</ul>';
                        $fileList.html(html);
                    }
                } else {
                    $('#aci-ftp-file-list').html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>');
                }
            },
            error: function() {
                $button.text(originalText).prop('disabled', false);
                $('#aci-ftp-file-list').html('<div class="notice notice-error inline"><p>Fehler beim Abrufen der Dateiliste.</p></div>');
            }
        });
    });
    
    /**
     * Dateigröße formatieren
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    /**
     * Importprozess starten beim Klick auf die Schaltfläche
     */
    $('.aci-start-import').on('click', function() {
        var $parent = $(this).closest('.aci-card');
        var $form = $parent.find('form');
        
        $form.submit();
    });
    
    /**
     * Aktualisiere die UI bei Änderungen an den Einstellungen
     */
    $('.aci-setting-toggle').on('change', function() {
        var target = $(this).data('target');
        var $target = $(target);
        
        if ($(this).is(':checked')) {
            $target.show();
        } else {
            $target.hide();
        }
    }).trigger('change'); // Initial auslösen
});



$.ajax({
    url: aciData.ajaxUrl,
    type: 'POST',
    data: data,
    success: function(response) {
        console.log('Rohe Antwort:', response);
        // Rest deines Codes
    },
    error: function(xhr, status, error) {
        console.error('AJAX-Fehler:');
        console.log('Status:', status);
        console.log('Fehler:', error);
        console.log('Antwort-Text:', xhr.responseText);
        $('#aci-test-result').html('<div class="notice notice-error inline"><p>Fehler beim Testen der Verbindung: ' + error + '</p></div>');
    }
});