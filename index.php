<?php
    error_reporting(E_ERROR | E_PARSE);
 require 'C:\xampp8.0.25\htdocs\phpelasticsearch\vendor\autoload.php';
 use Elastic\Elasticsearch\ClientBuilder;
//  use Elasticsearch\ClientBuilder;

 $client = ClientBuilder::create()
    ->setHosts(['https://sma-elasticsearch.kb.asia-south1.gcp.elastic-cloud.com:9243'])
    ->setBasicAuthentication('elastic', 'TYjFSKpX2E4MuOgmejxlGs9i')
    // ->setCABundle('./security/')
    ->build();

 
// $result = $client->info();
// //echo $result;
// die("end");
 // Connect to MySQL database
//  $servername = "localhost";
//  $username = "mz1xs9r5lanjhi9gpzqb";
//  $password = "pscale_pw_WLc7fLoQS70vZGaycxOzJjwMwEf2CLhLlBswQy46uuI";
//  $dbname = "sjmc-dev";
//  $conn = new mysqli($servername, $username, $password, $dbname);

$conn = mysqli_init();
$conn->ssl_set(NULL, NULL, "/etc/ssl/certs/ca-certificates.crt", NULL, NULL);
$conn->real_connect($_ENV["aws.connect.psdb.cloud"], $_ENV["mz1xs9r5lanjhi9gpzqb"], $_ENV["pscale_pw_WLc7fLoQS70vZGaycxOzJjwMwEf2CLhLlBswQy46uuI"], $_ENV["sjmc"]);
 //var_dump($conn);
 
 // Check connection
 if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
    }
    
    // Query data from MySQL table
    // $sql = "SELECT drug_id, name, drug_category FROM drugs";
    $sql = "SELECT drugs.drug_id, drugs.name, drug_details.key_benifits,drug_details.key_ingredients,drug_details.product_info FROM drugs JOIN drug_details ON drugs.drug_id = drug_details.drug_id";

    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Build Elasticsearch bulk index request body
        $params = [
            'index' => 'my_index',
            'body' => []
        ];

    while($row = $result->fetch_assoc()) {
        $params['body'][] = [
            'index' => [
                '_index' => 'my_index',
                '_id' => $row['drug_id']
            ]
        ];
        $params['body'][] = [
            'drug_id' => $row['drug_id'], 
            'name' => $row['name'],
            'key_benifits' => $row['key_benifits'],
            'key_ingredients' => $row['key_ingredients'],
            'product_info' => $row['product_info']
        ];
    } 
    // //print_r($params);
    // Create Elasticsearch index
    $response = $client->bulk($params);
    // die("End");

    // Process index creation response
    if ($response['errors']) {
        // Index creation failed
        echo 'Index creation failed: ' . json_encode($response);
    } else {
        // Index creation successful
        // echo 'Index creation successful';
    }
} else {
    echo "0 results";
}
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
// Close MySQL connection
$conn->close();



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
                    <div class=col-md-10>
                        <input type="text" name="search" id="search-box" placeholder="Search" <?php echo 'value="'.$search.'"'?> class="form-control col-7">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-success px-5">Search</button>
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