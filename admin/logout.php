<?php
    session_start();   //Start the session
   
   
    // session_unset();   //Unset the data
    // Unset all of the session variables.
    $_SESSION = array();
    
    // If it's desired to kill the session, also delete the session cookie.
    // Note: This will destroy the session, and not just the session data!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy(); //Destroy th session
    header('location: index.php'); //Goto index.php page
    exit(); 
    
?>