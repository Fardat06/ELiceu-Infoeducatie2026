<?php
include 'plugin/function.php';
ob_start();
session_start();
$pageTitle1 = 'High school';
include 'plugin/init.php';
global $con;
global $pageTitle1;
global $stmt1;
global $rows;
include 'template/header.php';
$id = $_GET['id'];

        $stmt = $con->prepare("SELECT * FROM " . DB_PREFIX . "numa_liceu  WHERE id_numa_liceu = ? AND stopx = 0");
        $stmt->execute(array($id));
        $rows = $stmt->fetch();
        $s_name = $rows['name'];

          $stmt1 = $con->prepare("SELECT * FROM " . DB_PREFIX . "liceu  WHERE name = ? AND stopx = 0");
          $stmt1->execute(array($s_name));
          $row1 = $stmt1->fetchAll();

          $stmt2 = $con->prepare("SELECT * FROM " . DB_PREFIX . "medie  WHERE name = ? AND stopx = 0");
          $stmt2->execute(array($s_name));
          $row2 = $stmt2->fetchAll();

          $stmt3 = $con->prepare("SELECT * FROM " . DB_PREFIX . "poztion  WHERE name = ? AND stopx = 0");
          $stmt3->execute(array($s_name));
          $row3 = $stmt3->fetchAll();

          $stmt4 = $con->prepare("SELECT DISTINCT profil FROM " . DB_PREFIX . "liceu  WHERE name = ? AND stopx = 0");
          $stmt4->execute(array($s_name));
          $row4 = $stmt4->fetchAll();


?>
<div class="overlay" id="overlay"></div>

<div class="toast" id="toast">Adăugat la favorite!</div>

