
// JavaScript for Back-to-Top button functionality
$(document).ready(function() {
    var backToTopBtn = $('#backToTopBtn');

    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            backToTopBtn.fadeIn();
        } else {
            backToTopBtn.fadeOut();
        }
    });

    backToTopBtn.on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop: 0}, '300');
    });
    
    // Optional: Smooth scroll for internal links
    $('a[href*="#"]').on('click', function(e) {
        if (this.hash !== "") {
            e.preventDefault();
            var hash = this.hash;
            $('html, body').animate({
                scrollTop: $(hash).offset().top - 70 // Adjust for fixed header height
            }, 800, function(){
                // window.location.hash = hash; // Optional: update URL hash
            });
        }
    });
});

    (function(){
        function setup(targetId){
            var scroller = document.querySelector(targetId);
            if(!scroller) return;
            function amount(){
                var first = scroller.querySelector('.card');
                return first ? first.getBoundingClientRect().width + 12 : scroller.clientWidth * 0.9;
            }
            document.querySelectorAll('[data-target="'+targetId+'"]').forEach(function(btn){
                btn.addEventListener('click', function(){
                    var dir = btn.getAttribute('data-scroll');
                    var a = amount();
                    var max = scroller.scrollWidth - scroller.clientWidth;
                    if(dir === 'right'){
                        if(scroller.scrollLeft >= max - 5){ scroller.scrollTo({left:0, behavior:'smooth'}); }
                        else { scroller.scrollBy({left:a, behavior:'smooth'}); }
                    } else {
                        if(scroller.scrollLeft <= 5){ scroller.scrollTo({left:max, behavior:'smooth'}); }
                        else { scroller.scrollBy({left:-a, behavior:'smooth'}); }
                    }
                });
            });
        }
        setup('#servicesScroller');
        setup('#departmentsScroller');
        setup('#doctorsScroller');
    })();