<?php
require 'C:\xampp8.0.25\htdocs\phpelasticsearch\vendor\autoload.php';
use Elastic\Elasticsearch\ClientBuilder;

// Set up Elasticsearch client
$client = ClientBuilder::create()
    ->setHosts(['https://localhost:9200'])
    ->setBasicAuthentication('elastic', 'Ela@9652')
    ->build();

// Get search query from request parameter
$searchQuery = $_GET['query'] ?? '';

// Build Elasticsearch search request
$params = [
    'index' => 'my_index',
    'body' => [
        'query' => [
            'multi_match' => [
                'query' => $searchQuery,
                'fields' => ['name', 'key_benifits', 'key_ingredients', 'product_info'],
                'fuzziness' => 'AUTO',
                'prefix_length' => 1
            ]
        ],
        'size' => 4000
    ]
];

// Execute Elasticsearch search query
$response = $client->search($params);
$numHits = $response['hits']['total']['value'];

// Return search results as JSON
header('Content-Type: application/json');
echo json_encode($response['hits']['hits']);
?>