<?php
// connection bdd
try{
$bdd=new PDO('mysql:host=localhost;dbname=db-ardennes','root','');
}
catch (Exception $e){
	die('Erreur :'.$e->getMessage());
}
// requête sur hosp avec critère de date
$req=$bdd->query("SELECT hosp,date,rea,dchosp,tx_incid,tx_pos,incid_hosp,incid_rea,incid_rad,pos,pos_7j FROM data_indicator_ardennes");

// récupérer les valeurs dans un tableau
while ($donnees=$req->fetch(PDO::FETCH_NUM)){
        $hosp[]=$donnees[0];
        $dateData[]=$donnees[1];
        $reaData[]=$donnees[2];
        $dcData[]=$donnees[3];
        $txincid[]=$donnees[4];
        $incid_hosp[]=$donnees[5];
        $incid_rea[]=$donnees[6];
        $incid_rad[]=$donnees[7];
        $positif[]=$donnees[8];
        $positif_[]=$donnees[9];
}


$data=json_encode($hosp); // passage des données php-js
$data=json_encode($dateData); // passage des données php-js
$data=json_encode($reaData); // passage des données php-js
$data=json_encode($dcData); // passage des données php-js
$data=json_encode($txincid); // passage des données php-js
$data=json_encode($positif); // passage des données php-js
$data=json_encode($positif_); // passage des données php-js


$hosp_moyenne_glissante = array();
$rea_moyenne_glissante = array();
$rad_moyenne_glissante = array();

for ($i = 0; $i < count($incid_hosp); $i++) {
    if ($i < 7) {
        // Pour les premiers éléments, la moyenne est calculée en prenant en compte uniquement les éléments disponibles
        $hosp_moyenne_glissante[] = array_sum(array_slice($incid_hosp, 0, $i + 1)) / ($i + 1);
        $rea_moyenne_glissante[] = array_sum(array_slice($incid_rea, 0, $i + 1)) / ($i + 1);
        $rad_moyenne_glissante[] = array_sum(array_slice($incid_rad, 0, $i + 1)) / ($i + 1);

    } else {
        // Pour les éléments suivants, la moyenne est calculée en prenant en compte les 7 éléments précédents
        $hosp_moyenne_glissante[] = array_sum(array_slice($incid_hosp, $i - 6, 7)) / 7;
        $rea_moyenne_glissante[] = array_sum(array_slice($incid_rea, $i - 6, 7)) / 7;
        $rad_moyenne_glissante[] = array_sum(array_slice($incid_rad, $i - 6, 7)) / 7;

    }
}



	$datas=$bdd -> prepare("SELECT `tx_incid`,`tx_pos`,`TO` FROM `data_indicator_ardennes` where `date`='2022-07-07'");
	$datas -> execute();
	$vaccinations = $datas -> fetchAll();

	foreach ($vaccinations as $vaccination ) {
		$tab_incid=$vaccination['tx_incid'];
		$tab_pos=$vaccination['tx_pos'];
		$tab_to=$vaccination['TO'];
	}
	$datas=$bdd -> prepare("SELECT `R` FROM `data_indicator_ardennes` where `R`='0.6'");
	$datas -> execute();
	$vac = $datas -> fetchAll();

	foreach ($vac as $vacc ) {
    $tab_R=$vacc['R'];}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
    integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="style.css" rel="stylesheet">
  <meta name="description" content="Dashboard de la SAE 303">
  <meta name="keywords" content="dashboard,SAE,Covid">
  <meta name="author" content="Nabil BOUKHORISSA">

  <title>Dashboard du covid-19 en Ardennes</title>
</head>
<body >


<header>
         <h1 class="col-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Dashboard des données COVID-19 en Ardennes</h1>
</header>

<form>
  <label for="start-date">Filtrer :</label>
  <input type="date" id="start-date" name="start-date">
  <input type="submit" value="Envoyer">
</form>

