Project Deployment Guide

This document provides simple step by step instructions for deploying and running this project. 


Step 1: Get the Code
Open your terminal or command prompt.
Run this command to download the code:
git clone URL_OF_THE_GIT_REPOSITORY
(Replace URL_OF_THE_GIT_REPOSITORY with the actual git link).

Next, go into the project folder by running:
cd NAME_OF_THE_PROJECT_FOLDER

Step 2: Set Up the Configuration
Take the .env file that was provided to you.
Place this .env file directly into the main folder of the project. This is the same folder where you see files like artisan and composer.json.

Step 3: Install Dependencies
Make sure your terminal is still inside the project folder.
Run this command to install required files:
composer install
This might take a few minutes.

Step 4: Run Artisan Commands
Run the following commands one by one in your terminal.

First, clear old caches:
php artisan optimize:clear

Next, set up the database:
php artisan migrate
If it asks for confirmation, type yes and press Enter.

Step 5: Start the Application
To start the project, run:
php artisan serve


