<?php

require __DIR__ . '/../vendor/autoload.php';

$template = new \Simplon\Template\Template();

$params = [
    'fullName' => 'Johnny Brave',
    'hasNames' => true,
    'names'    => [
        'Tino',
        'Johnny',
    ],
];

$customParsers = [
    [
        'pattern'  => '{{lang:(.*?):(.*?)}}',
        'callback' => function ($template, array $match)
        {
            foreach ($match[1] as $index => $key)
            {
                $langKey = 'lang:' . $match[1][$index] . ':' . $match[2][$index];
                $langString = 'LOCALE:' . $match[1][$index] . '-' . $match[2][$index];
                $template = str_replace('{{' . $langKey . '}}', $langString, $template);
            }

            return $template;
        },
    ]
];

$template->addAssetHeader('/css/master.css');
$template->addAssetBody('/js/jquery.js');

// ----------------------------------------------

$t = $template->renderMustache(__DIR__ . '/mustache/foo', $params, $customParsers);

echo '<h1>Mustache</h1>';
echo '<div style="background:#ffe">' . $t . '</div>';

// ----------------------------------------------

$params['lang'] = function ($group, $key)
{
    return 'LOCALE:' . $group . '-' . $key;
};

$t = $template->renderPhtml(__DIR__ . '/phtml/foo', $params);

echo '<h1>Phtml</h1>';
echo '<div style="background:#ffe">' . $t . '</div>';
