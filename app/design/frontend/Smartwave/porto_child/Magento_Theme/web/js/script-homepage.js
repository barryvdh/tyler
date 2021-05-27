require([
    'jquery',
    'owl.carousel/owl.carousel.min'
], function ($) {
    $("#banner-slider-home .owl-carousel").owlCarousel({
        autoplayTimeout: 5000,
        autoplay:true,
        margin: 20,
        nav: false,
        navText: ["<span class='prev'></span>","<span class='next'></span>"],
        dots: true,
        loop: true,
        singleItem:true,
        items : 1
    });
});
