<?php
/**
 * Template Name: Hero Slider Template
 * Description: A page template with scroll-based hero slider
 */

get_header(); ?>

<div class="hero-section" id="heroSection">
    <div class="hero-slider">
    <div class="slide" data-slide="1">
    <div class="media-container">
        <img class="media image" src="<?php echo get_theme_file_uri('/assets/images/DQEmprJ8TBqLjWRQ2qQnGw.webp'); ?>" alt="Slide 1 Image">
    </div>
</div>
    <div class="slide-content">
        <div class="content-wrapper">
            <h1 class="slide-title">Der perfekte Platz für Ihr Sportauto</h1>
            <p class="slide-description">Sichern Sie sich einen exklusiven Stellplatz für Ihr Fahrzeug in unserer hochwertigen Sportwagen-Zentrum. Perfekt für Ihre Leidenschaft, perfekt für Ihr Auto.</p>
            <button class="cta-button-hero">
  <svg viewBox="0 0 24 24" class="arr-2" xmlns="http://www.w3.org/2000/svg">
    <path
      d="M16.1716 10.9999L10.8076 5.63589L12.2218 4.22168L20 11.9999L12.2218 19.778L10.8076 18.3638L16.1716 12.9999H4V10.9999H16.1716Z"
    ></path>
  </svg>
  <span class="text">Jetzt Stellplatz sichern</span>
  <span class="circle"></span>
  <svg viewBox="0 0 24 24" class="arr-1" xmlns="http://www.w3.org/2000/svg">
    <path
      d="M16.1716 10.9999L10.8076 5.63589L12.2218 4.22168L20 11.9999L12.2218 19.778L10.8076 18.3638L16.1716 12.9999H4V10.9999H16.1716Z"
    ></path>
  </svg>
</button>
            
          
        </div>
    </div> 
    <div class="search-filter">
    <div class="filter-container">
        <h3>Find Your Sport Car</h3>
        <form id="car-search-form">
            <label for="model">Model:</label>
            <select id="model" name="model">
                <option value="">Select Model</option>
                <!-- Add options here based on your inventory -->
            </select>

            <label for="price-range">Price Range:</label>
            <input type="range" id="price-range" name="price-range" min="50000" max="300000" value="85000">
            <span id="price-value">$85,000</span>

            <label for="year">Year:</label>
            <select id="year" name="year">
                <option value="">Select Year</option>
                <!-- Dynamically generate or add year options -->
            </select>

            <label for="condition">Condition:</label>
            <select id="condition" name="condition">
                <option value="">Any</option>
                <option value="new">New</option>
                <option value="used">Used</option>
                <option value="certified">Certified Pre-Owned</option>
            </select>


            
            <button class="cta-button-hero" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
  <svg viewBox="0 0 24 24" class="arr-2" xmlns="http://www.w3.org/2000/svg">
    <path
      d="M16.1716 10.9999L10.8076 5.63589L12.2218 4.22168L20 11.9999L12.2218 19.778L10.8076 18.3638L16.1716 12.9999H4V10.9999H16.1716Z"
    ></path>
  </svg>
  <span class="text" style="display: block; text-align: center; flex: 1;">Jetzt suchen</span>
  <span class="circle"></span>
  <svg viewBox="0 0 24 24" class="arr-1" xmlns="http://www.w3.org/2000/svg">
    <path
      d="M16.1716 10.9999L10.8076 5.63589L12.2218 4.22168L20 11.9999L12.2218 19.778L10.8076 18.3638L16.1716 12.9999H4V10.9999H16.1716Z"
    ></path>
  </svg>
</button>


        </form>
    </div>
</div>
</div>
        <div class="slide" data-slide="2">
            <div class="media-container">
                <img class="media image" src="<?php echo get_theme_file_uri('/assets/images/img01.jpg'); ?>" alt="Slide 2">
            </div>
        </div>
        <div class="slide" data-slide="3">
    <div class="media-container">
        <img class="media image" src="<?php echo get_theme_file_uri('/assets/images/replacement_img03.jpg'); ?>" alt="Slide 3 Image">
    </div>
</div>
        <div class="slide" data-slide="4">
            <div class="media-container">
                <img class="media image" src="<?php echo get_theme_file_uri('/assets/images/img02.jpg'); ?>" alt="Slide 4">
            </div>
        </div>
    </div>
    
   <!-- panigation cycle -->
    <div class="slide-progress">
    <svg class="progress-ring" viewBox="0 0 48 48">
        <circle class="progress-ring-background" cx="24" cy="24" r="20" />
        <circle class="progress-ring-circle" cx="24" cy="24" r="20" />
    </svg>
    <div class="slide-counter">
        <span class="current">1</span>
        <span class="total">4</span>
    </div>
</div>

    <div class="scroll-indicator">
        <span>Scroll to explore</span>
        <div class="scroll-arrow"></div>
    </div>
</div>

<div class="page-content">
    <?php while (have_posts()) : the_post(); ?>
        <?php the_content(); ?>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>