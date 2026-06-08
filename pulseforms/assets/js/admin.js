(function ($) {
    "use strict";

    $(document).on("click", "#pfCopyBtc", function () {
        const text = $("#pfBtcAddress").text().trim();

        if (!text || text === "YOUR_BTC_WALLET_ADDRESS_HERE") {
            alert("BTC wallet address has not been configured yet.");
            return;
        }

        navigator.clipboard.writeText(text).then(function () {
            $("#pfCopyBtc").html('<span class="material-symbols-outlined">check_circle</span> Copied');
            setTimeout(function () {
                $("#pfCopyBtc").html('<span class="material-symbols-outlined">content_copy</span> Copy');
            }, 1400);
        });
    });

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
})(jQuery);