<h3 class="col-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Toutes les données sur cette page sont spécifiquement relatives au département de l'<strong>Ardennes</strong> (08) et sur la période du <strong> 18-03-2020 au 10-11-2022</strong> </h3>


  <div class="container">

    <section class="taux">
      <div class="row">

<h2 class="col-12 col-lg-12 col-sm-12"><u>Indicateurs de suivi de l’épidémie de COVID-19</u></h2>

        <div class="col-12 col-lg-3 col-md-12 col-sm-12 col-xs-12">
          <strong>Taux de positivité</strong> : <?= round($tab_incid,2); ?>
            <button  onclick="toggleText()" class="ml-3">▼</button></p>
            <p id="hiddenText" style="display: none;"><strong>Le taux de positivité</strong> Nombre de personnes testées positives pour la première fois depuis plus de 60 jours</p>
        </div>

        <div class="col-12 col-lg-3 col-md-12 col-sm-12 col-xs-12"><strong>Taux d'incidence</strong> : <?= $tab_pos; ?>
        <button  onclick="toggleText2()" class="ml-3">▼</button></p>            
            <p id="hiddenText2" style="display: none;"><strong>Le taux d'incidence</strong> correspond au nombre de personnes testées positives pour la première fois depuis plus de 60 jours rapporté à la taille de la population.</p>

        </div>
      
    
        <div class="col-12 col-lg-3 col-md-12 col-sm-6 col-xs-12"><strong>Taux de reproduction</strong> : <?= $tab_R; ?>
        <button  onclick="toggleText3()" class="ml-3">▼</button></p>            
            <p id="hiddenText3" style="display: none;">
            <strong>Facteur de reproduction du virus</strong>
            <br>C’est le nombre moyen d'individus qu’une personne infectée peut contaminer. 
            <br> <br>Si le R est supérieur à 1, l’épidémie se développe ; s’il est inférieur à 1, l’épidémie régresse</p>
        </div>


        <div class="col-12 col-lg-3 col-md-12 col-sm-6 col-xs-12"><strong>TO</strong> : <?= round($tab_to,2); ?>
        <button  onclick="toggleText4()" class="ml-3">▼</button></p>            
            <p id="hiddenText4" style="display: none;"><strong>Le taux d'occupation</strong> correspond à la tension hospitalière sur la capacité en réanimation (Proportion de patients en réanimation, soins intensifs ou en surveillance rapportée au total des lits en capacité initiale</p>

        </div>
      </div>
    </section>


    <script>//Bouttons cache/affiche texte
        function toggleText() {
          var hiddenText = document.getElementById("hiddenText");
          if (hiddenText.style.display === "none") {
            hiddenText.style.display = "block";
          } else {
            hiddenText.style.display = "none";
          }
        }

        function toggleText2() {
          var hiddenText2 = document.getElementById("hiddenText2");
          if (hiddenText2.style.display === "none") {
            hiddenText2.style.display = "block";
          } else {
            hiddenText2.style.display = "none";
          }
        }

        function toggleText3() {
          var hiddenText3 = document.getElementById("hiddenText3");
          if (hiddenText3.style.display === "none") {
            hiddenText3.style.display = "block";
          } else {
            hiddenText3.style.display = "none";
          }
        }

        function toggleText4() {
          var hiddenText4 = document.getElementById("hiddenText4");
          if (hiddenText4.style.display === "none") {
            hiddenText4.style.display = "block";
          } else {
            hiddenText4.style.display = "none";
          }
        }
      </script>


    <section class="chart1">
     <h2 class="col-12 col-lg-12 col-sm-12"><u>Données relatives au COVID-19</u></h2>
      <div class="row">
        <div class="col-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                  <!-- PREMIERS GRAPHIQUES -->
          <h3 class="w-100 text-center">Nombre d'hospitalisations et de dèces</h3>
          <canvas id="linechart1"></canvas>
        </div>

        <div class="col-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                  <!-- DEUXIEME GRAPHIQUES -->
          <h3 class="w-100 text-center">Nombre de réanimation</h3>
          <canvas id="linechart2"></canvas>
        </div>
      </div>
    </section>


    <section class="chart2">
      <div class="row">
        <div class="col-10 col-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                  <!-- TROISIÈME GRAPHIQUES -->
          <h3 class="w-100 text-center">Nombre de nouveaux patients hospitalisés, admis en réanimation et retourné à leurs domiciles chaques semaines </h3>
          <canvas id="linechart3"></canvas>
        </div>
      </div>
    </section>


    <script> //IMPORTATION DES DONNEES DES BDD DANS LES CHARTS
      new Chart(document.getElementById("linechart1"), {
        type: 'line',
        data: {
          labels: <?php echo json_encode($dateData); ?>,
        datasets: [
          {
            label: "Hospitalisations",
            pointRadius: 2,
            pointHoverRadius: 1.5,
            backgroundColor: "#FF000040",
            data: <?php echo json_encode($hosp); ?>
          },
          {
            label: "Déces en hopital",
            fill: true,
            pointRadius: 2,
            backgroundColor: "#80008040",
            data: <?php echo json_encode($dcData); ?>
          }
      ]
    },
        options: {
        legend: { display: false },
        title: {
          display: true,
          text: "Nombre d'hospitalisations et de dèces pendant la 1er vague de Covid-19 en Ardennes"
        }
      }
});

      new Chart(document.getElementById("linechart2"), {
        type: 'line',
        data: {
          labels: <?php echo json_encode($dateData); ?>,
        datasets: [
        {
            label: "Personnes déclarées positives",
            backgroundColor: "#ff800060",
            fill: true,
            pointRadius: 2,
            data: <?php echo json_encode($positif_); ?>
        },
      ]
    },
        options: {
        legend: { display: false },
        title: {
          display: true,
          text: "Nombre de réanimation"
        }
      }
});

      new Chart(document.getElementById("linechart3"), {
        type: 'line',
        data: {
          labels: <?php echo json_encode($dateData); ?>,
        datasets: [
          {
            label: "Nv hosp.7j",
            pointRadius: 3,
            pointHoverRadius: 1.5,
            backgroundColor: "#FF000040",
            data: <?php echo json_encode($hosp_moyenne_glissante); ?> 
          },
          {
            label: "Nv réa.7j",
            fill: true,
            pointRadius: 1.5,
            backgroundColor: "#80008040",
            data: <?php echo json_encode($rea_moyenne_glissante); ?> 
          },
          {
            label: "Nv rad.7j",
            fill: true,
            pointRadius: 1.5,
            backgroundColor: "#0000FF40",
            data: <?php echo json_encode($rad_moyenne_glissante); ?>
          }
      ]
    },
        options: {
        legend: { display: false },
        title: {
          display: true,
          text: "Nombre d'hospitalisations et de dèces pendant la 1er vague de Covid-19 en Ardennes"
        }
      }
});
    </script>


