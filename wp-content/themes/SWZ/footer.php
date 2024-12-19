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
                        <li><a href="#"> Sport Wagenzentrum</a></li>
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
                    <a href="mailto:online@breadcrumb.de" class="contact-link">
                        <i class="fas fa-envelope"></i> info@muster.de
                    </a>
                    <div class="social-links">
                    <a href="https://www.instagram.com/porsche_aschaffenburg/?hl=de" target="_blank" class="social-link1">
    <svg viewBox="0 0 16 16" class="bi bi-instagram" fill="currentColor" height="16" width="16" xmlns="http://www.w3.org/2000/svg" style="color: white"> <path fill="white" d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"></path> </svg>
  </a>
  <a href="https://www.facebook.com/porscheaschaffenburg" target="_blank" class="social-link2">
  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="20" height="20" viewBox="0 0 256 256" xml:space="preserve">

<defs>
</defs>
<g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)" >
	<path d="M 44.65 75.954 c -2.209 0 -4 -1.791 -4 -4 V 28.34 c 0 -7.568 3.869 -11.405 11.498 -11.405 l 4.913 -0.003 c 0.001 0 0.002 0 0.003 0 c 2.208 0 3.998 1.789 4 3.997 c 0.002 2.209 -1.788 4.001 -3.997 4.003 l -4.916 0.003 c -3.501 0 -3.501 0.586 -3.501 3.405 v 43.614 C 48.65 74.163 46.859 75.954 44.65 75.954 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
	<path d="M 57.064 45.981 H 34.799 c -2.209 0 -4 -1.791 -4 -4 s 1.791 -4 4 -4 h 22.266 c 2.209 0 4 1.791 4 4 S 59.273 45.981 57.064 45.981 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
	<path d="M 45 90 C 20.187 90 0 69.813 0 45 C 0 20.187 20.187 0 45 0 c 24.813 0 45 20.187 45 45 C 90 69.813 69.813 90 45 90 z M 45 8 C 24.598 8 8 24.598 8 45 c 0 20.402 16.598 37 37 37 c 20.402 0 37 -16.598 37 -37 C 82 24.598 65.402 8 45 8 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
</g>
</svg>
<a href="https://www.linkedin.com/company/porsche-zentrum-aschaffenburg/?originalSubdomain=de" target="_blank" class="social-link3">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="20" height="20" viewBox="0 0 256 256" xml:space="preserve">

<defs>
</defs>
<g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)" >
	<rect x="23.24" y="37.45" rx="0" ry="0" width="9.33" height="30" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) "/>
	<path d="M 27.904 33.356 c -2.988 0 -5.404 -2.423 -5.404 -5.406 c 0 -2.986 2.417 -5.41 5.405 -5.41 c 2.982 0 5.405 2.423 5.405 5.41 C 33.309 30.933 30.885 33.356 27.904 33.356 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
	<path d="M 67.5 67.46 h -9.321 V 52.869 c 0 -3.48 -0.06 -7.956 -4.847 -7.956 c -4.853 0 -5.594 3.793 -5.594 7.706 V 67.46 h -9.321 V 37.455 h 8.945 v 4.103 h 0.127 c 1.245 -2.36 4.288 -4.847 8.824 -4.847 c 9.444 0 11.187 6.213 11.187 14.292 V 67.46 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
	<path d="M 45 90 C 20.187 90 0 69.813 0 45 C 0 20.187 20.187 0 45 0 c 24.813 0 45 20.187 45 45 C 90 69.813 69.813 90 45 90 z M 45 6 C 23.495 6 6 23.495 6 45 s 17.495 39 39 39 s 39 -17.495 39 -39 S 66.505 6 45 6 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
</g>
</svg></a>
<a href="https://www.youtube.com/channel/UCUYRLKMbQ_0Z3QSTNXcvBhA" target="_blank" class="social-link4">
  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="20" height="20" viewBox="0 0 256 256" xml:space="preserve">

<defs>
</defs>
<g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)" >
	<path d="M 45 90 C 20.187 90 0 69.813 0 45 C 0 20.187 20.187 0 45 0 c 24.813 0 45 20.187 45 45 C 90 69.813 69.813 90 45 90 z M 45 4 C 22.393 4 4 22.393 4 45 s 18.393 41 41 41 s 41 -18.393 41 -41 S 67.607 4 45 4 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
	<path d="M 34.054 65.546 c -0.775 0 -1.551 -0.204 -2.257 -0.611 c -1.414 -0.816 -2.257 -2.278 -2.257 -3.91 V 28.975 c 0 -1.632 0.844 -3.093 2.257 -3.909 c 1.413 -0.816 3.101 -0.816 4.515 0 L 64.067 41.09 c 1.413 0.816 2.257 2.278 2.257 3.91 s -0.844 3.094 -2.257 3.91 l 0 0 L 36.311 64.935 C 35.604 65.342 34.829 65.546 34.054 65.546 z M 34.054 28.457 c -0.103 0 -0.191 0.034 -0.258 0.073 c -0.117 0.068 -0.257 0.2 -0.257 0.445 v 32.049 c 0 0.245 0.14 0.378 0.257 0.445 c 0.117 0.069 0.301 0.124 0.514 0 l 27.756 -16.024 c 0.212 -0.123 0.257 -0.31 0.257 -0.446 s -0.045 -0.323 -0.257 -0.446 L 34.311 28.53 C 34.219 28.477 34.133 28.457 34.054 28.457 z M 63.067 47.178 h 0.01 H 63.067 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
</g>
</svg> </a>
</div>

                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>&copy; Alle Rechte vorbehalten. Erstellt von Breadcrumb</p>
                <div class="footer-links">
                    <a href="https://wordpress-1275929-5116835.cloudwaysapps.com/nutzungsbedingungen/">Nutzungsbedingungen</a>
                    <a href="https://wordpress-1275929-5116835.cloudwaysapps.com/datenschutzerklarung/">Datenschutzerklärung</a>
                    <a href="https://wordpress-1275929-5116835.cloudwaysapps.com/barrierefreiheit/">Barrierefreiheit</a>
                    
                </div>
            </div>
        </div>
        </div>
    </footer>
</div> <!-- Close site-wrapper div -->

<?php wp_footer(); ?>
</body>
</html>