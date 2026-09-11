<?php

namespace App\Application\Launcher\Actions;

use App\Domain\Identity\Models\User;

class SignDirectLauncherDownloadUrlAction
{
    /**
     * Signs a direct download URL for nginx's secure_link module, so the link expires on
     * its own and the download host's access log can name the account it was issued to.
     *
     * Returns the URL untouched when no secret is configured, which is how a deployment
     * that has not set up secure_link keeps working exactly as before. Never throws: a
     * download that cannot be signed is still a download the user paid for.
     *
     * The nginx side must be:
     *     secure_link      $arg_md5,$arg_expires;
     *     secure_link_md5  "SECRET$secure_link_expires$uri$arg_u";
     * The secret is placed FIRST in that expression on purpose. The usual published
     * recipe puts it last, separated by a space, which makes the boundary between the
     * URI and the secret ambiguous and is a classic source of "works locally, 403s in
     * production" mismatches. Leading it removes the ambiguity entirely.
     */
    public function execute(string $url, User $user): string
    {
        $secret = (string)config('launcher.secure_link_secret', '');
        if ($secret === '') {
            return $url;
        }

        $parts = parse_url($url);
        if (!is_array($parts) || ($parts['path'] ?? '') === '') {
            return $url;
        }

        $expires = now()
            ->addMinutes((int)config('launcher.secure_link_ttl_minutes', 240))
            ->getTimestamp();

        // Stable for a given account so the access log groups one person's downloads
        // together across sessions and releases, and opaque so the log never carries a
        // real user id. Truncated to 16 hex chars: this is a log-correlation label, not
        // an authenticator -- the md5 signature is what actually authorises the request.
        $token = substr(
            hash_hmac('sha256', 'launcher-download|' . $user->getKey(), $secret),
            0,
            16
        );

        // nginx matches against $uri, which it has already percent-decoded, so the
        // signature has to be computed over the decoded path or every file whose name
        // contains a space or a non-ASCII character silently 403s.
        $uri = rawurldecode((string)$parts['path']);

        $signature = rtrim(strtr(
            base64_encode(md5($secret . $expires . $uri . $token, true)),
            '+/',
            '-_'
        ), '=');

        parse_str((string)($parts['query'] ?? ''), $query);
        $query['md5'] = $signature;
        $query['expires'] = $expires;
        $query['u'] = $token;

        $authority = (string)($parts['host'] ?? '');
        if (isset($parts['port'])) {
            $authority .= ':' . $parts['port'];
        }

        return ($parts['scheme'] ?? 'https') . '://' . $authority
            . (string)$parts['path'] . '?' . http_build_query($query);
    }
}
