<div class="modal fade" id="edit-user-permission-modal-{{ $user->id }}" style="z-index: 1051;">
    <div class="modal-dialog modal-xl ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Permissions for <strong>
                        {{ $user->name }}</strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('superadmin.users.permissions.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <select class="form-control select2" multiple="multiple" name="permissions[]"
                        id="permissions-{{ $user->id }}">
                        @foreach ($permissionList as $permission)
                            <option value="{{ $permission->id }}"
                                {{ $user->permissions->contains($permission->id) ? 'selected' : '' }}>
                                {{ $permission->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Initialize Select2 -->
<script type="module">
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select permissions',
            allowClear: true,
            width: '100%'
        });
    });
</script>
