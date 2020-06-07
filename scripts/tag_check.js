$(document).ready(function(){
    $("input:checkbox").change(function(){
        if ($('input:checkbox').filter(':checked').length < 1){
            document.getElementById("submit-btn").disabled = true;
            document.getElementById("clear-btn").disabled = true;
        }
        else {
            document.getElementById("submit-btn").disabled = false;
            document.getElementById("clear-btn").disabled = false;
        }
    });
    $("#clear-btn").click(function(){
        document.getElementById("submit-btn").disabled = true;
        document.getElementById("clear-btn").disabled = true;
    });
});