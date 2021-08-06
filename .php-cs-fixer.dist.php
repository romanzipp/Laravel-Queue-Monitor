<?php

return romanzipp\Fixer\Config::make()
    ->in(__DIR__)
    ->preset(
        new romanzipp\Fixer\Presets\PrettyLaravel()
    )
    ->out();
