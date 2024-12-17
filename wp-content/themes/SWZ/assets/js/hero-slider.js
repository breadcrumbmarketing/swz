class HeroSlider {
    constructor() {
        this.heroSection = document.getElementById('heroSection');
        this.slides = document.querySelectorAll('.slide');
        this.currentSlide = 0;
        this.isAnimating = false;
        this.slidesCount = this.slides.length;
        this.lastScrollTop = 0;
        this.isSliderActive = true;
        this.wheelEventTimeout = null;
        this.scrollThreshold = 10;
        this.lastWheelTime = Date.now();
        this.scrollDirection = null;
        this.scrollCount = 0;
        this.progressCircle = document.querySelector('.progress-ring-circle');
        this.currentSlideNum = document.querySelector('.slide-counter .current');
        this.progressIndicator = document.querySelector('.slide-progress');
        this.circumference = 2 * Math.PI * 20;
        this.isLastSlideViewed = false;
        this.init();
    }

    init() {
        this.slides[0].classList.add('active');
        
        this.slides.forEach(slide => {
            const video = slide.querySelector('video');
            if (video) {
                video.play().catch(() => {
                    console.log('Video autoplay failed');
                });
            }
        });
    
        if (this.progressCircle) {
            this.progressCircle.style.strokeDasharray = `${this.circumference} ${this.circumference}`;
            this.updateProgress();
        }
    
        if (this.progressIndicator) {
            this.progressIndicator.style.cursor = 'pointer';
            this.progressIndicator.addEventListener('click', () => this.resetToFirstSlide());
        }
    
        window.addEventListener('wheel', (e) => {
            if (!this.isSliderActive) return;
            
            e.preventDefault();
            
            const now = Date.now();
            const timeDiff = now - this.lastWheelTime;
            
            if (
                (e.deltaY > 0 && this.scrollDirection === 'up') || 
                (e.deltaY < 0 && this.scrollDirection === 'down') ||
                timeDiff > 200
            ) {
                this.scrollCount = 0;
                this.scrollDirection = e.deltaY > 0 ? 'down' : 'up';
            }
            
            this.scrollCount += Math.abs(e.deltaY);
            
            if ((this.scrollDirection === 'down' && this.scrollCount > 10) || 
                (this.scrollDirection === 'up' && this.scrollCount > 20)) {
                
                if (timeDiff > 50) {
                    this.lastWheelTime = now;
                    this.scrollCount = 0;
                    
                    if (this.scrollDirection === 'down') {
                        this.nextSlide();
                    } else {
                        this.prevSlide();
                    }
                }
            }
        }, { passive: false });
    
        window.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: false });
        
        this.touchStartY = 0;
        document.addEventListener('touchstart', (e) => {
            this.touchStartY = e.touches[0].clientY;
        });
    
        this.preventPageScroll();
    }

    updateProgress() {
        const progress = this.currentSlide / (this.slidesCount - 1);
        const offset = this.circumference - (progress * this.circumference);
        if (this.progressCircle) {
            this.progressCircle.style.strokeDashoffset = offset;
        }
        if (this.currentSlideNum) {
            this.currentSlideNum.textContent = this.currentSlide + 1;
        }
    }

    handleTouchMove(e) {
        if (!this.isSliderActive) return;
        
        e.preventDefault();
        
        if (this.isAnimating) return;
        
        const touchEndY = e.touches[0].clientY;
        const diff = this.touchStartY - touchEndY;
        
        if (Math.abs(diff) > 30) {
            if (diff > 0) {
                this.nextSlide();
            } else {
                this.prevSlide();
            }
            this.touchStartY = touchEndY;
        }
    }

    async nextSlide() {
        if (this.isAnimating) return;
        
        // If we're on the last slide and it's already been viewed
        if (this.currentSlide === this.slidesCount - 1 && this.isLastSlideViewed) {
            this.isSliderActive = false;
            this.enablePageScroll();
            return;
        }

        // If we're not on the last slide yet
        if (this.currentSlide < this.slidesCount - 1) {
            this.isAnimating = true;
            
            this.slides[this.currentSlide].classList.add('slide-up');
            this.currentSlide++;
            
            this.slides[this.currentSlide].classList.remove('slide-up', 'slide-down');
            this.slides[this.currentSlide].classList.add('active');
            
            this.updateProgress();
            
            const video = this.slides[this.currentSlide].querySelector('video');
            if (video) {
                video.currentTime = 0;
                video.play().catch(() => {});
            }
            
            await new Promise(resolve => setTimeout(resolve, 800));
            
            this.slides[this.currentSlide - 1].classList.remove('active', 'slide-up');
            this.isAnimating = false;

            // If we just arrived at the last slide, mark it for viewing
            if (this.currentSlide === this.slidesCount - 1) {
                this.isLastSlideViewed = true;
            }
        }
    }

    async prevSlide() {
        if (this.isAnimating || this.currentSlide <= 0) return;
        
        if (this.currentSlide === this.slidesCount - 1) {
            this.isLastSlideViewed = false;
        }
        
        this.isAnimating = true;
        
        if (!this.isSliderActive) {
            this.isSliderActive = true;
            this.preventPageScroll();
        }
        
        this.slides[this.currentSlide].classList.add('slide-down');
        this.currentSlide--;
        
        this.slides[this.currentSlide].classList.remove('slide-up', 'slide-down');
        this.slides[this.currentSlide].classList.add('active');
        
        this.updateProgress();
        
        const video = this.slides[this.currentSlide].querySelector('video');
        if (video) {
            video.currentTime = 0;
            video.play().catch(() => {});
        }
        
        await new Promise(resolve => setTimeout(resolve, 800));
        
        this.slides[this.currentSlide + 1].classList.remove('active', 'slide-down');
        this.isAnimating = false;
    }

    async resetToFirstSlide() {
        if (this.isAnimating || this.currentSlide === 0) return;
        
        this.isAnimating = true;
        this.isLastSlideViewed = false;
        
        if (!this.isSliderActive) {
            this.isSliderActive = true;
            this.preventPageScroll();
        }
        
        this.slides[this.currentSlide].classList.add('slide-down');
        this.currentSlide = 0;
        
        this.slides[this.currentSlide].classList.remove('slide-up', 'slide-down');
        this.slides[this.currentSlide].classList.add('active');
        
        this.updateProgress();
        
        const video = this.slides[this.currentSlide].querySelector('video');
        if (video) {
            video.currentTime = 0;
            video.play().catch(() => {});
        }
        
        await new Promise(resolve => setTimeout(resolve, 800));
        
        this.slides.forEach((slide, index) => {
            if (index !== 0) {
                slide.classList.remove('active', 'slide-up', 'slide-down');
            }
        });
        
        this.isAnimating = false;
    }

    preventPageScroll() {
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';
    }

    enablePageScroll() {
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new HeroSlider();
});