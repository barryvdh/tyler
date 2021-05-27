define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/translate',
    'mage/validation',
    'mage/mage',
    'domReady!'
], function ($, _, mageTemplate, $t) {
    'use strict';

    var addCss = false;

    $.widget('mage.BssProductGridInlineEditor', {
        options: {
            attr_sets: {},
            attrs_options: {},
            url_save: '',
            url_save_multiples: '',
            is_mass_edit: '',
            is_single_edit_field: '',
            symbols: {},
            symbol: '',
            position_symbol: 0,
            status_product: {},
            templates: {
                tr_edit: '<tr class="data-grid-editable-row bss-row-edit bss-row-edit-<%- data.productId %>"></tr>',
                tr_all: '<tr class="data-grid-bulk-edit-panel data-grid-editable-row bss-row-edit-all"></tr>',
                td_action: '<td class="data-grid-actions-cell"><span class="data-grid-row-changed">' +
                    '<span class="data-grid-row-changed-tooltip">' +
                    $.mage.__('Record contains unsaved changes') + '</span></span></td>',
                td_all: '<td><label class="admin__field-label admin__field-label-vertical">' +
                    $.mage.__('All in Column') + '</label>',
                buttonApply: '<td class="data-grid-actions-cell"><button class="action-default" type="button" disabled><span>'
                    + $.mage.__('Apply') + '</span></button></td>'
            }
        },

        _create: function () {
            this._EventListener();
        },

        /**
         * manager event of module
         */
        _EventListener: function () {
            var $widget = this;
            this.element.on('mouseenter', 'td', function (event) {
                $widget._onClick($(this));
            });

            this.element.on('change', 'td.ids input[type="checkbox"]', function (e) {
                $widget._checkboxClick($(this))
            });

            this.element.on('click', '.bss-edit-actions .action-tertiary, .bss-edit-all-actions .action-tertiary', function () {
                $widget.clearAll()
            });

            this.element.on('click', '.bss-edit-actions .action-primary, .bss-edit-all-actions .action-primary', function () {
                $widget._save($(this));
            });

            this.element.on('change', '.bss-row-edit-all input, .bss-row-edit-all select, .bss-row-edit-all textarea', function (e) {
                if (!$(this).hasClass('has-changed')) {
                    $(this).addClass('has-changed')
                }
                $(this).parents('tr').find('button.action-default').removeAttr('disabled');
            });

            this.element.on('click', '.bss-row-edit-all button.action-default', function (e) {
                $widget.applyAll($(this))
            });

            this.element.on('focusin', '.bss-row-edit input', function (e) {
                $(this).parent().addClass('_focus');
                if ($(this).parent().find('.mage-error').length) {
                    var message_error = $(this).parent().find('.mage-error').text();
                    if (message_error.trim() != '') {
                        $(this).parent().find('.admin__field-error').text(message_error)
                    }
                }
                if ($(this).parent().find('.admin__field-error').text() != '') {
                    $(this).parent().find('.admin__field-error').show()
                }
            });

            this.element.on('focusout', '.bss-row-edit input', function (e) {
                $(this).parent().removeClass('_focus');
                $(this).parent().find('.admin__field-error').hide()
            });

            // clear row edit when click outside grid product
            this.element.mouseup(function (e) {
                var container = $(this).find('table.data-grid').parent();
                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    $widget.clearAll()
                }
            });
        },

        /**
         * event click first row in grid
         */
        _onClick: function (elm) {

            var $widget = this,
                attr_sets = this.options.attr_sets;
            if (!$('.bss-row-edit').length && !elm.hasClass('ids')) {
                if (!elm.hasClass('thumbnail')) {
                    elm.unbind('click');
                }
                elm.on('click', function (e) {
                    if (!$(this).hasClass('actions')) {
                        if (!$.isEmptyObject(attr_sets) && $widget._checkCloumnEditexist($(this))) {
                            $widget.element.find('table').addClass('_in-edit');
                            $widget.element.find('table').find('td.ids input[type="checkbox"]:checked').trigger('click');
                            if (addCss == false) {
                                setTimeout(function () {
                                    addCss = true;
                                    var height = $('._in-edit[data-role="grid"] thead').height();
                                    $('head').append('<style>._in-edit thead:before{height:' + height + 'px}</style>');
                                }, 1000);
                            }
                            if (!$(this).parent().find('td.ids input[type="checkbox"]').is(':checked')) {
                                $(this).parent().find('td.ids input[type="checkbox"]').trigger('click');
                            }
                            if ($widget.options.is_single_edit_field == '1') {
                                $widget.builRow($(this).parent(), true, $(this).attr('class'));
                            } else {
                                $widget.builRow($(this).parent(), true);
                            }
                            $widget.builFieldCalendar()
                        }
                    }
                })
            }
        },

        /**
         * save data
         */
        _save: function (elm) {
            var $widget = this, productId, url, validate_field, data = {};
            validate_field = $widget.isValidFields();
            if (validate_field) {
                $('#anchor-content .bss-message').remove();
                $('.bss-row-edit').each(function () {
                    var productId = $(this).find('.data-grid-checkbox-cell.ids input[type="checkbox"]').val();
                    $(this).find('td input,td select, td textarea').each(function () {
                        if ($(this).attr('type') == 'checkbox') {
                            return true;
                        }
                        var key = 'items[' + productId + '][' + $(this).attr('name') + ']';
                        data[key] = $(this).val();
                    })
                });

                data['form_key'] = $widget.element.find('input[name="form_key"]').first().val();
                data['store_id'] = $widget.element.find('div[data-part="filter-form"] select[name="store_id"]').first().val();
                $widget.element.find('.admin__data-grid-loading-mask').show();

                url = elm.parents('.bss-edit-all-actions').length ? $widget.options.url_save_multiples : $widget.options.url_save;

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        var data = {
                            status: response.status,
                            message: response.message
                        };
                        var html = $widget._getMessage(data);
                        $(html).insertBefore($widget.element.find('table'));
                        if (response.status == 'success') {
                            window.hasRefresh = true;
                            var skus_haschange = response.skus_haschange;
                            if (!$.isEmptyObject(skus_haschange)) {
                                _.each(skus_haschange, function (skus) {
                                    var notice_message = $.mage.__('SKU for product ' + skus['name'] + ' has been changed to ' + skus['sku'] + '.');
                                    var data = {
                                        status: 'notice',
                                        message: notice_message
                                    }
                                    $widget.element.find('table').parent().prepend($widget._getMessage(data));
                                });
                            }
                            // $('table.data-grid thead th').unbind();
                            $widget._saveData(skus_haschange);
                            $widget.clearAll()
                        }
                    },
                    complete: function () {
                        $widget.element.find('.admin__data-grid-loading-mask').hide();
                        setTimeout(function () {
                            $('#anchor-content .bss-message').remove();
                        }, 10000)
                    },
                    error: function (xhr, status, error) {
                        // window.location.href = '';
                    }
                })
            }

        },

        _getMessage :function (data) {
            var html = '<div class="bss-message">';
            html += '<div class="messages">';
            html += '<div class="message message-' + data.status + ' ' + data.status + '">';
            if (data.status == 'error') {
                html += '<strong>' + $t('There are ') + data.counterror + $t(' messages requires your attention.') + '</strong>';
                html += $t('Please make corrections to the errors in the table below and re-submit.');
            } else {
                html += '<div>' + data.message + '</div>'
            }
            html += '</div>';
            html += '</div>';
            html += '</div>';
            return html;
        },

        /**
         * set data to filed after save success
         */
        _saveData: function (skus_haschange) {
            var $widget = this, productId, arr_info, status_product, row_setdata, index, filed, value, symbol,
                symbol_default, salable_quantity, qty;
            var check = 0, symbol_decimal = '.', symbol_thousand = ',';
            $('.bss-row-edit').each(function () {
                productId = $(this).find('.data-grid-checkbox-cell.ids input[type="checkbox"]').val();
                row_setdata = $(this).siblings('.bss-row-' + productId + '');
                arr_info = $(this).find('.bss_attribute_set_id input[type="checkbox"]').val().split('-');
                status_product = arr_info[2];
                if (arr_info[3].indexOf(',') !== -1) {
                    symbol_decimal = ',';
                    symbol_thousand = '.';
                }
                var qty_items = '.bss-row-' + productId + ' input[name=qty]';
                $(this).find('td').each(function () {
                    if ($(this).hasClass('ids') || $(this).hasClass('bss_attribute_set_id') || !$(this).find('input, select, textarea').length) {
                        return true;
                    }
                    index = $(this).index();
                    filed = $(this).find('input, select, textarea');
                    if (filed.is('input') || filed.is('textarea')) {
                        value = filed.val();
                        if (filed.hasClass('price') && value != '') {
                            if (filed.hasClass('price-symbol') && $widget.options.symbol != '') {
                                symbol = $(this).find('.bss-price-symbol').text();
                                value = $widget.parseNumber(value);
                                value = $widget.formatMoney(value, 2, '', symbol_thousand, symbol_decimal);
                                if (parseInt($widget.options.position_symbol) === 0) {
                                    value = symbol + value;
                                } else {
                                    value = symbol + value;
                                }
                            } else {
                                value = $widget.formatMoney(parseFloat(value), 4);
                            }
                        }
                        if ((filed.attr('name') == 'qty')) {
                            value = parseFloat(value).toFixed(4);
                            salable_quantity = parseFloat(value).toString();
                            if (value == 'NaN') {
                                value = '';
                                salable_quantity = '';
                            }
                        }
                        if (filed.attr('name') == 'sku' && !$.isEmptyObject(skus_haschange) && typeof skus_haschange[productId] !== 'undefined') {
                            value = skus_haschange[productId]['sku'];
                        }
                    } else if (filed.is("select")) {
                        if (filed.is("select[multiple]")) {
                            var values = [];
                            filed.find('option:selected').each(function () {
                                values.push($(this).text())
                            });
                            value = values.toString()
                        } else {
                            value = filed.find('option:selected').text();
                            if (filed.attr('name') == 'status') {
                                status_product = filed.val();
                            }
                        }
                    }

                    row_setdata.find('td').eq(index).find('div').text(value)

                })
                if (($(this).find('.salable_quantity').length > 0) && ($.isNumeric(row_setdata.find('td.salable_quantity span').text()) == true) && (salable_quantity != '')) {
                    if (status_product == 1) {
                        row_setdata.find('td.salable_quantity span').text(salable_quantity)
                    } else {
                        row_setdata.find('td.salable_quantity span').text(0)
                    }
                }
            })
        },

        /**
         * check validate field date
         */
        isValidDate: function (dateString) {
            if (dateString == '') {
                return true;
            }
            if (!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(dateString)) {
                return false;
            }
            var parts = dateString.split("/");
            var day = parseInt(parts[1], 10);
            var month = parseInt(parts[0], 10);
            var year = parseInt(parts[2], 10);

            if (year < 1000 || year > 3000 || month == 0 || month > 12) {
                return false;
            }
            var monthLength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

            if (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0)) {
                monthLength[1] = 29;
            }
            return day > 0 && day <= monthLength[month - 1];
        },

        /**
         * check validate field
         */
        isValidFields: function () {
            var $widget = this,
                result = true;

            $('.bss-row-edit').find('td input,td select').each(function () {
                if (!$.validator.validateSingleElement($(this)) || ($(this).hasClass('bss-date') && !$widget.isValidDate($(this).val()))) {
                    if ($(this).hasClass('bss-date')) {
                        $(this).parent().find('.admin__field-error').text($.mage.__('Please enter a valid date.'))
                    }
                    $(this).parent().addClass('_error')
                    result = false;
                }
            });
            if (!result) {
                var data = {
                    status: 'error',
                    counterror: $('.bss-row-edit ._error').length
                },
                html = $widget._getMessage(data);
                $('#anchor-content .bss-message').remove();
                $widget.element.find('table').parent().prepend(html)
            }
            return result;
        },

        /**
         * check column allow for edit
         */
        _checkCloumnEditexist: function (elm) {
            var $widget = this,
                attr_sets = this.options.attr_sets,
                product,
                product_type,
                column_name,
                attribute_set_id,
                isallow,
                result = false;
            product = elm.parent().find('.bss_attribute_set_id input[type="checkbox"]').val().split('-')
            product_type = product[1];
            attribute_set_id = product[0];

            if ($widget.options.is_single_edit_field == '1') {
                if (typeof attr_sets[attribute_set_id][elm.attr('class')] !== 'undefined') {
                    isallow = $widget.isAllowInputEditwithTypeProduct(attr_sets[attribute_set_id], product_type, elm.attr('class'));
                    if (isallow) {
                        result = true;
                    }
                }
            } else {
                elm.parent().find('td').each(function () {
                    column_name = $(this).attr('class');
                    if (typeof attr_sets[attribute_set_id][column_name] != 'undefined') {
                        isallow = $widget.isAllowInputEditwithTypeProduct(attr_sets[attribute_set_id], product_type, column_name);
                        if (isallow) {
                            result = true;
                        }
                    }
                })

            }
            return result;
        },

        /**
         * create or remove row edit when checkbox
         */
        _checkboxClick: function (checkbox) {
            var $widget = this, parent_elm, productId;
            parent_elm = checkbox.parents('tr');
            productId = parent_elm.find('.ids input[type="checkbox"]').val();
            if ($('.bss-row-edit').length > 0) {
                if (parent_elm.hasClass('bss-row-edit')) {
                    if (!checkbox.is(':checked')) {
                        if ($('.bss-row-edit').length == 1) {
                            $widget.removeRow(parent_elm, true)
                        } else {
                            if ($('.bss-row-edit').length == 2) {
                                $('.bss-row-edit-all, .bss-edit-all-actions').remove()
                                $widget.removeRow(parent_elm, false)
                                $widget.addRowAction($('.bss-row-edit').first().next())
                            } else {
                                $widget.removeRow(parent_elm, false)
                            }
                        }
                    }
                } else {
                    if ($widget.options.is_mass_edit == '1' && $widget.options.is_single_edit_field != '1') {
                        if (checkbox.is(':checked')) {
                            $('.bss-edit-actions').remove();
                            $widget.builRow(parent_elm, false)
                            if (!$('.bss-row-edit-all').length) {
                                $widget.addRowActionAll($widget.element.find('table'))
                                $widget.builRowAllInColumn(parent_elm, false)
                            }
                            $widget.builFieldCalendar()
                        }
                    }
                }
            }
        },

        /**
         * add button action
         */
        addRowAction: function (row) {
            var $widget = this,
                row_actions = mageTemplate('#bss-row-actions'),
                html = '';

            html = row_actions();
            $(html).insertBefore(row);
        },

        /**
         * add button action "all"
         */
        addRowActionAll: function (table) {
            var $widget = this,
                row_actions = mageTemplate('#bss-edit-all-actions'),
                html = '';

            html = row_actions();
            $(html).insertBefore(table);
        },

        /**
         * check input allow product type
         */
        isAllowInputEditwithTypeProduct: function (attr, product_type, column_name) {
            var $widget = this, no_allow_type_product;

            if (typeof attr[column_name] !== 'undefined' && typeof attr[column_name]['no_allow_type_product'] !== 'undefined') {
                no_allow_type_product = attr[column_name]['no_allow_type_product'];
                if (no_allow_type_product.trim() != '') {
                    if (no_allow_type_product.indexOf(product_type) > -1) {
                        return false;
                    }
                }
            }
            return true;
        },

        /**
         * render row html
         */
        builRow: function (row, action = false, single_field_name = '') {
            var $widget = this,
                attr_sets = this.options.attr_sets,
                productId, product, product_type, attribute_set_id, column_name, tr_edit, isallow;

            productId = row.find('.ids input[type="checkbox"]').val();
            tr_edit = mageTemplate(this.options.templates.tr_edit, {data: {productId: productId}});
            $(tr_edit).insertBefore(row);
            row.find('td').each(function () {
                product = row.find('.bss_attribute_set_id input[type="checkbox"]').val().split('-')
                product_type = product[1];
                attribute_set_id = product[0];
                column_name = $(this).attr('class');
                isallow = $widget.isAllowInputEditwithTypeProduct(attr_sets[attribute_set_id], product_type, column_name);
                if (typeof attr_sets[attribute_set_id][column_name] !== 'undefined' && single_field_name != '' && single_field_name == column_name && isallow) {
                    $('.bss-row-edit-' + productId + '').append('<td>' + $widget.builField(attr_sets[attribute_set_id][column_name], $(this), false) + '</td>');

                } else if (typeof attr_sets[attribute_set_id][column_name] !== 'undefined' && single_field_name == '' && isallow) {
                    $('.bss-row-edit-' + productId + '').append('<td>' + $widget.builField(attr_sets[attribute_set_id][column_name], $(this), false) + '</td>');
                } else {
                    if ($(this).hasClass('actions')) {
                        $('.bss-row-edit-' + productId + '').append($widget.options.templates.td_action);
                    } else {
                        $(this).clone().appendTo('.bss-row-edit-' + productId + '')
                    }
                }
            })
            if (action) {
                $widget.addRowAction(row)
            }
            row.addClass('bss-row-hide bss-row-' + productId + '');
            $('.bss-row-edit').find('.price-symbol').css('padding-left', ($('.bss-price-symbol').width() + 6) + 'px')
        },

        /**
         * render filed "all" html
         */
        builRowAllInColumn: function (row, action = false) {
            var $widget = this,
                attr_sets = this.options.attr_sets,
                productId,
                columns_name = {},
                column_name, td_all;

            $($widget.options.templates.tr_all).insertBefore(row.parents('table').find('tbody tr').first());
            _.each(attr_sets, function (attribute_set) {
                _.each(attribute_set, function (attr) {
                    if (typeof columns_name[attr['attribute_code']] == 'undefined') {
                        columns_name[attr['attribute_code']] = attr;
                    }
                })
            });
            row.find('td').each(function () {
                column_name = $(this).attr('class');
                if (typeof columns_name[column_name] !== 'undefined') {
                    td_all = $widget.options.templates.td_all + $widget.builField(columns_name[column_name], $(this), true) + '</td>';
                    $('.bss-row-edit-all').append(td_all);
                } else {
                    if ($(this).hasClass('actions')) {
                        $('.bss-row-edit-all').append($widget.options.templates.buttonApply);
                    } else {
                        $(this).clone().appendTo('.bss-row-edit-all').html('')
                    }
                }
            });
            $('.bss-row-edit-all').find('.price-symbol').css('padding-left', ($('.bss-price-symbol').width() + 6) + 'px')
        },

        /**
         * render filed html
         * @param {Object} json_info
         * @param {Object} field
         * @param {bool} allincolumn
         * @return {String}
         */
        builField: function (json_info, field, allincolumn) {
            var $widget = this,
                attribute_code = json_info['attribute_code'],
                type_input = json_info['frontend_input'],
                class_field = '',
                symbol = '',
                value = '',
                html = '';

            if (!allincolumn) {
                value = $(field).text()
            }

            if (json_info['is_required'] == 1) {
                class_field += ' required-entry';
            }
            if (json_info['frontend_input'] == 'price') {
                class_field += ' validate-zero-or-greater price';
                type_input = 'text';
                if (!allincolumn) {
                    symbol = $widget.options.symbol = $widget.getSymbol(value);
                }
                if ($.trim(value) != '') {
                    if (symbol != '') {
                        $widget.options.position_symbol = value.indexOf(symbol);
                    }
                    value = $(field).text().replace(symbol, '');
                    value = $widget.parseNumber(value)
                }
            }

            if (json_info['frontend_input'] == 'date') {
                class_field += ' bss-date';
                type_input = 'text';
            }


            if (attribute_code == 'qty') {
                class_field += ' validate-number';
                type_input = 'text';
                if (value != '') {
                    value = $widget.parseNumber(value);
                }
                if (!value) {
                    value = '';
                }
            }
            if (attribute_code && attribute_code != '') {
                html = mageTemplate('#bss-field-text', {
                    data: {
                        type_input: type_input,
                        name: attribute_code,
                        class: class_field,
                        id: $widget.generateID(),
                        value: value,
                        symbol: symbol
                    }
                });
                if (type_input == 'boolean' || type_input == 'select') {
                    html = mageTemplate('#bss-field-select', {
                        data: {
                            name: attribute_code,
                            class: class_field,
                            id: $widget.generateID(),
                            options: $widget.options.attrs_options[attribute_code],
                            selected: value
                        }
                    });
                }

                if (type_input == 'multiselect') {
                    html = mageTemplate('#bss-field-select-multiple', {
                        data: {
                            name: attribute_code,
                            class: class_field,
                            id: $widget.generateID(),
                            options: $widget.options.attrs_options[attribute_code],
                            selected: value
                        }
                    });
                }

                if (type_input == 'textarea') {
                    html = mageTemplate('#bss-field-text-area', {
                        data: {
                            type_input: type_input,
                            name: attribute_code,
                            class: class_field,
                            id: $widget.generateID(),
                            value: value,
                        }
                    });
                }
            }
            return html;
        },

        /**
         * get symbol
         *
         * @param {string} pricehtml
         * @returns {string}|bool
         */
        getSymbol: function (pricehtml) {
            var $widget = this,
                symbol = '',
                convertHtml;

            convertHtml = pricehtml.replace(/\d+/g, '').replace(/\./g,'').replace(/\,/g,'');
            _.each($widget.options.symbols, function (symbol_detail) {
                var displaySymbol = symbol_detail['displaySymbol'].replace(/\d+/g, '').replace(/\./g,'').replace(/\,/g,'');
                if ($.trim(convertHtml) == $.trim(displaySymbol)) {
                    symbol = symbol_detail['displaySymbol'];
                    return false;
                }
            })
            if (symbol == "") {
                symbol = convertHtml.trim();
            }

            return symbol;
        },

        /**
         * Create calendar
         */
        builFieldCalendar: function () {
            $('input.bss-date').calendar({
                dateFormat: 'mm/dd/yyyy',
                changeYear: true,
                changeMonth: true,
                buttonText: $.mage.__('Select Date'),
            });
        },

        /**
         * Remove a row edit
         */
        removeRow: function (row, action) {
            var $widget = this,
                tr_normal,
                productId;
            productId = $(row).find('td.ids input[type="checkbox"]').val();
            $('.bss-row-' + productId + '').find('.ids input[type="checkbox"]').trigger('click')
            $('.bss-row-' + productId + '').removeClass('bss-row-hide bss-row-' + productId + '')
            if (action) {
                row.parents('table').removeClass('_in-edit')
                row.siblings('.bss-edit-actions').remove()
            }
            row.remove();
        },

        /**
         * Set value filed from field all
         */
        applyAll: function (button) {
            this.element.find('.bss-row-edit-all td').each(function () {
                var index = $(this).index()
                var value = $(this).find('input, select, textarea').val()
                var has_changed = $(this).find('input, select, textarea').hasClass('has-changed')
                if ($(this).find('input, select, textarea').length && value != '' && value !== 'undefined' && has_changed) {
                    $('.bss-row-edit').each(function () {
                        $(this).find('td').eq(index).find('input, select, textarea').val(value)
                    })
                }
            })
            button.attr('disabled', 'disabled');
        },

        /**
         * Remove all function edit row
         */
        clearAll: function (container) {
            if ($('.bss-row-edit').length) {
                $('.bss-row-edit-all, .bss-edit-all-actions, .bss-edit-actions, .bss-row-edit').remove()
                $('._in-edit').removeClass('_in-edit');
                $('.bss-row-hide').each(function () {
                    var productId = $(this).find('td.ids input[type="checkbox"]').val()
                    $(this).removeClass('bss-row-hide bss-row-' + productId + '')
                })
                this.element.find('td.ids input[type="checkbox"]:checked').trigger('click');
            }
        },

        /**
         * Random Id of Field
         * @return {String}
         */
        generateID: function () {
            var id = "",
                length = 7,
                possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

            for (let i = 0; i < length; i++) {
                id += possible.charAt(Math.floor(Math.random() * possible.length))
            }

            return id;
        },

        parseNumber: function (value) {
            var isDot, isComa;

            if (typeof value !== 'string') {
                return parseFloat(value);
            }
            isDot = value.indexOf('.');
            isComa = value.indexOf(',');

            if (isDot !== -1 && isComa !== -1) {
                if (isComa > isDot) {
                    value = value.replace('.', '').replace(',', '.');
                } else {
                    value = value.replace(',', '');
                }
            } else if (isComa !== -1) {
                value = value.replace(',', '.');
            }
            value = value.replace(/\s/g, '');

            return parseFloat(value);
        },

        /**
         * Converting money
         * @param {Int} number
         * @param {Int} places
         * @param {String} symbol
         * @param {Int} thousand
         * @param {Int} decimal
         * @return {String}
         */
        formatMoney: function (number, places, symbol = '', thousand, decimal) {
            number = number || 0;
            places = !isNaN(places = Math.abs(places)) ? places : 2;
            symbol = symbol != '' ? symbol : "";
            thousand = thousand || ",";
            decimal = decimal || ".";
            var negative = number < 0 ? "-" : "",
                i = parseInt(number = Math.abs(+number || 0).toFixed(places), 10) + "",
                j = (j = i.length) > 3 ? j % 3 : 0;
            return symbol + negative + (j ? i.substr(0, j) + thousand : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousand) + (places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : "");
        }
    });

    return $.mage.BssProductGridInlineEditor;
});
