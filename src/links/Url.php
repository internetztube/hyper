<?php
namespace verbb\hyper\links;

use verbb\hyper\base\Link;

use Craft;
use craft\helpers\App;
use craft\validators\UrlValidator;

class Url extends Link 
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('hyper', 'URL');
    }

    public static function linkValuePlaceholder(): ?string
    {
        return rtrim(Craft::$app->getSites()->primarySite->baseUrl, '/');
    }


    // Properties
    // =========================================================================

    public ?string $placeholder = null;


    // Public Methods
    // =========================================================================

    public function getSettingsConfig(): array
    {
        $values = parent::getSettingsConfig();
        $values['placeholder'] = $this->placeholder;

        return $values;
    }

    public function defaultPlaceholder(): ?string
    {
        return rtrim(Craft::$app->getSites()->primarySite->baseUrl, '/');
    }


    // Protected Methods
    // =========================================================================

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        
        $rules[] = [['linkValue'], UrlValidator::class, 'enableIDN' => App::supportsIdn()];

        return $rules;
    }

}
