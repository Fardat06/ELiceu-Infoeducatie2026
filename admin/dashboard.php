<?php
     session_start(); 
  
    //include 'function.php';

    if (isset($_SESSION['username'])){ 
        $pageTitle1='DASHBOARD';
        include '../plugin/init.php';

     
        $numUser = 5;
        $latestUsers=getLatest('*','users','UserID',$numUser ); 

        
        $numComment = 5; 

    
        

include 'template/header.php';
?>
    <div class="dashboard-container">
  
<?php
  include 'template/sidebar.php';
?>

      <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>
      <main class="dashboard-main">
     
<?php
  include 'template/header_main.php';   
  include 'template/content.php';

?>
      </main>
    </div>
<?php
         include 'template/footer.php';
    } else{
        header('Location: index.php'); 
        exit();
    }

    ob_end_flush();
      ?>
