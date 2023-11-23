
$(function () {

    $("#power_gate_open").button();
    $("#power_gate_close").button();
    $("#power_gate_control_group").controlgroup();

    $('#power_gate').on("click", '#power_gate_open' ,function() {
        $.get("powerKey.php?label=gate_open&value=pulse", function () {});
    });
    $('#power_gate').on("click", '#power_gate_close' ,function() {
        $.get("powerKey.php?label=gate_close&value=pulse", function () {});
    });

});