<?php
include 'db.class.php';
use Utilities\DB;

$db = new DB( 'localhost', 'fruitsDB', 'user1', 'password1' );

// If you need another database connection, just create a new variable
$db2 = new DB( 'localhost', 'vegetablesDB', 'user1', 'password1', 'pgsql' );

// Simple query to obtain one object, the fruit with id = 1, in this case
$result = $db->sexecute(
	"SELECT * FROM fruits WHERE id = :id",
	array(
		':id' => 1
	)
);

echo "First query.<br>";
echo '<pre>';
var_dump( $result );
echo '</pre>';

// Simple query to obtain a list of objects, the first five fruits in this case
$results = $db->execute( "SELECT * FROM fruits LIMIT 5" );

echo "Second query.<br>";
echo '<pre>';
var_dump( $results );
echo '</pre>';

// Simple query to insert an item and get its id
$lastId = $db->queryId(
	"INSERT INTO fruits ( name, color ) VALUES ( :name, :color )",
	array(
		':name' => 'orange',
		':color' => 'orange'
	)
);

echo "Third query.<br>";
echo '<pre>';
var_dump( $lastId );
echo '</pre>';

// Simple query when you don't want to obtain results
$db2->query(
	"UPDATE vegetables SET color = :color WHERE id = :id",
	array(
		':id' => 1,
		':color' => 'green'
	)
);

// Get number of affected rows
$numRows = $db2->rows();

echo "Fourth query.<br>";
echo '<pre>';
var_dump( $numRows );
echo '</pre>';

// Simple query to get the first column of the first result
$color = $db2->get(
	"SELECT color FROM vegetables WHERE id = :id",
	array(
		':id' => 1
	)
);

echo "Fifth query.<br>";
echo '<pre>';
var_dump( $color );
echo '</pre>';


// Simple query to fetch fruits, one by one
$db->query( "SELECT * FROM fruits LIMIT 5" );

echo "Sixth query.<br>";
while ( $fruit = $db->fetch() ) {
	echo '<pre>';
	var_dump( $fruit );
	echo '</pre>';
}

// Transaction to update and rollback on error
$sql = "UPDATE fruits SET color = :color WHERE id = :id";

echo "Seventh query.<br>";
try {
	$db->beginTransaction();
	for( $i = 0; $i < 5; $i++ ) {
		$db->execute(
			$sql,
			array(
				':id' => $i,
				':color' => 'red'
			)
		);
	}
	$db->commit();
	echo "Transaction committed successfully";
} catch( PDOException $e ) {
	$db->rollBack();
	echo "Transaction rolled back successfully";
}

// Close the Database connections
$db->end();
$db2->end();
?>