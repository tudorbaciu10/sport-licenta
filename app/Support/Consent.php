<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Cookie consent stored by the banner in the "sportmd_consent" cookie (JSON, not encrypted,
 * written by the browser). Categories: necessary (always on) and preferences (optional).
 */
class Consent
{
    public const COOKIE = 'sportmd_consent';

    public const LOCALE_COOKIE = 'sportmd_locale';

    public const VERSION = 1;

    /** Days the choice is kept before the banner asks again. */
    public const DAYS = 180;

    public static function preferences(Request $request): bool
    {
        $data = json_decode((string) $request->cookie(self::COOKIE), true);

        return is_array($data) && ($data['v'] ?? null) === self::VERSION && ($data['preferences'] ?? false) === true;
    }
}
