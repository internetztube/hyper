<?php
namespace verbb\hyper\links;

use verbb\hyper\base\ElementLink;

use Craft;

use craft\commerce\elements\Variant as VariantElement;

class Variant extends ElementLink
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('hyper', 'Variant');
    }

    public static function getRequiredPlugins(): array
    {
        return ['commerce'];
    }

    public static function elementType(): string
    {
        return VariantElement::class;
    }

    public static function checkElementUri(): bool
    {
        return false;
    }
}
