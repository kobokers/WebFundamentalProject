# WebFundamentalProject
SEM1 2025/26 web fundamentals project (LMS)

•	Create an Online Learning Management System (LMS) that allows users to explore courses, enroll in classes, track progress, and engage with instructors. The website should include features like a homepage, course catalog, enrollment system, progress tracker, and a discussion forum for interaction.

# If you know how to use GIT please use GIT (below are for beginner setup)

# Local Setup Guide (GitHub Desktop + XAMPP)

This guide explains how to download the project using GitHub Desktop and run it locally using XAMPP.

## 1. Requirements
- GitHub Desktop https://desktop.github.com/download/
- XAMPP https://www.apachefriends.org/download.html

## 2. Install both App
- After installing please check where xampp is install usually at C:\xampp

## 3. Clone the Repository Using GitHub Desktop
1. Open GitHub Desktop
2. Click "File" → "Clone Repository…"
3. Select the "URL" tab
4. Paste the repository link
5. Set the local path to: C:\xampp\htdocs\ -> your installation directory
6. Click "Clone"

https://github.com/kobokers/WebFundamentalProject.git

<img width="961" height="910" alt="image" src="https://github.com/user-attachments/assets/50cddf0d-189a-471a-84e5-28efc2f390ff" />


## 3. Start XAMPP Services
1. Open XAMPP Control Panel
2. Start Apache
3. Start MySQL
4. Go phpmyadmin database and import database (make sure create olms database first !!!)

# Step 1 create database name to olms make sure no capital letter
<img width="802" height="142" alt="image" src="https://github.com/user-attachments/assets/3b774c40-5e12-4e98-99b8-1768518dc3bc" />

# Step 2 go to import tab (make sure click on olms database first) then click on choose a file, olms.sql can be found on htdocs folder)
<img width="1425" height="736" alt="image" src="https://github.com/user-attachments/assets/87cd17a8-f783-4fb2-8868-98b788765af8" />

# Step 3 
<img width="955" height="506" alt="image" src="https://github.com/user-attachments/assets/31aaa1db-b9bc-435d-9d3a-c98b7bc462f0" />

## 4. Run the Website Locally
1. Ensure the project folder is inside: C:\xampp\htdocs\ -> your installation directory
2. Open your browser
3. Enter the URL: http://localhost/WebFundamentalProject/index.php

## 5. Pull Latest Updates
1. Open GitHub Desktop
2. Select the project
3. Click "Fetch origin"
4. Click "Pull origin"

## 6. Push Your Changes (Optional)
1. Edit the project files
2. GitHub Desktop will show the file changes
3. Enter a commit message
4. Click "Commit to main"
5. Click "Push main"

