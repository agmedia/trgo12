@extends('back.layouts.base-admin')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Options</h5>
                <a href="{{ route('catalog.attributes.create') }}" class="btn btn-primary">Add attribute</a>
            </div>
            <div class="card-body">
                <form class="row g-2 mb-3" method="get">
                    <div class="col-sm-9"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search…"></div>
                    <div class="col-sm-3"><button class="btn btn-outline-secondary w-100">Filter</button></div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Values</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($attributes as $att)
                            <tr>
                                <td>{{ $att->id }}</td>
                                <td>{{ optional($att->translation())->title ?? '—' }}</td>
                                <td>{{ $att->attributeValues->count() }}</td>
                                <td>
                                    <span class="badge {{ $att->status ? 'bg-success':'bg-secondary' }}">{{ $att->status ? 'Active':'Inactive' }}</span>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('catalog.attributes.edit', $att) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No attributes.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $attributes->links() }}
            </div>
        </div>
    </div>
@endsection
