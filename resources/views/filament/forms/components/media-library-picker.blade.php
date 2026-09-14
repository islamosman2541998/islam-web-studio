@php
    use App\Support\Studio;

    $modalId = $field->getModalId();
    $selectedItem = $field->getSelectedMediaItem();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        class="iws-media-picker"
        x-data="{
            state: $wire.{!! $field->applyStateBindingModifiers("\$entangle('{$getStatePath()}')") !!},
            selected: @js($selectedItem),
            assets: [],
            hasLoaded: false,
            isLoading: false,
            search: '',
            previewId: '',
            init() {
                this.previewId = String(this.state || '')
                this.$watch('state', (value) => {
                    if (value) {
                        this.previewId = String(value)
                    }
                })
            },
            async loadAssets() {
                if (this.isLoading) {
                    return
                }

                this.isLoading = true

                try {
                    this.assets = (await $wire.callSchemaComponentMethod(@js($field->getKey()), 'getMediaLibraryItems')) ?? []
                    this.hasLoaded = true

                    if (! this.asset(this.previewId)) {
                        this.previewId = String(this.state || this.firstAssetId())
                    }
                } finally {
                    this.isLoading = false
                }
            },
            firstAssetId() {
                return this.assets[0]?.id ?? ''
            },
            asset(id) {
                return this.assets.find((asset) => asset.id === String(id ?? '')) ?? null
            },
            selectedAsset() {
                const current = String(this.state ?? '')

                if (! current) {
                    return null
                }

                return this.selected?.id === current ? this.selected : this.asset(current)
            },
            previewAsset() {
                return this.asset(this.previewId)
            },
            isSelected(id) {
                return String(this.state ?? '') === String(id)
            },
            hasSelection() {
                return this.selectedAsset() !== null
            },
            matches(text) {
                return ! this.search.trim() || text.includes(this.search.trim().toLocaleLowerCase())
            },
            filteredAssets() {
                return this.assets.filter((asset) => this.matches(asset.search))
            },
            hasMatches() {
                return this.filteredAssets().length > 0
            },
            openLibrary() {
                this.search = ''
                this.previewId = String(this.state || this.firstAssetId())
                this.$dispatch('open-modal', { id: @js($modalId) })
                this.loadAssets()
            },
            selectAsset(id) {
                this.selected = this.asset(id) ?? this.selected
                this.state = Number(id)
                this.previewId = String(id)
                this.$dispatch('close-modal', { id: @js($modalId) })
            },
            clearSelection() {
                this.state = null
                this.selected = null
            },
            handleUpload(event) {
                if (event.detail?.pickerId !== @js($modalId)) {
                    return
                }

                this.state = Number(event.detail.assetId)
                this.previewId = String(event.detail.assetId)
                this.$nextTick(() => setTimeout(() => this.openLibrary(), 75))
            },
        }"
        x-on:media-library-uploaded.window="handleUpload($event)"
    >
        <div class="iws-media-picker__control">
            <div class="iws-media-picker__current">
                <template x-if="selectedAsset()">
                    <div class="iws-media-picker__selected">
                        <span class="iws-media-picker__selected-preview">
                            <template x-if="selectedAsset().kind === 'image' && selectedAsset().thumbnailUrl">
                                <img x-bind:src="selectedAsset().thumbnailUrl" alt="" loading="lazy">
                            </template>
                            <template x-if="selectedAsset().kind === 'video' && selectedAsset().thumbnailUrl">
                                <video x-bind:src="selectedAsset().thumbnailUrl + '#t=0.1'" muted playsinline preload="metadata" tabindex="-1"></video>
                            </template>
                            <template x-if="selectedAsset().kind === 'video'">
                                <span class="iws-media-picker__selected-play" aria-hidden="true">▶</span>
                            </template>
                            <template x-if="selectedAsset().kind === 'file'">
                                <span aria-hidden="true">⌁</span>
                            </template>
                        </span>
                        <span class="iws-media-picker__selected-copy">
                            <strong x-text="selectedAsset().name"></strong>
                            <small><span x-text="selectedAsset().kindLabel"></span> · #<span x-text="selectedAsset().id"></span></small>
                        </span>
                    </div>
                </template>

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
                    wire:target="{{ $getStatePath() }}"
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
                    wire:target="{{ $getStatePath() }}"
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

                <div class="iws-media-library__no-results" x-show="! hasLoaded">
                    <x-filament::loading-indicator class="iws-media-library__loading-indicator" />
                    <span>{{ Studio::text('media_library_loading') }}</span>
                </div>

                <div class="iws-media-library__no-results" x-cloak x-show="hasLoaded && ! assets.length">
                    <strong>{{ Studio::text('media_library_empty') }}</strong>
                    <span>{{ Studio::text('media_no_results') }}</span>
                </div>

                <div class="iws-media-library__layout" x-cloak x-show="hasLoaded && assets.length">
                    <div class="iws-media-library__browser">
                        <div class="iws-media-library__grid" role="list">
                            <template x-for="asset in filteredAssets()" x-bind:key="asset.id">
                                <article
                                    class="iws-media-library__card"
                                    role="listitem"
                                    x-bind:data-asset-id="asset.id"
                                    x-bind:class="{ 'is-selected': isSelected(asset.id), 'is-previewing': previewId === asset.id }"
                                >
                                    <button
                                        type="button"
                                        class="iws-media-library__card-main"
                                        x-on:click="previewId = asset.id"
                                        x-bind:aria-label="@js(Studio::text('preview')).concat(': ', asset.name)"
                                    >
                                        <span class="iws-media-library__thumb">
                                            <template x-if="asset.kind === 'image' && asset.thumbnailUrl">
                                                <img x-bind:src="asset.thumbnailUrl" alt="" loading="lazy">
                                            </template>
                                            <template x-if="asset.kind === 'video' && asset.thumbnailUrl">
                                                <video x-bind:src="asset.thumbnailUrl + '#t=0.1'" muted playsinline preload="metadata" tabindex="-1"></video>
                                            </template>
                                            <template x-if="asset.kind === 'video'">
                                                <span class="iws-media-library__play" aria-hidden="true">▶</span>
                                            </template>
                                            <template x-if="asset.kind === 'file'">
                                                <span class="iws-media-library__file" aria-hidden="true">⌁</span>
                                            </template>
                                        </span>
                                        <span class="iws-media-library__card-copy">
                                            <strong x-bind:title="asset.name" x-text="asset.name"></strong>
                                            <small><span x-text="asset.kindLabel"></span> · #<span x-text="asset.id"></span></small>
                                        </span>
                                    </button>

                                    <div class="iws-media-library__card-actions">
                                        <button type="button" x-on:click="previewId = asset.id">
                                            {{ Studio::text('preview') }}
                                        </button>
                                        <button type="button" class="is-primary" x-on:click="selectAsset(asset.id)">
                                            {{ Studio::text('select_media') }}
                                        </button>
                                    </div>
                                </article>
                            </template>
                        </div>

                        <div class="iws-media-library__no-results" x-cloak x-show="! hasMatches()">
                            <strong>{{ Studio::text('media_no_results') }}</strong>
                        </div>
                    </div>

                    <aside class="iws-media-library__preview" aria-live="polite">
                        <template x-if="previewAsset()">
                            <section>
                                <div class="iws-media-library__preview-media">
                                    <template x-if="previewAsset().kind === 'image' && previewAsset().previewUrl">
                                        <img x-bind:src="previewAsset().previewUrl" x-bind:alt="previewAsset().alt" loading="lazy">
                                    </template>
                                    <template x-if="previewAsset().kind === 'video' && previewAsset().previewUrl">
                                        <video x-bind:src="previewAsset().previewUrl" controls playsinline preload="metadata"></video>
                                    </template>
                                    <template x-if="previewAsset().kind === 'file'">
                                        <div class="iws-media-library__file-preview" aria-hidden="true">⌁</div>
                                    </template>
                                </div>
                                <div class="iws-media-library__preview-copy">
                                    <small>{{ Studio::text('preview') }}</small>
                                    <h3 x-text="previewAsset().name"></h3>
                                    <p><span x-text="previewAsset().kindLabel"></span> · #<span x-text="previewAsset().id"></span></p>
                                </div>
                                <x-filament::button
                                    type="button"
                                    x-on:click="selectAsset(previewAsset().id)"
                                    wire:target="{{ $getStatePath() }}"
                                    class="iws-media-library__select-button"
                                >
                                    {{ Studio::text('select_this_media') }}
                                </x-filament::button>
                            </section>
                        </template>
                    </aside>
                </div>
            </div>
        </x-filament::modal>
    </div>
</x-dynamic-component>
