<?php
session_start();
require_once 'modules/PDOConnector.php';
require_once 'modules/Security.php';
require_once 'modules/Alert.php';

if(!Security::isLoggedIn())
{
    header('Location: login.php');
    exit();
}

function showTableHTML()
{
    $pdo = PDOConnector::connect($_GET['database']);
    if($pdo == null)
    {
        Alert::setAlert('red', 'Database with name ' . $_GET['database'] . ' does not exist!', 'index.php');
        exit();
    }

    $query = $pdo->prepare("DESCRIBE :table");
    $query->bindParam(":table", $_GET['table']);
    $query->execute();

    if(!$query)
    {
        Alert::setAlert('red', 'Table with name ' . $_GET['table'] . ' does not exist!', 'index.php');
        exit();
    }

    $result = '<div style="overflow-y: scroll;"><table class="w3-table w3-bordered w3-striped">';
    $result .= '<tr>';

    $fields = array();

    while ($column = $query->fetch(PDO::FETCH_OBJ))
    {
        $result .= '<th>'.$column->Field.'</th>';
        array_push($fields, $column->Field);
    }

    $result .= '</tr>';

    $fieldQuery = $pdo->query("SELECT * FROM " . $_GET['table']);
    while ($field = $fieldQuery->fetch(PDO::FETCH_ASSOC))
    {
        $result .= '<tr>';
        foreach ($fields as $fieldData)
        {
            $result .= '<td>' . $field[$fieldData] . '</td>';
        }
        $result .= '</tr>';
    }

    $result .= '</table></div>';
    return $result;
}
?>

<html>
<head>
    <title>&lambda; Databases Manager - <?php echo $_GET['table']; ?> column</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
<nav>
    <div class="w3-bar w3-blue">
        <div class="w3-bar-item">&lambda; Databases Manager</div>
        <div class="w3-bar-item"><?php echo $_SESSION['dbData']['user']; ?></div>
        <a class="w3-bar-item w3-button" href="index.php">All databases</a>

        <a class="w3-bar-item w3-button w3-red" style="text-align: right;" href="login.php?logout=true">Logout</a>
    </div>
</nav>

<div class="w3-container w3-row">
    <div class="w3-col s1 m1 l1">&nbsp;</div>
    <div class="w3-col s10 m10 s10">
        <?php echo Alert::displayAsText(); ?>
        <h1><?php echo $_GET['table']; ?> tables:</h1>
        <?php echo showTableHTML(); ?>
    </div>
    <div class="w3-col s1 m1 l1">&nbsp;</div>
</div>
</body>
</html>