/*
Template Name: Dosix - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Ecommerce Dashboard init js
*/

// get colors array from the string
function getChartColorsArray(chartId) {
    const chartElement = document.getElementById(chartId);
    if (chartElement) {
        const colors = chartElement.dataset.colors;
        if (colors) {
            const parsedColors = JSON.parse(colors);
            const mappedColors = parsedColors.map((value) => {
                const newValue = value.replace(/\s/g, "");
                if (!newValue.includes(",")) {
                    const color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                    return color || newValue;
                } else {
                    const val = value.split(",");
                    if (val.length === 2) {
                        const rgbaColor = `rgba(${getComputedStyle(document.documentElement).getPropertyValue(val[0])}, ${val[1]})`;
                        return rgbaColor;
                    } else {
                        return newValue;
                    }
                }
            });
            return mappedColors;
        } else {
            console.warn(`data-colors attribute not found on: ${chartId}`);
        }
    }
}

var areachartmini6Chart = "";
var areachartmini7Chart = "";
var cartChart = "";

function loadCharts() {

    // Chart-6
    var areachartmini6Colors = "";
    areachartmini6Colors = getChartColorsArray("mini-chart-6");
    if (areachartmini6Colors) {
        var options1 = {
            series: [{
                data: [50, 15, 35, 62, 23, 56, 44, 12, 62, 23, 56, 44, 12, 62, 23, 56, 44, 12, 62, 23, 56, 44, 12, 44, 12, 62, 23, 56, 44,]
            }],
            chart: {
                type: 'bar',
                height: 45,
                sparkline: {
                    enabled: true
                }

            },
            colors: areachartmini6Colors,
            stroke: {
                curve: 'smooth',
                width: 1,
            },
            tooltip: {
                fixed: {
                    enabled: false
                },
                x: {
                    show: false
                },
                y: {
                    title: {
                        formatter: function (seriesName) {
                            return ''
                        }
                    }
                },
                marker: {
                    show: false
                }
            }
        };

        if (areachartmini6Chart != "")
            areachartmini6Chart.destroy();
        areachartmini6Chart = new ApexCharts(document.querySelector("#mini-chart-6"), options1);
        areachartmini6Chart.render();
    }

    // Chart-7
    var areachartmini7Colors = "";
    areachartmini7Colors = getChartColorsArray("mini-chart-7");
    if (areachartmini7Colors) {
        var options1 = {
            series: [{
                data: [50, 15, 20, 34, 23, 56, 65, 41, 15, 20, 34, 23, 56, 65, 41, 15, 20, 34, 23, 56, 65, 41, 15, 20, 34, 23, 56, 65, 41]
            }],
            chart: {
                type: 'bar',
                height: 45,
                sparkline: {
                    enabled: true
                }

            },
            colors: areachartmini7Colors,
            stroke: {
                curve: 'smooth',
                width: 1,
            },
            tooltip: {
                fixed: {
                    enabled: false
                },
                x: {
                    show: false
                },
                y: {
                    title: {
                        formatter: function (seriesName) {
                            return ''
                        }
                    }
                },
                marker: {
                    show: false
                }
            }
        };

        if (areachartmini7Chart != "")
            areachartmini7Chart.destroy();
        areachartmini7Chart = new ApexCharts(document.querySelector("#mini-chart-7"), options1);
        areachartmini7Chart.render();
    }

    var chartLineColors = getChartColorsArray("chart-container");
    if (chartLineColors) {
        var dom = document.getElementById('chart-container');
        var myChart = echarts.init(dom, null, {
            renderer: 'canvas',
            useDirtyRect: false
        });
        var app = {};

        var option;

        const categories = (function () {
            let now = new Date();
            let res = [];
            let len = 10;
            while (len--) {
                res.unshift(now.toLocaleTimeString().replace(/^\D*/, ''));
                now = new Date(+now - 2000);
            }
            return res;
        })();
        const categories2 = (function () {
            let res = [];
            let len = 10;
            while (len--) {
                res.push(10 - len - 1);
            }
            return res;
        })();
        const data = (function () {
            let res = [];
            let len = 10;
            while (len--) {
                res.push(Math.round(Math.random() * 1000));
            }
            return res;
        })();
        const data2 = (function () {
            let res = [];
            let len = 0;
            while (len < 10) {
                res.push(+(Math.random() * 10 + 5).toFixed(0));
                len++;
            }
            return res;
        })();
        option = {
            chart: {
                borderColor: "red",
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'cross',
                    label: {
                        backgroundColor: '#283b56'
                    }
                }
            },
            darkMode: true,
            grid: {
                left: '0%',
                right: '0%',
                bottom: '0%',
                top: '10%',
                containLabel: true,
                borderColor: 'rgba(0, 0, 0, 0)', 
            },
            toolbox: {
                show: false,
                feature: {
                    dataView: { readOnly: false },
                    restore: {},
                    saveAsImage: {}
                }
            },
            color: chartLineColors,
            dataZoom: {
                show: false,
                start: 0,
                end: 100
            },
            xAxis: [
            
                {
                    type: 'category',
                    boundaryGap: true,
                    data: categories,
                },
                {
                    type: 'category',
                    boundaryGap: true,
                    data: categories2,
                },
                
            ],
            yAxis: [
                {
                    type: 'value',
                    scale: true,
                    name: 'Price',
                    max: 30,
                    min: 0,
                    boundaryGap: [0.2, 0.2],
                    splitLine: {
                        show: true,
                        lineStyle: {
                            color: chartLineColors[2]
                        }
                    }

                },
                {
                    type: 'value',
                    scale: true,
                    name: 'Order',
                    max: 1200,
                    min: 0,
                    boundaryGap: [0.2, 0.2],
                    splitLine: {
                        show: true,
                        lineStyle: {
                            color: chartLineColors[2]
                        }
                    }

                }
            ],
            series: [
                {
                    name: 'Total Orders',
                    type: 'bar',
                    xAxisIndex: 1,
                    yAxisIndex: 1,
                    data: data
                },
                {
                    name: 'Return Orders',
                    type: 'line',
                    data: data2
                }
            ]
        };
        app.count = 11;
        setInterval(function () {
            let axisData = new Date().toLocaleTimeString().replace(/^\D*/, '');
            data.shift();
            data.push(Math.round(Math.random() * 1000));
            data2.shift();
            data2.push(+(Math.random() * 10 + 5).toFixed(0));
            categories.shift();
            categories.push(axisData);
            categories2.shift();
            categories2.push(app.count++);
            myChart.setOption({
                xAxis: [
                    {
                        data: categories
                    },
                    {
                        data: categories2
                    }
                ],
                series: [
                    {
                        data: data
                    },
                    {
                        data: data2
                    }
                ]
            });
        }, 2100);

        if (option && typeof option === 'object') {
            myChart.setOption(option);
        }
        // window.addEventListener('resize', myChart.resize);
    }

    //Order Status
    var orderStatusColors = getChartColorsArray("orderStatus");
    if (orderStatusColors) { 
        var chartDom = document.getElementById('orderStatus');
        var orderStatusChart = echarts.init(chartDom, null, {
            renderer: 'canvas',
            useDirtyRect: false
        });
        option = {
            legend: {
                top: 'bottom',
                textStyle: {
                    color: '#9fa0a1'
                }
            },
            color: orderStatusColors,
            darkMode: 'auto',
            toolbox: {
                show: false,
                feature: {
                    mark: { show: true },
                    dataView: { show: true, readOnly: false },
                    restore: { show: true },
                    saveAsImage: { show: true }
                }
            },
            grid: {
                left: '0%',
                right: '0%',
                bottom: '0%',
                top: '-50%',
                containLabel: true
            },
            series: [
                {
                    name: 'Nightingale Chart',
                    type: 'pie',
                    radius: [30, 120],
                    center: ['50%', '50%'],
                    roseType: 'area',
                    itemStyle: {
                        borderRadius: 8
                    },
                    data: [
                        { value: 40, name: 'New' },
                        { value: 49, name: 'Delivered' },
                        { value: 32, name: 'Cancelled' },
                        { value: 28, name: 'Refund' },
                        { value: 30, name: 'Pending' },
                    ]
                }
            ]
        };

        option && orderStatusChart.setOption(option);
    }

    //Sales Earning
    var salesEarningColors = getChartColorsArray("salesEarning");
    if (salesEarningColors) { 
        var chartDom = document.getElementById('salesEarning');
        var salesEarningChart = echarts.init(chartDom);

        option = {
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: ['Purchase', 'Sales Earning'],
                textStyle: {
                    color: '#9fa0a1'
                }
            },
            darkMode: 'auto',
            grid: {
                top: '15%',
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            toolbox: {
                show: false,
                feature: {
                    saveAsImage: {}
                }
            },
            color: salesEarningColors,
            xAxis: {
                type: 'category',
                data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
            },
            yAxis: {
                type: 'value',
                splitLine: {
                    show: true,
                    lineStyle: {
                        color: chartLineColors[2]
                    }
                }
            },
            series: [
                {
                    name: 'Purchase',
                    type: 'line',
                    step: 'start',
                    data: [120, 132, 101, 134, 90, 230, 210]
                },
                {
                    name: 'Sales Earning',
                    type: 'line',
                    step: 'middle',
                    data: [220, 282, 201, 234, 290, 430, 410]
                }
            ]
        };

        option && salesEarningChart.setOption(option);
    }

    //
    var cartChartColors = "";
    cartChartColors = getChartColorsArray("cartChart");
    if (cartChartColors) {
        var options = {
            series: [75],
            chart: {
                height: 335,
                type: 'radialBar',
                toolbar: {
                    show: true
                }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -135,
                    endAngle: 225,
                    hollow: {
                        margin: 0,
                        size: '70%',
                        image: undefined,
                        imageOffsetX: 0,
                        imageOffsetY: 0,
                        position: 'front',
                    },
                    track: {
                        background: cartChartColors[2],
                        strokeWidth: '67%',
                        margin: 0, // margin is in pixels
                    },
                    dataLabels: {
                        show: true,
                        name: {
                            offsetY: -10,
                            show: true,
                            fontSize: '17px'
                        },
                        value: {
                            formatter: function (val) {
                                return parseInt(val);
                            },
                            fontSize: '36px',
                            show: true,
                        }
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    type: 'horizontal',
                    shadeIntensity: 0.5,
                    gradientToColors: [cartChartColors[1]],
                    inverseColors: true,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 100]
                }
            },
            colors: cartChartColors,
            stroke: {
                lineCap: 'round'
            },
            labels: ['Cart'],
        };
        if (cartChart != "")
            cartChart.destroy();
        cartChart = new ApexCharts(document.querySelector("#cartChart"), options);
        cartChart.render();
    }
}

window.addEventListener("resize", function () {
    setTimeout(() => {
        loadCharts();
    }, 250);
});

loadCharts();

var swiper = new Swiper(".sellingProduct", {
    slidesPerView: 1,
    loop: true,
    spaceBetween: 24,
    autoplay: {
        delay: 2500,
        disableOnInteraction: false,
    },
    pagination: {
        clickable: true,
        el: ".swiper-pagination",
        dynamicBullets: true,
    },
    breakpoints: {
        640: {
            slidesPerView: 1,
        },
        768: {
            slidesPerView: 2,
        },
        1024: {
            slidesPerView: 2,
        },
    },
});
