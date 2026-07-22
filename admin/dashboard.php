<?php
     session_start();
  
    include '../plugin/function.php';

    if (isset($_SESSION['username-x'])){ 
        $pageTitle1='DASHBOARD';
        include '../plugin/init.php';

        $numUser = 5;
        $latestUsers=getLatest('*','users','UserID',$numUser );

        
        $numComment = 5;


    
        

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
