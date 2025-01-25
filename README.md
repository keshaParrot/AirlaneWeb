# Airline Ticket Sales Backend API

This project is a backend server developed for a university course project on **REST API programming**. It powers a web-based airline ticket sales platform with key functionalities such as ticket browsing, filtering, purchasing, account management, and administrative features.

## Features
- **Ticket Management**:
  - View all available tickets.
  - Filter tickets based on specific criteria (e.g., availability for sale).
  - Purchase tickets directly from the platform.

- **User Account Management**:
  - Create an account and log in securely.
  - Add funds to the website wallet using a placeholder implementation for bank card payments.
  - View purchased tickets.
  - Reset password
  - Return tickets (if eligible based on specific conditions).

- **Admin/Moderator Functionalities**:
  - Add new flights and tickets to the system.
  - Add new airports and airplanes

## Technical Details
- **Authentication & Authorization**:
  - Uses Firebase for generating and validating access tokens.
  - Ensures users can only access their own resources securely.

- **Database**:
  - Built with **MySQL/MariaDB** using **PDO** for database interactions.

- **Unit Testing**:
  - Comprehensive unit tests written using **PHPUnit** to ensure the reliability of the system.

## Limitations
- The payment integration is currently a placeholder and does not implement real card processing logic.

This project demonstrates the use of modern PHP practices and technologies for creating robust backend APIs while ensuring secure user interactions and a scalable design.
