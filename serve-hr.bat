@ECHO OFF
cd /d "%~dp0"
"C:\Users\abdal\.config\herd\bin\php84\php.exe" artisan serve --host=127.0.0.1 --port=8000
