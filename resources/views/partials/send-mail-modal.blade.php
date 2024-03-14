
<div class="modal fade" id="send-mail-modal" tabindex="-1" role="dialog" aria-labelledby="sendMailModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('qaqc.report.sendEmail', $report->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Send Mail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
            </form>
        </div>
    </div>
</div>
