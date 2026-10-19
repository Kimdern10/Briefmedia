<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * Writes public/assets/css/bm-icons.css: the Bootstrap Icons stylesheet cut down to the icons the
 * site actually uses (about 110 of the 2,000+), so phones download a few KB instead of 98 KB.
 *
 * Run it again after using a new icon in a view. `--check` only reports whether the file is out
 * of date (the test suite runs it, so a missing icon is caught before it ships).
 */
class BuildIconStylesheet extends Command
{
    /**
     * @var string
     */
    protected $signature = 'icons:build
                            {--check : Only check that bm-icons.css has every icon in use}';

    /**
     * @var string
     */
    protected $description = 'Build the small icon stylesheet with only the icons the site uses';

    /**
     * Icons whose names are put together in JavaScript, so a search for "bi-…" can't find them.
     *
     * @var list<string>
     */
    private const EXTRA_ICONS = ['bi-facebook', 'bi-twitter-x', 'bi-linkedin', 'bi-whatsapp'];

    public function handle(): int
    {
        $source = File::get(public_path('assets/css/font/bootstrap-icons.css'));
        $target = public_path('assets/css/bm-icons.css');

        preg_match_all('/^\.(bi-[a-z0-9-]+)::before \{ content: "(\\\\[0-9a-f]+)"; \}$/m', $source, $matches, PREG_SET_ORDER);
        $glyphs = collect($matches)->mapWithKeys(fn (array $match): array => [$match[1] => $match[2]]);

        $used = $this->usedIcons()->filter(fn (string $icon): bool => $glyphs->has($icon))->values();
        $stylesheet = $this->stylesheet($used->mapWithKeys(fn (string $icon): array => [$icon => $glyphs[$icon]])->all());

        if ($this->option('check')) {
            if (File::exists($target) && File::get($target) === $stylesheet) {
                $this->info('bm-icons.css is up to date.');

                return self::SUCCESS;
            }

            $this->error('bm-icons.css is out of date: run php artisan icons:build');

            return self::FAILURE;
        }

        File::put($target, $stylesheet);
        $this->info("bm-icons.css written with {$used->count()} icons (".round(strlen($stylesheet) / 1024, 1).' KB).');

        return self::SUCCESS;
    }

    /**
     * Every "bi-…" name in the views, PHP code and site scripts.
     *
     * @return Collection<int, string>
     */
    private function usedIcons(): Collection
    {
        $files = collect([resource_path('views'), app_path()])
            ->flatMap(fn (string $directory): array => File::allFiles($directory))
            ->map(fn ($file): string => $file->getPathname())
            ->merge(File::glob(public_path('assets/js/bm-*.js')));

        return $files
            ->flatMap(function (string $path): array {
                preg_match_all('/\bbi-[a-z0-9]+(?:-[a-z0-9]+)*/', File::get($path), $found);

                return $found[0];
            })
            ->merge(self::EXTRA_ICONS)
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * @param  array<string, string>  $icons  class name => glyph code, e.g. ['bi-eye' => '\f341']
     */
    private function stylesheet(array $icons): string
    {
        $rules = collect($icons)
            ->map(fn (string $glyph, string $icon): string => ".{$icon}::before{content:\"{$glyph}\"}")
            ->implode("\n");

        return <<<CSS
/* Bootstrap Icons v1.11.3 (MIT), only the icons the site uses. Built by `php artisan icons:build`, don't edit by hand. */
@font-face{font-display:block;font-family:"bootstrap-icons";src:url("/assets/css/font/fonts/bootstrap-icons0107.woff2?dd67030699838ea613ee6dbda90effa6") format("woff2"),url("/assets/css/font/fonts/bootstrap-icons0107.woff?dd67030699838ea613ee6dbda90effa6") format("woff")}
.bi::before,[class^="bi-"]::before,[class*=" bi-"]::before{display:inline-block;font-family:bootstrap-icons!important;font-style:normal;font-weight:normal!important;font-variant:normal;text-transform:none;line-height:1;vertical-align:-.125em;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
{$rules}

CSS;
    }
}
