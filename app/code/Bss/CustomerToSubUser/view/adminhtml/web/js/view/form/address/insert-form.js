define([
    'Magento_Customer/js/form/components/insert-form'
], function (InsertAddressForm) {
    'use strict';

    return InsertAddressForm.extend({
        /**
         * Invokes initialize method of parent class,
         * contains reinit the exports properties to change the parent_id request field
         * vi: Thay đổi phần exports sang customer address form data biến parent id
         * nếu customer hiện tại là sub-user thì đẩy company account id thành parent id thay vì customer id là parent id
         *
         * @returns {*}
         */
        initialize: function () {
            var parentIdRequestField = 'customer_address_form.customer_address_form_data_source:data.parent_id';

            this._super();

            if (this.companyAccountId) {
                this.exports.companyAccountId = parentIdRequestField;
                this.initLinks();
            }

            return this;
        }
    });
});
