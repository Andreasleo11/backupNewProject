@extends('layouts.app')

@section('content')
{{-- @dd($importantDoc) --}}

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
                    <div class="col text-center">
                        <span class="h5">: {{$importantDoc->type->name}}</span>
                    </div>
                    <div class="col">
                        <span class="fw-bold h5">Date Expired</span>
                    </div>
                    <div class="col text-center">
                        <span class="h5">: {{\Carbon\Carbon::parse($importantDoc->expired_date)->format('d-m-Y')}}</span>
                    </div>
                </div>
            </div>

            <hr>

            <div class="container text-center">

                {{-- <h3>{{ $importantDoc->name }}</h3>
                <p>Expired Date: {{ $importantDoc->expired_date }}</p> --}}

                    @if ($importantDoc->files->isNotEmpty())
                        <div id="pdfViewerContainer_{{ $importantDoc->id }}">
                            <canvas id="pdfViewerCanvas_{{ $importantDoc->id }}"></canvas>
                        </div>

                    @endif
            </div>

            <div class="container text-center">
                @if ($importantDoc->files->first() !== null)
                    <div id="pdfViewer" style="width: auto; height: auto" class="py-5 mb-3"></div>
                @else
                    <h6 class="mb-3">No Document</h6>
                @endif
            </div>
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

    // Fetch PDF document (replace 'pdfUrl' with the URL of your PDF file)
    const pdfUrl = '{{ asset('storage/importantDocuments') }}/{{ $importantDoc->files->first()->name }}';
    console.log("url: " + pdfUrl);
    fetch(pdfUrl)
        .then(response => response.arrayBuffer())
        .then(data => {
            // Render PDF document
            pdfjsLib.getDocument({ data: data }).promise.then(pdfDoc => {
                // Display the first page of the PDF
                pdfDoc.getPage(1).then(page => {
                    const canvas = document.getElementById('pdfViewerCanvas');
                    const context = canvas.getContext('2d');
                    const viewport = page.getViewport({ scale: 1 });
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    page.render(renderContext);
                });
            });
        })
        .catch(error => {
            console.error('Error loading PDF:', error);
        });
</script>

@endpush


