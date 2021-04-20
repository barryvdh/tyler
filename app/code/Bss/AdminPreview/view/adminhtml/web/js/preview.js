/*
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 *  @category  BSS
 *  @package   Bss_AdminPreview
 *  @author    Extension Team
 *  @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 *  @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(['jquery'
], function ($) {
    $(document).ready(function () {
        $('body').on('click', '.parent-preview-clickable .showhide', function () {
            $parent = $(this).parent();
            var id = $parent.attr('item-id');
            if ($parent.hasClass('active')) {
                $parent.removeClass('active');
                $parent.nextUntil('.parent-preview-clickable').removeClass('active');
                $(this).text('+');
            } else {
                $parent.addClass('active');
                $parent.nextUntil('.parent-preview-clickable').addClass('active');
                $(this).text('-');
            }
        });
    });
});