humhub.module('analytics.chart', function (module, require, $) {
    module.initOnPjaxLoad = true;

    const init = function () {
        $(function () {
            $.each(module.config.dataTypes, function (dataKey, dataType) {
                setTimeout(function () {
                    $.ajax({
                        method: "POST",
                        dataType: 'json',
                        url: module.config.chartDataUrl,
                        data: {
                            'startDate': module.config.startDate,
                            'endDate': module.config.endDate,
                            'dataType': dataType,
                            'spaceGuids': module.config.spaceGuids,
                            'userGuids': module.config.userGuids,
                        },
                        key: dataType
                    })
                        .done(function (data) {
                            const id = '#' + module.config.idPrefix + this.key;
                            $(id).removeClass('analytics-chart-loader');
                            const chart = new ApexCharts(document.querySelector(id), data);
                            chart.render();
                        });
                }, Number(dataKey) * 1000);
            });
        });
    };

    /**
     * Outside of init some modules may not be available, so make sure to follow one of the other options
     * when using requiring a js module included in CoreAssetBundle.
     */

    module.export({
        init: init
    });
});