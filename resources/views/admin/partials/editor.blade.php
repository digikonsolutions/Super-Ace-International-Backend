{{-- Lightweight rich-text editor. Usage: @include('admin.partials.editor', ['name' => 'content', 'value' => $html]) --}}
<div class="editor" data-editor>
    <div class="editor__bar">
        <button type="button" class="editor__btn" data-cmd="formatBlock" data-value="<p>" title="Paragraph">P</button>
        <button type="button" class="editor__btn" data-cmd="formatBlock" data-value="<h2>" title="Heading">H2</button>
        <button type="button" class="editor__btn" data-cmd="formatBlock" data-value="<h3>" title="Sub-heading">H3</button>
        <span class="editor__sep"></span>
        <button type="button" class="editor__btn" data-cmd="bold" title="Bold"><b>B</b></button>
        <button type="button" class="editor__btn" data-cmd="italic" title="Italic"><i>I</i></button>
        <button type="button" class="editor__btn" data-cmd="underline" title="Underline"><u>U</u></button>
        <span class="editor__sep"></span>
        <button type="button" class="editor__btn" data-cmd="insertUnorderedList" title="Bullet list">• List</button>
        <button type="button" class="editor__btn" data-cmd="insertOrderedList" title="Numbered list">1. List</button>
        <button type="button" class="editor__btn" data-cmd="formatBlock" data-value="<blockquote>" title="Quote">❝</button>
        <span class="editor__sep"></span>
        <button type="button" class="editor__btn" data-cmd="link" title="Insert link">Link</button>
        <button type="button" class="editor__btn" data-cmd="image" title="Insert image by URL">Image</button>
        <button type="button" class="editor__btn" data-cmd="removeFormat" title="Clear formatting">Clear</button>
        <span class="editor__sep"></span>
        <button type="button" class="editor__btn" data-cmd="source" title="Edit HTML">&lt;/&gt; HTML</button>
    </div>
    <div class="editor__area" contenteditable="true" data-placeholder="{{ $placeholder ?? 'Start writing…' }}"></div>
    <textarea class="editor__source" name="{{ $name }}">{{ $value }}</textarea>
</div>
@error($name) <span class="error">{{ $message }}</span> @enderror
