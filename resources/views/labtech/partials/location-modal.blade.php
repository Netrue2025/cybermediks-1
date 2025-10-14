<div class="modal fade" id="locationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content locm">
            {{-- Close button --}}
            <div class="modal-body text-center p-4 p-md-5">
                <div class="locm-icon mx-auto mb-3">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <h4 class="fw-bold text-white mb-2">Enable Location Services</h4>
                <p class="locm-subtle mb-4">
                    To help you find nearby doctors, pharmacies, and for delivery services, please allow us to access
                    your
                    location. Your location data will be kept private.
                </p>

                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-later px-4 py-2" id="btnLocLater">
                        Maybe Later
                    </button>
                    <button type="button" class="btn btn-allow px-4 py-2" id="btnLocAllow">
                        <span class="btn-text">Allow Access</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .locm {
        background: #121a2c !important;
        border: 1px solid #25324a !important;
        border-radius: 18px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .55) !important;
    }

    .locm-subtle {
        color: #a7b0c3;
    }

    .locm-icon {
        width: 74px;
        height: 74px;
        border-radius: 50%;
        background: rgba(135, 88, 232, .15);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .locm-icon i {
        font-size: 30px;
        color: #c766ea;
    }

    .btn-allow {
        background-image: linear-gradient(90deg, #8758e8, #e0568a);
        border: 0;
        color: #fff;
        font-weight: 600;
        border-radius: 10px;
    }

    .btn-allow:disabled {
        opacity: .9;
    }

    .btn-later {
        background: #0e162b;
        border: 1px solid #283652;
        color: #e5e7eb;
        border-radius: 10px;
        font-weight: 600;
    }

    .modal-backdrop.show {
        backdrop-filter: blur(2px);
    }
</style>

<script>
    (function() {
        const MODAL_KEY_DISMISS = 'cm_loc_prompt_dismissed_until';
        const MODAL_KEY_GRANTED = 'cm_loc_permission_granted';

        function shouldShowModal() {
            // Don’t show if previously granted
            if (localStorage.getItem(MODAL_KEY_GRANTED) === '1') return false;

            // Dismissed until timestamp?
            const until = parseInt(localStorage.getItem(MODAL_KEY_DISMISS) || '0', 10);
            if (!isNaN(until) && until > Date.now()) return false;

            return true;
        }

        function showModal() {
            const modal = new bootstrap.Modal(document.getElementById('locationModal'));
            modal.show();
        }

        // Hook buttons
        $(document).on('click', '#btnLocLater', function() {
            // Snooze for 24h
            const day = 24 * 60 * 60 * 1000;
            localStorage.setItem(MODAL_KEY_DISMISS, (Date.now() + day).toString());
            const modalEl = document.getElementById('locationModal');
            bootstrap.Modal.getInstance(modalEl).hide();
        });

        $(document).on('click', '#btnLocAllow', function() {
            const $btn = $(this);
            lockBtn($btn);

            if (!('geolocation' in navigator)) {
                flash('danger', 'Geolocation is not supported on this device.');
                unlockBtn($btn);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const payload = {
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                        accuracy: pos.coords.accuracy
                    };

                    // Save locally to avoid asking again
                    localStorage.setItem(MODAL_KEY_GRANTED, '1');

                    // Send to backend (optional but recommended)
                    $.post(`{{ route('location.update') }}`, payload)
                        .done(res => {
                            flash('success', res.message || 'Location saved');
                            const modalEl = document.getElementById('locationModal');
                            bootstrap.Modal.getInstance(modalEl).hide();
                        })
                        .fail(xhr => {
                            const msg = xhr.responseJSON?.message || 'Could not save location';
                            flash('danger', msg);
                        })
                        .always(() => unlockBtn($btn));
                },
                function(err) {
                    let msg = 'Unable to get your location.';
                    // Friendly errors
                    if (err.code === 1) msg = 'Permission denied. Please allow access in your browser.';
                    if (err.code === 2) msg =
                        'Position unavailable. Please check your connection or GPS.';
                    if (err.code === 3) msg = 'Timed out while getting your location.';
                    flash('danger', msg);
                    unlockBtn($btn);
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });

        // Auto-open on pages where you include this partial
        $(function() {
            if (shouldShowModal()) showModal();
        });
    })();
</script>
