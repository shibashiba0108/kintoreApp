$(function(){
    var entry_url = $("#entry_url").val();

    $("#performance_in").click(function(){
        var excs_id = $("#excs_id").val();
        location.href = entry_url + "performance.php?excs_id=" + excs_id;
    });
});