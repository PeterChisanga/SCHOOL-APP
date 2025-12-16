@echo off
cd /d C:\laragon\www\SCHOOL-APP-main
git checkout local
git add .
git commit -m "Update: School Management Application with all changes"
git push origin local
pause

