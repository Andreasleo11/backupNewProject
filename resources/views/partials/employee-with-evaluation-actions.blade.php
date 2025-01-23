<!-- Button to trigger warning logs modal -->
<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#warningLogsModal{{ $employee->id }}">
    View Warnings
</button>

<!-- Warning Logs Modal -->
<div class="modal fade" id="warningLogsModal{{ $employee->id }}" tabindex="-1" role="dialog"
    aria-labelledby="warningLogsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="warningLogsModalLabel">Warning Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($employee->warningLogs->isEmpty())
                    <p>No warnings found for this employee.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Warning Type</th>
                                <th>Reason</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employee->warningLogs as $log)
                                <tr>
                                    <td>{{ $log->warning_type }}</td>
                                    <td>{{ $log->reason }}</td>
                                    <td>{{ $log->created_at->format('d-m-Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Button to trigger modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#warningModal">
    Add Warning
</button>

<!-- Modal -->
<div class="modal fade" id="warningModal" tabindex="-1" role="dialog" aria-labelledby="warningModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="warningModalLabel">Warning Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('director.warning-log.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" name="NIK" value="{{ $employee->NIK }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label" for="warningType">Warning Type</label>
                        <select class="form-select" id="warningType" name="warning_type" required>
                            <option value="SP 1">SP 1</option>
                            <option value="SP 2">SP 2</option>
                            <option value="SP 3">SP 3</option>
                            <option value="Terminate">Terminate</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="reason">Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
