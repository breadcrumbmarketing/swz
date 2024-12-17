<?php
global $wpdb;

// Fetch dynamic min and max values from the database
$price_data = $wpdb->get_row("SELECT MIN(price) as min_price, MAX(price) as max_price FROM wp_cars");
$year_data  = $wpdb->get_row("SELECT MIN(car_year) as min_year, MAX(car_year) as max_year FROM wp_cars");
$power_data = $wpdb->get_row("SELECT MIN(power_kw) as min_power, MAX(power_kw) as max_power FROM wp_cars");
$tuv_data   = $wpdb->get_row("SELECT MIN(tuv_year) as min_tuv, MAX(tuv_year) as max_tuv FROM wp_cars");

// Fallback values
$min_price = $price_data->min_price ?? 0;
$max_price = $price_data->max_price ?? 150000;
$min_year  = $year_data->min_year ?? 2000;
$max_year  = $year_data->max_year ?? 2024;
$min_power = $power_data->min_power ?? 50;
$max_power = $power_data->max_power ?? 1000;
$min_tuv   = $tuv_data->min_tuv ?? 2020;
$max_tuv   = $tuv_data->max_tuv ?? 2030;
?>

<!-- Sidebar Toggle Button -->


<!-- Filter Sidebar -->
<form id="car-filter-form" method="GET" action="">
    <aside id="filter-sidebar" class="filter-sidebar">

<!-- Close Button Inside Sidebar -->
<button id="toggle-filter-sidebar" type="button">
    <div class="button-box">
      <span class="button-elem">
        <svg viewBox="0 0 46 40" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M46 20.038c0-.7-.3-1.5-.8-2.1l-16-17c-1.1-1-3.2-1.4-4.4-.3-1.2 1.1-1.2 3.3 0 4.4l11.3 11.9H3c-1.7 0-3 1.3-3 3s1.3 3 3 3h33.1l-11.3 11.9c-1 1-1.2 3.3 0 4.4 1.2 1.1 3.3.8 4.4-.3l16-17c.5-.5.8-1.1.8-1.9z"
            ></path>
          </svg>
        </span>
        <span class="button-elem">
          <svg viewBox="0 0 46 40">
            <path
              d="M46 20.038c0-.7-.3-1.5-.8-2.1l-16-17c-1.1-1-3.2-1.4-4.4-.3-1.2 1.1-1.2 3.3 0 4.4l11.3 11.9H3c-1.7 0-3 1.3-3 3s1.3 3 3 3h33.1l-11.3 11.9c-1 1-1.2 3.3 0 4.4 1.2 1.1 3.3.8 4.4-.3l16-17c.5-.5.8-1.1.8-1.9z"
            ></path>
          </svg>
        </span>
      </div>
    </button>



        <h3 class="filter-title">Filter</h3>

        <!-- Compare Button -->
        <div class="filter-compare">
            <button id="compare-selected-cars" class="compare-button" disabled>Vergleichen</button>
        </div>

        <!-- Reset Button -->
        <div class="filter-reset">
            <button type="button" id="filter-reset">Zurücksetzen</button>
        </div>

        <!-- Price Range -->
        <div class="filter-section">
    <label>Preis</label>
    <div class="range-slider">
        <!-- Lower/Min Range Input -->
        <input type="range" 
               id="price-min" 
               name="price_min" 
               min="<?php echo $min_price; ?>" 
               max="<?php echo $max_price; ?>" 
               value="<?php echo $min_price; ?>" 
               step="100"
               class="range-min">
        <!-- Upper/Max Range Input -->
        <input type="range" 
               id="price-max" 
               name="price_max" 
               min="<?php echo $min_price; ?>" 
               max="<?php echo $max_price; ?>" 
               value="<?php echo $max_price; ?>" 
               step="100"
               class="range-max">
    </div>
    <div class="range-values">
        Preis (€): <span id="price-min-value"><?php echo $min_price; ?></span> — <span id="price-max-value"><?php echo $max_price; ?></span>
    </div>
