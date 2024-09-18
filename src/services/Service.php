<?php
namespace verbb\hyper\services;

use verbb\hyper\base\LinkInterface;
use verbb\hyper\fields\HyperField;
use verbb\hyper\helpers\Plugin;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Entry;
use craft\elements\db\ElementQueryInterface;
use craft\events\ConfigEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use craft\models\FieldLayout;
use craft\models\FieldLayoutForm;

class Service extends Component
{
    // Properties
    // =========================================================================

    private array $_layoutsByUid = [];


    // Public Methods
    // =========================================================================

    public function handleChangedField(ConfigEvent $event): void
    {
        $data = $event->newValue ?? [];

        if (!is_array($data)) {
            return;
        }

        // This handler fires on every field-change, so we need to ensure this field is a Hyper field.
        // We want to watch for Hyper field changes to update each block type's field layout.
        if ($data['type'] !== HyperField::class) {
            return;
        }

        $linkTypes = $data['settings']['linkTypes'] ?? [];
        $linkTypes = ProjectConfigHelper::unpackAssociativeArrays($linkTypes);
        $this->saveField($linkTypes, $event);
    }

    public function saveField(array $linkTypes, ?ConfigEvent $event = null): void
    {
        $fieldsService = Craft::$app->getFields();

        // Ensure we update all field layouts, for each blocktype
        foreach ($linkTypes as $linkType) {
            $layoutUid = $linkType['layoutUid'] ?? '';
            $layoutConfig = $linkType['layoutConfig'] ?? [];

            if (!$layoutUid || !$layoutConfig) {
                continue;
            }

            // Ensure we remove `uid` from the `layoutConfig` - we don't want it
            ArrayHelper::remove($layoutConfig, 'uid');

            $fieldLayout = FieldLayout::createFromConfig($layoutConfig);
            $fieldLayout->type = $linkType['type'];
            $fieldLayout->uid = $layoutUid;
            $fieldsService->saveLayout($fieldLayout);
        }
    }

    public function handleDeletedField(ConfigEvent $event): void
    {
        $data = $event->oldValue ?? [];

        $fieldsService = Craft::$app->getFields();

        if (!is_array($data)) {
            return;
        }

        // This handler fires on every field-change, so we need to ensure this field is a Hyper field.
        // We want eo watch for Hyper field changes to update each block type's field layout.
        if ($data['type'] !== HyperField::class) {
            return;
        }

        $linkTypes = $data['settings']['linkTypes'] ?? [];
        $linkTypes = ProjectConfigHelper::unpackAssociativeArrays($linkTypes);

        foreach ($linkTypes as $linkType) {
            $layoutUid = $linkType['layoutUid'] ?? '';

            // Add an extra check in here to ensure the layout exists, before deleting it. Deleting via ID may throw an error
            // if the field layout doesn't exist.
            if ($layout = $fieldsService->getLayoutByUid($layoutUid)) {
                $fieldsService->deleteLayout($layout);
            }
        }
    }

    public function handleChangedBlockType(ConfigEvent $event): void
    {
        $fields = $event->newValue['fields'] ?? [];

        foreach ($fields as $field) {
            if ($field['type'] === HyperField::class) {
                $configEvent = new ConfigEvent([
                    'newValue' => $field,
                ]);

                // Call the regular event handler with a fake event to prevent duplicate code
                $this->handleChangedField($configEvent);
            }
        }
    }

    public function handleDeletedBlockType(ConfigEvent $event): void
    {
        $fields = $event->oldValue['fields'] ?? [];

        foreach ($fields as $field) {
            if ($field['type'] === HyperField::class) {
                $configEvent = new ConfigEvent([
                    'oldValue' => $field,
                ]);

                // Call the regular event handler with a fake event to prevent duplicate code
                $this->handleDeletedField($configEvent);
            }
        }
    }

    public function isPluginInstalledAndEnabled(string $pluginHandle): bool
    {
        return Plugin::isPluginInstalledAndEnabled($pluginHandle);
    }

    public function getRelatedElementsQuery(array $params = []): ?ElementQueryInterface
    {
        $fieldHandle = $params['relatedTo']['field'] ?? null;

        if (!$fieldHandle) {
            return null;
        }

        $hyperField = Craft::$app->getFields()->getFieldByHandle($fieldHandle);

        if (!$hyperField) {
            return null;
        }

        $fieldId = $hyperField->id;

        $elementType = $params['elementType'] ?? Entry::class;

        // Find all the element IDs that match the field and type
        $result = (new Query())
            ->select(['targetId AS id', 'targetSiteId AS siteId'])
            ->from(['{{%hyper_element_cache}}'])
            ->where(['fieldId' => $fieldId, 'targetType' => $elementType])
            ->indexBy(function($row) {
                return $row['id'] . ':' . $row['siteId'];
            })
            ->all();

        $elementParams = [];

        foreach ($result as $value) {
            $elementParams['id'][] = $value['id'];
            $elementParams['siteId'][] = $value['siteId'];
        }

        if (isset($params['site'])) {
            $elementParams['site'] = $params['site'];
        }

        if (isset($params['criteria'])) {
            $elementParams = array_merge($elementParams, $params['criteria']);
        }

        $elementQuery = $elementType::find();
        Craft::configure($elementQuery, $elementParams);

        return $elementQuery;
    }


    public function createForm(FieldLayout $fieldLayout, ?LinkInterface $link = null, bool $static = false, array $config = []): FieldLayoutForm
    {
        $element = new \verbb\hyper\models\FakeElement();

        // Create a fake link ID so that some fields like Matrix will work with this fake element
        $element->id = rand();
        $element->fieldLayoutId = $fieldLayout->id;

        foreach ($link->fields as $fieldHandle => $value) {
            if ($field = $link->getFieldLayout()->getFieldByHandle($fieldHandle)) {
                $element->setFieldValues([$fieldHandle => $value]);
            }
        }

        return $fieldLayout->createForm($element);
    }
}
