#!/bin/bash

echo "📦 Build image..."
DOCKER_BUILDKIT=1 docker build -t monsters-image .

echo "🛑 Stop container si existant..."
docker stop monsters 2>/dev/null

echo "🗑️ Suppression container..."
docker rm monsters 2>/dev/null

echo "🚀 Lancement container..."
docker run -d \
  --network mariadb-network \
  -p 127.0.0.1:10000:80 \
  --name monsters \
  --restart unless-stopped \
  monsters-image:latest

echo "✅ Done ! monsters restarted."