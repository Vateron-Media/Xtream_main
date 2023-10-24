<?php
require "./init.php";

// Get the username, password, and type from the request
$username = A78bf8D35765bE2408C50712cE7a43Ad::$request["username"];
$password = A78BF8d35765Be2408c50712cE7A43Ad::$request["password"];
$type = a78BF8d35765BE2408C50712cE7A43AD::$request["type"];

// Check if the output is empty, if not, assign it to a variable
$output = empty(a78Bf8D35765BE2408c50712cE7a43Ad::$request["output"]) ? '' : A78BF8D35765be2408C50712ce7a43AD::$request["output"];

// Query the database to check if the username and password exist
$f566700a43ee8e1f0412fe10fbdf03df->query("SELECT `id`,`username`,`password` FROM `users` WHERE `username` = '%s' AND `password` = '%s' LIMIT 1", $username, $password);

// If no matching user is found, go to the error handling section
if ($f566700a43ee8e1f0412fe10fbdf03df->D1e5CE3b87Bb868B9e6efd39Aa355A4F() <= 0) {
    D9f93b7C177E377D0BbFe315eAEae505();
    http_response_code(404);
    // [PHPDeobfuscator] Implied script end
    return;
}

// Get the user's information from the database
$user_data = $f566700a43ee8e1f0412fe10fbdf03df->f1ED191d78470660edFf4A007696bc1F();
$userID = $user_data["id"];

// Check if the username and password match the database records
if (!(A78Bf8D35765bE2408C50712ce7A43Ad::$settings["case_sensitive_line"] == 0 || $user_data["username"] == $username && $user_data["password"] == $password)) {
    D9f93b7C177E377D0BbFe315eAEae505();
    http_response_code(404);
    // [PHPDeobfuscator] Implied script end
    return;
}

// Set memory limit to unlimited
ini_set("memory_limit", -1);

// Call a function to generate the output
$output = Gen_User_Playlist($userID, $type, $output, true);

// Output the result
echo $output;
die;
