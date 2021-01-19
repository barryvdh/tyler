define([
    'ko',
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'chartJs',
    'mage/translate'
], function (ko, $, SalesOrderReport, Chart, $t) {
    'use strict';

    return SalesOrderReport.extend({
        defaults: {
            chart: null,
            imports: {
                orderRemain: '${ $.provider }:data.order_restriction.order_remain'
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();

            this._chartInitialize();

            return this;
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {SalesOrderReport} Chainable.
         */
        initObservable: function () {
            this._super();

            this.observe('orderRemain');

            return this;
        },

        /**
         * Init chartjs
         *
         * @private
         */
        _chartInitialize: function () {
            var self = this;

            ko.bindingHandlers.barChart = this._koBinding(self._getChartSettings(self.value()));
            ko.bindingHandlers.doughnutChart = this._koBinding(self._doughnutChartSettings(
                {
                    datasets: [
                        {
                            data: self.orderRemain(),
                            backgroundColor: [
                                'rgb(255, 99, 132)',
                                'rgb(57,176,0)'
                            ]
                        }
                    ],
                    labels: [
                        $t('Used'),
                        $t('Remain')
                    ]
                }
            ));
        },

        /**
         * Ko binding
         *
         * @param {Object} settings
         * @returns {Object}
         * @private
         */
        _koBinding: function (settings) {
            return {
                /**
                 * This will be called when the binding is first applied to an element
                 * Set up any initial state, event handlers, etc. here
                 *
                 * @param {Object} el - The DOM element involved in this binding
                 * @param {Function} valueAccessor - A JavaScript function
                 * that you can call to get the current model property that is involved in this binding.
                 * Call this without passing any parameters (i.e., call valueAccessor())
                 * to get the current model property value. To easily accept both observable and plain values,
                 * call ko.unwrap on the returned value.
                 * @param {Function} allBindingsAccessor
                 */
                init: function (el, valueAccessor, allBindingsAccessor) {
                    var newChart, $element;

                    // $(el).chosen(ko.unwrap(valueAccessor()));
                    ko.utils.domNodeDisposal.addDisposeCallback(el, function () {
                        $(el).chart.destroy();
                        delete $(el).chart;
                    });

                    newChart = new Chart($(el), settings);
                    $element = $(el)[0];

                    if ($element.chart) {
                        $element.chart.destroy();
                        delete $element.chart;
                    }

                    $element.chart = newChart;
                    // self.chart = newChart;
                }
            };
        },

        /**
         * Get doughnutChart settings
         *
         * @param {Object} data
         * @returns {Object}
         * @private
         */
        _doughnutChartSettings: function (data) {
            return {
                type: 'doughnut',
                data: data,
                options: {
                    legend: {
                        onClick: this.handleChartLegendClick,
                        position: 'bottom'
                    }
                }
            };
        },

        /**
         * Get Chartjs configuration
         *
         * @returns {Object} chart object configuration
         * @private
         */
        _getChartSettings: function (data) {
            return {
                type: 'bar',
                data: {
                    datasets: [{
                        data: data,
                        backgroundColor: '#f1d4b3',
                        borderColor: '#eb5202',
                        borderWidth: 1,
                        label: $t('Number Quantity')
                    }]
                },
                options: {
                    legend: {
                        onClick: this.handleChartLegendClick,
                        position: 'bottom'
                    },
                    scales: {
                        xAxes: [{
                            offset: true,
                            type: 'time',
                            time: {
                                unit: 'day',
                                displayFormats: {
                                    day: 'MMMM DD'
                                }
                            },
                            ticks: {
                                autoSkip: true,
                                source: 'data'
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0
                            }
                        }]
                    }
                }
            };
        },

        /**
         * @public
         */
        handleChartLegendClick: function () {
            // don't hide dataset on clicking into legend item
        }
    });
});
