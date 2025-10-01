<a href="{{ route('employee_trainings.show', $training->id) }}" class="btn btn-secondary btn-sm">Show</a>
<a href="{{ route('employee_trainings.edit', $training->id) }}" class="btn btn-warning btn-sm">Edit</a>
<form action="{{ route('employee_trainings.destroy', $training->id) }}" method="POST" class="d-inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
</form>
@if (!$training->evaluated)
    <form action="{{ route('employee_trainings.evaluate', $training->id) }}" method="post" class="d-inline">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-success btn-sm"
            onclick="return confirm('Are you sure?')">Evaluate</button>
    </form>
@endif
