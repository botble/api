<?php

namespace Botble\Api\Forms\Settings;

use Botble\Api\Facades\ApiHelper;
use Botble\Api\Http\Requests\ApiSettingRequest;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Setting\Forms\SettingForm;

class ApiSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setValidatorClass(ApiSettingRequest::class)
            ->setSectionTitle(trans('packages/api::api.setting_title'))
            ->setSectionDescription(trans('packages/api::api.setting_description'))
            ->contentOnly()
            ->add(
                'api_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('packages/api::api.api_enabled'))
                    ->value(ApiHelper::enabled())
                    ->toArray()
            );
    }
}
