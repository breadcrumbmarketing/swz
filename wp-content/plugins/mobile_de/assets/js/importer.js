jQuery(document).ready(function($) {
    $('#sportwagen-import-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'sportwagen_import');
        formData.append('nonce', SportwagenImporter.nonce);
        formData.append('update_existing', $('#update_existing').prop('checked'));
        formData.append('import_images', $('#import_images').prop('checked'));
        
        // UI aktualisieren
        $('#submit-import').prop('disabled', true);
        $('#sportwagen-import-progress').show();
        $('.sportwagen-progress-bar-fill').css('width', '0%');
        $('#sportwagen-import-results').hide();
        
        // AJAX-Anfrage senden
        $.ajax({
            url: SportwagenImporter.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total * 100;
                        $('.sportwagen-progress-bar-fill').css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                $('#submit-import').prop('disabled', false);
                
                if (response.success) {
                    var data = response.data;
                    var resultHtml = '<p>Import abgeschlossen!</p>';
                    resultHtml += '<ul>';
                    resultHtml += '<li>Verarbeitete Einträge: ' + data.processed + '</li>';
                    resultHtml += '<li>Neue Einträge erstellt: ' + data.created + '</li>';
                    resultHtml += '<li>Bestehende Einträge aktualisiert: ' + data.updated + '</li>';
                    resultHtml += '<li>Bilder importiert: ' + (data.images_imported || 0) + '</li>';
                    resultHtml += '</ul>';
                    
                    if (data.error_count > 0) {
                        resultHtml += '<div class="sportwagen-error-log"><h4>Fehler (' + data.error_count + '):</h4><ul>';
                        $.each(data.errors, function(i, error) {
                            resultHtml += '<li>' + error + '</li>';
                        });
                        resultHtml += '</ul></div>';
                    }
                    
                    $('#sportwagen-results-content').html(resultHtml);
                    $('#sportwagen-import-results').show();
                    
                    // Fortschrittsbalken auf 100%
                    $('.sportwagen-progress-bar-fill').css('width', '100%');
                    $('.sportwagen-progress-status').text('Import abgeschlossen');
                } else {
                    $('#sportwagen-results-content').html('<div class="sportwagen-error-log"><p>Fehler beim Import: ' + response.data + '</p></div>');
                    $('#sportwagen-import-results').show();
                    $('.sportwagen-progress-status').text('Import fehlgeschlagen');
                }
            },
            error: function(xhr, status, error) {
                $('#submit-import').prop('disabled', false);
                $('#sportwagen-results-content').html('<div class="sportwagen-error-log"><p>Fehler beim Import: ' + error + '</p></div>');
                $('#sportwagen-import-results').show();
                $('.sportwagen-progress-status').text('Import fehlgeschlagen');
            }
        });
    });
});