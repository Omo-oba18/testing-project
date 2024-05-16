<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\DynamicLink\AndroidInfo;
use Kreait\Firebase\DynamicLink\CreateDynamicLink;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;
use Kreait\Firebase\DynamicLink\IOSInfo;
use Kreait\Firebase\Factory;

class Util
{
    /**
     * Escape special characters for a LIKE query.
     */
    public static function escape_like(string $value, string $char = '\\'): string
    {
        return str_replace(
            [$char, '%', '_'],
            [$char.$char, $char.'%', $char.'_'],
            $value
        );
    }

    /**
     * Escape special characters for a LIKE query.
     *
     * @param  int  $type: 1 - send to bussiness, 0 - send to personal
     * @return string
     */
    public static function createDynamicLink(?int $type, string $params)
    {

        $path = config('firebase.credentials.path_file');

        $factory = (new Factory)->withServiceAccount($path);
        $dynamicLinks = $factory->createDynamicLinksService();
        $baseUrl = url('/');
        $urlDomain = $type ? config('constant.bussiness_link') : config('constant.personal_link');
        $bundle = $type ? config('constant.bussiness_bundle') : config('constant.personal_bundle');
        $app_store_id = config('constant.app_store_id');

        try {
            $action = CreateDynamicLink::forUrl($baseUrl.'/'.$params)
                ->withDynamicLinkDomain($urlDomain)
                ->withUnguessableSuffix() // default
                ->withIOSInfo(
                    IOSInfo::new()
                        ->withAppStoreId($app_store_id)
                        ->withBundleId($bundle)
                    // ->withFallbackLink($baseUrl)
                )
                ->withAndroidInfo(
                    AndroidInfo::new()
                        // ->withFallbackLink($baseUrl)
                        ->withPackageName($bundle)
                        ->withMinPackageVersionCode('1')
                );
            $link = $dynamicLinks->createDynamicLink($action);

            return $link->__toString();
        } catch (FailedToCreateDynamicLink $e) {
            echo $e->getMessage();
            exit;
        }
    }

    public static function file_delete(string|array $paths = null, $disk = null)
    {
        $disk = $disk ?: config('filesystems.default');
        if (empty($paths)) {
            return;
        }

        return Storage::disk($disk)->delete($paths);
    }

    public static function file_url(string $path = null, $disk = null)
    {
        $disk = $disk ? $disk : config('filesystems.default');
        if (! $path) {
            return;
        }

        $url = Storage::disk($disk)->url($path);

        return match ($disk) {
            's3' => $url,
            default => asset($url)
        };
    }
}
