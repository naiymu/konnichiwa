document.onkeydown = function(evt) {
    if (evt.key === "ArrowRight") {
        var href = $('#next').attr('href');
        if(href == null) {
            return;
        }
        window.location.replace(href);
    }
    if (evt.key === "ArrowLeft") {
        var href = $('#prev').attr('href');
        if(href == null) {
            return;
        }
        window.location.replace(href);
    }
};