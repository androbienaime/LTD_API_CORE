{{-- resources/views/components/image-select-option.blade.php --}}
<div class="flex items-center gap-2">
    {{ dd("OKKK") }}
    @if($imageUrl)
        <img src="{{ $imageUrl }}"
             alt="{{ $label }}"
             class="w-8 h-8 object-cover rounded-full"
             loading="lazy">
    @endif
    <span>{{ $label }}</span>
</div>
