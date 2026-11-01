{{-- Usage: @include('admin.partials.switch', ['name' => 'is_active', 'checked' => true, 'title' => 'Visible', 'text' => 'Show on website']) --}}
<label class="switch">
    <span class="switch__text">
        <strong>{{ $title }}</strong>
        @isset($text)<span>{{ $text }}</span>@endisset
    </span>
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked))>
    <span class="switch__ui"></span>
</label>
