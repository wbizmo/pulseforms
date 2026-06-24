(function ($) {
    "use strict";

    function copyText(text, button) {
        if (!text) {
            return;
        }

        function showCopied() {
            const original = button.html();

            button
                .addClass("is-copied")
                .html('<span class="material-symbols-outlined">check_circle</span>');

            setTimeout(function () {
                button.removeClass("is-copied").html(original);
            }, 1300);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(showCopied).catch(function () {
                fallbackCopy(text, showCopied);
            });
        } else {
            fallbackCopy(text, showCopied);
        }
    }

    function fallbackCopy(text, callback) {
        const textarea = document.createElement("textarea");

        textarea.value = text;
        textarea.setAttribute("readonly", "");
        textarea.style.position = "fixed";
        textarea.style.left = "-9999px";
        textarea.style.top = "-9999px";

        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();

        try {
            document.execCommand("copy");
            callback();
        } catch (error) {
            alert("Could not copy. Please copy manually.");
        }

        document.body.removeChild(textarea);
    }

    $(document).on("click", ".pf-copy-shortcode", function (event) {
        event.preventDefault();

        const button = $(this);
        const shortcode = button.attr("data-shortcode") || button.data("shortcode");

        copyText(shortcode, button);
    });

    $(document).on("click", ".pf-copy-log", function (event) {
        event.preventDefault();

        const button = $(this);
        const target = button.attr("data-target") || button.data("target");
        const text = $("#" + target).text().trim();

        copyText(text, button);
    });

    $(document).on("click", ".pf-toggle-submission", function (event) {
        event.preventDefault();

        const target = $(this).attr("data-target") || $(this).data("target");

        $("#" + target).toggleClass("is-open");
    });
})(jQuery);