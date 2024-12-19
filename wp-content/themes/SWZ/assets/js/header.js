document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const mobileMenuBtn = document.querySelector('.swz-mobile-menu-btn');
    const mobileMenu = document.querySelector('.swz-mobile-menu');
    const closeMenuBtn = document.querySelector('.swz-close-menu');
    const body = document.body;
    const hamburgerIcon = document.querySelector('.hamburger-icon');
    const headerTop = document.querySelector('.swz-header-top');
    const headerMain = document.querySelector('.swz-header-main');
    let lastScroll = 0;
    let headerTopHidden = false;
    let headerMainHidden = false;

    // Toggle Menu Function with smooth transitions
    function toggleMenu(show) {
        if (show) {
            mobileMenu.style.display = 'block';
            mobileMenu.offsetHeight; // Force reflow
            mobileMenu.classList.add('active');
            mobileMenuBtn.classList.add('active');
            body.style.overflow = 'hidden';
        } else {
            mobileMenu.classList.remove('active');
            mobileMenuBtn.classList.remove('active');
            body.style.overflow = '';
            setTimeout(() => {
                if (!mobileMenu.classList.contains('active')) {
                    mobileMenu.style.display = 'none';
                }
            }, 400);
        }
    }

    // Header Scroll Animation
    let scrollTimer;
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        clearTimeout(scrollTimer);

        if (currentScroll > 100) {
            if (currentScroll > lastScroll) {
                // Scrolling down - sequential hide
                if (!headerMainHidden) {
                    headerMain.classList.add('header-slide-up');
                    headerMainHidden = true;
                }
                
                scrollTimer = setTimeout(() => {
                    if (!headerTopHidden) {
                        headerTop.classList.add('header-slide-up');
                        headerTopHidden = true;
                    }
                }, 150);
            } else {
                // Scrolling up - reverse sequential show
                if (headerTopHidden) {
                    headerTop.classList.remove('header-slide-up');
                    headerTopHidden = false;
                }
                
                setTimeout(() => {
                    if (headerMainHidden) {
                        headerMain.classList.remove('header-slide-up');
                        headerMainHidden = false;
                    }
                }, 100);
            }
        } else {
            // At top of page
            headerTop.classList.remove('header-slide-up');
            headerMain.classList.remove('header-slide-up');
            headerTopHidden = false;
            headerMainHidden = false;
        }
        
        lastScroll = currentScroll;
    });

    // Mobile Menu Event Listeners
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isMenuActive = mobileMenu.classList.contains('active');
            toggleMenu(!isMenuActive);
        });
    }

    if (closeMenuBtn) {
        closeMenuBtn.addEventListener('click', function() {
            toggleMenu(false);
        });
    }

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (mobileMenu?.classList.contains('active')) {
            if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                toggleMenu(false);
            }
        }
    });

    // Prevent click inside mobile menu from closing
    mobileMenu?.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Close menu on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenu?.classList.contains('active')) {
            toggleMenu(false);
        }
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768 && mobileMenu?.classList.contains('active')) {
                toggleMenu(false);
            }
        }, 250);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    if (mobileMenu?.classList.contains('active')) {
                        toggleMenu(false);
                    }
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Submenu accessibility
    const menuItems = document.querySelectorAll('.swz-menu-items li');
    menuItems.forEach(item => {
        const link = item.querySelector('a');
        const submenu = item.querySelector('ul');
        
        if (submenu) {
            link.setAttribute('aria-expanded', 'false');
            link.setAttribute('aria-haspopup', 'true');
            
            link.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const isExpanded = link.getAttribute('aria-expanded') === 'true';
                    link.setAttribute('aria-expanded', !isExpanded);
                }
            });
        }
    });

    // Phone QR code popup
   //const phoneNumber = document.querySelector('.phone-number');
   // const contactCard = document.querySelector('.contact-card');
    //const overlay = document.querySelector('.overlay');
   // const closeBtn = document.querySelector('.close-btn');

   //function openCard() {
      ////  overlay?.classList.add('active');
  //  }

   // function closeCard() {
       // contactCard?.classList.remove('active');
      //  overlay?.classList.remove('active');
    //}

   // phoneNumber?.addEventListener('click', openCard);
   // closeBtn?.addEventListener('click', closeCard);
   /// overlay?.addEventListener('click', closeCard);
});

// -------------------- //
//     FULL SCREEN      //
// ------------------- //

document.addEventListener('DOMContentLoaded', function() {
    const fullscreenBtn = document.getElementById('FSfullscreen-btn');
    
    // Initialize fullscreen if it was active
    if (localStorage.getItem('FSisFullscreen') === 'true') {
        enterFullscreen();
    }

    fullscreenBtn.addEventListener('click', toggleFullScreen);

    function toggleFullScreen(event) {
        event.preventDefault();
        if (!document.fullscreenElement) {
            enterFullscreen();
        } else {
            exitFullscreen();
        }
    }

    function enterFullscreen() {
        const docEl = document.documentElement;
        if (docEl.requestFullscreen) {
            docEl.requestFullscreen();
        } else if (docEl.webkitRequestFullscreen) {
            docEl.webkitRequestFullscreen();
        } else if (docEl.mozRequestFullScreen) {
            docEl.mozRequestFullScreen();
        } else if (docEl.msRequestFullscreen) {
            docEl.msRequestFullscreen();
        }
        localStorage.setItem('FSisFullscreen', 'true');
        fullscreenBtn.classList.add('FSis-fullscreen');
    }

    function exitFullscreen() {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
        localStorage.setItem('FSisFullscreen', 'false');
        fullscreenBtn.classList.remove('FSis-fullscreen');
    }

    // Handle fullscreen changes
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('mozfullscreenchange', handleFullscreenChange);
    document.addEventListener('MSFullscreenChange', handleFullscreenChange);

    function handleFullscreenChange() {
        if (document.fullscreenElement) {
            fullscreenBtn.classList.add('FSis-fullscreen');
        } else {
            fullscreenBtn.classList.remove('FSis-fullscreen');
        }
    }

    // Restore fullscreen when page loads
    window.addEventListener('load', function() {
        if (localStorage.getItem('FSisFullscreen') === 'true') {
            enterFullscreen();
        }
    });
});