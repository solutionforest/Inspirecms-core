<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\Group;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class PropertyDataGroup extends Group
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->key('propertyData');

        if (blank($this->childComponents)) {
            $this->schema(function (Get $get, ?Model $record, string $operation) {

                $fieldGroups = $this->getFieldGroupsFromDocumentType($operation == 'create' ? $get('document_type_id') : $record->documentType);

                $groupComponents = [];

                foreach ($fieldGroups as $fieldGroupModel) {

                    $groupComponents[] = $fieldGroupModel->toFilamentComponent();
                }

                return $groupComponents;
            });
        }
    }

    public function getFieldGroupsFromDocumentType(int | string | Model | null $documentType)
    {
        if ($documentType instanceof Model) {

        } elseif (is_null($documentType)) {
            return collect();
        } else {

            $documentType = InspireCmsConfig::getDocumentTypeModelClass()::query()
                ->with(['fieldGroups'])
                ->whereHas('fieldGroups')
                ->find($documentType);

            if (! $documentType) {
                return collect();
            }
        }

        // With parent document type
        $documentTypes = collect($documentType->ancestors())->push($documentType);

        return $documentTypes->pluck('fieldGroups')->flatten(1);

    }
}
