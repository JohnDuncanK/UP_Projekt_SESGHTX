<?php

// IP-adressen for serveren (localhost er bare en label, for den lokale IP-adresse, som altid er og kan være 127.0.0.1)
$servername = "localhost";

$servername = "localhost";

// Navnet på vores database
$dbname = "esp_data";

// Brugernavnet for adgang til databasen
$username = "user";

// Adgangskode for adgang til databasen
$password = "covid19";

//API-nøgle som skal matche samme ID, som EPS-modulet sender.
$api_key_value = "tPmAT5Ab3j7F9";

//Fastsætter nedstående variabler til tomme.
$api_key= $sensor = $placering = $temp = $fugt = "";

//Tjekker om request/anmodningens metoden er "Post"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = tjek_input($_POST["api_key"]); //Kalder funktionen "tjek_input" med den givende data. Dette er API-nøglen, som er sendt fra ESP-modulet.
    echo "Første if-statement";
    
    //Hvis variablen ESP-modulets API-nøgle  har samme værdi som API-nøglen, skal den kalde funktionen "tjek_input" for hver variabel med den givende data, der er sendt fra ESP-modulet.
    if($api_key == $api_key_value) {
        $sensor = tjek_input($_POST["sensor"]);
        $placering = tjek_input($_POST["placering"]); 
        $temp = tjek_input($_POST["temp"]); 
        $fugt = tjek_input($_POST["fugt"]); 
    echo "Anden if-statement";
        
        // Opretter en forbindelse til databasen med de givne oplysninger: servername, username, password og databasenavn.
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Tjekker forbindelsen
        if ($conn->connect_error) {
            //Hvis der ikke kan forbindes, anullerer den resten af processen og fejlmeddeler denne besked, hvor "$conn->connect_error" returnere, hvad fejlen er.
            die("Der kunne ikke forbindes til serveren. Fejlmeddelse: " . $conn->connect_error);
        } 
        
        //Her indgår der MySQLi-sprog, som fortæller: Indset data til tabellen "SensorData" i kolonnerne "sensor", "placering", "temp" og "fugt", med værdierne fra variablerne "sensor", "placering", "temp" og "fugt".
        $sql = "INSERT INTO SensorData (sensor, placering, temp, fugt)
        VALUES ('" . $sensor . "', '" . $placering . "', '" . $temp . "', '" . $fugt . "')";
        
        //Opretter en forspørgelse til databasen med dataen vi skal indsætte i databasen, og returnerer en true, hvis handling blev gennemført.
        if ($conn->query($sql) === TRUE) {
            echo "Handlingen lykkes. Data'en er nu i databasen";
        } 
        else {
            //Hvis forspørgselen og importerin af data'en ikke lykkes. 
            echo "Fejl, noget gik galt";
        }
    
        $conn->close(); //Lukker forbindelsen til databasen
    }
    else {
        //Hvis API-nøglen ikke stemmer over ens med
        echo "Forkert API-nøgle.";
    }

}
else {
    //Hvis der ikke modtaget noget data over HTTP post
    echo "Data fra ESP-modulet er ikke sendt.";
}

function tjek_input($data) {
    $data = trim($data); //Fjerner eventuelle mellemrum før og efter data'en.
    $data = stripslashes($data); //Fjerner backslash (\) fra dataen
    $data = htmlspecialchars($data); //Omskriver HTML operatører som: <>'"& til tekst. 
    return $data; //Retunerer data'en
}
