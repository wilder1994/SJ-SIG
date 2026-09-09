@php
    $canEdit = $canEdit ?? false;
    $autosubmit = $autosubmit ?? false;
    $action = $action ?? null;
    $src = $person?->photo_path ? route('people.photo', $person) : null;
    $initials = $person?->initials() ?? '';
@endphp
@if($canEdit && $autosubmit && $action)
    <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="avatar-ring-form">
        @csrf
        <label class="avatar-ring" title="Cambiar foto">
            <span class="avatar-disk">
                <img id="photo-preview" src="{{ $src }}" alt="" @unless($src) hidden @endunless>
                <span id="photo-icon" class="avatar-fallback avatar-lg" @if($src) hidden @endif>{{ $initials }}</span>
            </span>
            <span class="avatar-cam" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 8h3l2-2h6l2 2h3v11H4V8Z"/><circle cx="12" cy="13" r="3.2"/>
                </svg>
            </span>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" id="photo-input" data-photo-autosubmit>
        </label>
    </form>
@elseif($canEdit)
    <label class="avatar-ring" title="Subir foto">
        <span class="avatar-disk">
            <img id="photo-preview" src="{{ $src }}" alt="" @unless($src) hidden @endunless>
            <span id="photo-icon" class="avatar-fallback avatar-lg" @if($src) hidden @endif>{{ $initials !== '' ? $initials : '+' }}</span>
        </span>
        <span class="avatar-cam" aria-hidden="true">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 8h3l2-2h6l2 2h3v11H4V8Z"/><circle cx="12" cy="13" r="3.2"/>
            </svg>
        </span>
        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" id="photo-input">
    </label>
@else
    <span class="avatar-ring is-static">
        <span class="avatar-disk">
            @if($src)
                <img src="{{ $src }}" alt="">
            @else
                <span class="avatar-fallback avatar-lg">{{ $initials }}</span>
            @endif
        </span>
    </span>
@endif
