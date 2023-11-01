var boilerChartLog1;
var boilerChartLog2;

function graphLog() {
    const type =  $("#heater_boiler_log_dialog").attr("type");
    $.get("data/heater/heating.php?dev=heatingLog&type="+type+"&data=logGraph", function (jsonData) {

        const $grafica1 = document.querySelector("#graphCurveLog1");
        const $grafica2 = document.querySelector("#graphCurveLog2");

        const tags = Object.values(jsonData.tags);
        const data1 = Object.values(jsonData.data1);
        data1.forEach(function (item){
            item.borderWidth = 1;
            item.fill = false;
        });
        const data2 = Object.values(jsonData.data2);
        data2.forEach(function (item){
            item.borderWidth = 1;
            item.fill = false;
        });

        boilerChartLog1 = new Chart($grafica1, {
            type: 'line',
            data: {
                labels: tags,
                datasets: data1
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            drawTicks: false
                        },
                    },
                    y: {
                        grid: {
                            drawTicks: false
                        },
                    }
                },
                elements: {
                    point: {
                        pointStyle: false
                    }
                }
            }
        });
        boilerChartLog2 = new Chart($grafica2, {
            type: 'line',
            data: {
                labels: tags,
                datasets: data2
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            drawTicks: false
                        },
                    },
                    y: {
                        grid: {
                            drawTicks: false
                        },
                    }
                },
                elements: {
                    point: {
                        pointStyle: false
                    }
                }
            }
        });


    }, 'json');
}

$(document).ready(function () {

    graphLog();

})