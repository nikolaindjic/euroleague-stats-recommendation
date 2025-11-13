# Docker Build and Test Script

## Build the Docker image
echo "Building Docker image..."
docker build -t euroleague-stats:test .

## Run the container
echo "Starting container..."
docker run -d -p 8080:80 --name euroleague-test euroleague-stats:test

## Wait for startup
echo "Waiting for application to start..."
sleep 5

## Check logs
echo "Container logs:"
docker logs euroleague-test

## Test the application
echo ""
echo "Testing application at http://localhost:8080"
curl -I http://localhost:8080

## Cleanup instructions
echo ""
echo "To stop and remove the container:"
echo "docker stop euroleague-test && docker rm euroleague-test"

