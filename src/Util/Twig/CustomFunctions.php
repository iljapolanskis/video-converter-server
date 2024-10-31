<?php

namespace App\Util\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CustomFunctions extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('img', fn(string $filename) => '/view/img/' . $filename),
            new TwigFunction('css', fn(string $filename) => '/view/css/' . $filename),
        ];
    }
}
