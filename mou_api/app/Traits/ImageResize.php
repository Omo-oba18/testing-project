<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait ImageResize
{
    protected static $imageSize = [
        'large' => 1280,
        'medium' => 780,
        'small' => 250,
    ];

    protected static $imageFolder = '/images/';

    /**
     * @param  null  $subFolder
     * @param  null  $filename
     * @return bool|string
     */
    public static function upload($file, $subFolder = null, $filename = null)
    {
        if (empty($file)) {
            return false;
        }

        $folder = '/'.self::$imageFolder.'/'.($subFolder ? $subFolder.'/' : '');
        $folder = str_replace('//', '/', $folder);

        //tao folder neu chua co
        if (! \Storage::disk(config('filesystems.disks.public.visibility'))->has($folder)) {
            \Storage::makeDirectory(config('filesystems.disks.public.visibility').$folder);
        }

        $fileExt = $file->getClientOriginalExtension();
        if (empty($filename)) {
            $filename = basename($file->getClientOriginalName(), $fileExt);
        }
        $filename = time().'_'.Str::slug($filename);
        // large size
        $pathFile = $folder.$filename.'_'.Str::random(4).'.'.$fileExt;
        \Image::make($file->getRealPath())->resize(self::$imageSize['large'], self::$imageSize['large'], function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save(public_path('/storage').$pathFile, 90);
        // medium size
        \Image::make($file->getRealPath())->resize(self::$imageSize['medium'], self::$imageSize['medium'], function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save(public_path('/storage').self::getMediumSize($pathFile), 90);
        // small size
        \Image::make($file->getRealPath())->resize(self::$imageSize['small'], self::$imageSize['small'], function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save(public_path('/storage').self::getSmallSize($pathFile), 90);

        return config('filesystems.disks.public.visibility').$pathFile;
    }

    /**
     * @param  null  $size
     * @param  null  $subFolder
     * @param  null  $filename
     * @return bool|string
     */
    public static function uploadOrigin($file, $subFolder = null, $size = 'medium', $filename = null)
    {
        if (empty($file)) {
            return false;
        }

        $folder = '/'.self::$imageFolder.'/'.($subFolder ? $subFolder.'/' : '');
        $folder = str_replace('//', '/', $folder);

        //tao folder neu chua co
        if (! \Storage::disk(config('filesystems.disks.public.visibility'))->has($folder)) {
            \Storage::makeDirectory(config('filesystems.disks.public.visibility').$folder);
        }

        $fileExt = $file->getClientOriginalExtension();
        if (empty($filename)) {
            $filename = basename($file->getClientOriginalName(), $fileExt);
        }
        $filename = time().'_'.Str::slug($filename);

        // large size
        $pathFile = $folder.$filename.'_'.Str::random(4).'.'.$fileExt;
        \Image::make($file->getRealPath())->resize(self::$imageSize[$size], self::$imageSize[$size], function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save(public_path('/storage').$pathFile, 90);

        return config('filesystems.disks.public.visibility').$pathFile;
    }

    /**
     * Delete all image size by Path
     */
    public static function deleteImage($path)
    {
        if (empty($path)) {
            return;
        }
        \Storage::delete(self::getMediumSize($path));
        \Storage::delete(self::getSmallSize($path));
        \Storage::delete($path);
    }

    /**
     * Get path image with medium size
     *
     * @return string
     */
    private static function getMediumSize($path)
    {
        if (empty($path)) {
            return null;
        }
        $arStr = explode('/', $path);
        $arStr[count($arStr) - 1] = 'medium_'.$arStr[count($arStr) - 1];

        return implode('/', $arStr);
    }

    /**
     * Get path image with small size
     *
     * @return string
     */
    private static function getSmallSize($path)
    {
        if (empty($path)) {
            return null;
        }
        $arStr = explode('/', $path);
        $arStr[count($arStr) - 1] = 'small_'.$arStr[count($arStr) - 1];

        return implode('/', $arStr);
    }

    /**
     * @param  string  $size = "large|medium|small|thumbnail"
     * @return string
     */
    public static function getPhotoAsset($path, $size = 'large')
    {
        if (empty($path)) {
            return null;
        }
        $newPath = $path;
        switch ($size) {
            case 'medium':
                $newPath = self::getMediumSize($path);
                break;
            case 'small':
            case 'thumbnail':
                $newPath = self::getSmallSize($path);
                break;
        }

        return asset(\Storage::url($newPath));
    }
}
