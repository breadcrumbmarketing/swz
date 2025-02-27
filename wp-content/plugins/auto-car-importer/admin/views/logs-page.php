<div class="wrap">
    <h1><?php _e('Auto Import Protokolle', 'auto-car-importer'); ?></h1>
    
    <div class="aci-logs-actions">
        <button id="aci-clear-logs" class="button button-secondary">
            <?php _e('Protokolle löschen', 'auto-car-importer'); ?>
        </button>
        
        <a href="<?php echo admin_url('admin-ajax.php?action=aci_export_logs&nonce=' . wp_create_nonce('aci_ajax_nonce')); ?>" class="button button-secondary">
            <?php _e('Protokolle exportieren (CSV)', 'auto-car-importer'); ?>
        </a>
    </div>
    
    <div class="aci-card">
        <div class="aci-card-header">
            <h2><?php _e('Protokolleinträge', 'auto-car-importer'); ?></h2>
        </div>
        <div class="aci-card-content">
            <?php if (empty($logs)): ?>
            <p><?php _e('Keine Protokolleinträge vorhanden.', 'auto-car-importer'); ?></p>
            <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Zeit', 'auto-car-importer'); ?></th>
                        <th><?php _e('Level', 'auto-car-importer'); ?></th>
                        <th><?php _e('Nachricht', 'auto-car-importer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['time'])); ?></td>
                        <td>
                            <span class="aci-log-level aci-log-level-<?php echo esc_attr($log['level']); ?>">
                                <?php echo esc_html(ucfirst($log['level'])); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($log['message']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Protokolle löschen
    $('#aci-clear-logs').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('<?php _e('Sind Sie sicher, dass Sie alle Protokolleinträge löschen möchten?', 'auto-car-importer'); ?>')) {
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
                        alert('<?php _e('Fehler beim Löschen der Protokolle.', 'auto-car-importer'); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e('Fehler beim Löschen der Protokolle.', 'auto-car-importer'); ?>');
                }
            });
        }
    });
});
</script>