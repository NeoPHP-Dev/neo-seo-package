<?php

declare(strict_types=1);

namespace Vendor\NeoPHP\SeoPackage;

use Neo\Core\Package\Abstract\AbstractPackage;


class NeoSeoPackage extends AbstractPackage
{
    public function getName(): string
    {
        return 'Seo';
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}