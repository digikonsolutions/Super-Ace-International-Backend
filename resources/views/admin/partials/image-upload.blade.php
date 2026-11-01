{{-- Usage: @include('admin.partials.image-upload', ['name' => 'image', 'current' => $model->image_url, 'label' => 'Main image']) --}}
<div class="field">
    <span class="label">{{ $label ?? 'Image' }}</span>
    <label class="upload {{ !empty($current) ? 'has-image' : '' }}">
        <input type="file" name="{{ $name }}" accept="image/*">
        <img class="upload__preview" src="{{ $current ?? '' }}" alt="" @if (empty($current)) hidden @endif>
        <div class="upload__empty">
            <svg class="i"><use href="#i-upload"/></svg>
            <strong>Click or drop an image</strong>
            JPG, PNG or WEBP · max {{ $max ?? '5' }} MB
        </div>
    </label>
    @if (!empty($current))
        <label class="hint" style="display:flex;align-items:center;gap:8px;cursor:pointer">
            <input type="checkbox" class="checkbox" name="remove_{{ $name }}" value="1"> Remove current image
        </label>
    @endif
    @error($name) <span class="error">{{ $message }}</span> @enderror
</div>
