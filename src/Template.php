<?php

namespace Simplon\Template;

use Simplon\Mustache\Mustache;
use Simplon\Mustache\MustacheException;
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
     * @return array
     */
    public function getAssetsCss()
    {
        return $this->assetsCss;
    }

    /**
     * @param array $pathAssets
     *
     * @return Template
     */
    public function addMultipleAssetsCss(array $pathAssets)
    {
        foreach ($pathAssets as $blockId => $paths)
        {
            foreach ($paths as $path)
            {
                $this->addAssetCss($path, $blockId);
            }
        }

        return $this;
    }

    /**
     * @param string $pathAsset
     * @param string $blockId
     *
     * @return Template
     */
    public function addAssetCss($pathAsset, $blockId = null)
    {
        if ($blockId === null)
        {
            $blockId = 'default';
        }

        $this->assetsCss = $this->addAsset($this->assetsCss, $blockId, $pathAsset);

        return $this;
    }

    /**
     * @return array
     */
    public function getAssetsJs()
    {
        return $this->assetsJs;
    }

    /**
     * @param array $pathAssets
     *
     * @return Template
     */
    public function addMultipleAssetsJs(array $pathAssets)
    {
        foreach ($pathAssets as $blockId => $paths)
        {
            foreach ($paths as $path)
            {
                $this->addAssetJs($path, $blockId);
            }
        }

        return $this;
    }

    /**
     * @param string $pathAsset
     * @param string $blockId
     *
     * @return Template
     */
    public function addAssetJs($pathAsset, $blockId = null)
    {
        if ($blockId === null)
        {
            $blockId = 'default';
        }

        $this->assetsJs = $this->addAsset($this->assetsJs, $blockId, $pathAsset);

        return $this;
    }

    /**
     * @return array
     */
    public function getAssetsCode()
    {
        return $this->assetsCode;
    }

    /**
     * @param array $items
     *
     * @return Template
     */
    public function addMultipleAssetsCode(array $items)
    {
        foreach ($items as $blockId => $codes)
        {
            foreach ($codes as $code)
            {
                $this->addAssetCode($code, $blockId);
            }
        }

        return $this;
    }

    /**
     * @param string $code
     * @param string $blockId
     *
     * @return Template
     */
    public function addAssetCode($code, $blockId = null)
    {
        if ($blockId === null)
        {
            $blockId = 'default';
        }

        $this->assetsCode = $this->addAsset($this->assetsCode, $blockId, $code);

        return $this;
    }

    /**
     * @param string $pathTemplate
     * @param array $params
     * @param array $customerParsers
     * @param bool $withFileExtension
     *
     * @return string
     * @throws MustacheException
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
     * @throws \Simplon\Phtml\PhtmlException
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
     * @param array $assets
     * @param string $blockId
     * @param string $item
     *
     * @return array
     */
    private function addAsset(array $assets, $blockId, $item)
    {
        if (empty($assets[$blockId]))
        {
            $assets[$blockId] = [];
        }

        $assets[$blockId][] = $item;

        return $assets;
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

        $wrappers = [
            'css'  => '<link rel="stylesheet" href="{item}">',
            'js'   => '<script type="text/javascript" src="{item}"></script>',
            'code' => '<script type="text/javascript">{item}</script>',
        ];

        foreach ($blockAssets as $blockId => $assets)
        {
            $lines = [];

            foreach ($assets as $item)
            {
                if (in_array($type, ['css', 'js'], true))
                {
                    $item = str_replace('{item}', $item, $wrappers[$type]);
                }

                $lines[] = $item;
            }

            $code = "\n" . implode("\n", $lines) . "\n";

            // wrap code item in <script...> if code does not hold </script> nor </noscript>
            if ($isCode === true && strpos($code, '</script>') === false && strpos($code, '</noscript>') === false)
            {
                $code = str_replace('{item}', $code, $wrappers['code']);
            }

            $flatAssets[$type . ucfirst(strtolower($blockId))] = $code;
        }

        return $flatAssets;
    }
}