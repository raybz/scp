;

/**
 *
 * @param args
 * @constructor
 */
function Hcharts(args) {
    this.api = args.api;
    this.container = args.container;
    this.title = args.title;
    this.subtitle = args.subtitle;
    this.param = args.param;
}

Hcharts.prototype = {
    //条形图
    showBar: function () {
        var _this = this;
        // console.log(this.param);
        $.post(this.api, this.param, function (data) {
            Highcharts.chart(_this.container, {
                chart: {
                    type: 'bar'
                },
                title: {
                    text: _this.title.text + data.data.title,
                    align: _this.title.align,
                    x: _this.title.x
                },
                subtitle: {
                    text: _this.subtitle
                },
                xAxis: {categories: data.data.xAxis},
                yAxis: {},
                tooltip: {
                    shared: true
                },
                plotOptions: {
                    bar: {
                        dataLabels: {
                            enabled: true
                        }
                    }
                },
                series: data.data.series
            });
        }, 'json');
    },
    //饼图
    showPie: function () {
        var _this = this;
        $.getJSON(this.api, this.param, function (data) {
            Highcharts.chart(_this.container, {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: _this.title.text,
                    align: _this.title.align,
                    x: _this.title.x
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: data.data.series
            });

        });
    },
    //折线图
    showLine: function () {
        var _this = this;
        $.post(this.api, this.param, function (data) {
            Highcharts.chart(_this.container, {
                title: {
                    text: _this.title.text + data.data.title,
                    align: _this.title.align,
                    x: _this.title.x
                },
                subtitle: {
                    text: _this.subtitle
                },
                xAxis: {categories: data.data.xAxis},
                yAxis: {},
                tooltip: {
                    shared: true
                },
                plotOptions: {
                    spline: {
                        marker: {
                            enabled: false
                        }
                    }
                },
                series: data.data.series
            });
        }, 'json');
    },
    //曲线图
    showSpline: function () {
        var _this = this;
        $.getJSON(this.api, this.param, function (data) {
            Highcharts.chart(_this.container, {
                chart: {
                    type: 'spline'
                },
                title: {
                    text: _this.title.text + data.data.title,
                    align: _this.title.align,
                    x: _this.title.x
                },
                subtitle: {
                    text: _this.subtitle
                },
                xAxis: {categories: data.data.xAxis},
                yAxis: {},
                tooltip: {
                    shared: true
                },
                plotOptions: {
                    spline: {
                        marker: {
                            enabled: data.data.marker
                        }
                    }
                },
                series: data.data.series
            });
        });
    },
    //区域图
    showAreaSpline: function () {
        var _this = this;
        $.post(this.api, this.param, function (data) {
            Highcharts.chart(_this.container, {
                chart: {
                    type: 'areaspline'
                },
                title: {
                    text: _this.title.text + data.data.title,
                    align: _this.title.align,
                    x: _this.title.x
                },
                xAxis: {
                    categories: data.data.xAxis
                },
                tooltip: {
                    shared: true
                },
                credits: {
                    enabled: false
                },
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.5,
                        marker: {
                            enabled: false
                        }
                    }
                },
                series: data.data.series
            });
        }, 'json');
    },
    //双轴 折线+柱状图
    showDualAxesLineColumn: function () {
        var _this = this;
        $.post(this.api, this.param, function (data) {
            Highcharts.chart(_this.container, {
                chart: {
                    zoomType: 'xy'
                },
                title: {
                    text: _this.title.text + data.data.title,
                    align: _this.title.align,
                    x: _this.title.x
                },
                subtitle: {
                    text: data.data.subtitle
                },
                xAxis: [{
                    categories: data.data.xAxis,
                    crosshair: true
                }],
                yAxis: [{ // Primary yAxis
                    labels: {
                        format: '{value} ' + data.data.series.right.unit
                    },
                    title: {
                        text: data.data.series.right.text
                    },
                    opposite: true
                }, { // Secondary yAxis
                    title: {
                        text: data.data.series.left.text
                    },
                    labels: {
                        format: '{value} ' + data.data.series.left.unit
                    }

                }],
                tooltip: {
                    shared: true
                },
                series: [{
                    name: data.data.series.left.name,
                    type: 'column',
                    yAxis: 1,
                    data: data.data.series.left.data
                }, {
                    name: data.data.series.right.name,
                    type: 'spline',
                    data: data.data.series.right.data
                }]
            });
        }, 'json');
    },
    //散点图
    showScatterPlot: function () {
        var _this = this;
        $.post(this.api, this.param, function (data) {
            Highcharts.chart(_this.container, {
                chart: {
                    type: 'scatter',
                    zoomType: 'xy'
                },
                title: {
                    text: _this.title.text + data.data.title,
                    align: _this.title.align,
                    x: _this.title.x
                },
                subtitle: {
                    text: data.data.subtitle
                },
                plotOptions: {
                    scatter: {
                        marker: {
                            radius: 6,
                            states: {
                                hover: {
                                    enabled: true,
                                    lineColor: 'rgb(100,100,100)'
                                }
                            }
                        },
                        states: {
                            hover: {
                                marker: {
                                    enabled: false
                                }
                            }
                        },
                        tooltip: {
                            headerFormat: '<b>{series.name}</b><br>',
                            pointFormat: '{point.x} ' + data.data.format.x + ', {point.y} ' + data.data.format.y
                        }
                    }
                },
                series: [{
                    name: data.data.series.left.name,
                    color: 'rgba(223, 83, 83, .5)',
                    data: data.data.series.left.data

                }, {
                    name: data.data.series.right.name,
                    color: 'rgba(119, 152, 191, .5)',
                    data: data.data.series.right.data,
                    visible: false
                }]
            });
        }, 'json');
    }
};

