<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;

use Closure;

class FileRequestFactory
{
    private string $wpRootDir;
    /**
     * @var array<string,string>
     */
    private array $redirectFiles;
    /**
     * @var array<string,mixed>
     */
    private array $presetLocalVars;

    public function __construct(string $wpRootDir, array $redirectFiles = [], array $presetLocalVars = [])
    {
        $this->wpRootDir = $wpRootDir;
        $this->redirectFiles = $redirectFiles;
        $this->presetLocalVars = $presetLocalVars;
    }

    public function buildGetRequest(string $requestUri, array $queryArgs, int $userId): FileGetRequest
    {
        $targetFile = rtrim($this->wpRootDir, '\\/') . '/' . ltrim($requestUri, '\\/');
        $cookies = [];

        if ($userId > 0) {
            $previousUserId = get_current_user_id();
            wp_set_current_user($userId);
            $cookies = [
                AUTH_COOKIE => wp_generate_auth_cookie($userId, time() + 86400)
            ];

            $queryArgs = $this->resolveQueryArgs($queryArgs);

            wp_set_current_user($previousUserId);
        } else {
            $queryArgs = $this->resolveQueryArgs($queryArgs);
        }


        return new FileGetRequest(
            $requestUri,
            $targetFile,
            $queryArgs,
            $cookies,
            $this->redirectFiles,
            $this->presetLocalVars
        );
    }

    /**
     * @return array<string,mixed>
     */
    protected function resolveQueryArgs(array $queryArgs): array
    {
        $resolved = [];

        foreach ($queryArgs as $key => $value) {
            $resolved[$key] = $value instanceof Closure ? $value() : $value;
        }

        return $resolved;
    }
}