<div class="page-wrapper">

  <div class="breadcrumb">
    <a href="index.php">Acasă</a>
    <span>›</span>
    <a href="licee_general.php">Toate Liceele</a>
    <span>›</span>
    <span><?php echo $rows['tip']. ' '.$rows['name']; ?></span> 
  </div>

  <div class="product-main">

    <div class="product-top">

      <div class="gallery-col fade-in">
        <div class="main-image-wrap">
          <img id="mainImg"
               src="src/images/liceu/<?php echo $rows['photo'] ?>"
               alt="<?php echo $rows['tip']. ' '.$rows['name']; ?>">
          <div class="main-image-overlay"></div>
        </div>
        <div class="thumb-row">
          <div class="thumb active" onclick="setImg(this,'src/images/liceu/<?php echo $rows['photo'] ?>')">
            <img src="src/images/liceu/<?php echo $rows['photo'] ?>" alt="">
          </div>
          <div class="thumb" onclick="setImg(this,'src/images/liceu/<?php echo $rows['name'].'2.jpg' ?>')">
            <img src="src/images/liceu/<?php echo $rows['name']."2.jpg" ?>" alt="">
          </div>
        </div>
      </div>

      <div class="info-col fade-in fade-in-1">
        <h1 class="school-name"><?php echo $rows['tip']?><br><?php echo $rows['name']?></h1>

        <p class="school-desc"><?= $rows['long_description'] ?>          
        </p>

        <div class="quick-stats">
          <div class="stat-box">
            <div class="stat-icon"></div>
            <div class="stat-value"><?php echo  $rows['total_no_student'] ?></div>
            <div class="stat-label">Elevi</div>
          </div>
          <div class="stat-box">
            <div class="stat-icon"></div>
            <div class="stat-value"><?php echo  countspecializare($rows['name']);?></div>
            <div class="stat-label">Specializare</div>
          </div>
          <div class="stat-box">
            <div class="stat-icon"></div>
            <div class="stat-value"><?php echo  media($rows['name']) ?></div>
            <div class="stat-label">Medie min. 2025</div>
          </div>
        </div>


      </div>
    </div>

    <div class="bottom-grid"> 

      <div class="section-block fade-in fade-in-2">
        <div class="section-header">
          <h2>Specificații</h2>
        </div>
        <div class="section-body" style="padding:0;">
          <table class="specs-table">
            <tr><td>Denumire oficială</td><td><?php echo $rows['tip']. ' '.$rows['name']; ?></td></tr>
            <tr><td>Locație</td><td><?php echo $rows['address']; ?></td></tr>
            <tr><td>Sector</td><td><?php echo $rows['zone']; ?></td></tr>
            <tr><td>Program</td><td>

                    <?php
                    $program_clasa = program_clasa($rows['name']);
                    foreach ($program_clasa as $program) { ?>
                        <div class="pm-item">
                            <span class="pm-name">Clasă 9</span>
                            <span class="pm-val" style="padding-left: 45px;"><?=  '&nbsp;'.$program['program_9'] ?></span>
                        </div>
                        <div class="pm-item">
                            <span class="pm-name">Clasă 10</span>
                            <span class="pm-val" style="padding-left: 45px;"><?= $program['program_10'] ?></span>
                        </div>
                        <div class="pm-item">
                            <span class="pm-name">Clasă 11</span>
                            <span class="pm-val" style="padding-left: 45px;"><?= $program['program_11'] ?></span>
                        </div>
                        <div class="pm-item">
                            <span class="pm-name">Clasă 12</span>
                            <span class="pm-val" style="padding-left: 45px;"><?= $program['program_12'] ?></span>
                        </div>

                    <?php } ?>


            </td></tr>
            <tr><td>Tip liceu</td><td><?php echo $rows['tip']; ?></td></tr>
            <tr><td>Profiluri</td><td>
              <?php foreach($row4 as $row4) { ?>
                <span class="spec-badge"><?php echo $row4['profil'].' '; ?></span>
              <?php } ?>  

            
            </td></tr>
            <tr><td>Număr elevi</td><td><?php echo $rows['total_no_student']?></td></tr>
            <tr><td>Site oficial</td><td><a class="spec-link" href="<?php echo $rows['web_page']; ?>" target="_blank"><?php echo $rows['name']; ?> ↗</a></td></tr>
          </table>
        </div>
      </div>

      <div class="section-block fade-in fade-in-3">
        <div class="section-header">
          <h2>Istoricul Admiterii</h2>
        </div>
        <div class="section-body" style="display:flex;flex-direction:column;gap:24px;">

          <div style="display:flex;gap:8px;border-bottom:2px solid var(--border);padding-bottom:0;">
            <button class="adm-tab active" onclick="switchTab('medii',this)">Arhivă medii</button>
            <button class="adm-tab" onclick="switchTab('pozitii',this)">Arhivă poziții</button>
          </div>

          <div id="tab-medii">
            <p style="font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:14px;">Ultima medie acceptată pe specializare și an</p>
            <div class="admitere-table-wrap">
              <table class="admitere-table arch-table">
                <thead>
                  <tr>
                    <th style="text-align:left;">Specializare</th>
                    <th>Bilingv</th>
                    <th>2025</th><th>2024</th><th>2023</th><th>2022</th><th>2021</th><th>2020</th>
                  </tr>
                </thead>
                <tbody>
                <?php           
 
                foreach($row2 as $row2){?>
  
                
                
                <tr>
                    <td class="spec-name"><?= $row2['specializare'];?></td>
                    <td class="bil-cell"><?= $row2['bilingv'];?></td>
                    <td><span class="medie-badge high"><?php echo $row2['u_medie_2025'];?></span></td>
                    <td><span class="medie-badge high"><?= $row2['u_medie_2024'];?></span></td>
                    <td><span class="medie-badge high"><?= $row2['u_medie_2023'];?></span></td>
                    <td><span class="medie-badge high"><?= $row2['u_medie_2022'];?></span></td>
                    <td><span class="medie-badge high"><?= $row2['u_medie_2021'];?></span></td>
                    <td><span class="medie-badge high"><?= $row2['u_medie_2020'];?></span></td>
                  </tr>
<?php
}
 ?>
                </tbody>
              </table>
            </div>
            
          </div>

          <div id="tab-pozitii" style="display:none;">
            <p style="font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:14px;">Locul în ierarhia din București al ultimului elev admis pe fiecare specializare</p>
            <div class="admitere-table-wrap">
              <table class="admitere-table arch-table">
                <thead>
                  <tr>
                    <th style="text-align:left;">Specializare</th>
                    <th>Bilingv</th>
                    <th>2025</th><th>2024</th><th>2023</th><th>2022</th><th>2021</th><th>2020</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>

                 <?php           
 
                foreach($row3 as $row2){?>
                 
                    <td class="spec-name"><?= $row2['specializare'];?></td>
                    <td class="bil-cell"><?= $row2['bilingv'];?></td>
                    <td><span class="medie-badge high"><?php echo $row2['u_pozition_2025'];?></span></td>
                    <td><span class="medie-badge high"><?= $row2['u_pozition_2024'];?></span></td>
                    <td><span class="medie-badge high"><?= $row2['u_pozition_2023'];?></span></td>
                    <td><span class="medie-badge high"><?= $row2['u_pozition_2022'];?></span></td>
                    <td><span class="medie-badge high"><?= $row2['u_pozition_2021'];?></span></td>
                    <td><span class="medie-badge high"><?= $row2['u_pozition_2020'];?></span></td>
                  </tr>
<?php
}
 ?>
             </tbody>
              </table>
            </div>
            <p style="font-size:12px;color:var(--text-muted);margin-top:12px;">* Pozițiile reprezintă locul în ierarhia generală a candidaților din București.</p>
          </div>

        </div>
      </div>

    </div>

  </div><!-- /product-main -->

<?php
include 'template/footer.php'; ?>
