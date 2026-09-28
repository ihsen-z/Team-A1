<?php
/**
 * Génère theme.json à partir de la charte client.
 *
 * C'est la brique qui rend le design automatisable : la charte est une donnée,
 * le thème s'y conforme. Aucun agent n'écrit de CSS à la main.
 *
 * Usage : php render-theme-json.php <client.yml> <sortie.json>
 */

declare(strict_types=1);

if ($argc < 3) {
    fwrite(STDERR, "Usage: render-theme-json.php <client.yml> <out.json>\n");
    exit(1);
}

[$_, $brief_path, $out_path] = $argv;

if (! is_readable($brief_path)) {
    fwrite(STDERR, "Brief illisible : {$brief_path}\n");
    exit(1);
}

$brief = yaml_read($brief_path);
$charte = $brief['charte'] ?? [];
$couleurs = $charte['couleurs'] ?? [];
$typos = $charte['typos'] ?? [];

foreach (['primaire', 'fond', 'texte'] as $required) {
    if (empty($couleurs[$required])) {
        fwrite(STDERR, "Couleur obligatoire manquante : charte.couleurs.{$required}\n");
        exit(1);
    }
}

$rayon = (int) ($charte['rayon'] ?? 6);
$densite = $charte['densite'] ?? 'confortable';

// Échelle d'espacement dérivée de la densité — une seule décision, tout le
// site suit. C'est ce qui évite le patchwork de marges.
$echelles = [
    'compacte'     => ['base' => 0.75, 'ratio' => 1.4],
    'confortable'  => ['base' => 1.0,  'ratio' => 1.5],
    'aeree'        => ['base' => 1.35, 'ratio' => 1.6],
];
$echelle = $echelles[$densite] ?? $echelles['confortable'];

$spacing_sizes = [];
foreach (range(0, 6) as $i) {
    $rem = round($echelle['base'] * pow($echelle['ratio'], $i), 3);
    $spacing_sizes[] = [
        'slug' => (string) (($i + 1) * 10),
        'name' => 'Espace ' . ($i + 1),
        'size' => $rem . 'rem',
    ];
}

$palette = [];
foreach ([
    'primaire'   => 'Primaire',
    'secondaire' => 'Secondaire',
    'accent'     => 'Accent',
    'fond'       => 'Fond',
    'texte'      => 'Texte',
] as $slug => $name) {
    if (! empty($couleurs[$slug])) {
        $palette[] = ['slug' => $slug, 'name' => $name, 'color' => $couleurs[$slug]];
    }
}
// Neutres dérivés — toujours présents, quelle que soit la charte.
$palette[] = ['slug' => 'neutre-clair', 'name' => 'Neutre clair', 'color' => mix_hex($couleurs['fond'], $couleurs['texte'], 0.08)];
$palette[] = ['slug' => 'neutre',       'name' => 'Neutre',       'color' => mix_hex($couleurs['fond'], $couleurs['texte'], 0.45)];

$font_families = [];
foreach (['titres' => 'titres', 'corps' => 'corps'] as $key => $slug) {
    $famille = $typos[$key]['famille'] ?? null;
    if (! $famille) {
        continue;
    }
    $fallback = $key === 'titres'
        ? 'Georgia, "Times New Roman", serif'
        : 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';
    $font_families[] = [
        'slug'       => $slug,
        'name'       => ucfirst($key),
        'fontFamily' => sprintf('"%s", %s', $famille, $fallback),
    ];
}

$theme = [
    '$schema' => 'https://schemas.wp.org/trunk/theme.json',
    'version' => 3,
    'settings' => [
        'appearanceTools' => true,
        'useRootPaddingAwareAlignments' => true,
        'layout' => ['contentSize' => '720px', 'wideSize' => '1200px'],
        'color' => [
            'palette'         => $palette,
            'custom'          => false,
            'customDuotone'   => false,
            'defaultPalette'  => false,
            'defaultGradients'=> false,
        ],
        'typography' => [
            'fluid'            => true,
            'fontFamilies'     => $font_families,
            'customFontSize'   => false,
            'fontSizes'        => [
                ['slug' => 'small',   'name' => 'Petit',      'size' => '0.9rem',  'fluid' => false],
                ['slug' => 'medium',  'name' => 'Normal',     'size' => '1.05rem', 'fluid' => false],
                ['slug' => 'large',   'name' => 'Grand',      'size' => '1.5rem',  'fluid' => ['min' => '1.3rem', 'max' => '1.6rem']],
                ['slug' => 'x-large', 'name' => 'Très grand', 'size' => '2.4rem',  'fluid' => ['min' => '1.9rem', 'max' => '3rem']],
            ],
        ],
        'spacing' => [
            'units'         => ['rem', 'px', '%'],
            'spacingSizes'  => $spacing_sizes,
            'customSpacingSize' => false,
        ],
        'border' => ['radius' => true, 'color' => true, 'style' => true, 'width' => true],
    ],
    'styles' => [
        'color' => [
            'background' => 'var(--wp--preset--color--fond)',
            'text'       => 'var(--wp--preset--color--texte)',
        ],
        'typography' => [
            'fontFamily' => 'var(--wp--preset--font-family--corps)',
            'fontSize'   => 'var(--wp--preset--font-size--medium)',
            'lineHeight' => '1.65',
        ],
        'spacing' => [
            'padding' => ['left' => 'var(--wp--preset--spacing--20)', 'right' => 'var(--wp--preset--spacing--20)'],
            'blockGap' => 'var(--wp--preset--spacing--30)',
        ],
        'elements' => [
            'heading' => [
                'typography' => [
                    'fontFamily' => 'var(--wp--preset--font-family--titres)',
                    'lineHeight' => '1.15',
                    'fontWeight' => (string) ($typos['titres']['poids'][1] ?? 700),
                ],
            ],
            'link' => [
                'color' => ['text' => 'var(--wp--preset--color--primaire)'],
                ':hover' => ['typography' => ['textDecoration' => 'underline']],
                // Focus visible : exigence WCAG, pas une option.
                ':focus' => ['outline' => ['color' => 'var(--wp--preset--color--accent)', 'style' => 'solid', 'width' => '2px', 'offset' => '2px']],
            ],
            'button' => [
                'color'  => ['background' => 'var(--wp--preset--color--primaire)', 'text' => 'var(--wp--preset--color--fond)'],
                'border' => ['radius' => $rayon . 'px'],
                'spacing'=> ['padding' => ['top' => '0.7rem', 'bottom' => '0.7rem', 'left' => '1.4rem', 'right' => '1.4rem']],
                ':hover' => ['color' => ['background' => 'var(--wp--preset--color--accent)']],
            ],
        ],
    ],
    'customTemplates' => [],
];

