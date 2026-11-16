<?php

namespace App\Support\Seo;

/** Малка линейна графика като вграден SVG, без библиотека. */
class Sparkline
{
    /**
     * @param list<float|int|null> $values       стойности по ред във времето (null се прескача)
     * @param bool                 $lowerIsBetter позиции: по-малкото число е по-горе
     */
    public static function svg(array $values, bool $lowerIsBetter = true, int $width = 120, int $height = 28, string $label = 'Графика'): string
    {
        $values = array_values(array_filter($values, fn ($v) => $v !== null));
        if (count($values) < 2) {
            return '<span class="sx-nodata" title="Трябват поне два записа">·</span>';
        }
        $min = min($values);
        $max = max($values);
        $span = max($max - $min, 0.0001);
        $pad = 3;
        $points = [];
        $n = count($values);
        foreach ($values as $i => $v) {
            $x = $pad + ($width - 2 * $pad) * ($n === 1 ? 0 : $i / ($n - 1));
            $ratio = ($v - $min) / $span;
            $y = $lowerIsBetter ? $pad + ($height - 2 * $pad) * $ratio : $height - $pad - ($height - 2 * $pad) * $ratio;
            $points[] = round($x, 1) . ',' . round($y, 1);
        }
        [$lx, $ly] = explode(',', end($points));
        $first = $values[0];
        $last = end($values);
        $better = $lowerIsBetter ? $last < $first : $last > $first;
        $class = $last == $first ? 'flat' : ($better ? 'up' : 'down');

        return '<svg class="sx-spark ' . $class . '" viewBox="0 0 ' . $width . ' ' . $height . '" width="' . $width . '" height="' . $height . '" role="img" aria-label="' . e($label) . '">'
            . '<polyline fill="none" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round" points="' . implode(' ', $points) . '"/>'
            . '<circle cx="' . $lx . '" cy="' . $ly . '" r="2.4"/></svg>';
    }
}
