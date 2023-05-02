<?php
require 'C:\xampp8.0.25\htdocs\phpelasticsearch\vendor\autoload.php';
use Elastic\Elasticsearch\ClientBuilder;
//  use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
   ->setHosts(['https://localhost:9200'])
   ->setBasicAuthentication('elastic', 'Ela@9652')
   // ->setCABundle('./security/')
   ->build();



// Connect to MySQL database
$servername = "localhost";
$username = "root";
$password = "ahmad";
$dbname = "sjmc-dev";
$conn = new mysqli($servername, $username, $password, $dbname);

// $conn = mysqli_init();
// $conn->ssl_set(NULL, NULL, "/etc/ssl/certs/ca-certificates.crt", NULL, NULL);
// $conn->real_connect($_ENV["aws.connect.psdb.cloud"], $_ENV["mz1xs9r5lanjhi9gpzqb"], $_ENV["pscale_pw_WLc7fLoQS70vZGaycxOzJjwMwEf2CLhLlBswQy46uuI"], $_ENV["sjmc"]);
//var_dump($conn);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
   }
   
   // Query data from MySQL table
   // $sql = "SELECT drug_id, name, drug_category FROM drugs";
   $sql = "SELECT drugs.drug_id, drugs.name, drug_details.key_benifits,drug_details.key_ingredients,drug_details.product_info , drug_tags.tags FROM drugs JOIN drug_details ON drugs.drug_id = drug_details.drug_id JOIN drug_tags ON drugs.drug_id = drug_tags.drug_id";

   $result = $conn->query($sql);
   
   if ($result->num_rows > 0) {
       // Build Elasticsearch bulk index request body
       $params = [
           'index' => 'my_index',
           'body' => []
       ];

   while($row = $result->fetch_assoc()) {
       // indexing
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
           'product_info' => $row['product_info'],
           'tags' => $row['tags']
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
$conn->close();
header('Location: index.php');
exit;
?>