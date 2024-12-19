<div class="car-card-container">
    <!-- Image Section -->
    <div class="car-card-image">
        <img src="<?php echo esc_url($car->image_thumbnail); ?>" 
             alt="<?php echo esc_attr($car->car_name); ?>">
    </div>

    <!-- Information Section -->
    <div class="car-card-content">
        <!-- First Row: Typ des Autos -->
        <p class="car-card-type"><?php echo esc_html($car->car_type); ?></p>

        <!-- Second Row: Auto Name -->
        <h2 class="car-card-name"><?php echo esc_html($car->car_name); ?></h2>

        <!-- Separator Line -->
        <div class="car-card-separator"></div>

        <!-- Third Row: Icons with Information -->
        <div class="car-card-info">
            <div class="car-card-info-item">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/icons/filter/power.svg'); ?>" alt="Baujahr">
                <span><?php echo esc_html($car->car_year); ?></span>
            </div>
            <div class="car-card-info-item">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/icons/filter/power.svg'); ?>" alt="Kraftstoff">
                <span><?php echo esc_html($car->fuel_type); ?></span>
            </div>
            <div class="car-card-info-item">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/icons/filter/tire.svg'); ?>" alt="Kilometerstand">
                <span><?php echo number_format($car->mileage_km); ?> Km</span>
            </div>
            <div class="car-card-info-item">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/icons/filter/power.svg'); ?>" alt="Leistung">
                <span><?php echo esc_html($car->power_kw); ?> kW</span>
            </div>
        </div>

        <!-- Separator Line -->
        <div class="car-card-separator"></div>

        <!-- Fourth Row: Preis und Aktionen -->
        <div class="car-card-footer">
            <span class="car-card-price">Preis: <strong>€<?php echo number_format($car->price, 2); ?></strong></span>
           
           
            <div class="car-card-actions">
    <!-- Favorites Button -->
    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/icons/filter/Heart.svg'); ?>" 
         alt="Favorisieren" title="Zu Favoriten hinzufügen">

    <!-- Compare Checkbox -->
    <div class=" car-card-actions compare-checkbox">
        <label>
            <input type="checkbox" class="compare-check" 
                data-car-id="<?php echo $car->id; ?>"> 
         
        </label>
    </div>
</div>



            
        </div>
    </div>
</div>