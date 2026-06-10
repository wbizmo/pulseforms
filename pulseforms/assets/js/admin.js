(function ($) {
    "use strict";

    $(document).on("click", ".pf-copy-shortcode", function () {
        const shortcode = $(this).data("shortcode");
        const button = $(this);

        navigator.clipboard.writeText(shortcode).then(function () {
            button.html('<span class="material-symbols-outlined">check_circle</span>');
            setTimeout(function () {
                button.html('<span class="material-symbols-outlined">content_copy</span>');
            }, 1200);
        });
    });

    $(document).on("click", ".pf-toggle-submission", function () {
        const target = $(this).data("target");
        $("#" + target).toggleClass("is-open");
    });

    $(document).on("click", ".pf-copy-log", function () {
        const target = $(this).data("target");
        const text = $("#" + target).text().trim();
        const button = $(this);

        navigator.clipboard.writeText(text).then(function () {
            button.html('<span class="material-symbols-outlined">check_circle</span>');
            setTimeout(function () {
                button.html('<span class="material-symbols-outlined">content_copy</span>');
            }, 1200);
        });
    });
})(jQuery);