if (file_put_contents($out_path, json_encode($theme, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n") === false) {
    fwrite(STDERR, "Écriture impossible : {$out_path}\n");
    exit(1);
}

fwrite(STDOUT, "theme.json généré : {$out_path}\n");

/**
 * Mélange deux couleurs hex. Sert à dériver les neutres de la charte plutôt
 * que d'imposer des gris arbitraires qui jurent avec les couleurs du client.
 */
function mix_hex(string $a, string $b, float $ratio): string
{
    $pa = hex_to_rgb($a);
    $pb = hex_to_rgb($b);
    $out = [];
    foreach ([0, 1, 2] as $i) {
        $out[$i] = (int) round($pa[$i] * (1 - $ratio) + $pb[$i] * $ratio);
    }
    return sprintf('#%02X%02X%02X', $out[0], $out[1], $out[2]);
}

/** @return array{0:int,1:int,2:int} */
function hex_to_rgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        fwrite(STDERR, "Couleur hex invalide : #{$hex}\n");
        exit(1);
    }
    return [
        (int) hexdec(substr($hex, 0, 2)),
        (int) hexdec(substr($hex, 2, 2)),
        (int) hexdec(substr($hex, 4, 2)),
    ];
}

/**
 * Lecteur YAML minimal, suffisant pour la forme du brief (voir
 * governance/client.schema.yml). Utilise ext-yaml quand elle est disponible.
 */
function yaml_read(string $path): array
{
    if (function_exists('yaml_parse_file')) {
        $parsed = yaml_parse_file($path);
        return is_array($parsed) ? $parsed : [];
    }

    $out = [];
    $stack = [&$out];
    $indents = [-1];

    foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
        if (preg_match('/^\s*(#|$)/', $line)) {
            continue;
        }
        preg_match('/^(\s*)/', $line, $m);
        $indent = strlen($m[1]);
        $trimmed = trim($line);

        while (count($indents) > 1 && $indent <= end($indents)) {
            array_pop($indents);
            array_pop($stack);
        }
        $parent = &$stack[count($stack) - 1];

        if (str_starts_with($trimmed, '- ')) {
            $parent[] = yaml_scalar(substr($trimmed, 2));
            continue;
        }
        if (! str_contains($trimmed, ':')) {
            continue;
        }

        [$key, $rest] = array_pad(explode(':', $trimmed, 2), 2, '');
        $key = trim($key);
        $rest = trim($rest);

        if ($rest === '') {
            $parent[$key] = [];
            $stack[] = &$parent[$key];
            $indents[] = $indent;
            unset($parent);
            continue;
        }
        $parent[$key] = yaml_scalar($rest);
        unset($parent);
    }

    return $out;
}

function yaml_scalar(string $v): mixed
{
    $v = trim($v);
    if (preg_match('/^\[(.*)\]$/', $v, $m)) {
        if (trim($m[1]) === '') {
            return [];
        }
        return array_map(static fn ($p) => yaml_scalar($p), explode(',', $m[1]));
    }
    if (preg_match('/^\{(.*)\}$/', $v, $m)) {
        $out = [];
        foreach (explode(',', $m[1]) as $pair) {
            if (! str_contains($pair, ':')) {
                continue;
            }
            [$k, $val] = explode(':', $pair, 2);
            $out[trim($k)] = yaml_scalar($val);
        }
        return $out;
    }
    if (preg_match('/^"(.*)"$/s', $v, $m) || preg_match("/^'(.*)'$/s", $v, $m)) {
        return $m[1];
    }
    return match (strtolower($v)) {
        'true', 'yes'  => true,
        'false', 'no'  => false,
        'null', '~', '' => null,
        default => is_numeric($v) ? ($v + 0) : $v,
    };
}
