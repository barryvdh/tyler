require([
    'jquery',
    'mage/url'
],function($,url) {
    "use strict";

    $(document).ready(function($){
        var currentPopupFrequently = $('#popup_frequently').val();
        var currentPopupEventDisplay = $('#popup_event_display').val();
        if ( currentPopupEventDisplay === '5') {
            $("#popup_frequently option[value=" + 1 + "]").remove();
        }
        $('#popup_event_display').change(function () {
            var currentOptionValue = $(this).val();
            if(currentOptionValue === '5'){
                $('#popup_frequently').children('option').each(function(){
                    if ($(this).val() === '1'){
                        $(this).remove();
                        $("#popup_frequently option[value=" + currentPopupFrequently + "]").attr('selected','selected');
                    }
                });
            } else {
                var issetPopupFrequently = $("#popup_frequently option[value=1]").val();
                if (typeof(issetPopupFrequently) === undefined ){
                    $("#popup_frequently").append($('<option>', {
                        value: 1,
                        text: 'When all conditions are satisfied'
                    }));
                }
            }
        });
    });
    return;
});
