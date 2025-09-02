@php
    $groups = [
      'products' => __('back/categories.tabs.products'),
      'blog'     => __('back/categories.tabs.blog'),
      'pages'    => __('back/categories.tabs.pages'),
      'footer'   => __('back/categories.tabs.footer'),
    ];
    $currentGroup = old('group', $category->group ?? request('group','products'));
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">{{ __('back/categories.form.group') }}</label>
        <select name="group" class="form-select">
            @foreach($groups as $val => $label)
                <option value="{{ $val }}" @selected($currentGroup === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @error('group') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8">
        <label class="form-label">{{ __('back/categories.form.parent') }}</label>
        <select name="parent_id" class="form-select">
            <option value="">{{ __('back/common.none') }}</option>
            @foreach($parents as $p)
                <option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id ?? null) == $p->id)>
                    {{ $p->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">{{ __('back/categories.form.parent_hint') }}</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ __('back/categories.form.name') }}</label>
        <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" class="form-control" required>
        @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ __('back/categories.form.slug') }}</label>
        <input type="text" name="slug" value="{{ old('slug', $category->slug ?? '') }}" class="form-control">
        <div class="form-text">{{ __('back/categories.form.auto_slug_hint') }}</div>
        @error('slug') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">{{ __('back/categories.form.title') }}</label>
        <input type="text" name="title" value="{{ old('title', $category->title ?? '') }}" class="form-control">
    </div>

    <div class="col-12">
        <label class="form-label">{{ __('back/categories.form.description') }}</label>
        <textarea name="description" rows="4" class="form-control">{{ old('description', $category->description ?? '') }}</textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ __('back/categories.form.image_url') }}</label>
        <input type="text" name="image" value="{{ old('image', $category->image ?? '') }}" class="form-control" placeholder="https://… or /storage/…">
    </div>

    <div class="col-md-3">
        <label class="form-label">{{ __('back/categories.form.sort_order') }}</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-control">
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" value="1" name="is_active" id="is_active"
                    @checked(old('is_active', ($category->is_active ?? true)))>
            <label class="form-check-label" for="is_active">{{ __('back/categories.form.is_active') }}</label>
        </div>
    </div>

    {{-- SEO --}}
    <div class="col-md-6">
        <label class="form-label">{{ __('back/categories.form.meta_title') }}</label>
        <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('back/categories.form.meta_description') }}</label>
        <input type="text" name="meta_description" value="{{ old('meta_description', $category->meta_description ?? '') }}" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('back/categories.form.meta_keywords') }}</label>
        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $category->meta_keywords ?? '') }}" class="form-control">
        <div class="form-text">{{ __('back/categories.form.keywords_hint') }}</div>
    </div>
</div>
