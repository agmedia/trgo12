@extends('back.layouts.base-admin')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Options</h5>
                <a href="{{ route('catalog.options.create') }}" class="btn btn-primary">Add option</a>
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
                        @forelse($options as $o)
                            <tr>
                                <td>{{ $o->id }}</td>
                                <td>{{ optional($o->translation())->title ?? '—' }}</td>
                                <td>{{ $o->values->count() }}</td>
                                <td>
                                    <span class="badge {{ $o->status ? 'bg-success':'bg-secondary' }}">{{ $o->status ? 'Active':'Inactive' }}</span>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('catalog.options.edit', $o) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No options.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $options->links() }}
            </div>
        </div>
    </div>
@endsection
