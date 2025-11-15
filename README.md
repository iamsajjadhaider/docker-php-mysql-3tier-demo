💾 Project Description

This repository contains a simple, fully containerized 3-Tier Web Application (PHP/Apache web service and a MySQL database) orchestrated using Docker Compose.

The project demonstrates core Docker concepts including:

    Service Networking: Communication between the web and db containers.

    Data Persistence: Using a named volume (db_data) for MySQL data.

    Custom Images: Building the application layer using a custom Dockerfile.web.

🚀 Architecture

The application is composed of two main services defined in docker-compose.yml:

Component	        Technology	            Role
Web Tier	        PHP 8.2 & Apache	      Serves the frontend application and executes database queries.
Data Tier	        MySQL 8.0	              Stores code snippets persistently.

🏗️ Setup and Run Guide

  1. Prerequisites

    You must have Docker Engine and Docker Compose (V2) installed.

  2. File Structure

    Ensure the required files are present in the src/ directory:

    .
    ├── Dockerfile.db
    ├── Dockerfile.web
    ├── docker-compose.yml
    └── src/
        ├── docker-entrypoint.sh  # Ensures database readiness
        ├── index.php             # Main application code
        └── setup.sql             # Database schema initialization

  3. Build and Start the Stack

  Use the following commands to ensure a clean launch. The --build flag is necessary for the first run or after any changes to the Dockerfile.web.

      Stop and Delete Existing Volumes (Recommended for first time):
    
      docker compose down --volumes

      Build and Start Services:

      docker compose up --build -d

  4. Access the Application

      Once the containers are running, the application will be available at:

      http://localhost:8080

🛑 Cleanup

  To stop and remove containers and the network (without deleting the database data):

  docker compose stop

  To stop containers and remove ALL resources, including the database data volume:

  docker compose down --volumes