<?php
$datas = $bdd->prepare("SELECT `libelle_classe_age`,`effectif_cumu_termine` FROM `data_vac_ardennes` WHERE `type_vaccin`='Tout vaccin' AND `semaine_injection`='2022-39' AND `classe_age`!='TOUT_AGE'");
$datas->execute();
$vaccinations = $datas->fetchAll();

$tab_donnees = array();
$tab_etiquettes = array();

foreach ($vaccinations as $vaccination) {
  $tab_etiquettes[] = $vaccination['libelle_classe_age'];
  $tab_donnees[] = $vaccination['effectif_cumu_termine'];
}
?>


    <section class="piechart">
   <h2 class="col-12 col-lg-12 col-sm-12"><u>Données relatives à la vaccination</u></h2> 
      <div class="row">
        <div class=" col-12 col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                            <!-- PIE CHART -->
          <h4 class="w-100 text-center mt-6">Proportion de vaccins administrés aux différentes classes d'âges</h4>
          <canvas id="pie_chart">Désolé, votre navigateur ne prend pas en charge &lt;canvas&gt;.</canvas>
        </div>



        <div class="col-12 col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                            <!-- DOUGHNUT CHART -->
          <h4 class="w-100 text-center">Répartition des differents vaccins parmi toute la population</h4>
          <canvas id="pie_chart2">Désolé, votre navigateur ne prend pas en charge &lt;canvas&gt;.</canvas>
        </div>

      </div>
    </section>





        <?php
