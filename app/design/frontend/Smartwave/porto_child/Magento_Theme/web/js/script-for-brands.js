require([
	'jquery',
	'owl.carousel/owl.carousel.min'
], function ($) {
	$("#brands-slider-1 .owl-carousel").owlCarousel({
		autoplay: true,
		autoplayTimeout: 5000,
		autoplayHoverPause: true,
		margin: 20,
		nav: true,
		navText: ["<span class='prev'></span>","<span class='next'></span>"],
		dots: false,
		loop: true,
		responsive: {
			0: {
				items:1
			},
			640: {
				items:2
			},
			960: {
				items:3
			}
		}
	});
	$("#brands-slider-2 .owl-carousel").owlCarousel({
		autoplay: true,
		autoplayTimeout: 5000,
		autoplayHoverPause: true,
		margin: 20,
		nav: true,
		navText: ["<span class='prev'></span>","<span class='next'></span>"],
		dots: false,
		loop: true,
		responsive: {
			0: {
				items:1
			},
			640: {
				items:2
			},
			960: {
				items:3
			}
		}
	});
	$("#blog-slider").owlCarousel({
	    items: 1,
	    autoplay: false,
	    dots: false,
	    nav: true,
	    loop: true,
	    navText: ["<span class='prev'></span>","<span class='next'></span>"],
	    navRewind: true
	});					  

});