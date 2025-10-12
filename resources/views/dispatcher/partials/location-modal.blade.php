@php
    $isDispatcher = auth()->check() && auth()->user()->role === 'dispatcher';
@endphp

<div class="modal fade" id="locationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content locm">
            <div class="modal-body text-center p-4 p-md-5">
                <div class="locm-icon mx-auto mb-3">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <h4 class="fw-bold text-white mb-2">
                    {{ $isDispatcher ? 'Share your location to accept deliveries' : 'Enable Location Services' }}
                </h4>
                <p class="locm-subtle mb-4">
                    {{ $isDispatcher
                        ? 'We need your current location to show nearby pickup jobs and calculate delivery distance/fees.'
                        : 'To help you find nearby doctors, pharmacies, and for delivery services, please allow us to access your location. Your location data will be kept private.' }}
                </p>

                <div class="d-flex gap-3 justify-content-center">
                    {{-- Hide "Later" for dispatchers (enforced) --}}
                    @unless ($isDispatcher)
                        <button type="button" class="btn btn-later px-4 py-2" id="btnLocLater">Maybe Later</button>
                    @endunless
                    <button type="button" class="btn btn-allow px-4 py-2" id="btnLocAllow">
                        <span class="btn-text">{{ $isDispatcher ? 'Share my location' : 'Allow Access' }}</span>
                    </button>
                </div>
                @if ($isDispatcher)
                    <div class="mt-3 small locm-subtle">
                        Tip: enable GPS/high-accuracy for better matching.
                    </div>
                @endif
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
        const IS_DISPATCHER = {{ $isDispatcher ? 'true' : 'false' }};
        // Role-scoped keys so dispatcher enforcement doesn’t affect others
        const KEY_PREFIX = IS_DISPATCHER ? 'cm_dispatch' : 'cm_loc';
        const KEY_DISMISS = KEY_PREFIX + '_prompt_dismissed_until';
        const KEY_GRANTED = KEY_PREFIX + '_permission_granted';

        function shouldShowModal() {
            // If previously granted, don't show
            if (localStorage.getItem(KEY_GRANTED) === '1') return false;

            // Dispatchers: always show until granted (no snooze)
            if (IS_DISPATCHER) return true;

            // Others: respect snooze
            const until = parseInt(localStorage.getItem(KEY_DISMISS) || '0', 10);
            if (!isNaN(until) && until > Date.now()) return false;
            return true;
        }

        function showModal() {
            const modal = new bootstrap.Modal(document.getElementById('locationModal'));
            modal.show();
        }

        // Later (disabled for dispatchers)
        $(document).on('click', '#btnLocLater', function() {
            if (IS_DISPATCHER) return; // safety: shouldn’t be rendered anyway
            const day = 24 * 60 * 60 * 1000;
            localStorage.setItem(KEY_DISMISS, (Date.now() + day).toString());
            bootstrap.Modal.getInstance(document.getElementById('locationModal')).hide();
        });

        // Allow
        $(document).on('click', '#btnLocAllow', function() {
            const $btn = $(this);
            lockBtn($btn);

            if (!('geolocation' in navigator)) {
                flash('danger', 'Geolocation is not supported on this device.');
                if (!IS_DISPATCHER) unlockBtn($btn);
                // Dispatchers stay locked in the modal (enforced)
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const payload = {
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                        accuracy: pos.coords.accuracy
                    };

                    // mark granted
                    localStorage.setItem(KEY_GRANTED, '1');

                    // Choose the correct endpoint
                    const url = `{{ route('location.update') }}`;

                    $.post(url, payload)
                        .done(res => {
                            flash('success', res.message || 'Location saved');
                            bootstrap.Modal.getInstance(document.getElementById('locationModal'))
                                .hide();
                            unlockBtn($btn);
                            // Optional: reload to re-run proximity queries
                            if (IS_DISPATCHER) location.reload();
                        })
                        .fail(xhr => {
                            const msg = xhr.responseJSON?.message || 'Could not save location';
                            flash('danger', msg);
                            // For dispatchers: keep modal open, allow retry
                            unlockBtn($btn);
                        });
                },
                function(err) {
                    let msg = 'Unable to get your location.';
                    if (err.code === 1) msg = 'Permission denied. Please allow access in your browser.';
                    if (err.code === 2) msg =
                        'Position unavailable. Please check your connection or GPS.';
                    if (err.code === 3) msg = 'Timed out while getting your location.';
                    flash('danger', msg);
                    // Enforced: keep modal open for dispatchers
                    unlockBtn($btn);
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });

        // Auto-open wherever included
        $(function() {
            if (shouldShowModal()) showModal();
        });
    })();
</script>
