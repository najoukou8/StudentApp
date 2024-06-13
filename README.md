# StudentApp

#Instructions to deploy the project using docker
#prerequires 
- docker installed


### Steps
- clone the project
    ```bash
    git clone https://github.com/najoukou8/StudentApp.git
    cd StudentApp
    ```
-install the composer
    ```bash
    composer install 
    ```
-Build the compose
    ```bash
    docker-compose build
    ```
-compose up
    ```bash
    docker-compose up
    ```

-Access the Application
   - Open a web browser and navigate to `http://localhost`.

## Environment Variables

- Set any required environment variables in the `.env` file.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
