async function getMedicine(str){
    const response = await fetch(`http://localhost/phpelasticsearch/elasticsearchapi.php?query=${str}`);
    return response.json();
}

const response = getMedicine('ABACAVIR');
console.log(response);
response.then((data)=>console.log(data)).catch((err)=>console.log("Error is",err));