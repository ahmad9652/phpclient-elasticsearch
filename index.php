<?php
    error_reporting(E_ERROR | E_PARSE);
    require 'C:\xampp8.0.25\htdocs\phpelasticsearch\vendor\autoload.php';
    use Elastic\Elasticsearch\ClientBuilder;
    //  use Elasticsearch\ClientBuilder;
    
    $client = ClientBuilder::create()
       ->setHosts(['https://localhost:9200'])
       ->setBasicAuthentication('elastic', 'Ela@9652')
       // ->setCABundle('./security/')
       ->build();
    
    
    
$search = '';
$numHits = 0;
//var_dump($_SERVER['REQUEST_METHOD']);
if($_SERVER['REQUEST_METHOD']==='POST'){
    //echo 'entered';
    $search = $_POST['search'];
    //echo $search;
    $params = [
        'index' => 'my_index',
    'body' => [
        'query' => [
            'multi_match' => [
                'query' => $search,
                'fuzziness' => 'AUTO',
                'prefix_length' => 1
            ]
        ],
        'size' => 4000
    ]
];
//print_r($params);

// Execute search query
$response = $client->search($params);
$numHits = $response['hits']['total']['value'];
//var_dump($response);
// Process search results
    // foreach ($response['hits']['hits'] as $hit) {
    //     // Do something with search results
    //     echo "<pre>";
    //     print_r($hit);
    // }
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elastic Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
</head>
<body>
    <header>
    <nav class="navbar bg-body-tertiary bg-dark" data-bs-theme="dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1 mx-auto">Elastc Search</span>
        </div>
    </nav>
    </header>
    <main>
        <div class="w-100 mx-auto my-2">
            <form action="index.php" method="post">
                <div class="row my-3 mx-auto">
                    <div class=col-md-8>
                        <input type="text" name="search" id="search-box" placeholder="Search" <?php echo 'value="'.$search.'"'?> class="form-control col-7">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-success px-5">Search</button>
                    </div>
                    <div class="col-md-2">
                        <a href="elastic-indexing.php" class="btn btn-outline-success px-5">Refresh DB</a>
                    </div>
                </div>
            </form>
            <div class="my-3 mx-auto text-center
            ">
                <div class="mx-auto">
                    <span class="mx-auto">Number Of Search Result: </span><span><?php echo $numHits?></span>
                </div>
            </div>
            <table class="table table-striped border">
                <thead>
                    <th scope="col">Id</th>
                    <th scope="col">Drug Name</th>
                    <th scope="col">Key Ingredients</th>
                    <th scope="col">Key Benifit</th>
                    <th scope="col">Product Info</th>
                    <th scope="col">Tags</th>
                </thead>
                <?php
                foreach ($response['hits']['hits'] as $hit) {
                    // Do something with search results
                    // echo "<pre>";
                    // print_r($hit);
                    echo '
                    <tbody>
                        <tr>
                            <td>'.$hit["_source"]["drug_id"].'</td>
                            <td>'.$hit["_source"]["name"].'</td>
                            <td>'.$hit["_source"]["key_ingredients"].'</td>
                            <td>'.$hit["_source"]["key_benifits"].'</td>
                            <td>'.$hit["_source"]["product_info"].'</td>
                            <td>'.$hit["_source"]["tags"].'</td>
                        </tr>
                        
                    </tbody> ';
                }
                ?>
            </table>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-zYPOMqeu1DAVkHiLqWBUTcbYfZ8osu1Nd6Z89ify25QV9guujx43ITvfi12/QExE" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" integrity="sha384-Y4oOpwW3duJdCWv5ly8SCFYWqFDsfob/3GkgExXKV4idmbt98QcxXYs9UoXAB7BZ" crossorigin="anonymous"></script>
</body>
</html>