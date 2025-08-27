<div class="mt-2 container">
    <div class="d-flex justify-content-around">
        @foreach ($header->approvals as $approval)
            <div>
                <div class="text-center">
                    <div class="col">
                        <div class="d-flex justify-content-center"></div>
                        <div class="fs-3">{{ ucwords(str_replace('_', ' ', $approval->step->role_slug)) }}
                        </div>
                        @if ($approval->status === 'approved')
                            <span class="badge bg-success">✓ Signed
                                {{ optional($approval->signed_at)->diffForHumans() }}</span>
                        @elseif ($approval->status === 'rejected')
                            <span class="badge bg-danger">✗ Rejected</span>
                        @endif
                    </div>
                </div>
                <div class="border mt-2" style="width:200px; height:100px;">
                    <img src="{{ asset('autographs/' . $approval->signature_path) }}" alt=""
                        class="object-fit-cover">
                </div>
                <div class="mt-2">
                    @if ($approval->approver)
                        <div class="text-center">
                            {{-- Signed-at + calendar icon --}}
                            <div class="gap-1">
                                <i class="bi bi-calendar-event text-muted"></i>
                                <span class="small text-muted">
                                    {{ optional($approval->signed_at)->timezone('Asia/Jakarta')->format('d-m-Y · H:i') ?? '—' }}

                                </span>
                            </div>

                            {{-- Approver name + person icon --}}
                            <div class="gap-1">
                                <i class="bi bi-person-check-fill text-primary"></i>
                                <span class="fw-semibold">
                                    {{ Str::title($approval->approver->name ?? 'Pending') }}
                                </span>
                            </div>
                        </div>
                    @else
                        @php
                            $isCurrentStep = optional($header->currentStep())->id === $approval->flow_step_id;
                            // dd($header->currentStep()->id);
                            // dd($approval->flow_step_id);
                            $isPending = $approval->status === 'pending';
                            $allowedByRole = false;
                            $user = auth()->user();

                            switch ($approval->step->role_slug) {
                                case 'dept_head':
                                    $allowedByRole =
                                        $user->is_head && $user->department->name === $approval->form->department->name;

                                    if (
                                        $user->is_head &&
                                        $user->department->name === 'LOGISTIC' &&
                                        $approval->form->department->name
                                    ) {
                                        $allowedByRole = true;
                                    }
                                    break;
                                case 'director':
                                    $allowedByRole = $user->specification->name === 'DIRECTOR';
                                    break;
                                case 'supervisor':
                                    $allowedByRole = $user->specification->name === 'SUPERVISOR';
                                    break;
                                case 'gm':
                                    $allowedByRole = $user->is_gm;
                                    break;
                                case 'creator':
                                    $allowedByRole = $approval->form->user_id === $user->id;
                                    break;
                            }

                            $showApprovalButtons = $isCurrentStep && $isPending && $allowedByRole;
                        @endphp

                        <div class="d-flex justify-content-between {{ $showApprovalButtons ? '' : 'd-none' }}">
                            <div class="modal fade" id="rejectModal">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('overtime.reject', $header->id) }}" method="post">
                                            @method('PUT')
                                            @csrf
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5">Reason</h1>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="approval_id" value="{{ $approval->id }}">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea name="description" class="form-control" placeholder="Tell us why you rejecting this report..." required></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Confirm</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">Reject</button>
                            <form
                                action="{{ route('overtime.sign', ['id' => $approval->form->id, 'step_id' => $approval->flow_step_id]) }}"
                                method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">Approve</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
