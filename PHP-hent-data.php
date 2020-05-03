<?php

// IP-adressen for serveren (localhost er bare et label, for den lokale IP-adresse)
$servername = "localhost";

// Navnet på vores database
$dbname = "esp_data";

// Brugernavnet for adgang til databasen
$username = "user";

// Adgangskode for adgang til databasen
$password = "covid19";


// Opretter en forbindelse til databasen med de givne oplysninger: servername, username, password og databasenavn.
$conn = new mysqli($servername, $username, $password, $dbname);

// Tjekker forbindelsen
if ($conn->connect_error) { 
    echo("Der kunne ikke forbindes til databasen!");
} 

//Her indgår der MySQLi sprog, som fortæller: Hent data fra kolonnerne "placering", "temp" og "fugt" fra tabellen "SensorData", hvor den kun skal tag data fra rækken, hvis kolonne-værdien er "Indenfor". Derudover sortere den dataen efter den nyeste data (dvs. "seneste_daata).
//DESC betyder at sortering sker fra bund til top (normal er det fra top til bund).
//LIMIT fortæller, at den kun skal loade x-antal rækker (vores tilfælde én, da vi bare skal den seneste).

//Her indgår der MySQLi sprog med opsat kriterier.
$sql = "SELECT placering, temp, fugt FROM SensorData WHERE placering = 'Udenfor' ORDER BY seneste_data DESC LIMIT 1";


//Opretter en forspørgelse til databasen med kriterierne vi lavede ved "$sql", og outputter data'en til variablen "$result".
if ($result = $conn->query($sql)) {
    
    //Opret array med hentet data
    //Sæt variabler lig med værdierne i arrayet
    while ($row = $result->fetch_assoc()) {
        $placering_1 = $row["placering"]; //Værdi i kolonne ”placering”
        $temperatur_1 = $row["temp"]; //Værdi i kolonne ”temp”
        $fugtighed_1 = $row["fugt"]; //Værdi i kolonne ”fugt”
    }
    $result->free(); //Sletter den hentede data på $result.
}


$conn->close(); //Lukker forbindelsen til databasen.
echo "$temperatur_1,$fugtighed_1,$placering_1,"; //Sender dataen til html-filen, som bliver modtaget af javascript AJAX.

?> 
