var config = {
    config: {
        mixins: {
            'Magento_Customer/js/address/default-address': {
                'Bss_CustomerToSubUser/js/view/form/element/default-address-mixin': true
            },
            'Magento_Ui/js/form/element/abstract': {
                'Bss_CustomerToSubUser/js/view/form/element/abstract-mixin': true
            }
        }
    }
};
