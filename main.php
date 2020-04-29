<?php

/*

Rui Santos
Complete project details at https://RandomNerdTutorials.com/esp32-esp8266-mysql-database-php/

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files.

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

*/


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
    //Hvis der ikke kan forbindes, kommer denne besked frem.
    echo("Der kunne ikke forbindes til serveren!");
} 

//Her indgår der MySQLi sprog, som fortæller: Hent data fra kolonnerne "placering", "temp" og "fugt" fra tabellen "SensorData", hvor den kun skal tag data fra rækken, hvis kolonne-værdien er "Indenfor". Derudover sortere den dataen efter den nyeste data (dvs. "seneste_daata).
//DESC betyder at sortering sker fra bund til top (normal er det fra top til bund).
//LIMIT fortæller, at den kun skal loade x-antal rækker (vores tilfælde én, da vi bare skal den seneste).
$sql = "SELECT placering, temp, fugt FROM SensorData WHERE placering = 'Udenfor' ORDER BY seneste_data DESC LIMIT 1";


//Opretter en forspørgelse til databasen med kriterierne vi lavede ved "$sql", og outputter rækkerne til variablen "$result".
if ($result = $conn->query($sql)) {
    //Tjekker hvis der er flere end 0 rækker med data, så vil funktionen "fetch_assoc()" skabe et array med al dataen, som vi looper igennem med while'en. While'en køre igennem arrayet med dataen, hvor der inde i while'en er variabler det blive sat lige med værdierne i arrayet.
    while ($row = $result->fetch_assoc()) {
        $placering_1 = $row["placering"]; //Sætter variabel lig med værdien i kolonnnen "placering" i den seneste række med data. 
        $temperatur_1 = $row["temp"]; //Sætter variabel lig med værdien i kolonnnen "temp" i den seneste række med data.
        $fugtighed_1 = $row["fugt"]; //Sætter variabel lig med værdien i kolonnnen "fugt" i den seneste række med data.
    }
    $result->free(); //Sletter den gemte data, som variablen $result har.
}


$conn->close(); //Lukker forbindelsen til databasen.
echo "$temperatur_1,$fugtighed_1,$placering_1,"; //Sender dataen til html-filen, som bliver modtaget af javascript AJAX.

?> 
