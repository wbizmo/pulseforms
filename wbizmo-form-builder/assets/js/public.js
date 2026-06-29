(function ($) {
    "use strict";

    function showFeedback(form, type, message) {
        const feedback = form.find(".wbizfobu-feedback");
        const icon = type === "success" ? "check_circle" : "error";

        feedback
            .removeClass("is-success is-error")
            .addClass("is-visible is-" + type)
            .html('<span class="dashicons dashicons-info"></span><span>' + message + '</span>');
    }

    $(document).on("submit", ".wbizfobu-form", function (event) {
        event.preventDefault();

        const form = $(this);
        const button = form.find(".wbizfobu-submit");
        const formData = new FormData(this);

        button.addClass("is-loading");
        form.find(".wbizfobu-feedback").removeClass("is-visible is-success is-error").empty();

        $.ajax({
            url: WbizfobuPublic.ajaxUrl,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                button.removeClass("is-loading");

                if (response && response.success) {
                    const message = response.data && response.data.message
                        ? response.data.message
                        : "Thank you. Your submission has been received.";

                    showFeedback(form, "success", message);
                    form[0].reset();
                    return;
                }

                const errorMessage = response && response.data && response.data.message
                    ? response.data.message
                    : "Something went wrong. Please try again.";

                showFeedback(form, "error", errorMessage);
            },
            error: function (xhr) {
                button.removeClass("is-loading");

                let message = "Something went wrong. Please try again.";

                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    message = xhr.responseJSON.data.message;
                }

                showFeedback(form, "error", message);
            }
        });
    });
})(jQuery);