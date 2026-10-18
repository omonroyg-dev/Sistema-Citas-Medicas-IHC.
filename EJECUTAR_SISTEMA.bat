@echo off
title Sistema Web de Citas Medicas - Servidor Local
echo Iniciando el servidor local en la red Wi-Fi...
start "" http://localhost:8080/index.php
C:\xampp\php\php.exe -S 0.0.0.0:8080
pause