<?php

declare(strict_types=1);

namespace App\Github;

use Github\Client;

final class LastPrRetriever
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @return array<string, array<string,string>|string>
     */
    public function get(int $size = 3): array
    {
        $data = $this->client->graphql()->execute(<<<EOL
            query {
              viewer {
                pullRequests(first: {$size}, states: [OPEN], orderBy: {field:UPDATED_AT, direction: DESC} ) {
                  nodes {
                    title
                    permalink
                    state
                    repository {
                      name
                    }
                  }
                }
              }
            }
            EOL);

        return $data['data']['viewer']['pullRequests']['nodes'] ?? [];
    }
}
