<?php

namespace App\Application\Seo\Actions\Schema;

final class BuildWebsiteSearchActionSchemaAction
{
    /**
     * @param string $searchUrlTemplate
     * @param string $queryParameterName
     * @return array<string, mixed>
     */
    public function execute(
        string $searchUrlTemplate,
        string $queryParameterName = 'search_term_string',
    ): array
    {
        return [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $searchUrlTemplate,
            ],
            'query-input' => [
                '@type' => 'PropertyValueSpecification',
                'valueRequired' => true,
                'valueName' => $queryParameterName,
            ],
        ];
    }
}
