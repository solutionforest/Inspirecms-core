<div class="ve-templates-panel">
    {{-- Search --}}
    <div class="ve-templates-panel__search">
        <div class="ve-search-input">
            <svg class="ve-search-input__icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('visual-editor::visual-editor.templates.search_placeholder') }}"
                class="ve-search-input__field"
            >
            @if($search)
                <button wire:click="$set('search', '')" class="ve-search-input__clear">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Categories --}}
    <div class="ve-templates-panel__categories">
        <button
            wire:click="setActiveCategory(null)"
            class="ve-category-chip {{ !$activeCategory ? 've-category-chip--active' : '' }}"
        >
            {{ __('visual-editor::visual-editor.templates.all') }}
        </button>
        @foreach($this->categories as $key => $label)
            <button
                wire:click="setActiveCategory('{{ $key }}')"
                class="ve-category-chip {{ $activeCategory === $key ? 've-category-chip--active' : '' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Templates Grid --}}
    <div class="ve-templates-panel__grid">
        @forelse($this->flatTemplates as $template)
            <div class="ve-template-card" wire:key="template-{{ $template['id'] }}">
                {{-- Thumbnail --}}
                <div class="ve-template-card__thumbnail">
                    @if($template['thumbnail'])
                        <img src="{{ $template['thumbnail'] }}" alt="{{ $template['name'] }}">
                    @else
                        <div class="ve-template-card__placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <path d="M3 9h18"></path>
                                <path d="M9 21V9"></path>
                            </svg>
                        </div>
                    @endif

                    {{-- Badges --}}
                    <div class="ve-template-card__badges">
                        @if($template['is_global'])
                            <span class="ve-badge ve-badge--info">{{ __('visual-editor::visual-editor.templates.global') }}</span>
                        @endif
                        @if($template['is_public'])
                            <span class="ve-badge ve-badge--success">{{ __('visual-editor::visual-editor.templates.public') }}</span>
                        @endif
                    </div>

                    {{-- Actions Overlay --}}
                    <div class="ve-template-card__overlay">
                        <button
                            wire:click="useTemplate('{{ $template['id'] }}')"
                            class="ve-btn ve-btn--primary ve-btn--sm"
                            title="{{ __('visual-editor::visual-editor.templates.use') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14"></path>
                                <path d="M5 12h14"></path>
                            </svg>
                            {{ __('visual-editor::visual-editor.templates.use') }}
                        </button>
                        <div class="ve-template-card__actions">
                            <button
                                wire:click="previewTemplate('{{ $template['id'] }}')"
                                class="ve-btn ve-btn--ghost ve-btn--icon"
                                title="{{ __('visual-editor::visual-editor.templates.preview') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            <button
                                wire:click="duplicateTemplate('{{ $template['id'] }}')"
                                class="ve-btn ve-btn--ghost ve-btn--icon"
                                title="{{ __('visual-editor::visual-editor.templates.duplicate') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                            </button>
                            <button
                                wire:click="deleteTemplate('{{ $template['id'] }}')"
                                wire:confirm="{{ __('visual-editor::visual-editor.templates.delete_confirm') }}"
                                class="ve-btn ve-btn--ghost ve-btn--icon ve-btn--danger"
                                title="{{ __('visual-editor::visual-editor.templates.delete') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Info --}}
                <div class="ve-template-card__info">
                    <h4 class="ve-template-card__name">{{ $template['name'] }}</h4>
                    @if($template['description'])
                        <p class="ve-template-card__description">{{ Str::limit($template['description'], 60) }}</p>
                    @endif
                    <div class="ve-template-card__meta">
                        <span class="ve-template-card__type">{{ $template['type'] }}</span>
                        <span class="ve-template-card__date">{{ $template['created_at'] }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="ve-templates-panel__empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <path d="M3 9h18"></path>
                    <path d="M9 21V9"></path>
                </svg>
                <p>{{ __('visual-editor::visual-editor.templates.empty') }}</p>
                <span class="ve-templates-panel__empty-hint">{{ __('visual-editor::visual-editor.templates.empty_hint') }}</span>
            </div>
        @endforelse
    </div>

    {{-- Save Template Modal --}}
    @if($showSaveModal)
        <div class="ve-modal" x-data="{ open: true }" x-show="open">
            <div class="ve-modal__backdrop" wire:click="closeSaveModal"></div>
            <div class="ve-modal__content">
                <div class="ve-modal__header">
                    <h3>{{ __('visual-editor::visual-editor.templates.save_title') }}</h3>
                    <button wire:click="closeSaveModal" class="ve-modal__close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveTemplate" class="ve-modal__body">
                    <div class="ve-form-group">
                        <label for="templateName" class="ve-label">{{ __('visual-editor::visual-editor.templates.name') }} *</label>
                        <input
                            type="text"
                            id="templateName"
                            wire:model="templateName"
                            class="ve-input"
                            required
                        >
                        @error('templateName') <span class="ve-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="ve-form-group">
                        <label for="templateDescription" class="ve-label">{{ __('visual-editor::visual-editor.templates.description') }}</label>
                        <textarea
                            id="templateDescription"
                            wire:model="templateDescription"
                            class="ve-textarea"
                            rows="3"
                        ></textarea>
                    </div>

                    <div class="ve-form-group">
                        <label for="templateCategory" class="ve-label">{{ __('visual-editor::visual-editor.templates.category') }}</label>
                        <select id="templateCategory" wire:model="templateCategory" class="ve-select">
                            @foreach($this->categories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ve-form-row">
                        <label class="ve-checkbox">
                            <input type="checkbox" wire:model="templateIsPublic">
                            <span>{{ __('visual-editor::visual-editor.templates.make_public') }}</span>
                        </label>

                        <label class="ve-checkbox">
                            <input type="checkbox" wire:model="templateIsGlobal">
                            <span>{{ __('visual-editor::visual-editor.templates.make_global') }}</span>
                        </label>
                    </div>
                </form>

                <div class="ve-modal__footer">
                    <button wire:click="closeSaveModal" class="ve-btn ve-btn--ghost">
                        {{ __('visual-editor::visual-editor.templates.cancel') }}
                    </button>
                    <button wire:click="saveTemplate" class="ve-btn ve-btn--primary">
                        {{ __('visual-editor::visual-editor.templates.save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Preview Modal --}}
    @if($showPreviewModal && $this->previewTemplate)
        <div class="ve-modal ve-modal--lg" x-data="{ open: true }" x-show="open">
            <div class="ve-modal__backdrop" wire:click="closePreviewModal"></div>
            <div class="ve-modal__content">
                <div class="ve-modal__header">
                    <h3>{{ $this->previewTemplate['name'] }}</h3>
                    <button wire:click="closePreviewModal" class="ve-modal__close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="ve-modal__body">
                    <div class="ve-preview">
                        @if($this->previewTemplate['thumbnail'])
                            <img src="{{ $this->previewTemplate['thumbnail'] }}" alt="{{ $this->previewTemplate['name'] }}" class="ve-preview__image">
                        @else
                            <div class="ve-preview__placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <path d="M3 9h18"></path>
                                    <path d="M9 21V9"></path>
                                </svg>
                                <p>{{ __('visual-editor::visual-editor.templates.no_preview') }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="ve-preview__details">
                        @if($this->previewTemplate['description'])
                            <p class="ve-preview__description">{{ $this->previewTemplate['description'] }}</p>
                        @endif
                        <div class="ve-preview__meta">
                            <span><strong>{{ __('visual-editor::visual-editor.templates.type') }}:</strong> {{ $this->previewTemplate['type'] }}</span>
                            <span><strong>{{ __('visual-editor::visual-editor.templates.category') }}:</strong> {{ $this->categories[$this->previewTemplate['category']] ?? $this->previewTemplate['category'] }}</span>
                            <span><strong>{{ __('visual-editor::visual-editor.templates.created') }}:</strong> {{ $this->previewTemplate['created_at'] }}</span>
                            <span><strong>{{ __('visual-editor::visual-editor.templates.creator') }}:</strong> {{ $this->previewTemplate['creator'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="ve-modal__footer">
                    <button wire:click="closePreviewModal" class="ve-btn ve-btn--ghost">
                        {{ __('visual-editor::visual-editor.templates.close') }}
                    </button>
                    <button wire:click="useTemplate('{{ $this->previewTemplate['id'] }}')" wire:click.then="closePreviewModal" class="ve-btn ve-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14"></path>
                            <path d="M5 12h14"></path>
                        </svg>
                        {{ __('visual-editor::visual-editor.templates.use') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
