# PHP WebSocket Application with AWS Lambda and DynamoDB

This project implements a serverless WebSocket application using PHP, AWS Lambda, and DynamoDB. It provides real-time communication capabilities through WebSocket connections managed by API Gateway.

The application demonstrates how to handle WebSocket connections, messages, and disconnections using PHP in a serverless environment. It leverages the Bref framework to simplify PHP deployment on AWS Lambda and uses DynamoDB to store active WebSocket connections.

## Repository Structure

- `src/`: Contains the main application code
  - `index.php`: Entry point for the Lambda function
- `vendor/`: Dependencies installed via Composer
- `serverless.yml`: Serverless Framework configuration file
- `composer.json`: PHP dependency management file

Key integration points:
- AWS API Gateway: Manages WebSocket connections
- AWS DynamoDB: Stores WebSocket connection IDs
- AWS Lambda: Executes the PHP code for WebSocket events

## Usage Instructions

### Installation

Prerequisites:
- PHP 8.4 or later
- Composer
- AWS CLI configured with appropriate credentials
- Serverless Framework

Steps:
1. Clone the repository
2. Run `composer install` to install dependencies
3. Deploy the application using Serverless Framework:
   ```
   serverless deploy
   ```

### Configuration

The `serverless.yml` file contains the main configuration for the application. Key settings include:

- AWS region: `ap-northeast-1` (modify as needed)
- Lambda function timeout: 900 seconds
- DynamoDB table name: `WebSocketConnections`

### WebSocket Handling

The application handles three main WebSocket events:

1. Connection (`$connect`)
2. Disconnection (`$disconnect`)
3. Message reception (`message`)

To interact with the WebSocket API:

1. Obtain the WebSocket URL from the Serverless deployment output
2. Use a WebSocket client to connect to the provided URL
3. Send messages using the established WebSocket connection

### Data Flow

1. Client initiates a WebSocket connection
2. API Gateway routes the connection request to the Lambda function
3. Lambda function handles the connection event and stores the connection ID in DynamoDB
4. Client sends a message through the WebSocket
5. API Gateway routes the message to the Lambda function
6. Lambda function processes the message and can broadcast to other connected clients
7. When a client disconnects, the Lambda function removes the connection ID from DynamoDB

```
Client <-> API Gateway <-> Lambda <-> DynamoDB
```

## Infrastructure

The application uses the following AWS resources:

- Lambda:
  - `websocket`: Handles WebSocket events (connect, disconnect, message)
- DynamoDB:
  - `WebSocketConnectionsTable`: Stores active WebSocket connection IDs
- API Gateway:
  - WebSocket API (created automatically by Serverless Framework)

## Deployment

The application is deployed using the Serverless Framework. To deploy:

1. Ensure you have the Serverless Framework installed and configured
2. Run the following command in the project root:
   ```
   serverless deploy
   ```
3. Note the WebSocket URL provided in the deployment output

## Troubleshooting

Common issues and solutions:

1. Connection failures:
   - Check that the WebSocket URL is correct
   - Verify that the Lambda function has the necessary permissions to access DynamoDB

2. Messages not being received:
   - Ensure the `message` route is correctly configured in `serverless.yml`
   - Check Lambda logs for any error messages

3. High latency:
   - Consider adjusting the Lambda function's memory allocation
   - Optimize the PHP code for better performance

To enable verbose logging:
1. Set the `BREF_LOOP_MAX` environment variable in `serverless.yml`
2. Redeploy the application
3. Check CloudWatch logs for detailed execution information

For performance profiling:
1. Use AWS X-Ray to trace requests through the application
2. Monitor Lambda execution times and memory usage in CloudWatch