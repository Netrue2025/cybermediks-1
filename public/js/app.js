// CSRF header for all AJAX
$.ajaxSetup({
    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
});

// Helper: disable a button and show spinner
window.lockBtn = function ($btn) {
    // Save original text if not already saved
    if (!$btn.data("original-text")) {
        $btn.data("original-text", $btn.html());
    }

    $btn.prop("disabled", true).addClass("btn-loading");
    $btn.html(
        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...'
    );
};

// Helper: enable button and restore text
window.unlockBtn = function ($btn) {
    $btn.prop("disabled", false).removeClass("btn-loading");

    // Restore original text if available
    if ($btn.data("original-text")) {
        $btn.html($btn.data("original-text"));
        $btn.removeData("original-text");
    }
};

// Simple Bootstrap toast alert
window.flash = function (type, msg) {
    const html = `
    <div class="toast align-items-center text-bg-${type} border-0 show" role="alert" aria-live="assertive" aria-atomic="true" style="position:fixed;right:1rem;top:1rem;z-index:1080;">
      <div class="d-flex">
        <div class="toast-body">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`;
    const $t = $(html).appendTo("body");
    setTimeout(() => $t.fadeOut(300, () => $t.remove()), 4000);
};
