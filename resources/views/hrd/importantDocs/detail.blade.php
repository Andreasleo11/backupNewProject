@extends('layouts.app')

@section('content')

<section class="header">
    <h2 class="">Detail Important Document</h2>
</section>

<section class="breadcrumb">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{route('hrd.home')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{route('hrd.importantDocs.index')}}">Important Documents</a></li>
          <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>
</section>

<section aria-label="content">
    <div class="container mt-5">
        <div class="card">
            <div class="mx-3 mt-4 mb-5 text-center">
                <span class="h1">{{$importantDoc->name}}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <span class="fw-bold h5">Type</span>
                    </div>
                    <div class="col">
                        <span>: {{$importantDoc->type->name}}</span>
                    </div>
                    <div class="col">
                        <span class="fw-bold h5">Date Expired</span>
                    </div>
                    <div class="col">
                        <span>: {{$importantDoc->expired_date}}</span>
                    </div>
                </div>
            </div>



            <!--
                <div class="container">
                    <iframe src="{{ asset('storage/attachments/1706156978_test.pdf')}}" frameborder="0" style="width: 100%; height:500px"></iframe>
                </div>
            -->
        </div>
        <div class="container text-center border">
            <div id="pdfViewer" style="width: auto; height: auto" class="my-4"></div>
        </div>
    </div>
</section>

<!-- PDF.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

@endsection

@push('extraJs')
<script>
    // PDF.js worker from the 'pdfjs-dist' package
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';

    // Fetch PDF document
    const pdfUrl = '{{ asset('storage/attachments/1706156978_test.pdf') }}';
    fetch(pdfUrl)
        .then(response => response.arrayBuffer())
        .then(data => {
            // Render PDF document
            pdfjsLib.getDocument({ data: data }).promise.then(pdfDoc => {
                // Display the first page of the PDF
                pdfDoc.getPage(1).then(page => {
                    const scale = 1;
                    const viewport = page.getViewport({ scale: scale });
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    page.render(renderContext);
                    document.getElementById('pdfViewer').appendChild(canvas);
                });
            });
        })
        .catch(error => {
            console.error('Error fetching PDF: ', error);
        });
</script>

@endpush


