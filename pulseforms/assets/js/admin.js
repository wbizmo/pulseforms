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
})(jQuery);