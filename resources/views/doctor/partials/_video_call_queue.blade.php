<div class="sec-head cursor-pointer" onclick="window.location.href='{{ route('doctor.queue') }}'">
    <i class="fa-regular fa-folder-open"></i>
    <span>Video Call Queue</span>
    @isset($videoQueueCount)
        <span class="badge bg-secondary ms-2">{{ $videoQueueCount }}</span>
    @endisset
</div>




@if ($videoQueueCount > 0)
    <div class="d-flex flex-column gap-2">
        @foreach ($videoQueue as $appt)
            <div class="ps-row d-flex justify-content-between align-items-center">
                <div class="me-2">
                    <div class="fw-semibold">
                        {{ $appt->patient?->first_name }} {{ $appt->patient?->last_name }}
                        <span class="badge bg-info ms-2">Video</span>
                    </div>
                    <div class="subtle small">Reason: {{ $appt->reason }}</div>
                    <div class="subtle small">Scheduled at:
                        {{ $appt->scheduled_at ? $appt->scheduled_at->format('M d, Y h:i A') : 'As soon as possible' }}
                    </div>
                    @if (!empty($appt->meeting_link))
                        <div class="subtle small mt-1">
                            Meeting: <a class="link-light text-decoration-none" href="{{ $appt->meeting_link }}"
                                target="_blank">
                                Open link
                            </a>
                        </div>
                    @endif
                </div>

                <div class="d-flex flex-column align-items-end gap-2" style="min-width:260px;">
                    @if ($appt->status === 'pending')
                        <div class="d-flex gap-2">
                            <button class="btn btn-success btn-sm" data-accept-appt="{{ $appt->id }}">
                                <i class="fa-solid fa-check me-1"></i> Accept
                            </button>
                            <button class="btn btn-outline-light btn-sm" data-reject-appt="{{ $appt->id }}">
                                <i class="fa-solid fa-xmark me-1"></i> Reject
                            </button>
                        </div>
                    @elseif (in_array($appt->status, ['accepted', 'scheduled']))
                        <button class="btn btn-outline-light btn-sm" data-reject-appt="{{ $appt->id }}">
                            <i class="fa-solid fa-xmark me-1"></i> Reject
                        </button>

                        @if (!$appt->prescription_issued)
                            <button class="btn btn-gradient" id="btnPrescription"
                                data-patientid="{{ $appt->patient?->id }}"
                                data-patientname="{{ $appt->patient?->first_name . ' ' . $appt->patient?->last_name }}"
                                data-appointmentid="{{ $appt->id }}">Add Prescription</button>
                        @endif
                        @if (!empty($appt->meeting_link))
                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-sm" data-completed-appt="{{ $appt->id }}">
                                    <i class="fa-solid fa-check me-1"></i> Mark Completed
                                </button>
                            </div>
                        @endif



                        <div class="subtle small">The patient will automatically see this link</div>
                    @else
                        <span class="subtle small text-uppercase">{{ ucfirst($appt->status) }}</span>
                    @endif

                </div>
            </div>
        @endforeach


    </div>
@else
    <div class="empty">
        <div class="ico"><i class="fa-solid fa-users"></i></div>
        <div>No patients in the video call queue.</div>
    </div>
@endif
