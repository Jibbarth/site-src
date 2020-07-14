<?php

declare(strict_types=1);

namespace App\Github;

use Github\Client;

final class LastStarRetriever
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return array<string, string>
     */
    public function get(int $size = 3): array
    {
        $data = $this->client->graphql()->execute(<<<EOL
            query {
              viewer {
                starredRepositories(first: {$size}, orderBy: {field:STARRED_AT, direction:DESC}) {
                  nodes {
                    nameWithOwner
                    description
                    url
                  }
                }
              }
            }
            EOL);

        return $data['data']['viewer']['starredRepositories']['nodes'] ?? [];
    }
}
