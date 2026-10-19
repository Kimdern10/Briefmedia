<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Picks tags for a story from its own words, so admins don't have to type them:
 *  - topics already used on the site (existing tags) that the story mentions, and
 *  - names, places and organisations that keep coming up in it ("Charles Soludo",
 *    "Awka South", "INEC"), with extra weight for words in the title.
 * No outside service is used.
 */
class AutoTagger
{
    public const MAX_TAGS = 6;

    /** Words that start a capitalised phrase but aren't topics. */
    private const SKIP_WORDS = [
        'a', 'an', 'the', 'this', 'that', 'these', 'those', 'it', 'its', 'he', 'she', 'they', 'we', 'i', 'you', 'his', 'her', 'their', 'our',
        'in', 'on', 'at', 'by', 'for', 'from', 'to', 'of', 'with', 'as', 'and', 'but', 'or', 'if', 'when', 'while', 'after', 'before', 'also',
        'however', 'meanwhile', 'according', 'speaking', 'reacting', 'recall', 'others', 'some', 'many', 'all', 'there', 'here', 'what', 'who',
        'mr', 'mrs', 'ms', 'dr', 'prof', 'chief', 'hon', 'sen', 'gov', 'engr', 'barr', 'rev', 'sir', 'lady', 'alhaji', 'pastor', 'bishop',
        'archbishop', 'governor', 'president', 'senator', 'minister', 'commissioner', 'chairman', 'chairperson', 'coach',
        'honourable', 'honorable', 'deputy', 'igwe', 'prince', 'princess', 'comrade', 'apostle', 'evangelist', 'father', 'mother',
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
        'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december',
        'breaking', 'news', 'photos', 'video', 'read', 'more', 'source', 'credit', 'briefmedia',
        // Common headline and reporting verbs
        'says', 'said', 'say', 'tells', 'told', 'announces', 'announced', 'receives', 'received', 'rules', 'ruled', 'sparks',
        'dies', 'died', 'wins', 'won', 'slams', 'reacts', 'reveals', 'revealed', 'calls', 'called', 'urges', 'urged', 'warns',
        'warned', 'vows', 'denies', 'denied', 'confirms', 'confirmed', 'blasts', 'opens', 'speaks', 'meets', 'visits',
        'launches', 'appoints', 'appointed', 'arrests', 'arrested', 'sues', 'condemns', 'congratulates', 'mourns', 'set',
        'not', 'no', 'yes', 'has', 'have', 'had', 'is', 'are', 'was', 'were', 'will', 'can', 'new', 'first', 'last', 'state',
    ];

    /** Fine inside a name ("Idemili South", "Police Command") but too general as a tag on their own. */
    private const GENERIC_ALONE = [
        'north', 'south', 'east', 'west', 'central', 'province', 'diocese', 'church', 'government', 'police', 'court',
        'federal', 'national', 'house', 'assembly', 'council', 'committee', 'ministry', 'office', 'party', 'people',
        'community', 'town', 'city', 'village', 'local', 'area', 'association', 'union', 'group', 'company', 'team',
        'club', 'school', 'university', 'hospital', 'market', 'road', 'street', 'day', 'week', 'year', 'month',
    ];

