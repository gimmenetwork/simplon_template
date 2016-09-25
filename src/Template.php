<?php

namespace Simplon\Template;

use Simplon\Mustache\Mustache;
use Simplon\Phtml\Phtml;

/**
 * Template
 * @package Simplon\Template
 * @author  Tino Ehrich (tino@bigpun.me)
 */
class Template
{
    /**
     * @var array
     */
    private $assetsCss = [];

    /**
     * @var array
     */
    private $assetsJs = [];

    /**
     * @var array
     */
    private $assetsCode = [];

    /**
     * @param array $pathAssets
     *
     * @return bool
     */
    public function setAssetsCss(array $pathAssets)
    {
        foreach ($pathAssets as $blockId => $paths)
        {
            foreach ($paths as $path)
            {
                $this->addAssetCss($path, $blockId);
            }
        }

        return true;
    }

    /**
     * @param string $pathAsset
     * @param string $blockId
     *
     * @return bool
     */
    public function addAssetCss($pathAsset, $blockId = null)
    {
        if ($blockId === null)
        {
            $blockId = 'default';
        }

        $this->assetsCss[$blockId][md5($pathAsset)] = '<link rel="stylesheet" href="' . $pathAsset . '">';

        return true;
    }

    /**
     * @param array $pathAssets
     *
     * @return bool
     */
    public function setAssetsJs(array $pathAssets)
    {
        foreach ($pathAssets as $blockId => $paths)
        {
            foreach ($paths as $path)
            {
                $this->addAssetJs($path, $blockId);
            }
        }

        return true;
    }

    /**
     * @param string $pathAsset
     * @param string $blockId
     *
     * @return bool
     */
    public function addAssetJs($pathAsset, $blockId = null)
    {
        if ($blockId === null)
        {
            $blockId = 'default';
        }

        $this->assetsJs[$blockId][md5($pathAsset)] = '<script type="text/javascript" src="' . $pathAsset . '"></script>';

        return true;
    }

    /**
     * @param string $code
     * @param string $blockId
     *
     * @return bool
     */
    public function addAssetCode($code, $blockId = null)
    {
        if ($blockId === null)
        {
            $blockId = 'default';
        }

        $this->assetsCode[$blockId][] = $code;

        return true;
    }

    /**
     * @param string $pathTemplate
     * @param array $params
     * @param array $customerParsers
     * @param bool $withFileExtension
     *
     * @return string
     */
    public function renderMustache($pathTemplate, array $params = [], array $customerParsers = [], $withFileExtension = false)
    {
        // handle assets
        $params = $this->enrichParamsWithAssets($params);

        // render template
        $template = Mustache::renderByFile($pathTemplate, $params, $customerParsers, $withFileExtension ? '' : 'mustache');

        return (string)$template;
    }

    /**
     * @param string $pathTemplate
     * @param array $params
     * @param bool $withFileExtension
     *
     * @return string
     */
    public function renderPhtml($pathTemplate, array $params = [], $withFileExtension = false)
    {
        // handle assets
        $params = $this->enrichParamsWithAssets($params);

        // render template
        $template = Phtml::render($pathTemplate, $params, $withFileExtension ? '' : '.phtml');

        return (string)$template;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    private function enrichParamsWithAssets(array $params)
    {
        $params = array_merge($params, $this->flattenAssets('css', $this->assetsCss));
        $params = array_merge($params, $this->flattenAssets('js', $this->assetsJs));
        $params = array_merge($params, $this->flattenAssets('code', $this->assetsCode, true));

        return $params;
    }

    /**
     * @param string $type
     * @param array $blockAssets
     * @param bool $isCode
     *
     * @return array
     */
    private function flattenAssets($type, array $blockAssets, $isCode = false)
    {
        $flatAssets = [];

        foreach ($blockAssets as $blockId => $assets)
        {
            $code = "\n" . join("\n", $assets) . "\n";

            if ($isCode === true && strpos($code, '<script') === false)
            {
                /** @noinspection BadExpressionStatementJS */
                $code = str_replace('{code}', $code, '<script type="text/javascript">{code}</script>');
            }

            $flatAssets[$type . ucfirst(strtolower($blockId))] = $code;
        }

        return $flatAssets;
    }
}