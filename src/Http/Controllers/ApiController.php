<?php

namespace Botble\Api\Http\Controllers;

use Botble\Api\Forms\Settings\ApiSettingForm;
use Botble\Api\Http\Requests\ApiSettingRequest;
use Botble\Api\Tables\SanctumTokenTable;
use Botble\Base\Facades\Assets;
use Botble\Setting\Http\Controllers\SettingController;

class ApiController extends SettingController
{
    public function edit(SanctumTokenTable $sanctumTokenTable)
    {
        $this->pageTitle(trans('packages/api::api.settings'));

        $this->breadcrumb()
            ->add(trans('core/setting::setting.title'), route('settings.index'))
            ->add(trans('packages/api::api.settings'));

        Assets::addScriptsDirectly('vendor/core/packages/api/js/api-settings.js')
            ->addStylesDirectly('vendor/core/packages/api/css/api-settings.css');

        $form = ApiSettingForm::create();

        $sanctumTokenTable->setAjaxUrl(route('api.sanctum-token.index'));

        // Add translations for JavaScript
        $translations = [
            'api_key_generated' => trans('packages/api::api.api_key_generated'),
            'api_key_copied' => trans('packages/api::api.api_key_copied'),
            'api_key_edit_enabled' => trans('packages/api::api.api_key_edit_enabled'),
        ];

        return view('packages/api::settings', compact('form', 'sanctumTokenTable', 'translations'));
    }

    public function update(ApiSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
