/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'Bss_Popup/js/mfpopup',
    'mage/trim-input'
], function ($) {
    return function (config) {
        $("#newsletter").trimInput();
        var eventDisplay = config.eventDisplay;
        var showed = false;

        // IN IOS, if page has scroll, do not do it again
        var didScroll = true;

        // After opened, assign popup object to this var
        var mfgPop = undefined;

        var typeTemplatePopup = config.typeTemplatePopup;
        const TYPE_TEMPLATE_AGE_VERIFICATION = 2;

        // Ios/Iphone/Ipad... check
        var iosCheck = function () {
            var isSafari = !!navigator.userAgent.match(/Version\/[\d\.]+.*Safari/);
            var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

            if (isSafari && iOS) {
                return 1;
            } else if (isSafari) {
                return 2;
            }
            return 3;
        };

        // Mobile check
        var mobileCheck = function () {
            if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)                 || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) {
                return true;
            }
            return false;
        };

        // Add class css
        var addClassToBody = function () {
            if (mobileCheck()) {
                $('body').addClass('bss-popup-opened');
            }
            var topAlign = $(document).scrollTop();
            if (iosCheck() === 1 || iosCheck() === 2) {
                // Pure js + jquery scroll
                document.body.style.position = 'fixed';
                if (undefined !== topAlign || topAlign !== 0) {
                    document.body.style.top = '-' + topAlign.toString() + 'px';
                } else {
                    document.body.style.top = `-${window.scrollY}px`;
                }

                $('.mfp-wrap').addClass('safari-context-wrap');
                $('body').addClass('safari-context');
                didScroll = false;
                // End
            }
        };

        // Remove class css
        var removeClassFromBody = function () {
            $('body').removeClass('bss-popup-opened');
            if (iosCheck() === 1 || iosCheck() === 2) {
                // Pure js scroll
                var scrollY = document.body.style.top;
                document.body.style.position = '';
                document.body.style.top = '';
                if (!didScroll) {
                    window.scrollTo(0, parseInt(scrollY || '0') * -1);
                    didScroll = true;
                }
                $('body').removeClass('safari-context');
                // End
            }
        };

        // Add class css
        var addClassToBssPopup = function () {
            $('.bss_popup').addClass('opened');
        };

        // Remove class css
        var removeClassFromBssPopup = function () {
            $('.bss_popup').removeClass('opened');
        };

        // Ajax call
        var updatePopupDisplayed = function (popupId) {
            $.ajax({
                url: config.updateUrl,
                type: 'POST',
                data: {
                    popupId: popupId
                }
            });
        };

        // Add class css
        var addNewClassToPopup = function ($popupObject) {
            if (iosCheck() === 1 || iosCheck() === 2) {
                var newClass = "safari-context-wrap";
                $popupObject.st.mainClass = $popupObject.st.mainClass + ' ' + newClass;
            }
        };

        // Remove class css
        var removeClassFromPopup = function ($popupObject) {
            if (iosCheck() === 1 || iosCheck() === 2) {
                var newClass = "safari-context-wrap";
                $($popupObject.st.mainClass).removeClass(newClass);
            }
        };

        $(document).ready(function () {
            var autoClose = config.hideAfter;
            var timeDelay = (config.effectDisplay) ? 500 : 0;
            var displayed = false;
            var allowDisplay = config.popupIsAllowedDisplay;

            function displayPopup(allowDisplay, autoClose, timeDelay)
            {
                $.ajax({
                    url: config.checkTimeUrl,
                    type: 'POST',
                    data: {
                        popupId: config.popupId,
                        isPreview: config.preview ? 1 : 0
                    },
                    success: function (result) {
                        if (allowDisplay != 2 || allowDisplay == 2 && !showed) {
                            if (result.res === true) {
                                addClassToBody();
                                $(".popup_wrapper").css({"display": "block"});
                                if (typeTemplatePopup == TYPE_TEMPLATE_AGE_VERIFICATION) {
                                    $.magnificPopup.open({
                                        items: {
                                            src: '.popup_wrapper',
                                        },
                                        callbacks: {
                                            beforeOpen: function () {
                                                addNewClassToPopup(this);
                                                if ($('body').width() <= 1444) {
                                                    addClassToBssPopup();
                                                }
                                                mfgPop = this;
                                            },
                                            close: function () {
                                                removeClassFromBody();
                                                removeClassFromPopup(this);
                                                removeClassFromBssPopup();
                                            }
                                        },
                                        removalDelay: timeDelay,
                                        mainClass: config.animation,
                                        alignTop: config.flagTop,
                                        closeOnBgClick: false,
                                        enableEscapeKey: false,
                                        showCloseBtn: false
                                    });
                                } else {
                                    $.magnificPopup.open({
                                        items: {
                                            src: '.popup_wrapper',
                                        },
                                        callbacks: {
                                            beforeOpen: function () {
                                                addNewClassToPopup(this);
                                                if ($('body').width() <= 1444) {
                                                    addClassToBssPopup();
                                                }
                                                mfgPop = this;
                                            },
                                            close: function () {
                                                removeClassFromBody();
                                                removeClassFromPopup(this);
                                                removeClassFromBssPopup();
                                            }
                                        },
                                        removalDelay: timeDelay,
                                        mainClass: config.animation,
                                        alignTop: config.flagTop,
                                    });
                                }
                                $.magnificPopup.instance.close = function () {
                                    // jQuery('body').removeAttr('style');
                                    removeClassFromBody();
                                    if (undefined !== mfgPop) {
                                        removeClassFromPopup(mfgPop);
                                    }
                                    removeClassFromBssPopup();
                                    $.magnificPopup.proto.close.call(this);
                                };
                                if (autoClose) {
                                    setTimeout(function () {
                                        removeClassFromBody();
                                        if (undefined !== mfgPop) {
                                            removeClassFromPopup(mfgPop);
                                        }
                                        removeClassFromBssPopup();
                                        $.magnificPopup.close();
                                    }, autoClose * 1000);
                                }
                            }
                        }
                        showed = true;
                    }
                });
            };

            function checkExitIntent()
            {
                $(document).mouseleave(function(event){
                    if (event.clientY <= 0) {
                        displayPopup(allowDisplay, autoClose, timeDelay);
                        updatePopupDisplayed(config.popupId);
                    }
                });
            }
            switch (eventDisplay) {
                case 1:
                    setTimeout(function () {
                        displayPopup(allowDisplay, autoClose, timeDelay);
                        updatePopupDisplayed(config.popupId);
                    }, config.afterLoad * 1000);
                    break;
                case 2:
                    $(window).on('scroll', function () {
                        var scrollPosition = config.afterScroll * $(window).height() / 100;
                        if ($(this).scrollTop() >= scrollPosition && !displayed) {
                            displayPopup(allowDisplay, autoClose, timeDelay);
                            updatePopupDisplayed(config.popupId);
                            displayed = true;
                        }
                    });
                    break;
                case 3:
                    var pagesViewed = config.pagesViewed;
                    var popupPages = config.popupPages;
                    if (popupPages <= pagesViewed) {
                        displayPopup(allowDisplay, autoClose, timeDelay);
                        updatePopupDisplayed(config.popupId);
                    }
                    break;
                case 4:
                    displayPopup(allowDisplay, autoClose, timeDelay);
                    updatePopupDisplayed(config.popupId);
                    break;
                case 5:
                    checkExitIntent();
                default:
                    break;
            }
        });
    };
});
