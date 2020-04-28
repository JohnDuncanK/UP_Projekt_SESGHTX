/*
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
|||||||||||          ESP8266 UDENDØRS SENSOR-PROGRAM          |||||||||||
|||||||||||           WiFi / HTTP / FLASH / BME280            |||||||||||
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
*/


/////////////////////////////////////////////////////////////////////////
///////////       DEFINITIONER & HENTNING AF BIBLIOTEKER      ///////////
/////////////////////////////////////////////////////////////////////////

#include <ESP8266WiFi.h>
//Bibliotek til håndtering af WiFi på ESP8266

#include <WiFiClient.h>
//Bibliotek til håndtering af WiFi på ESP8266 som en Client
//(enhed på et netværk, i modsætning til en router eller access point f.eks.)

IPAddress ip(192, 168, x, x);
IPAddress dns(8, 8, 8, 8);
IPAddress gateway(192, 168, x, x);
//Funktion til definition af netværksinformationer på det lokale netværk (LAN)

const char* ssid     = "wifiname"; //wifi-navn
const char* password = "verydifficultpassword"; //wifi-kode
//Login-oplysninger til WiFi

#include <ESP8266HTTPClient.h>
//Bibliotek til håndtering af datakommunikationsprotokollen HTTP på ESP8266

const char* serverNavn = "http://192.168.x.x//post-esp-data.php";
//IP-adressen og scriptet (rettere sagt linket), som EPS8266 skal sende data til

String apiKeyValue = "tPmAT5Ab3j7F9";
//API-KEY er den nøgle som giver adgang til kommunikation. Nøglen skal være den samme på ESP og Raspberry Pi for at data kan transmitteres

String sensorNavn = "BME280";
String sensorPlacering = "Udenfor";
//Navnet på den sensor hvis info skal sendes til serveren (Raspberry Pi)

/////////////////////////////////////////////////////////////////////////

#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
//Biblioteker til håndtering af datakommunikationsprotokoller mellem ESP8266 og BME280 sensor

#include <SPI.h>
//Bibliotek til software-versionen af SPI (Serial Peripheral Interace)
//(Giver muligheden for at bruge hvilke som helst porte til clock, MISO & MOSI osv.)

/////////////////////////////////////////////////////////////////////////

#define D8 15
#define D5 14
#define D7 13
#define D6 12
//Definitioner af pins på boardet i forhold til Arduino IDE
//(På ESP-boardet har hver pin sin label, men i Arduino IDE hedder de andet)

#define BME_SCK D5
#define BME_MISO D8
#define BME_MOSI D6
#define BME_CS D7
//Definition af pins til SPI (Serial Peripheral Interface) igennem Software

Adafruit_BME280 bme(BME_CS, BME_MOSI, BME_MISO, BME_SCK); 
//Software SPI-opsætning

#define SEALEVELPRESSURE_HPA (1013.25)
//Definition af Havoverfladetrykket


/////////////////////////////////////////////////////////////////////////
///////////                 PROGRAMOPSÆTNING                  ///////////
/////////////////////////////////////////////////////////////////////////

void setup() {
  Serial.begin(115200);
  //Starter en seriel kommunikation, med betingelsen at den binære signalværdi kan skiftes 9600g/sek. (g for gange)

  Serial.println("Opstarter");
  //Printer statusmeddelelse

  bool status = bme.begin(0x76);
  if (!status) {
    Serial.println("Kunne ikke finde en BME280 sensor, check opkoblingen og SPI-definitionerne!");
    while (1);
  } 
  //Sørger for, at BME280-sensoren er forbundet korret!

  Serial.println("Begynder netværksopsætning");
  netvaerksopsaetning();
  Serial.println("Done!");
  //Opsætter netværksforbindelse mm.
    
  Serial.println("Begynder HTTP-forbindelsen");
  http();
  //Opsætter HTTP-datakommunikationsprotokollen mm.  

  Serial.println("begynder deep sleep");
  ESP.deepSleep(1200000000);

}

///////////////////////////////////

void loop() {}

///////////////////////////////////

void netvaerksopsaetning() {
  
  WiFi.setPhyMode(WIFI_PHY_MODE_11N);

  WiFi.config(ip, dns, gateway);
  //Indlæser de før-opskrevne indstillinger til WiFi-forbindelsen
  
  WiFi.begin(ssid, password);
  //Kalder WiFi-library med login-oplysningerne, hvormed den forbinder til WiFi
  
  //WiFi.begin(ssid);
  //Kalder WiFi-library med ssid (WiFi-navnet) i betragtning
  
  while(WiFi.status() != WL_CONNECTED) {
    Serial.print(".");
    delay(500);
  }
  //Hvis nettet ikke er forbundet, skal den vente med at fortsøtte forbindelsen er fuldført

  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());
  //Printer statusbesked og IP-adressen til det forbundene WiFi
    
  if(WiFi.status()== WL_CONNECTED){
    Serial.print("Physical mode:");
    Serial.println(WiFi.getPhyMode());
  } else {
      Serial.println("WiFi er ikke forbundet");
    }
  //Tjekker forbindelsen til WiFi
}

/////////////////////////////////////////////////////////////////////////

void http() {
    HTTPClient http;
    //Initialiserer biblioteket og de nødvendige ressurcer
  
    http.begin(serverNavn);
    //Begynder HTTP-protokollen med fokus på at forbinde til Serveren
    //(serverNavn er en string, der er defineret tidligt i denne kode)
    
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    //Specify content-type header
    
    String httpRequestData = "api_key=" + apiKeyValue + "&sensor=" + sensorNavn
      + "&placering=" + sensorPlacering + "&temp=" + String(bme.readTemperature())
      + "&fugt=" + String(bme.readHumidity()) /*+ "&tryk=" + String(bme.readPressure()) */+ "";    
    //En streng af tekst, der bliver sendt til http-serveren (el. php-scriptet),
    //der indfører det ind i MySQL-databasen
        
    Serial.print("httpRequestData: ");
    Serial.println(httpRequestData);
    //Printer strengen ud, med de forskellige variable indsat
    
    int httpResponsKode = http.POST(httpRequestData);
    // Send HTTP POST request
         
    if (httpResponsKode>0) {
      Serial.print("HTTP Respons Kode: ");
      Serial.println(httpResponsKode);
    } else {
      Serial.print("Fejlkode: ");
      Serial.println(httpResponsKode);
    }
    //Printer responskoden fra den tidligere request
    //Et eksempel på en responskode er 404: Page Not Found
    
    http.end();
    // Frigør resourcerne forbundet med http-protokollen
}

/////////////////////////////////////////////////////////////////////////
