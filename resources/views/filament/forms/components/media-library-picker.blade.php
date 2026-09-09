@php
    use App\Support\Studio;

    $assets = $field->getMediaAssets();
    $modalId = $field->getModalId();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        class="iws-media-picker"
        x-data="{
            state: $wire.$entangle(@js($getStatePath())),
            search: '',
            previewId: '',
            init() {
                this.previewId = String(this.state || this.firstAssetId())
                this.$watch('state', (value) => {
                    if (value) {
                        this.previewId = String(value)
                    }
                })
            },
            assetIds() {
                return [...this.$root.querySelectorAll('[data-asset-id]')].map((el) => el.dataset.assetId)
            },
            firstAssetId() {
                return this.$root.querySelector('[data-asset-id]')?.dataset.assetId ?? ''
            },
            isSelected(id) {
                return String(this.state ?? '') === String(id)
            },
            hasSelection() {
                return this.assetIds().includes(String(this.state ?? ''))
            },
            matches(text) {
                return ! this.search.trim() || text.includes(this.search.trim().toLocaleLowerCase())
            },
            hasMatches() {
                const term = this.search.trim().toLocaleLowerCase()

                return ! term || [...this.$root.querySelectorAll('[data-search]')].some((el) => el.dataset.search.includes(term))
            },
            openLibrary() {
                this.search = ''
                this.previewId = String(this.state || this.firstAssetId())
                this.$dispatch('open-modal', { id: @js($modalId) })
            },
            async selectAsset(id) {
                const value = Number(id)

                this.state = value
                this.previewId = String(id)
                await this.$wire.set(@js($getStatePath()), value, true)
                this.$dispatch('close-modal', { id: @js($modalId) })
            },
            async clearSelection() {
                this.state = null
                await this.$wire.set(@js($getStatePath()), null, true)
            },
            handleUpload(event) {
                if (event.detail?.pickerId !== @js($modalId)) {
                    return
                }

                this.state = Number(event.detail.assetId)
                this.previewId = String(event.detail.assetId)
                this.search = ''
                this.$nextTick(() => setTimeout(() => {
                    this.$dispatch('open-modal', { id: @js($modalId) })
                }, 75))
            },
        }"
        x-on:media-library-uploaded.window="handleUpload($event)"
    >
        <div class="iws-media-picker__control">
            <div class="iws-media-picker__current">
                @foreach ($assets as $asset)
                    @php
                        $name = $asset->titleText() ?: $asset->titleText('ar') ?: $asset->titleText('en') ?: '#'.$asset->getKey();
                        $thumbnailUrl = $asset->kind === 'image' ? $asset->imageUrl(320) : $asset->publicUrl();
                    @endphp
                    <div class="iws-media-picker__selected" x-cloak x-show="isSelected(@js((string) $asset->getKey()))">
                        <span class="iws-media-picker__selected-preview">
                            @if ($asset->kind === 'image' && filled($thumbnailUrl))
                                <img src="{{ $thumbnailUrl }}" alt="" loading="lazy">
                            @elseif ($asset->kind === 'video' && filled($thumbnailUrl))
                                <video src="{{ $thumbnailUrl }}#t=0.1" muted playsinline preload="metadata" tabindex="-1"></video>
                                <span class="iws-media-picker__selected-play" aria-hidden="true">▶</span>
                            @elseif ($asset->kind === 'video')
                                <span aria-hidden="true">▶</span>
                            @else
                                <span aria-hidden="true">⌁</span>
                            @endif
                        </span>
                        <span class="iws-media-picker__selected-copy">
                            <strong>{{ $name }}</strong>
                            <small>{{ Studio::text($asset->kind) }} · #{{ $asset->getKey() }}</small>
                        </span>
                    </div>
                @endforeach

                <div class="iws-media-picker__empty" x-show="! hasSelection()">
                    <span class="iws-media-picker__empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h13A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 18.5z"/><circle cx="9" cy="9" r="1.4"/><path d="m5 17 4.2-4.2a1.4 1.4 0 0 1 2 0l1.3 1.3 1.3-1.3a1.4 1.4 0 0 1 2 0L20 17"/></svg>
                    </span>
                    <span>{{ Studio::text('media_nothing_selected') }}</span>
                </div>
            </div>

            <div class="iws-media-picker__buttons">
                <x-filament::button
                    type="button"
                    icon="heroicon-o-photo"
                    x-on:click="openLibrary()"
                    :disabled="$isDisabled()"
                >
                    {{ Studio::text('choose_from_media_library') }}
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="gray"
                    outlined
                    x-cloak
                    x-show="hasSelection()"
                    x-on:click="clearSelection()"
                    :disabled="$isDisabled()"
                >
                    {{ Studio::text('clear_media_selection') }}
                </x-filament::button>
            </div>
        </div>

        <x-filament::modal
            :id="$modalId"
            :heading="Studio::text('media_library')"
            :description="Studio::text('media_library_help')"
            close-button
            sticky-header
            width="7xl"
            class="iws-media-library-modal"
        >
            <div class="iws-media-library">
                <div class="iws-media-library__toolbar">
                    <label class="iws-media-library__search">
                        <span class="fi-sr-only">{{ Studio::text('media_search_prompt') }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                        <input
                            type="search"
                            x-model.debounce.180ms="search"
                            placeholder="{{ Studio::text('media_search_prompt') }}"
                            autocomplete="off"
                        >
                    </label>

                    <div x-on:click.capture="$dispatch('close-modal', { id: @js($modalId) })">
                        {{ $field->getAction('uploadMedia') }}
                    </div>
                </div>

                @if ($assets->isEmpty())
                    <div class="iws-media-library__no-results">
                        <strong>{{ Studio::text('media_library_empty') }}</strong>
                        <span>{{ Studio::text('media_no_results') }}</span>
                    </div>
                @else
                    <div class="iws-media-library__layout">
                        <div class="iws-media-library__browser">
                            <div class="iws-media-library__grid" role="list">
                                @foreach ($assets as $asset)
                                    @php
                                        $name = $asset->titleText() ?: $asset->titleText('ar') ?: $asset->titleText('en') ?: '#'.$asset->getKey();
                                        $thumbnailUrl = $asset->kind === 'image' ? $asset->imageUrl(480) : $asset->publicUrl();
                                        $searchText = mb_strtolower(trim($asset->titleText('ar').' '.$asset->titleText('en').' '.$asset->kind.' '.$asset->getKey()));
                                    @endphp
                                    <article
                                        class="iws-media-library__card"
                                        role="listitem"
                                        wire:key="iws-media-card-{{ $asset->getKey() }}"
                                        data-asset-id="{{ $asset->getKey() }}"
                                        data-search="{{ $searchText }}"
                                        x-show="matches(@js($searchText))"
                                        x-bind:class="{ 'is-selected': isSelected(@js((string) $asset->getKey())), 'is-previewing': previewId === @js((string) $asset->getKey()) }"
                                    >
                                        <button
                                            type="button"
                                            class="iws-media-library__card-main"
                                            x-on:click="previewId = @js((string) $asset->getKey())"
                                            aria-label="{{ Studio::text('preview') }}: {{ $name }}"
                                        >
                                            <span class="iws-media-library__thumb">
                                                @if ($asset->kind === 'image' && filled($thumbnailUrl))
                                                    <img src="{{ $thumbnailUrl }}" alt="" loading="lazy">
                                                @elseif ($asset->kind === 'video' && filled($thumbnailUrl))
                                                    <video src="{{ $thumbnailUrl }}#t=0.1" muted playsinline preload="metadata" tabindex="-1"></video>
                                                    <span class="iws-media-library__play" aria-hidden="true">▶</span>
                                                @else
                                                    <span class="iws-media-library__file" aria-hidden="true">⌁</span>
                                                @endif
                                            </span>
                                            <span class="iws-media-library__card-copy">
                                                <strong title="{{ $name }}">{{ $name }}</strong>
                                                <small>{{ Studio::text($asset->kind) }} · #{{ $asset->getKey() }}</small>
                                            </span>
                                        </button>

                                        <div class="iws-media-library__card-actions">
                                            <button type="button" x-on:click="previewId = @js((string) $asset->getKey())">
                                                {{ Studio::text('preview') }}
                                            </button>
                                            <button type="button" class="is-primary" x-on:click="selectAsset(@js((string) $asset->getKey()))">
                                                {{ Studio::text('select_media') }}
                                            </button>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <div class="iws-media-library__no-results" x-cloak x-show="! hasMatches()">
                                <strong>{{ Studio::text('media_no_results') }}</strong>
                            </div>
                        </div>

                        <aside class="iws-media-library__preview" aria-live="polite">
                            @foreach ($assets as $asset)
                                @php
                                    $name = $asset->titleText() ?: $asset->titleText('ar') ?: $asset->titleText('en') ?: '#'.$asset->getKey();
                                    $previewUrl = $asset->kind === 'image' ? $asset->imageUrl(1600) : $asset->publicUrl();
                                @endphp
                                <section x-cloak x-show="previewId === @js((string) $asset->getKey())">
                                    <div class="iws-media-library__preview-media">
                                        @if ($asset->kind === 'image' && filled($previewUrl))
                                            <img src="{{ $previewUrl }}" alt="{{ $asset->text('alt') ?: $name }}" loading="lazy">
                                        @elseif ($asset->kind === 'video' && filled($previewUrl))
                                            <video src="{{ $previewUrl }}" controls playsinline preload="metadata"></video>
                                        @else
                                            <div class="iws-media-library__file-preview" aria-hidden="true">⌁</div>
                                        @endif
                                    </div>
                                    <div class="iws-media-library__preview-copy">
                                        <small>{{ Studio::text('preview') }}</small>
                                        <h3>{{ $name }}</h3>
                                        <p>{{ Studio::text($asset->kind) }} · #{{ $asset->getKey() }}</p>
                                    </div>
                                    <x-filament::button
                                        type="button"
                                        x-on:click="selectAsset(@js((string) $asset->getKey()))"
                                        class="iws-media-library__select-button"
                                    >
                                        {{ Studio::text('select_this_media') }}
                                    </x-filament::button>
                                </section>
                            @endforeach
                        </aside>
                    </div>
                @endif
            </div>
        </x-filament::modal>
    </div>
</x-dynamic-component>
