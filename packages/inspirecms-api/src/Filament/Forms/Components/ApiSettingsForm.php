<?php

namespace SolutionForest\InspireCmsApi\Filament\Forms\Components;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Str;

class ApiSettingsForm
{
    /**
     * Get the form schema for DocumentType API settings.
     */
    public static function getDocumentTypeSchema(): array
    {
        return [
            Section::make('API Configuration')
                ->description('Configure how this content type is exposed via the REST API.')
                ->icon('heroicon-o-code-bracket')
                ->collapsible()
                ->collapsed(fn ($record) => ! ($record?->api_settings['enabled'] ?? false))
                ->schema([
                    Toggle::make('api_settings.enabled')
                        ->label('Enable API')
                        ->helperText('Expose this content type via the REST API.')
                        ->live()
                        ->default(false),

                    TextInput::make('api_settings.slug')
                        ->label('API Endpoint Slug')
                        ->prefix('/api/v1/')
                        ->placeholder(fn ($record) => Str::slug($record?->name ?? 'content-type', '-'))
                        ->helperText('Custom URL slug for API endpoints. Leave empty to auto-generate from name.')
                        ->visible(fn ($get) => $get('api_settings.enabled'))
                        ->maxLength(100)
                        ->alphaDash(),

                    Section::make('Access Control')
                        ->visible(fn ($get) => $get('api_settings.enabled'))
                        ->schema([
                            Toggle::make('api_settings.public_read')
                                ->label('Public Read Access')
                                ->helperText('Allow unauthenticated users to read content.')
                                ->default(false)
                                ->live(),

                            Toggle::make('api_settings.public_write')
                                ->label('Public Write Access')
                                ->helperText('Allow unauthenticated users to create and update content. Use with caution!')
                                ->default(false)
                                ->visible(fn ($get) => $get('api_settings.public_read')),

                            CheckboxList::make('api_settings.allowed_operations')
                                ->label('Allowed Operations')
                                ->options([
                                    'index' => 'List (GET /items) - Retrieve list of items',
                                    'show' => 'Show (GET /items/{id}) - Get single item',
                                    'store' => 'Create (POST /items) - Create new item',
                                    'update' => 'Update (PUT /items/{id}) - Update existing item',
                                    'destroy' => 'Delete (DELETE /items/{id}) - Remove item',
                                ])
                                ->default(['index', 'show'])
                                ->columns(1)
                                ->helperText('Select which API operations are available for this content type.'),
                        ]),

                    Section::make('Advanced Settings')
                        ->visible(fn ($get) => $get('api_settings.enabled'))
                        ->collapsed()
                        ->schema([
                            TextInput::make('api_settings.max_per_page')
                                ->label('Maximum Items Per Page')
                                ->numeric()
                                ->default(100)
                                ->minValue(1)
                                ->maxValue(500)
                                ->helperText('Maximum number of items that can be requested per page.'),

                            TextInput::make('api_settings.default_includes')
                                ->label('Default Includes')
                                ->placeholder('parent,children,author')
                                ->helperText('Comma-separated list of relationships to include by default in API responses.'),
                        ]),
                ]),
        ];
    }

    /**
     * Get the form schema for Field API settings.
     */
    public static function getFieldSchema(): array
    {
        return [
            Section::make('API Settings')
                ->description('Configure how this field is exposed in API responses.')
                ->icon('heroicon-o-code-bracket')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Toggle::make('api_settings.exposed')
                        ->label('Expose in API')
                        ->helperText('Include this field in API responses.')
                        ->default(true)
                        ->live(),

                    Toggle::make('api_settings.readable')
                        ->label('Readable')
                        ->helperText('Include in GET responses.')
                        ->default(true)
                        ->visible(fn ($get) => $get('api_settings.exposed')),

                    Toggle::make('api_settings.writable')
                        ->label('Writable')
                        ->helperText('Allow setting via POST/PUT requests.')
                        ->default(true)
                        ->visible(fn ($get) => $get('api_settings.exposed')),

                    TextInput::make('api_settings.alias')
                        ->label('API Field Name')
                        ->placeholder('Uses original field name')
                        ->helperText('Optional: Use a different name for this field in API responses.')
                        ->visible(fn ($get) => $get('api_settings.exposed'))
                        ->maxLength(100)
                        ->alphaDash(),
                ]),
        ];
    }

    /**
     * Mutate form data before filling.
     */
    public static function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure api_settings is properly formatted
        if (isset($data['api_settings']) && is_string($data['api_settings'])) {
            $data['api_settings'] = json_decode($data['api_settings'], true) ?? [];
        }

        // Parse default_includes from array to comma-separated string
        if (isset($data['api_settings']['default_includes']) && is_array($data['api_settings']['default_includes'])) {
            $data['api_settings']['default_includes'] = implode(',', $data['api_settings']['default_includes']);
        }

        return $data;
    }

    /**
     * Mutate form data before saving.
     */
    public static function mutateFormDataBeforeSave(array $data): array
    {
        // Parse default_includes from comma-separated string to array
        if (isset($data['api_settings']['default_includes']) && is_string($data['api_settings']['default_includes'])) {
            $includes = $data['api_settings']['default_includes'];
            $data['api_settings']['default_includes'] = array_filter(
                array_map('trim', explode(',', $includes))
            );
        }

        // Ensure slug is set
        if (! empty($data['api_settings']['enabled']) && empty($data['api_settings']['slug'])) {
            $data['api_settings']['slug'] = Str::slug($data['name'] ?? 'content', '-');
        }

        return $data;
    }
}
