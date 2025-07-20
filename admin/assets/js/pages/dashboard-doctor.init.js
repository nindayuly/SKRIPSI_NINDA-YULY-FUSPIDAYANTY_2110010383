/*
Template Name: Dosix - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: dashboard doctor init js
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

var chartHeatMapBasicChart = "";
var totalPatientsChart = "";
var chartRadarMultiChart = "";

function loadCharts() {
    // Basic Heatmap Charts
    var chartHeatMapBasicColors = "";
    chartHeatMapBasicColors = getChartColorsArray("basic_heatmap");
    if (chartHeatMapBasicColors) {
        var options = {
            series: [
            {
                name: 'SUN',
                data: generateData(18, {
                    min: 0,
                    max: 90
                })
            },
            {
                name: 'SAT',
                data: generateData(18, {
                    min: 0,
                    max: 90
                })
            },
            {
                name: 'FRI',
                data: generateData(18, {
                    min: 0,
                    max: 90
                })
            },
            {
                name: 'THU',
                data: generateData(18, {
                    min: 0,
                    max: 90
                })
            },
            {
                name: 'WED',
                data: generateData(18, {
                    min: 0,
                    max: 90
                })
            },
            {
                name: 'TUE',
                data: generateData(18, {
                    min: 0,
                    max: 90
                })
            },
            {
                name: 'MON',
                data: generateData(18, {
                    min: 0,
                    max: 90
                })
            }
            ],
            chart: {
                height: 380,
                type: 'heatmap',
                toolbar: {
                    show: false
                },
            },
            grid: {
                padding: {
                    top: -20,
                    right: 0,
                    bottom: 0,
                },
            },
            dataLabels: {
                enabled: false
            },
            colors: [chartHeatMapBasicColors[0]],
            stroke: {
                width: 10,
                colors: [chartHeatMapBasicColors[1]]
            }
        };
        if (chartHeatMapBasicChart != "")
            chartHeatMapBasicChart.destroy();
        chartHeatMapBasicChart = new ApexCharts(document.querySelector("#basic_heatmap"), options);
        chartHeatMapBasicChart.render();
    }

    // Generate Data Script

    function generateData(count, yrange) {
        var i = 0;
        var series = [];
        while (i < count) {
            var x = (i + 1).toString();
            var y = Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;

            series.push({
                x: x,
                y: y
            });
            i++;
        }
        return series;
    }

    // Basic Bar chart
    var totalPatientsColors = "";
    totalPatientsColors = getChartColorsArray("totalPatients");
    if (totalPatientsColors) {
        var options = {
            chart: {
                height: 260,
                type: 'bar',
                toolbar: {
                    show: false,
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                }
            },
            dataLabels: {
                enabled: false
            },
            series: [{
                name: 'Total Patients',
                data: [46, 16, 42, 10, 32, 27, 36, 44, 30, 16, 47, 11, 18 ]
            }],
            colors: totalPatientsColors,
            grid: {
                show: false,
                padding: {
                    top: -20,
                    right: 0,
                    bottom: -15,
                },
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            }
        }

        if (totalPatientsChart != "")
            totalPatientsChart.destroy();
        totalPatientsChart = new ApexCharts(document.querySelector("#totalPatients"), options);
        totalPatientsChart.render();
    }

    // Radar Chart - Multi series
    var chartRadarMultiColors = "";
    chartRadarMultiColors = getChartColorsArray("multi_radar");
    if (chartRadarMultiColors) {
        var options = {
            series: [{
                name: 'Male',
                data: [80, 50, 30, 40, 100, 20],
            },
            {
                name: 'Female',
                data: [20, 30, 40, 80, 20, 80],
            }],
            chart: {
                height: 335,
                type: 'radar',
                dropShadow: {
                    enabled: true,
                    blur: 1,
                    left: 1,
                    top: 1
                },
                toolbar: {
                    show: false
                },
            },
            stroke: {
                width: 2
            },
            fill: {
                opacity: 0.2
            },
            markers: {
                size: 0
            },
            colors: chartRadarMultiColors,
            xaxis: {
                categories: ['2019', '2020', '2021', '2022', '2023', '2024']
            }
        };

        if (chartRadarMultiChart != "")
            chartRadarMultiChart.destroy();
        chartRadarMultiChart = new ApexCharts(document.querySelector("#multi_radar"), options);
        chartRadarMultiChart.render();
    }
    
}
window.addEventListener("resize", function () {
    setTimeout(() => {
        loadCharts();
    }, 250);
});
loadCharts();


//random number generator
(function repeat() {
    // Get all elements with the class "randomNumberDisplay"
    var elements = document.getElementsByClassName('randomNumberDisplay');

    // Iterate through the elements and update each one
    for (var i = 0; i < elements.length; i++) {
        // Get a random number
        var number = Math.floor((Math.random() * 1000) + 1);

        // Display the random number in the current div element
        elements[i].innerText = number.toFixed(0);
    }

    // Schedule the repeat function to run again after 1000 milliseconds (1 second)
    setTimeout(repeat, 1000);
})();

//
var swiper = new Swiper(".mySwiper", {
    slidesPerView: "auto",
    spaceBetween: 10,
});


//Orders Table
var options = {
    valueNames: [
        "id",
        "name",
        "age",
        "diseases",
        "date"
    ],
};

// Init list
var patientsList = new List("patientsList", options).on("updated", function (list) {
    list.matchingItems.length == 0 ?
        (document.getElementsByClassName("noresult")[0].style.display = "block") :
        (document.getElementsByClassName("noresult")[0].style.display = "none");

    if (list.matchingItems.length > 0) {
        document.getElementsByClassName("noresult")[0].style.display = "none";
    } else {
        document.getElementsByClassName("noresult")[0].style.display = "block";
    }
});