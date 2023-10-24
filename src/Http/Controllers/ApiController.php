<?php

namespace Botble\Api\Http\Controllers;

use Botble\Api\Http\Requests\ApiSettingRequest;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    public function settings()
    {
        PageTitle::setTitle(trans('packages/api::api.settings'));

        Assets::addScriptsDirectly('vendor/core/core/setting/js/setting.js');
        Assets::addStylesDirectly('vendor/core/core/setting/css/setting.css');

        if (version_compare('7.0.0', get_core_version(), '>=')) {
            return view('packages/api::settings');
        }

        return view('packages/api::settings-v6');
    }

    public function storeSettings(ApiSettingRequest $request, BaseHttpResponse $response)
    {
        $this->saveSettings($request->except([
            '_token',
        ]));

        return $response
            ->setPreviousUrl(route('api.settings'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    protected function saveSettings(array $data)
    {
        foreach ($data as $settingKey => $settingValue) {
            if (is_array($settingValue)) {
                $settingValue = json_encode(array_filter($settingValue));
            }

            setting()->set($settingKey, (string)$settingValue);
        }

        setting()->save();
    }
}
