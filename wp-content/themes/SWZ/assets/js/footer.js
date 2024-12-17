jQuery(document).ready(function($) {
    const form = $('#footer-newsletter-form');
    const messageDiv = form.find('.newsletter-message');
    const emailInput = form.find('input[name="newsletter_email"]');
    
    form.on('submit', function(e) {
        e.preventDefault();
        
        const submitButton = form.find('button[type="submit"]');
        
        // Clear previous messages
        messageDiv.removeClass('success error').text('');
        
        // Validate email
        const email = emailInput.val();
        if (!isValidEmail(email)) {
            messageDiv.addClass('error').text('Bitte geben Sie eine gültige E-Mail-Adresse ein.');
            return;
        }
        
        // Add submitting class for loading icon
        form.addClass('submitting');
        submitButton.prop('disabled', true);
        
        $.ajax({
            url: swzAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'subscribe_newsletter',
                email: email,
                nonce: swzAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    messageDiv.addClass('success').text(response.data);
                    emailInput.val(''); // Clear input on success
                } else {
                    messageDiv.addClass('error').text(response.data);
                }
            },
            error: function() {
                messageDiv.addClass('error').text('Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
            },
            complete: function() {
                form.removeClass('submitting');
                submitButton.prop('disabled', false);
            }
        });
    });
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
});

// back to top 

document.addEventListener('DOMContentLoaded', function() {
    const backToTop = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTop.style.display = 'block';
        } else {
            backToTop.style.display = 'none';
        }
    });

    backToTop.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});