<?php
/**
 * Footer Template for SWZ Design
 */
?>
    </main> <!-- Close main content area -->

    <footer class="site-footer">
    <div class="footer-inner">
        <div class="container">
            <div class="footer-grid">

                <!-- Newsletter Bereich -->
                <div class="footer-newsletter">
                    <a href="<?php echo home_url(); ?>" class="footer-logo">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/logo/SWZ-logo-white.webp" alt="SWZ Logo">
                    </a>
                    <h3>Newsletter abonnieren</h3>
                    <p>Verpassen Sie keine relevanten Angebote!</p>
                    <form class="newsletter-form" id="footer-newsletter-form">
                        <div class="input-wrapper">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/atsign.svg" class="email-icon" alt="Email">
                            <input type="email" name="newsletter_email" placeholder="Ihre E-Mail" required>
                            <button type="submit">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/sendmail.svg" class="submit-icon" alt="Submit">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/loading.svg" class="loading-icon" alt="Loading">
                            </button>
                        </div>
                        <div class="newsletter-message"></div>
                        <?php wp_nonce_field('swz_newsletter_nonce', 'newsletter_nonce'); ?>
                    </form>
                </div>

                <!-- Back to top -->
                <button id="backToTop" class="back-to-top" aria-label="Back to top">
                    <svg class="svgIcon" viewBox="0 0 384 512">
                        <path d="M214.6 41.4c-12.5-12.5-32.8-12.5-45.3 0l-160 160c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 141.2V448c0 17.7 14.3 32 32 32s32-14.3 32-32V141.2L329.4 246.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-160-160z"></path>
                    </svg>
                </button>

                <!-- Kaufen & Verkaufen -->
                <div class="footer-section">
                    <h4>Kaufen & Verkaufen</h4>
                    <ul>
                        <li><a href="#">Auto finden</a></li>
                        <li><a href="#">Auto verkaufen</a></li>
                        <li><a href="#">Autohändler</a></li>
                        <li><a href="#">Autos vergleichen</a></li>
                        <li><a href="#">Online Autobewertung</a></li>
                    </ul>
                </div>

                <!-- Über uns -->
                <div class="footer-section">
                    <h4>Über uns</h4>
                    <ul>
                        <li><a href="#">Über Sport Wagenzentrum</a></li>
                        <li><a href="#">Kontakt</a></li>
                        <li><a href="#">FAQ & Support</a></li>
                        <li><a href="#">Mobile App</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>

                <!-- Profil -->
                <div class="footer-section">
                    <h4>Profil</h4>
                    <ul>
                        <li><a href="#">Mein Konto</a></li>
                        <li><a href="#">Merkliste</a></li>
                        <li><a href="#">Meine Anzeigen</a></li>
                        <li><a href="#">Anzeige aufgeben</a></li>
                    </ul>
                </div>

                <!-- Kontakt Info -->
                <div class="footer-contact">
                    <a href="tel:(176) 47-666-407" class="contact-link">
                        <i class="fas fa-phone"></i> (176) 47-666-407
                    </a>
                    <a href="mailto:hamy@breadcrumb.de" class="contact-link">
                        <i class="fas fa-envelope"></i> hamy@breadcrumb.de
                    </a>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-google"></i></a>
                        <a href="#"><i class="fab fa-telegram-plane"></i></a>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>&copy; Alle Rechte vorbehalten. Erstellt von Breadcrumb</p>
                <div class="footer-links">
                    <a href="#">Nutzungsbedingungen</a>
                    <a href="#">Datenschutzerklärung</a>
                    <a href="#">Barrierefreiheit</a>
                    <a href="#">Interessenbasierte Werbung</a>
                </div>
            </div>
        </div>
        </div>
    </footer>
</div> <!-- Close site-wrapper div -->

<?php wp_footer(); ?>
</body>
</html>