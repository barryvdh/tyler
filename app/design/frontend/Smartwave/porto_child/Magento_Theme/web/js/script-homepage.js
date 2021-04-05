require([
	'jquery',
	'owl.carousel/owl.carousel.min'
], function ($) {
	$("#banner-slider-home .owl-carousel").owlCarousel({
		autoplayTimeout: 5000,
		autoplayHoverPause: true,
		margin: 20,
		nav: true,
		navText: ["<span class='prev'></span>","<span class='next'></span>"],
		dots: false,
		loop: true,
		singleItem:true,
		items : 1
	});
});