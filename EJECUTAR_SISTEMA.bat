@echo off
title Sistema Medico Portable - Clinica MONROY
color 0B
cls

echo ====================================================================
echo             INICIANDO SISTEMA MEDICO PORTABLE
echo            Clinica de Especialidades MONROY
echo ====================================================================
echo.

:: 1. Buscar PHP en XAMPP o en la variable de entorno
SET "PHP_BIN=C:\xampp\php\php.exe"

IF NOT EXIST "%PHP_BIN%" (
    WHERE php >nul 2>nul
    IF %ERRORLEVEL% EQU 0 (
        SET "PHP_BIN=php"
    ) ELSE (
        echo [ERROR] No se encontro PHP en C:\xampp\php\php.exe ni en el sistema.
        echo Asegurate de tener instalado XAMPP o PHP.
        echo.
        pause
        exit
    )
)

echo [1/2] Levantando servidor PHP en http://localhost:8000 ...
start "Servidor PHP" /min "%PHP_BIN%" -S localhost:8000

echo [2/2] Abriendo el sistema en tu navegador web...
timeout /t 2 >nul
start http://localhost:8000/index.php

echo.
echo ====================================================================
echo  El servidor esta funcionando en segundo plano.
echo  NO cierres esta ventana mientras utilices el sistema.
echo ====================================================================
echo.
pause