@extends('layouts.app')

@push('extraCss')
  <style>
    .ag-format-container {
      width: 1080px;
      margin: 0 auto;
    }

    body {
      background-color: #000;
    }

    .ag-courses_box {
      display: flex;
      align-items: flex-start;
      flex-wrap: wrap;
      padding: 50px 0;
    }

    .ag-courses_item {
      flex-basis: calc(25% - 20px);
      margin: 0 10px 20px;
      overflow: hidden;
      border-radius: 28px;
      border: 2px solid #000;
    }

    .ag-courses-item_link {
      display: block;
      padding: 30px 20px;
      background-color: #fff;
      overflow: hidden;
      position: relative;
    }

    .ag-courses-item_link:hover,
    .ag-courses-item_link:hover .ag-courses-item_date {
      text-decoration: none;
      color: #000000;
    }

    .ag-courses-item_link:hover .ag-courses-item_bg {
      transform: scale(10);
    }

    .ag-courses-item_title {
      min-height: 60px;
      margin: 0 0 15px;
      overflow: hidden;
      font-weight: bold;
      font-size: 28px;
      color: #000;
      z-index: 2;
      position: relative;
    }

    .ag-courses-item_date-box {
      font-size: 14px;
      color: #000;
      z-index: 2;
      position: relative;
    }

    .ag-courses-item_date {
      font-weight: bold;
      color: #ffa200;
      transition: color .5s ease;
    }

    .ag-courses-item_bg {
      height: 80px;
      width: 80px;
      background-color: #3ecd5e;
      z-index: 1;
      position: absolute;
      top: -40px;
      right: -40px;
      border-radius: 50%;
      transition: all .5s ease;
    }

    .ag-courses_item:nth-child(2n) .ag-courses-item_bg {
      background-color: #de2920;
    }

    .ag-courses_item:nth-child(3n) .ag-courses-item_bg {
      background-color: #ecdcbf;
    }

    .ag-courses_item:nth-child(4n) .ag-courses-item_bg {
      background-color: #f4cd8a;
    }

    .ag-courses_item:nth-child(5n) .ag-courses-item_bg {
      background-color: #f7bf5d;
    }

    .ag-courses_item:nth-child(6n) .ag-courses-item_bg {
      background-color: #f4ac2f;
    }

    .ag-courses_item:nth-child(7n) .ag-courses-item_bg {
      background-color: #dd910d;
    }

    @media only screen and (max-width: 979px) {
      .ag-courses_item {
        flex-basis: calc(50% - 20px);
      }

      .ag-courses-item_title {
        font-size: 24px;
      }
    }

    @media only screen and (max-width: 767px) {
      .ag-format-container {
        width: 96%;
      }
    }

    @media only screen and (max-width: 639px) {
      .ag-courses_item {
        flex-basis: 100%;
      }

      .ag-courses-item_title {
        min-height: 50px;
        line-height: 1;
        font-size: 18px;
      }

      .ag-courses-item_link {
        padding: 20px;
      }

      .ag-courses-item_date-box {
        font-size: 14px;
      }
    }
  </style>
@endpush

@section('content')
  <div class="ag-format-container">
    <div class="ag-courses_box">
      @foreach ($data as $key => $count)
        <div class="ag-courses_item">
          <a href="#" class="ag-courses-item_link">
            <div class="ag-courses-item_bg"></div>
            <div class="ag-courses-item_title">
              {{ ucfirst(str_replace('_', ' ', $key)) }}
            </div>
            <div class="ag-courses-item_date-box">
              Count:
              <span class="ag-courses-item_date">
                {{ $count }}
              </span>
            </div>
          </a>
        </div>
      @endforeach
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-9">
      <h3>PR Over 2 Days</h3>
      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr class="text-center">
                  <th class="fw-semibold align-middle">No</th>
                  <th class="fw-semibold align-middle">Date PR</th>
                  <th class="fw-semibold align-middle">From Department</th>
                  <th class="fw-semibold align-middle">To Department</th>
                  <th class="fw-semibold align-middle">PR No </th>
                  <th class="fw-semibold align-middle">Supplier</th>
                  <th class="fw-semibold align-middle">Action</th>
                  <th class="fw-semibold align-middle">Status</th>
                  <th class="fw-semibold align-middle">Description</th>
                  <th class="fw-semibold align-middle">Approved Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($prOver2Days as $pr)
                  <tr class="align-middle text-center">
                    <td>{{ $loop->iteration }}</td>
                    <td> @formatDate($pr->date_pr) </td>
                    <td>{{ $pr->from_department ?? $pr->createdBy->department->name }}</td>
                    <td>{{ $pr->to_department }}</td>
                    <td>{{ $pr->pr_no }}</td>
                    <td>{{ $pr->supplier }}</td>
                    <td>
                      <a href="{{ route('purchaserequest.detail', ['id' => $pr->id]) }}"
                        class="btn btn-secondary">
                        <i class='bx bx-info-circle'></i> Detail
                      </a>
                      @php
                        $user = Auth::user();
                      @endphp

                      {{-- Edit Feature --}}
                      {{-- @if (($pr->status == 1 && $user->specification->name == 'PURCHASER') || ($pr->status == 6 && $user->is_head == 1) || ($pr->status == 2 && ($user->department->name == 'PERSONALIA' && $user->is_head == 1)))
                                            <a href="{{ route('purchaserequest.edit', $pr->id) }}" class="btn btn-primary">
                                                <i class='bx bx-edit'></i> Edit
                                            </a>
                                        @endif --}}

                      {{-- Delete Feature --}}
                      @if ($pr->user_id_create === Auth::user()->id)
                        @include('partials.delete-pr-modal', [
                            'id' => $pr->id,
                            'doc_num' => $pr->doc_num,
                        ])
                        <button class="btn btn-danger" data-bs-toggle="modal"
                          data-bs-target="#delete-pr-modal-{{ $pr->id }}">
                          <i class='bx bx-trash-alt'></i> <span
                            class="d-none d-sm-inline">Delete</span>
                        </button>
                      @endif
                    </td>
                    <td>
                      @include('partials.pr-status-badge')
                    </td>
                    <td>
                      {{ $pr->description ?? '-' }}
                    </td>
                    <td>@formatDate($pr->approved_at)</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10">No Data</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
