(function ($) {
    "use strict";

    function showFeedback(form, type, message) {
        const feedback = form.find(".pulseforms-feedback");
        const icon = type === "success" ? "check_circle" : "error";

        feedback
            .removeClass("is-success is-error")
            .addClass("is-visible is-" + type)
            .html('<span class="material-symbols-outlined">' + icon + '</span><span>' + message + '</span>');
    }

    $(document).on("submit", ".pulseforms-form", function (event) {
        event.preventDefault();

        const form = $(this);
        const button = form.find(".pulseforms-submit");

        button.addClass("is-loading");

        setTimeout(function () {
            button.removeClass("is-loading");
            showFeedback(form, "error", "Submission processing will be connected in the next step.");
        }, 500);
    });
})(jQuery);