define([
    'jquery'
], function ($) {
    'use strict';

    return function (configs, element) {
        var form = $(element);

        if (configs.isSubUser && form.length > 0) {
            // Remove form action
            form.removeAttr('action');

            // disabled form input
            $(form).find('input').each(function (index, e) {
                $(e).prop('disabled', true);
            });

            // remove submit button
            $(form).find('[type=submit]').remove();
        }
    }
});