    /**
     * Suggested tag names for a post, best first.
     *
     * @return array<int, string>
     */
    public static function suggest(Post $post, int $max = self::MAX_TAGS): array
    {
        $title = static::plain($post->title);
        $body = static::plain($post->excerpt.' '.$post->content);
        $text = $title.' . '.$body;

        if (trim($text) === '.') {
            return [];
        }

        $scores = collect();

        // 1. Topics already used on the site that this story mentions
        foreach (Tag::pluck('name') as $name) {
            if (mb_strlen($name) < 3) {
                continue;
            }
            // One-word tags must match as written ("May" the name, not "may"); longer ones in any case
            $caseSensitive = ! str_contains(trim($name), ' ');
            $count = static::countMentions($name, $text, $caseSensitive);
            if ($count > 0) {
                $scores[$name] = $count + 2 + (static::countMentions($name, $title, $caseSensitive) > 0 ? 3 : 0);
            }
        }

        // 2. Names, places and organisations that keep coming up. Found in the story text only:
        //    headlines are written In Title Case, so every word there looks like a name.
        //    A name that is also in the headline gets a bonus.
        foreach (static::phrases($body) as $phrase => $count) {
            $inTitle = static::countMentions($phrase, $title) > 0;
            $words = count(explode(' ', $phrase));
            $isAcronym = (bool) preg_match('/^[A-Z]{2,8}$/', $phrase);

            // Single ordinary words must come up 3 times (2 if also in the headline); names of 2+
            // words and acronyms need 2 mentions, or 1 when they're in the headline
            $needed = $words === 1 && ! $isAcronym ? ($inTitle ? 2 : 3) : ($inTitle ? 1 : 2);
            if ($count < $needed || ($words === 1 && in_array(mb_strtolower($phrase), self::GENERIC_ALONE, true))) {
                continue;
            }

            $existing = $scores->keys()->first(fn ($name) => mb_strtolower($name) === mb_strtolower($phrase));
            $key = $existing ?? $phrase;
            $scores[$key] = max($scores[$key] ?? 0, $count + ($inTitle ? 3 : 0) + ($words > 1 ? 1 : 0));
        }

        return static::dropOverlaps($scores->sortDesc())->take($max)->keys()->values()->all();
    }

    /**
     * Tag the post automatically (used when the Tags box is left empty).
     */
    public static function apply(Post $post): array
    {
        $names = static::suggest($post);

        if ($names !== []) {
            $post->syncTags(implode(', ', $names));
        }

        return $names;
    }

    /**
     * Capitalised phrases (up to 3 words) and short acronyms, with how often each appears.
     *
     * @return Collection<string, int>
     */
    private static function phrases(string $text): Collection
    {
        // A capitalised word ("Soludo", "2Baba", "Okonkwo-Eze") or an acronym ("INEC"); words can be
        // joined by "of" ("Certificate of Return", "Bank of Industry")
        $word = "(?:[0-9]*[A-Z][a-z]+(?:['’-][A-Za-z]+)?|[A-Z]{2,8})";
        preg_match_all("/(?<![\\p{L}\\p{N}]){$word}(?:(?:\\s+of)?\\s+{$word}){0,2}(?![\\p{L}\\p{N}])/u", $text, $matches);

        return collect($matches[0])
            ->map(fn (string $phrase) => static::trimPhrase($phrase))
            ->filter(fn (?string $phrase) => $phrase !== null)
            ->countBy();
    }

    /**
     * Drop leading words like "The", "Mr" or "Governor of" pieces that aren't part of the name.
     */
    private static function trimPhrase(string $phrase): ?string
    {
        // "Natasha’s" is about "Natasha"
        $phrase = preg_replace("/['’]s\\b/u", '', $phrase);
        $words = preg_split('/\s+/u', trim($phrase));

        while ($words !== [] && in_array(mb_strtolower(rtrim($words[0], '.')), self::SKIP_WORDS, true)) {
            array_shift($words);
        }
        while ($words !== [] && in_array(mb_strtolower(end($words)), self::SKIP_WORDS, true)) {
            array_pop($words);
        }

        $phrase = trim(implode(' ', $words));

        if ($phrase === '' || mb_strlen($phrase) < 3 || mb_strlen($phrase) > 40) {
            return null;
        }

        return $phrase;
    }

    /**
     * "Soludo" and "Charles Soludo" are the same topic: keep the fuller one.
     */
    private static function dropOverlaps(Collection $scores): Collection
    {
        $names = $scores->keys();

        return $scores->filter(function ($score, $name) use ($names) {
            foreach ($names as $other) {
                if ($other !== $name && mb_strlen($other) > mb_strlen($name)
                    && preg_match('/\b'.preg_quote($name, '/').'\b/iu', $other)) {
                    return false;
                }
            }

            return true;
        });
    }

    private static function countMentions(string $needle, string $haystack, bool $caseSensitive = false): int
    {
        return preg_match_all('/(?<![\p{L}\p{N}])'.preg_quote($needle, '/').'(?![\p{L}\p{N}])/u'.($caseSensitive ? '' : 'i'), $haystack);
    }

    private static function plain(?string $html): string
    {
        $text = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ' . ', (string) $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', Str::of($text)->replace("\u{00A0}", ' ')->toString()));
    }
}
