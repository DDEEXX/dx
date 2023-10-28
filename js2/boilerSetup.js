var boilerChart;

function graph() {
    $.get("data/heater/heating.php?dev=dialogSetup&data=curveGraph", function (jsonData) {

        const $grafica = document.querySelector("#graphCurve");

        const tags = Object.values(jsonData.tags);
        const data1 = Object.values(jsonData.data1);
        const data_b = Object.values(jsonData.data_b);
        const data_b1 = Object.values(jsonData.data_b1);
        const data_f = Object.values(jsonData.data_f);
        const curve1 = {
            label: "наклон 1, t 20°С",
            data: data1,
            borderColor: 'rgba(255,255,0,0.8)',
            borderWidth: 1,
            fill: false,
        };
        const curve_b = {
            label: "СО верх",
            data: data_b,
            borderColor: 'rgb(255,0,0, 0.8)',
            borderWidth: 1,
            fill: false,
        };
        const curve_b1 = {
            label: "СО низ",
            data: data_b1,
            borderColor: 'rgb(0,255,0, 0.8)', // Цвет границ
            borderWidth: 1,
            fill: false,
        };
        const curve_f = {
            label: "полы",
            data: data_f,
            borderColor: 'rgb(0,0,255)', // Цвет границ
            borderWidth: 1,
            fill: false,
        };
        boilerChart = new Chart($grafica, {
            type: 'line',
            data: {
                labels: tags,
                datasets: [
                    curve1,
                    curve_b,
                    curve_b1,
                    curve_f,
                ]
            },
            options: {
                plugins: {
                    legend: {
                        //display: false
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'температура на улице °С'
                        },
                        ticks: {
                            autoSkip: false
                        },
                        grid: {
                            color: "black",
                            drawTicks: false
                        },
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'температура воды °С'
                        },
                        grid: {
                            color: "black",
                            drawTicks: false
                        },
                        ticks: {
                            stepSize: 10
                        }
                    }
                },
                elements: {
                    point: {
                        pointStyle: false
                    }
                }
            }
        });

    }, "json");
}

function updateGraph() {
    $.get("data/heater/heating.php?dev=dialogSetup&data=curveGraph", function (jsonData) {

        const data1 = Object.values(jsonData.data1);
        const data_b = Object.values(jsonData.data_b);
        const data_b1 = Object.values(jsonData.data_b1);
        const data_f = Object.values(jsonData.data_f);

        boilerChart.data.datasets[0].data = data1;
        boilerChart.data.datasets[1].data = data_b;
        boilerChart.data.datasets[2].data = data_b1;
        boilerChart.data.datasets[3].data = data_f;
        boilerChart.update();

    }, "json");

}

function getData() {
    // $.get("data/heater/heating.php?dev=dialogSetup&data=boilerData&label=boiler_opentherm", function (jsonData) {
    //
    //     // $('input[name="boiler_mode_radio"][value="'+jsonData.mode+'"]').prop('checked', true);
    //
    // });
}

$(function () {

    $('input[name="boiler_mode_radio"]').checkboxradio({
        icon: false
    });
    $( "#boiler_mode_radio_group" ).controlgroup();

    $(".property_spinner").spinner({
        step: 0.1,
        numberFormat: "n",
        change: function( event, ui ) {
            const property = $(this).attr("property");
            const value = $(this).spinner( "value" );
            $.get("data/heater/heating.php?dev=setProperty&property="+property+"&value="+value, function (data) {
                updateGraph();
            })
        },
        stop: function( event, ui ) {
            const property = $(this).attr("property");
            const value = $(this).spinner( "value" );
            $.get("data/heater/heating.php?dev=setProperty&property="+property+"&value="+value, function (data) {
                updateGraph();
            })
        }
    });

    //getData();

    graph();
})