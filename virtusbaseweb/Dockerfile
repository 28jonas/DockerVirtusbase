FROM node:20-alpine AS builder

WORKDIR /app

# Eerst dependencies installeren
COPY ./virtusbaseweb/package*.json .
RUN npm install

# Dan de rest kopiëren
COPY . .

# Build de app
RUN npm run build

# Resultaat zit in /app/dist