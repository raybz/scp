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
        $.post(this.api, this.param, function(data) {
            Highcharts.chart(_this.container, {
                chart: {
                    type: 'bar'
                },
                title: {
                    text: _this.title.text+data.data.title,
                    align: _this.title.align,
                    x: _this.title.x
                },
                subtitle: {
                    text: _this.subtitle
                },
                xAxis: {categories: data.data.xAxis},
                yAxis: {
                },
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
                series:data.data.series
            });
        }, 'json');
    },
    //饼图
    showPie: function(){
        var _this = this;
        $.getJSON(this.api, this.param, function(data) {
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
    showLine: function() {
        var _this = this;
        $.post(this.api, this.param, function(data) {
            Highcharts.chart(_this.container, {
                title: {
                    text: _this.title.text+data.data.title,
                    align: _this.title.align,
                    x: _this.title.x
                },
                subtitle: {
                    text: _this.subtitle
                },
                xAxis: {categories: data.data.xAxis},
                yAxis: {
                },
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
                series:data.data.series
            });
        }, 'json');
    },
    //曲线图
    showSpline: function() {
        var _this = this;
        $.getJSON(this.api, this.param, function(data) {
            Highcharts.chart(_this.container, {
                chart: {
                    type: 'spline'
                },
                title: {
                    text: _this.title.text+data.data.title,
                    align: _this.title.align,
                    x: _this.title.x
                },
                subtitle: {
                    text: _this.subtitle
                },
                xAxis: {categories: data.data.xAxis},
                yAxis: {
                },
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
                series:data.data.series
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
                    text: _this.title.text+data.data.title,
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
                series:data.data.series
            });
        }, 'json');
    },
    //双轴 折线+柱状图
    showDualAxesLineColumn:function () {
        var _this = this;
        $.post(this.api, this.param, function (data) {
            Highcharts.chart(_this.container, {
            chart: {
                zoomType: 'xy'
            },
            title: {
                text: _this.title.text+data.data.title,
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
                    format: '{value}°C',
                    style: {
                        color: Highcharts.getOptions().colors[1]
                    }
                },
                title: {
                    text: 'Temperature',
                    style: {
                        color: Highcharts.getOptions().colors[1]
                    }
                }
            }, { // Secondary yAxis
                title: {
                    text: 'Rainfall',
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                },
                labels: {
                    format: '{value} mm',
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                },
                opposite: true
            }],
            tooltip: {
                shared: true
            },
            series: [{
                name: data.data.first.name,
                type: 'column',
                yAxis: 1,
                data: data.data.first.series,
                tooltip: {
                    valueSuffix: ' mm'
                }

            }, {
                name: data.data.second.name,
                type: 'spline',
                data: data.data.second.series,
                tooltip: {
                    valueSuffix: '°C'
                }
            }]
        });
        }, 'json');
    }
};

