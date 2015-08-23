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
     * @param array $pathAssets
     *
     * @return bool
     */
    public function setAssetsCss(array $pathAssets)
    {
        foreach ($pathAssets as $path)
        {
            $this->addAssetCss($path);
        }

        return true;
    }

    /**
     * @param string $pathAsset
     *
     * @return bool
     */
    public function addAssetCss($pathAsset)
    {
        $this->assetsCss[md5($pathAsset)] = '<link rel="stylesheet" href="' . $pathAsset . '">';

        return true;
    }

    /**
     * @param array $pathAssets
     *
     * @return bool
     */
    public function setAssetsJs(array $pathAssets)
    {
        foreach ($pathAssets as $path)
        {
            $this->addAssetJs($path);
        }

        return true;
    }

    /**
     * @param string $pathAsset
     *
     * @return bool
     */
    public function addAssetJs($pathAsset)
    {
        $this->assetsJs[md5($pathAsset)] = '<script type="text/javascript" src="' . $pathAsset . '"></script>';

        return true;
    }

    /**
     * @param string $code
     * @param string $blockId
     *
     * @return bool
     */
    public function addAssetCode($code, $blockId = 'default')
    {
        $this->assetsCode[$blockId][] = $code;

        return true;
    }

    /**
     * @param string $pathTemplate
     * @param array $params
     * @param array $customerParsers
     *
     * @return string
     * @throws MustacheException
     */
    public function renderMustache($pathTemplate, array $params = [], array $customerParsers = [])
    {
        // handle assets
        $params = $this->enrichParamsWithAssets($params);

        // render template
        $template = Mustache::renderByFile($pathTemplate, $params, $customerParsers);

        return (string)$template;
    }

    /**
     * @param string $pathTemplate
     * @param array $params
     *
     * @return string
     * @throws MustacheException
     */
    public function renderPhtml($pathTemplate, array $params = [])
    {
        // handle assets
        $params = $this->enrichParamsWithAssets($params);

        // render template
        $template = Phtml::render($pathTemplate, $params);

        return (string)$template;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    private function enrichParamsWithAssets(array $params)
    {
        $params['assetsCode'] = ['default' => null];
        $params['assetsCss'] = "\n" . join("\n", $this->assetsCss) . "\n";
        $params['assetsJs'] = "\n" . join("\n", $this->assetsJs) . "\n";

        if (empty($this->assetsCode) === false)
        {
            foreach ($this->assetsCode as $blockId => $code)
            {
                $params['assetsCode'][$blockId] = "\n<script type=\"text/javascript\">\n" . join(";\n", $code) . "</script>\n";
            }
        }

        return $params;
    }
}