</div>

        <!-- Year Range -->
        <div class="filter-section">
            <label>Baujahr</label>
            <div class="range-slider">
                <input type="range" id="year-min" name="year_min" min="<?php echo $min_year; ?>" max="<?php echo $max_year; ?>" value="<?php echo $min_year; ?>" step="1">
                <input type="range" id="year-max" name="year_max" min="<?php echo $min_year; ?>" max="<?php echo $max_year; ?>" value="<?php echo $max_year; ?>" step="1">
            </div>
            <div class="range-values">
                Jahr: <span id="year-min-value"><?php echo $min_year; ?></span> — <span id="year-max-value"><?php echo $max_year; ?></span>
            </div>
        </div>

        <!-- Power Range -->
        <div class="filter-section">
            <label>Leistung (kW)</label>
            <div class="range-slider">
                <input type="range" id="power-min" name="power_min" min="<?php echo $min_power; ?>" max="<?php echo $max_power; ?>" value="<?php echo $min_power; ?>" step="10">
                <input type="range" id="power-max" name="power_max" min="<?php echo $min_power; ?>" max="<?php echo $max_power; ?>" value="<?php echo $max_power; ?>" step="10">
            </div>
            <div class="range-values">
                Leistung (KW): <span id="power-min-value"><?php echo $min_power; ?></span>  — <span id="power-max-value"><?php echo $max_power; ?></span> 
            </div>
        </div>

        <!-- TÜV Range -->
        <div class="filter-section">
            <label>TÜV</label>
            <div class="range-slider">
                <input type="range" id="tuv-min" name="tuv_min" min="<?php echo $min_tuv; ?>" max="<?php echo $max_tuv; ?>" value="<?php echo $min_tuv; ?>" step="1">
                <input type="range" id="tuv-max" name="tuv_max" min="<?php echo $min_tuv; ?>" max="<?php echo $max_tuv; ?>" value="<?php echo $max_tuv; ?>" step="1">
            </div>
            <div class="range-values">
                TÜV: <span id="tuv-min-value"><?php echo $min_tuv; ?></span> — <span id="tuv-max-value"><?php echo $max_tuv; ?></span>
            </div>
        </div>

        <!-- Fuel Type -->
        <div class="filter-section">
            <label>Treibstoff</label>
            <div class="filter-checkbox">
                <label><input type="checkbox" name="fuel_type[]" value="Benzin"> Benzin</label>
                <label><input type="checkbox" name="fuel_type[]" value="Diesel"> Diesel</label>
                <label><input type="checkbox" name="fuel_type[]" value="Elektro"> Elektro</label>
                <label><input type="checkbox" name="fuel_type[]" value="Hybrid"> Hybrid</label>
            </div>
        </div>

        <!-- Car Type -->
        <div class="filter-section">
    <label>Fahrzeugtyp</label>
    <div class="car-type-slider-container">
        <button type="button" class="slider-arrow prev-arrow" aria-label="Previous">
            <div class="arrow-top"></div>
            <div class="arrow-bottom"></div>
        </button>
        <div class="car-type-slider-wrapper">
            <div class="car-type-options">
                <label><input type="checkbox" name="car_type[]" value="SUV"><img src="<?php echo get_template_directory_uri(); ?>/assets/icons/car_type/coupe.svg" alt="SUV"><span>SUV</span></label>
                <label><input type="checkbox" name="car_type[]" value="Coupé"><img src="<?php echo get_template_directory_uri(); ?>/assets/icons/car_type/coupe.svg" alt="Coupé"><span>Coupé</span></label>
                <label><input type="checkbox" name="car_type[]" value="Limousine"><img src="<?php echo get_template_directory_uri(); ?>/assets/icons/car_type/coupe.svg" alt="Limousine"><span>Limousine</span></label>
                <label><input type="checkbox" name="car_type[]" value="Cabrio"><img src="<?php echo get_template_directory_uri(); ?>/assets/icons/car_type/coupe.svg" alt="Cabrio"><span>Cabrio</span></label>
            </div>
        </div>
        <button type="button" class="slider-arrow next-arrow" aria-label="Next">
            <div class="arrow-top"></div>
            <div class="arrow-bottom"></div>
        </button>
    </div>
</div>

        <!-- Color Picker -->
        <div class="filter-section">
            <label>Farbe</label>
            <div class="color-options">
                <span class="color-circle" style="background-color: red;"></span>
                <span class="color-circle" style="background-color: blue;"></span>
                <span class="color-circle" style="background-color: black;"></span>
                <span class="color-circle" style="background-color: white;"></span>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="filter-submit">Filtern</button>
    </aside>
</form>
<!-- Hidden Button (Appears after closing sidebar) -->
<button id="reopen-sidebar-button" type="button" style="display: none; position: fixed; top: 220px; left: 20px; z-index:1000000;">
  <span aria-hidden="true" class="circle">
    <span class="icon arrow"></span>
  </span>
  <span class="button-text">Filter anzeigen</span>
</button>

<script>
document.getElementById('toggle-filter-sidebar').addEventListener('click', function() {
    var sidebar = document.getElementById('filter-sidebar');
    var reopenButton = document.getElementById('reopen-sidebar-button');
    var gridContainer = document.querySelector('.grid-container');

    // Hide sidebar and show reopen button
    sidebar.style.display = 'none';
    reopenButton.style.display = 'block';

    // Add expanded class to grid container
    gridContainer.classList.add('expanded');
});

document.getElementById('reopen-sidebar-button').addEventListener('click', function() {
    var sidebar = document.getElementById('filter-sidebar');
    var reopenButton = document.getElementById('reopen-sidebar-button');
    var gridContainer = document.querySelector('.grid-container');

    // Show sidebar and hide reopen button
    sidebar.style.display = 'block';
    reopenButton.style.display = 'none';

    // Remove expanded class from grid container
    gridContainer.classList.remove('expanded');
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.querySelector('.car-type-slider-wrapper');
    const slider = document.querySelector('.car-type-options');
    const prevBtn = document.querySelector('.prev-arrow');
    const nextBtn = document.querySelector('.next-arrow');
    let currentPosition = 0;
    let sliderWidth = wrapper.offsetWidth;
    let contentWidth = slider.scrollWidth;

    // Update measurements on window resize
    window.addEventListener('resize', function() {
        sliderWidth = wrapper.offsetWidth;
        contentWidth = slider.scrollWidth;
        updateArrowState();
    });

    function updateArrowState() {
        prevBtn.disabled = currentPosition >= 0;
        nextBtn.disabled = currentPosition <= -(contentWidth - sliderWidth);
    }

    function slide(direction) {
        const step = sliderWidth * 0.8;
        if (direction === 'prev') {
            currentPosition = Math.min(currentPosition + step, 0);
        } else {
            currentPosition = Math.max(currentPosition - step, -(contentWidth - sliderWidth));
        }
        slider.style.transform = `translateX(${currentPosition}px)`;
        updateArrowState();
    }

    prevBtn.addEventListener('click', () => slide('prev'));
    nextBtn.addEventListener('click', () => slide('next'));

    // Initial arrow state
    updateArrowState();
});
</script>