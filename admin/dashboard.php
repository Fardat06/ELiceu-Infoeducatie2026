<?php
     session_start(); //array
  
    //include 'function.php';

    if (isset($_SESSION['username'])){ // If the user is login
        $pageTitle1='DASHBOARD';
        include '../plugin/init.php';

        ////////////////////////////// Start Dashboard page //////////////////////////////////
        $numUser = 5; // Number of latest users
        $latestUsers=getLatest('*','users','UserID',$numUser ); // latest users array

        
        $numComment = 5; // Number of latest comments


    
        

include 'template/header.php';
?>
    <div class="dashboard-container">
      <!-- Dashboard Sidebar -->
<?php
  include 'template/sidebar.php';
?>
      <!-- Dashboard Sidebar Overlay -->


      <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>
      <!-- Dashboard Main Content -->
      <main class="dashboard-main">
        <!-- Dashboard Header -->
<?php
  include 'template/header_main.php';   
  include 'template/content.php';

?>
        <!-- Dashboard Content -->

      </main>
    </div>
<?php
         include 'template/footer.php';
    } else{
        header('Location: index.php');  //Go back to index page
        exit();
    }

    ob_end_flush(); // Release the output
      ?>