$datas = $bdd->prepare("SELECT `libelle_classe_age`, `effectif_cumu_termine`,`type_vaccin` FROM `data_vac_ardennes` WHERE `semaine_injection`='2022-44' AND `type_vaccin`!='Tout vaccin' AND `classe_age`='TOUT_AGE'");
$datas->execute();
$vacc = $datas->fetchAll();

$tab_effect = array();
$tab_vac= array();

foreach ($vacc as $vaccination) {
  $tab_vac[] = $vaccination['type_vaccin'];
  $tab_effect[] = $vaccination['effectif_cumu_termine'];
}
?>
    <script type="text/javascript">

      new Chart(document.getElementById("pie_chart"), {
        type: 'doughnut',
        data: {
          labels: <?php echo json_encode($tab_vac); ?>,
        datasets: [{
          data: <?php echo json_encode($tab_effect); ?>
                }]
              },  
          });

      new Chart(document.getElementById("pie_chart2"), {
        type: 'pie',
        data: {
          labels: <?php echo json_encode($tab_etiquettes); ?>,
        datasets: [{
          data: <?php echo json_encode($tab_donnees); ?>
                }]
              },  
          });
    </script>




    <?php
$datas = $bdd->prepare("SELECT `effectif_cumu_termine`,`date` FROM `data_vac_ardennes` WHERE `type_vaccin`='Tout vaccin' and `libelle_classe_age`='Tout âge' and `effectif_cumu_termine`!='';
");
$datas->execute();
$vacc = $datas->fetchAll();

$tab_cumu_fini = array();
$date_vac = array();


foreach ($vacc as $vaccination) {
  $tab_cumu_fini[] = $vaccination['effectif_cumu_termine'];
  $date_vac[] = $vaccination['date'];

}


?>
    <section class="myChart">
      <div class="row">
        <div class="col-12">
                                            <!-- BAR CHART -->
          <h3 class="w-100 text-center mt-6 col-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Evolution du nombre de personnes ayant un schéma vaccinal complet</h3>
          <canvas id="myChart"></canvas>
          <p class="explication">Les données sont exprimés par le cumul des personnes ayant un schéma vaccinal complet <u>tous vaccins confondus</u></p>
        </div>
      </div>
    </section>

    <script>
      const ctx = document.getElementById('myChart');

      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: <?php echo json_encode($date_vac); ?>,
        datasets: [{
          label: 'Schéma vaccinal complet',
          data: <?php echo json_encode($tab_cumu_fini); ?> ,
          backgroundColor: "#90ee9090",
          borderWidth: 1
              }]
            },
        options: {
        scales: {
          y: {
          }
        }
      }
          });
    </script>
  </div>


<footer>
    <p>Les données sont tirés de la base de donnée officiel <a href="https://datavaccin-covid.ameli.fr/explore/dataset/donnees-vaccination-par-tranche-dage-type-de-vaccin-et-departement/explore/information?sort=departement_residence&q=date%3D2022-11-06+and+not+(departement_residence:%27999%27+OR+departement_residence:%27Tout+d%C3%A9partement%27)&refine.type_vaccin=Tout+vaccin&refine.classe_age=TOUT_AGE">Ameli</a></p>
    <p class="text-center">Copyright &copy; 2022 Dashboard Nabil Boukhorissa. Tous droits réservés.</p>
</footer>

</body>
</html>