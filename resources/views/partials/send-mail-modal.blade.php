<div class="modal fade modal-lg" id="send-mail-modal" tabindex="-1" role="dialog"
  aria-labelledby="sendMailModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="{{ route('qaqc.report.sendEmail', $report->id) }}" method="post"
        enctype="multipart/form-data">
        @method('POST')
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Send Mail</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="container mt-2">
            <div class="form-group row">
              <label for="fromInput" class="col-form-label col-sm-2">From</label>
              <div class="col">
                <input id="fromInput" class="form-control" type="text"
                  value="{{ Auth::user()->email }}" readonly disabled>
              </div>
            </div>
            <div class="form-group mt-3 row">
              <label for="toInput" class="col-form-label col-sm-2">To</label>
              <div class="col">
                <textarea name="to" id="toInput" cols="30" rows="4"
                  class="form-control semicolon-input">andriani@daijo.co.id; sriyati@daijo.co.id; anik@daijo.co.id; albert@daijo.co.id; nurul@daijo.co.id; riki@daijo.co.id; rony@daijo.co.id; sukiyono@daijo.co.id; budiman@daijo.co.id; heri@daijo.co.id; leny@daijo.co.id; popon@daijo.co.id; sukur@daijo.co.id; supri@daijo.co.id; wiji@daijo.co.id; agus_s@daijo.co.id; catur@daijo.co.id; yeyen@daijo.co.id; </textarea>
              </div>
            </div>
            <div class="form-group mt-3 row">
              <label class="col-form-label col-sm-2">CC</label>
              <div class="col">
                <textarea name="cc" id="ccInput" cols="30" rows="4"
                  class="form-control semicolon-input">deni_qc@daijo.co.id; beata.qc@daijo.co.id; erizal@daijo.co.id; nurul_hidayati@daijo.co.id; herlina@daijo.co.id; srie@daijo.co.id; bayu@daijo.co.id; ekoqc@daijo.co.id; QA01_daijo@daijo.co.id; qa02_daijo@daijo.co.id; umi@daijo.co.id; yuli@daijo.co.id; emma@daijo.co.id; abdulrahim@daijo.co.id; raditya_qc@daijo.co.id; naya@daijo.co.id; adi@daijo.co.id; dian@daijo.co.id; dedi.agung@daijo.co.id; </textarea>
              </div>
            </div>
            <div class="form-group mt-3 row">
              <div class="col-form-label col-sm-2">Subject</div>
              <div class="col">
                <input type="text" name="subject" placeholder="subject for the email"
                  class="form-control">
              </div>
            </div>
            <div class="form-group mt-3 row">
              <div class="col-form-label col-sm-2">Body</div>
              <div class="col">
                <textarea name="body" placeholder="body for the email" class="form-control"></textarea>
              </div>
            </div>
            <div class="mt-3">
              <div class="form-label">Attachments</div>
              @php
                $fileName = 'verification-report-' . $report->id . '.pdf';
                $filePath = Storage::url('pdfs/' . $fileName);
                $fileExists = file_exists(public_path('storage/pdfs/' . $fileName));
              @endphp
              @if ($fileExists)
                <p>
                  <a href="{{ asset($filePath) }}" download="{{ $fileName }}">
                    <div class="card btn btn-light col">
                      <div class="card-body text-start p-0">
                        <span class="text-secondary">{{ $fileName }}</span>
                      </div>
                    </div>
                  </a>
                </p>
              @else
                <div class="mt-2 mb-1 text-secondary fw-bold">
                  You need to export the document first
                </div>
                <a href="{{ route('qaqc.report.savePdf', $report->id) }}"
                  class="btn btn-outline-primary">Export
                  PDF</a>
              @endif
              @foreach ($files as $file)
                @php
                  $filename = basename($file->name);
                  $filepath = Storage::url('files/' . $filename);
                @endphp
                <p>
                  <a href="{{ $filepath }}" download="{{ $filename }}">
                    <div class="card btn btn-light col">
                      <div class="card-body text-start p-0">
                        <span class="text-secondary">{{ $filename }}</span>
                      </div>
                    </div>
                  </a>
                </p>
              @endforeach
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Send</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const inputFields = document.querySelectorAll('.semicolon-input');
  let backspaceCounts = {};

  inputFields.forEach((inputField, index) => {
    backspaceCounts[index] = 0;
    inputField.addEventListener('keydown', function(event) {
      if (event.key === 'Backspace') {
        backspaceCounts[index]++;
        clearTimeout(timeout);
        timeout = setTimeout(() => backspaceCounts[index] = 0,
          300); // Reset backspace count after 300 milliseconds
      }
    });

    inputField.addEventListener('input', function(event) {
      let inputValue = event.target.value;
      // If the last character typed is a semicolon, append a space
      if (inputValue.endsWith(';')) {
        inputValue += ' ';
      }
      event.target.value = inputValue;
      // If backspace key pressed twice consecutively and cursor is positioned after a semicolon, remove the last entry along with the semicolon
      if (backspaceCounts[index] === 2 && inputValue.endsWith('; ')) {
        const semicolonIndex = inputValue.lastIndexOf(';');
        if (semicolonIndex !== -1) {
          event.target.value = inputValue.slice(0, semicolonIndex);
        }
        backspaceCounts[index] = 0; // Reset backspace count
      }
    });
  });
</script>
