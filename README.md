# Event Booking API

This is a simple event booking API built with Symfony 7.2 and API Platform. 
The project is designed to demonstrate the use of Symfony and its components for building a REST API. 
The API allows users to create, read, update, and delete events, as well as manage bookings for those events.

The project use Docker for containerization and deployment, making it easy to set up and run the application in different environments.

## Software Stack
- Symfony 7.2
- PHP 8.3
- API Platform
- Doctrine ORM
- PostgreSQL
- Docker
- Caddy
- Mercure
- PHPUnit
- Swagger/OpenAPI
- EasyAdminBundle

## Installation

1. If not already done, install docker desktop or compose (https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start
4. Run the following command to install the dependencies:
   ```bash
   docker compose exec php composer install
   ```
4. Setup the database by running the following command:
   ```bash
   docker compose exec php bin/console doctrine:database:create --if-not-exists
   docker compose exec php bin/console doctrine:migrations:migrate
   ```
5. Run the database fixtures to populate the database with sample data:
   ```bash
   docker compose exec php bin/console doctrine:fixtures:load
   ```
7. Open `https://localhost` in your favorite web browser and accept the auto-generated TLS certificate
5. If you have any issues with the TLS certificate, you can disable HTTPS by setting the following environment variables:
   ```bash
   SERVER_NAME=http://localhost \
   MERCURE_PUBLIC_URL=http://localhost/.well-known/mercure \
   docker compose up --pull always -d --wait
   ```
   Ensure your application is accessible over HTTP by visiting `http://localhost` in your web browser.
6. To view the API documentation, navigate to `https://localhost/api` in your web browser.
6. Run `docker compose down --remove-orphans` to stop the Docker containers.

- EasyAdmin Dashboard: https://localhost
- API Documentation: https://localhost/api

## Database Access
I have used PostgreSQL as the database for this project. The database connection details are stored in the `.env` file.

To access the database from host machine, please use the following credentials:
- Host: `localhost`
- Port: `5432`
- Database: `app`
- user: `app`
- password: `!ChangeMe!`

## API Design
I followed API Platform's best practices and conventions for designing the API. I used built in Doctrine ORM providers and processors for rapid development.

The API is designed to be RESTful, with resources representing entities in the system. The API supports standard HTTP methods (GET, POST, PUT, DELETE) for interacting with resources.

Also, the API supports multiple formats, including JSON and JSON-LD. The system can be easily extended to support other formats as needed.

The validation of the API is done using Symfony's built-in validation component. I have added validation rules for each entity to ensure that the data is valid before it is persisted to the database.

The error handling is done using API Platform error handling component. I have added custom error messages for some validation rule to provide clear feedback to the user.

Database Design
The database design is based on the requirements of the API. I have created the following entities:
- `Event`: Represents an event that can be booked. It has a name, description, event_date, and a location/country.
- `Attendee`: Represents a user who can book an event. It has a name and email and date of birth.
- `EventBooking`: Represents a booking made by an attendee for an event. It has a status (confirmed, pending, canceled) and a date of booking.
- `AdminUser`: Represents an admin user who can manage events and bookings. 

### Relationships
- `Event` → `EventBookings` (1:N): An event can have multiple bookings.
- `Attendee` → `EventBookings` (1:N): An attendee can book multiple events, but not the same event twice.
- Constraints:
    - Unique constraint on `EventBooking` (`event_id`, `attendee_id`) to prevent duplicate bookings.
    - Check constraint on `EventBooking` to ensure capacity limits are not exceeded.
  
I added doctrine UniqueConstraint attribute to the `EventBooking` entity to enforce this constraint at the database level. Also added Symfony validation
constraints UniqueEntity to the `EventBooking` entity to ensure to prevent duplicate booking.

Additionally added custom validation constraint to check the capacity of the event is not exceeded when creating a booking.

### Authentication & Authorization
I have created an Admin User entity to manage certain events resource operations. I commented out the code to demonstrate my approach.

The other option is to use the JWT authentication bundle. I have not implemented it in this project, but I can provide you with a working example if needed.
API Platform supports JWT authentication out of the box, and you can easily integrate it into the project.

Currently, all the API endpoints are **publicly** accessible.

## Running Tests

I have written some tests to ensure the functionality of the API. I used integration tests to verify the behavior of the API endpoints.
The tests are located in the `tests/` directory. The test suite includes tests for the following features:
- Event creation, retrieval, updating, and deletion
- Attendee creation, retrieval, updating
- Booking creation, retrieval, updating, and deletion

To run the tests, use the following command:
```bash
docker compose exec php bin/phpunit
```

## Bonus Features

I have added some bonus features to the API to enhance its functionality and usability

- API Pagination: The API supports pagination for endpoints that return lists of resources. You can use the `page` query parameters to control the pagination. 
- API Filtering: The API supports filtering of resources based on various criteria. You can use query parameters to filter the `Events` results. I included filter by name and country.
- Docker Support
- Swagger/OpenAPI Documentation - The API Platform automatically generates OpenAPI documentation for the API based on ApiResource attribute definition in the entity.
- Admin CRUD - I added simple Dashboard to manage events and bookings. This is built in using EasyAdminBundle where CRUD operations are generated based on the entity.


## Known Issues
When you run fixtures load you may get an error 
```bash
An exception occurred while executing a query: SQLSTATE[23505]: Unique violation: 7 ERROR:  duplicate key value violates unique constraint "uniq_event_bookings"  
  DETAIL:  Key (event_id, attendee_id)=(288, 86) already exists.
```
This is due to the fact that the fixtures are trying to create duplicate bookings for the same event and attendee.
To fix this, run the command again.
```bash
docker compose exec php bin/console doctrine:fixtures:load
```
