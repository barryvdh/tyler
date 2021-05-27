define([

   'underscore',

   'uiRegistry',

   'Magento_Ui/js/form/element/select',

   'Magento_Ui/js/modal/modal',
   
   'jquery',

   'domReady!',

   'ko'

], function (_, uiRegistry, select, modal, $, ko) {


   'use strict';

   return select.extend({


       initialize: function () {
    
           this._super();
           this.fieldDepend(this.value());
           return this;

       },

       onUpdate: function (value)
       {

         if (value === '1') {

            $('div[data-index="assign_products"]').hide(); //fieldset
            
          }

         else {
            $('div[data-index="assign_products"]').show();//fieldset
         }
         return this._super();

       },

        fieldDepend: function (value)
        {
    
            $(document).ready(function(){

                if (value === '1') {
                  
                    var i = setInterval(function(){
                        if (document.getElementById('tab_related_products')) {
                          $('div[data-index="assign_products"]').hide(); //fieldset 
                          clearInterval(i);
                        } 
                    });
                }
            });
        }
   });

});
