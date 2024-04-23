<div class="modal fade" id="approve-pr-confirmation-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {!! $body !!}
            </div>
            <div class="modal-footer">
                <button id="{{ $confirmButton['id'] }}" class="{{ $confirmButton['class'] }}"
                    onclick="{{ $confirmButton['onclick'] }}">{{ $confirmButton['text'] }}</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
