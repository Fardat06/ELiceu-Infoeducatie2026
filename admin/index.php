<?php
    ob_start();
   // session_id("session1");
    session_start(); //array
    $noNavbar = '';
    global $con;

    $pageTitle1='LOGIN';
  //  echo $_SERVER['REMOTE_ADDR'];
    $id=isset($_GET['id']) ? $_GET['id'] : 'Manage';
  // echo $id;

    if (isset($_SESSION['username'])){ // If the user is login
        header('Location: dashboard.php');  //Redirect to dashboard page
    }
    include '../plugin/init.php';
    echo '<link rel="stylesheet" href="layout/css/login.css" />';
     //Check if user coming from HTTP post request
    if($_SERVER['REQUEST_METHOD']== 'POST'){
        
        $username = $_POST['user'];
        $password = $_POST['pass'];
      //  $hashedpass = sha1($password);

        $salt = md5(313);
        $hashedpass = md5($password . $salt);

        // Check if the user exist in database
        
        $stmt = $con->prepare("SELECT 
                                    userid ,username , password ,Language
                              FROM " . DB_PREFIX . "users 
                              WHERE 
                                    username = ? 
                              AND 
                                    password = ? 
                              AND 
                                     RegStatus=0 
                                     LIMIT 1");
        $stmt->execute(array($username , $hashedpass));
        $row = $stmt->fetch(); // save the data from mysql tabel on array
        $count = $stmt->rowCount();
        
        //If $count > 0 this mean the database contain record about this username
        if ($count >0){
           // print_r($row); to print the array 

            $path = $_SERVER['REQUEST_URI'];
            $urlParts = explode("/", $path);
            $_SESSION['dir']  =$urlParts[1];

          
            $_SESSION['ID'] = $row['userid'] ;  //Rgister session ID userid sensble case
            $_SESSION['username'] = $username ; //Rgister session name
            $_SESSION['Language'] = $row['Language'] ; //Rgister session Lang
             
            $stat3 = $con->prepare("SELECT Lang_Code  , Lang_Name , Order_No FROM " . DB_PREFIX . "languages WHERE Stopx=0 AND Translate='1' ORDER BY Order_No ASC");
            $stat3->execute();
            $lang = $stat3->fetchAll();
            $i=1;
            foreach($lang as $langu){
                $_SESSION[$i.'lang_flag'] = 'uploads/flags/'.$langu['Lang_Code'].'.png';
                $_SESSION[$i.'lang_name'] = $langu['Lang_Name'];
                $i = $i + 1;
            }
             
             
            header('Location: dashboard.php');  //Redirect to dashboard page
        //    exit();  
        }
    }

include 'template/header.php';
?>




    <form class="login" action="<?php $_SERVER['PHP_SELF']?>" autocomplete="off" method="POST">

        <span class="logo">E Liceu</span>

        <label for="email">User Name</label>
        <input type="text" name="user" placeholder="Username" autocomplete="off" >

        <label for="senha">Password</label>
        <input type="password" name="pass" placeholder="Password" autocomplete="new-password">
         

        <button class="btn" type="submit">Login</button>


    </form>


<?php
  include 'template/footer.php'; 
  ob_end_flush();
?>
