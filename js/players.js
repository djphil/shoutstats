/* player.js v0.1 by djphil (CC-BY-NC-SA 4.0) */

$("audio").each(function() {$(this).bind("play", stopAll);});

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