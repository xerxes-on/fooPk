<?php

namespace App\Repositories;

use App\Exceptions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Handles translations.
 */
class Dictionaries
{
    /**
     * Get translations.
     */
    public function get(string $dictionary, string $language): array
    {
        $files     = Storage::disk('local');
        $hardcoded = __($dictionary, [], $language);
        $rawCustom = $files->get("lang/$language/$dictionary.json");
        $custom    = is_null($rawCustom) ? [] : json_decode($rawCustom, true);
        return array_merge($hardcoded, $custom);
    }

    /**
     * Override or extend list of existing translations.
     */
    public function override(
        UploadedFile $translations,
        string       $language,
        string       $dictionary,
    ): array {
        $overrides = json_decode($translations->getContent(), true);

        if (is_null($overrides)) {
            throw new Exceptions\PublicException(
                'Parsing failed! Make sure your JSON is valid and doesn\'t have trailing commas.'
            );
        }

        foreach ($overrides as $translation) {
            if (!is_string($translation)) {
                throw new Exceptions\PublicException(
                    'Only simple mapping of keys to strings is expected. Any other structure isn\'t supported.'
                );
            }
        }

        $files           = Storage::disk('local');
        $currentFile     = $files->get("lang/$language/$dictionary.json");
        $current         = is_null($currentFile) ? [] : json_decode($currentFile, true);
        $allTranslations = array_merge($current, $overrides);
        $files->put("lang/$language/$dictionary.json", json_encode($allTranslations));
        return $overrides;
    }
}
