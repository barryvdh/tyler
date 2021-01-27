require([
    'jquery'
], function ($) {
    // Configure/customize these variables.
    var showChar = 90;  // How many characters are shown by default
    var ellipsestext = "...";
    var moretext = "Show more ";
    var lesstext = "Show less";


    $('.more').each(function() {
        var content = $(this).html();

        if(content.length > showChar) {

            var c = content.substr(0, showChar);
            var h = content.substr(showChar, content.length - showChar);

            var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';

            $(this).html(html);
        }

    });

    $(".morelink").click(function(){
        if($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(moretext);
        } else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });

    function equal_cols(el)
    {
        var h = 0;
        $(el).each(function(){
            $(this).css({'height':'auto'});
            if($(this).outerHeight() > h)
            {
                h = $(this).outerHeight();
            }
        });
        $(el).each(function(){
            $(this).css({'min-height':h});
        });
    }
    if($(window).width() > 768) {
        equal_cols('.catalog-category-view .main, .catalog-category-view .sidebar-main');
    }

});