$("audio").each(function() {
  $(this).bind("play", stopAll);
});

function stopAll(e) {
    var currentElementId=$(e.currentTarget).attr("id");
    $("audio").each(function() {
        var $this = $(this);
        var elementId = $this.attr("id");
        if (elementId != currentElementId) {
            $this[0].pause();
        }
    });
}