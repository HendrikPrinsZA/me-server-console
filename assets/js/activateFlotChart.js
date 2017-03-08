var activateFlotChart = function(params) {

    var id       = params.id,
        endpoint = params.endpoint,
        interval = params.interval,
        maximum  = params.maximum;


    var container = $('#'+id);

    // Determine how many data points to keep based on the placeholder's initial size;
    // this gives us a nice high-res plot while avoiding more than one point per pixel.
    // var maximum = container.outerWidth() / 2 || 300;
    var maximum = 100;

    
    function getRandomData(arr, force_y) {

        if (arr.length) {
            arr = arr.slice(1);
        }

        while (arr.length < maximum) {
            var previous = arr.length ? arr[arr.length - 1] : 50;
            var y = force_y ? force_y : previous + Math.random() * 10 - 5;
            arr.push(y < 0 ? 0 : y > 100 ? 100 : y);
        }

        // zip the generated y values with the x values
        var res = [];
        for (var i = 0; i < arr.length; ++i) {
            res.push([i, arr[i]])
        }

        return res;
    }

    series = [{
        data: [],
        lines: {
            fill: true
        }
    }];
    //
    var plot = $.plot(container, series, {
        grid: {
            borderWidth: 1,
            minBorderMargin: 20,
            labelMargin: 10,
            backgroundColor: {
                colors: ["#fff", "#e4f4f4"]
            },
            margin: {
                top: 8,
                bottom: 20,
                left: 20
            },
            markings: function(axes) {
                var markings = [];
                var xaxis = axes.xaxis;
                for (var x = Math.floor(xaxis.min); x < xaxis.max; x += xaxis.tickSize * 2) {
                    markings.push({
                        xaxis: {
                            from: x,
                            to: x + xaxis.tickSize
                        },
                        color: "rgba(232, 232, 255, 0.2)"
                    });
                }
                return markings;
            }
        },
        xaxis: {
            min: 0,
            max: 50,
            tickFormatter: function() {
                return "";
            }
        },
        yaxis: {
            min: -1,
            max: 3,
            ticks: 20
        },
        legend: {
            show: false,
            noColumns: 2
        }
    });
    
    // Uses series[0] && series[1]
    function recursiveAjaxCall_0(params) {

        var startTime = +new Date();
        $.ajax({type: 'GET', url: params.endpoint, data: params.extraParams, dataType: 'json'}).success(function(reply) {

        }).fail(function(reply) {

        }).always(function(reply) {

            var endTime = +new Date();
            reply.requestTime = (endTime - startTime) / 100;

            params.series[1].data.push([(params.series[1].data.length - 1) , reply.requestTime]);

            plot.setData(params.series);
            plot.draw();

            params.count++;
            if (params.count > (params.max - 1)) {
                
                if (params.series[0].data.length >= params.max && params.series[1].data.length >= params.max && params.series[2].data.length >= params.max && params.series[3].data.length >= params.max) {
                    if (typeof params.callback === "function") {
                        params.callback();
                    }
                }

            } else {
                recursiveAjaxCall_0(params)
            }
        });
    }

    function recursiveAjaxCall_0_default(params) {

        window.setTimeout(function() {

            params.series[0].data.push([(params.series[0].data.length - 1) , params.delay]);

            plot.setData(series);
            plot.draw();

            params.count++;
            if (params.count > (params.max - 1)) {
                
                if (params.series[0].data.length >= params.max && params.series[1].data.length >= params.max && params.series[2].data.length >= params.max && params.series[3].data.length >= params.max) {
                    if (typeof params.callback === "function") {
                        params.callback();
                    }
                }

            } else {
                recursiveAjaxCall_0_default(params)
            }

        }, (params.delay * 100));

    }

    // Uses series[2] && series[3]
    function recursiveAjaxCall_1(params) {

        var startTime = +new Date();
        $.ajax({type: 'GET', url: params.endpoint, data: params.extraParams, dataType: 'json'}).success(function(reply) {

        }).fail(function(reply) {

        }).always(function(reply) {
            var endTime = +new Date();
            reply.requestTime = (endTime - startTime) / 100;

            series[3].data.push([(series[3].data.length - 1) , reply.timeTaken]);

            plot.setData(series);
            plot.draw();

            params.count++;
            if (params.count > (params.max - 1)) {

                if (params.series[0].data.length >= params.max && params.series[1].data.length >= params.max && params.series[2].data.length >= params.max && params.series[3].data.length >= params.max) {
                    if (typeof params.callback === "function") {
                        params.callback();
                    }
                }

            } else {
                recursiveAjaxCall_1(params)
            }
        });
    }

    function recursiveAjaxCall_1_default(params) {

        window.setTimeout(function() {

            params.series[2].data.push([(params.series[2].data.length - 1) , params.delay]);

            plot.setData(series);
            plot.draw();

            params.count++;
            if (params.count > (params.max - 1)) {
                
                if (params.series[0].data.length >= params.max && params.series[1].data.length >= params.max && params.series[2].data.length >= params.max && params.series[3].data.length >= params.max) {
                    if (typeof params.callback === "function") {
                        params.callback();
                    }
                }

            } else {
                recursiveAjaxCall_1_default(params)
            }

        }, (params.delay * 100));

    }


    series = [{
        data: [],
        label: 'Ping Default',
        
        color: "#8cff66",
        lines: {
            show: true
        }
    },{
        data: [],
        label: 'Ping Value',
        color: "#208000",
        lines: {
            show: true
        }
    },{
        data: [],
        label: 'MySQL Connect Default',
        color: "#b3b3ff",
        lines: {
            show: true
        }
    },{
        data: [],
        label: 'MySQL Connect Value',
        color: "#0000ff",
        lines: {
            show: true
        }
    }];

    recursiveAjaxCall_0({
        count: 0,
        max: 50,
        endpoint: App.api + '/performance/pingtest',
        extraParams: params.extraParams,
        series: series,
        callback: params.callback
    });

    recursiveAjaxCall_0_default({
        count: 0,
        max: 50,
        series: series,
        delay: 0.4,
        callback: params.callback
    });

    recursiveAjaxCall_1({
        count: 0,
        max: 50,
        endpoint: App.api + '/performance/phpmysqltest',
        extraParams: params.extraParams,
        series: series,
        callback: params.callback
    });

    recursiveAjaxCall_1_default({
        count: 0,
        max: 50,
        series: series,
        delay: 0.5,
        callback: params.callback
    });


};