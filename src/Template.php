<?php

namespace Simplon\Template;

use Simplon\Mustache\Mustache;
use Simplon\Phtml\Phtml;

/**
 * Template
 * @package Simplon\Template
 * @author Tino Ehrich (tino@bigpun.me)
 */
class Template
{
    /**
     * @var array
     */
    private $assetsHeader = [];

    /**
     * @var array
     */
    private $assetsBody = [];

    /**
     * @param array $pathAssets
     *
     * @return bool
     */
    public function setAssetsHeader(array $pathAssets)
    {
        foreach ($pathAssets as $path)
        {
            $this->addAssetHeader($path);
        }

        return true;
    }

    /**
     * @param $pathAsset
     *
     * @return bool
     */
    public function addAssetHeader($pathAsset)
    {
        $this->assetsHeader[] = '<link rel="stylesheet" href="' . $pathAsset . '">';

        return true;
    }

    /**
     * @param array $pathAssets
     *
     * @return bool
     */
    public function setAssetsBody(array $pathAssets)
    {
        foreach ($pathAssets as $path)
        {
            $this->addAssetBody($path);
        }

        return true;
    }

    /**
     * @param $pathAsset
     *
     * @return bool
     */
    public function addAssetBody($pathAsset)
    {
        $this->assetsBody[] = '<script type="text/javascript" src="' . $pathAsset . '"></script>';

        return true;
    }

    /**
     * @param $pathTemplate
     * @param array $params
     * @param array $customerParsers
     *
     * @return string
     * @throws \Simplon\Mustache\MustacheException
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
     * @param $pathTemplate
     * @param array $params
     *
     * @return string
     * @throws \Simplon\Mustache\MustacheException
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
        $params['assetsHeader'] = "\n" . join("\n", $this->assetsHeader) . "\n";
        $params['assetsBody'] = "\n" . join("\n", $this->assetsBody) . "\n";

        return $params;
    }
}