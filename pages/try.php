<?php

if (isset($_POST['btna'])){
    echo 'button is clicked';

    $firstname = $_POST['fname'];
    $lastname = $_POST['lname'];
    echo 'firstname: . ' ' . $lastname;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="" method="POST">
        <input type="text" name="fname">
        <input type="text" name="lname">
        <button name="btna" type="submit">Submit</button>
    </form>
</body>
</html>