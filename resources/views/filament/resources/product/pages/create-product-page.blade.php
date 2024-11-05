<!-- resources/views/filament/pages/products/create-header.blade.php -->
<div class="filament-main-content">
    <div class="relative py-6">
        <h2>
            {{ __('Create Product') }}
        </h2>

        <div class="filament-page-actions">
            {{ dd($this->form )}}
{{--            {{ $this->getFormActions() }}--}}
        </div>
    </div>

{{--    <div class="mt-4">--}}
{{--        {{ $this->form }}--}}
{{--    </div>--}}
</div>

<style>
    .filament-main-content {
        position: relative;
        padding: 1rem;
        margin: 1rem;
        border-radius: 0.5rem;
        background-color: white;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }

    .filament-page-actions {
        position: absolute;
        top: 1rem;
        right: 1rem;
        display: flex;
        gap: 1rem;
    }

    .filament-button-create {
        background-color: rgb(59 130 246);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .filament-button-cancel {
        background-color: rgb(239 68 68);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .filament-button-create:hover,
    .filament-button-cancel:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        opacity: 0.9;
    }
</style>
