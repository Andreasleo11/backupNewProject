@extends('layouts.app')

@section('content')

  <div class="container">
    <h1>Detail for Master Inventory</h1>

    <div class="card mb-4">
      <a href="{{ route('masterinventory.editpage', $data->id) }}" class="btn btn-warning">Edit</a>
      <div class="card-header">
        <h5>Master Inventory Details</h5>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <tbody>
            <tr>
              <th>No Asset</th>
              <td>{{ $data->ip_address }}</td>
            </tr>
            <tr>
              <th>Username</th>
              <td>{{ $data->username }}</td>
            </tr>
            <tr>
              <th>Position Image</th>
              <td>
                @if ($data->position_image)
                  <a href="{{ asset('storage/' . $data->position_image) }}" data-fancybox="gallery"
                    data-caption="Position Image">
                    <img src="{{ asset('storage/' . $data->position_image) }}" alt="Position Image"
                      style="max-width: 200px; max-height: 100px;">
                  </a>
                @else
                  No image available
                @endif
              </td>
            </tr>
            <tr>
              <th>Department</th>
              <td>{{ $data->dept }}</td>
            </tr>
            <tr>
              <th>Type</th>
              <td>{{ $data->type }}</td>
            </tr>
            <tr>
              <th>Tanggal Pembelian</th>
              <td>{{ $data->purpose }}</td>
            </tr>
            <tr>
              <th>Status</th>
              <td>{{ $data->brand }}</td>
            </tr>
            <tr>
              <th>OS</th>
              <td>{{ $data->os }}</td>
            </tr>
            <tr>
              <th>Description</th>
              <td>{{ $data->description }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <a class="nav-link active" id="hardware-tab" data-toggle="tab" href="#hardware" role="tab"
          aria-controls="hardware" aria-selected="true">Hardware</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="software-tab" data-toggle="tab" href="#software" role="tab"
          aria-controls="software" aria-selected="false">Software</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="repair-tab" data-toggle="tab" href="#repair" role="tab"
          aria-controls="repair-history" aria-selected="false">Repair History</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="maint-tab" data-toggle="tab" href="#maint" role="tab"
          aria-controls="maint" aria-selected="false">Maintenance History</a>
      </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="inventoryTabsContent">
      <!-- Hardware Tab -->
      <div class="tab-pane fade show active" id="hardware" role="tabpanel"
        aria-labelledby="hardware-tab">
        <h2>Hardware Details</h2>
        @if ($data->hardwares->isEmpty())
          <p>No hardware details available.</p>
        @else
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Type</th>
                <th>Nomor Inventaris</th>
                <th>Hardware Name</th>
                <th>Tanggal Pembelian</th>
                <th>Last Update</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data->hardwares as $hardware)
                <tr>
                  <td>{{ $hardware->hardwareType->name ?? 'Unknown Type' }}</td>
                  <td>{{ $hardware->brand }}</td>
                  <td>{{ $hardware->hardware_name }}</td>
                  <td>{{ $hardware->remark }}</td>
                  <td>{{ $hardware->updated_at->format('Y-m-d') }}</td>
                  <td>
                    <!-- Button to generate QR Code -->
                    <form action="{{ route('generate.hardware.qrcode', $hardware->id) }}"
                      method="POST">
                      @csrf
                      <button type="submit" class="btn btn-primary">
                        Generate QR Code
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>

      <!-- Software Tab -->
      <div class="tab-pane fade" id="software" role="tabpanel" aria-labelledby="software-tab">
        <h2>Software Details</h2>
        @if ($data->softwares->isEmpty())
          <p>No software details available.</p>
        @else
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Type</th>
                <th>Software Brand</th>
                <th>Software Name</th>
                <th>License</th>
                <th>Tanggal Pembelian</th>
                <th>Last Update</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data->softwares as $software)
                <tr>
                  <td>{{ $software->softwareType->name ?? 'Unknown Type' }}</td>
                  <td>{{ $software->software_brand }}</td>
                  <td>{{ $software->software_name }}</td>
                  <td>{{ $software->license }}</td>
                  <td>{{ $software->remark }}</td>
                  <td>{{ $software->updated_at->format('Y-m-d') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>

      <div class="tab-pane fade" id="repair" role="tabpanel" aria-labelledby="repair-tab">
        <h2>Repair History</h2>
        <button type="button" class="btn btn-primary" data-toggle="modal"
          data-target="#repairHistoryModal">
          Create Repair History
        </button>

        <!-- Repair History Table -->
        <!-- Replace this comment with your table or message for repair history -->
        <table class="table table-striped mt-3">
          <thead>
            <tr>
              <th>Master ID</th>
              <th>Request Name</th>
              <th>Action</th>
              <th>Type</th>
              <th>Old Part</th>
              <th>Item Type</th>
              <th>Item Brand</th>
              <th>Item Name</th>
              <th>Action Date</th>
              <th>Tanggal Pembelian</th>
              <!-- <th>Created At</th>
                                                                    <th>Updated At</th> -->
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($processedHistories as $history)
              <tr>
                <td>{{ $history->master_id }}</td>
                <td>{{ $history->request_name }}</td>
                <td>{{ $history->action }}</td>
                <td>{{ $history->type }}</td>
                <td>{{ $history->old_part }}</td>
                <td>
                  @if ($history->type === 'hardware')
                    {{ $history->hardwareType->name ?? 'N/A' }}
                  @elseif ($history->type === 'software')
                    {{ $history->softwareType->name ?? 'N/A' }}
                  @else
                    N/A
                  @endif
                </td>
                <td>{{ $history->item_brand }}</td>
                <td>{{ $history->item_name }}</td>
                <td>{{ $history->action_date }}</td>
                <td>{{ $history->remark }}</td>
                <!-- <td>{{ $history->created_at ? $history->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                                                        <td>{{ $history->updated_at ? $history->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</td> -->
                <td>
                  @if ($history->action_date)
                    <!-- Show text with styling if action_date is filled -->
                    <span class="text-success">Finished</span>
                  @else
                    <!-- Show button if action_date is not filled -->
                    <form action="{{ route('inventory.update', $history->id) }}" method="POST">
                      @csrf
                      @method('PUT')
                      <button type="submit" class="btn btn-warning">Update/Sync</button>
                    </form>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="13" class="text-center">No data available</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="tab-pane fade" id="maint" role="tabpanel" aria-labelledby="maint-tab">
        <h2>Maintenance History</h2>

        <!-- Repair History Table -->
        <!-- Replace this comment with your table or message for repair history -->
        <table class="table table-striped mt-3">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nomor Dokumen</th>
              <th>Username</th>
              <th>Periode</th>
              <th>Created Date</th>
              <th>Revision Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>

            @forelse ($inventoryHistories as $data)
              <tr>
                <td>{{ $data->id }}</td>
                <td>{{ $data->no_dokumen }}</td>
                <td>{{ $data->master->username }}</td>
                <td>{{ $data->periode_caturwulan }}</td>
                <td>{{ $data->created_at }}</td>
                <td>{{ $data->revision_date }}</td>
                <td>
                  <a href="{{ route('maintenance.inventory.show', $data->id) }}"
                    class="btn btn-secondary">Detail</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="13" class="text-center">No data available</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="repairHistoryModal" tabindex="-1" role="dialog"
    aria-labelledby="repairHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="repairHistoryModalLabel">Create Repair History</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="{{ route('repair.store') }}" method="POST">
            @csrf
            <input type="hidden" name="master_id" value="{{ $data->id }}">

            <!-- Request Name -->
            <div class="form-group">
              <label for="requestName">Request Name</label>
              <input type="text" class="form-control" id="requestName" name="requestName"
                required>
            </div>

            <!-- Type -->
            <div class="form-group">
              <label for="type">Type</label>
              <select class="form-control" id="type" name="type">
                <option value="">Select Type</option>
                <option value="hardware">Hardware</option>
                <option value="software">Software</option>
              </select>
            </div>

            <!-- Action -->
            <div class="form-group">
              <label for="action">Action</label>
              <select class="form-control" id="action" name="action" required>
                <option value="">Select Action</option>
                <option value="replacement">Replacement</option>
                <option value="installation">Installation</option>
              </select>
            </div>

            <!-- Replacement Fields -->
            <div id="replacementFields" style="display: none;">
              <div class="form-group">
                <label for="oldPart">Old Part</label>
                <select class="form-control" id="oldPart" name="oldPart">
                  <option value="">Select Item</option>
                </select>
              </div>

              <div class="form-group">
                <label for="itemType">Item Type</label>
                <select class="form-control" id="itemType" name="itemType">
                  <option value="">Select Item Type</option>
                </select>
              </div>

              <div class="form-group">
                <label for="itemBrand">Item Brand</label>
                <input type="text" class="form-control" id="itemBrand" name="itemBrand">
              </div>

              <div class="form-group">
                <label for="itemName">Item Name</label>
                <input type="text" class="form-control" id="itemName" name="itemName">
              </div>
            </div>

            <!-- Installation Fields -->
            <div id="installationFields" style="display: none;">
              <div class="form-group">
                <label for="itemTypeInstallation">Item Type</label>
                <select class="form-control" id="itemTypeInstallation" name="itemTypeInstallation">
                  <option value="">Select Item Type</option>
                </select>
              </div>

              <div class="form-group">
                <label for="itemBrandInstallation">Item Brand</label>
                <input type="text" class="form-control" id="itemBrandInstallation"
                  name="itemBrandInstallation">
              </div>

              <div class="form-group">
                <label for="itemNameInstallation">Item Name</label>
                <input type="text" class="form-control" id="itemNameInstallation"
                  name="itemNameInstallation">
              </div>
            </div>

            <!-- Remark -->
            <div class="form-group">
              <label for="remark">Tanggal Pembelian (YYYY-MM-DD) </label>
              <input type="text" class="form-control" id="remark" name="remark">
            </div>

            <button type="submit" class="btn btn-primary">Save Repair History</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Include jQuery (Bootstrap requires it) -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

  <!-- Include Bootstrap JavaScript -->
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js">
  </script>

  <script type="module">
    document.addEventListener('DOMContentLoaded', function() {
      const actionSelect = document.getElementById('action');
      const replacementFields = document.getElementById('replacementFields');
      const installationFields = document.getElementById('installationFields');
      const oldPartSelect = document.getElementById('oldPart');
      const itemTypeSelect = document.getElementById('itemType');
      const itemTypeInstallationSelect = document.getElementById('itemTypeInstallation');
      const masterId = document.querySelector('input[name="master_id"]').value;

      actionSelect.addEventListener('change', function() {
        const action = this.value;
        if (action === 'replacement') {
          replacementFields.style.display = 'block';
          installationFields.style.display = 'none';
          loadOldParts();
          loadItemTypes(); // Load old parts for replacement
        } else if (action === 'installation') {
          replacementFields.style.display = 'none';
          installationFields.style.display = 'block';
          loadItemTypesInstallation(); // Load item types for installation
        } else {
          replacementFields.style.display = 'none';
          installationFields.style.display = 'none';
        }
      });

      function loadOldParts() {
        const type = document.getElementById('type').value;
        if (type) {
          fetch(`/items/available?type=${type}&master_id=${masterId}`)
            .then(response => response.json())
            .then(data => {
              console.log(data);
              oldPartSelect.innerHTML = `<option value="">Select Item</option>` + data.map(
                item =>
                `<option value="${item.name}">${item.name}</option>`).join('');
            })
            .catch(error => console.error('Error fetching old parts:', error));
        }
      }

      function loadItemTypes() {
        const type = document.getElementById('type').value;
        if (type) {
          fetch(`/items/types/${type}`)
            .then(response => response.json())
            .then(data => {
              console.log(data);
              itemTypeSelect.innerHTML = `<option value="">Select Item Type</option>` + data
                .map(
                  item => `<option value="${item.id}">${item.name}</option>`).join('');
            })
            .catch(error => console.error('Error fetching item types:', error));
        }
      }

      function loadItemTypesInstallation() {
        const type = document.getElementById('type').value;
        if (type) {
          fetch(`/items/types/${type}`)
            .then(response => response.json())
            .then(data => {
              console.log(data);
              itemTypeInstallationSelect.innerHTML =
                `<option value="">Select Item Type</option>` + data.map(item =>
                  `<option value="${item.id}">${item.name}</option>`).join('');
            })
            .catch(error => console.error('Error fetching item types for installation:', error));
        }
      }

      document.getElementById('type').addEventListener('change', function() {
        const action = actionSelect.value;
        if (action === 'replacement') {
          loadOldParts();
          loadItemTypes();
        } else if (action === 'installation') {
          loadItemTypesInstallation();
        }
      });

      Fancybox.bind("[data-fancybox]", {
        // Your options here
      });
    });
  </script>

@endsection
