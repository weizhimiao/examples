<?php

require __DIR__ . '/../vendor/autoload.php';

use RecipeSearchSimple\Constants;

// Get search results from Elasticsearch if the user searched for something
$results = [];
if (!empty($_REQUEST['q'])) {

    $esPort = getenv('APP_ES_PORT') ?: 9200;

    $client = new Elasticsearch\Client([
        'hosts' => [ 'localhost:' . $esPort ]
    ]);

    $searchParams['index'] = Constants::ES_INDEX;
    $searchParams['type']  = Constants::ES_TYPE;
    $searchParams['body']['query']['multi_match']['query'] = $_REQUEST['q'];
    $searchParams['body']['query']['multi_match']['fields'] = [ 'name', 'description', 'tags' ];

    $queryResponse = $client->search($searchParams);
    $results = $queryResponse['hits']['hits'];
}
?>
<html>
<head>
  <title>Recipe Search</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"></head>
</head>
<body>
<div class="container">
<h1>Recipe Search</h1>
<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-inline">
  <input name="q" value="<?php echo $_REQUEST['q']; ?>" type="text" placeholder="What are you hungry for?" class="form-control input-lg" size="40" />
  <input type="submit" value="Search" class="btn btn-lg" />
</form>
<?php
if (count($results) > 0) {
?>
<table class="table table-striped">
<thead>
  <th>Name</th>
  <th>Description</th>
  <th>Cooking time (minutes)</th>
</thead>
<?php
    foreach ($results as $result) {
?>
<tr>
  <td><?php echo $result['_source']['name']; ?></td>
  <td><?php echo $result['_source']['description']; ?></td>
  <td><?php echo $result['_source']['cooking_time_min']; ?></td>
</tr>
<?php
    } // END foreach loop over results
?>
</table>
<?php
} // END if there are search results

elseif (!empty($_REQUEST['q'])) {
?>
<p>Sorry, no recipes with <em><?php echo $_REQUEST['q']; ?></em> found :( Would you like to <a href="/add.php">add</a> one?</p>
<?php

} // END elsif there are no search results

?>
</div>
</body>
</html>
