# Basis image met Node.js
FROM node:18-alpine

# Werkdirectory instellen
WORKDIR /app

# Eerst dependencies installeren (cache optimalisatie)
COPY package*.json ./
RUN npm install

# Rest van de app kopiëren
COPY . .

# Ionic CLI globaal installeren (optioneel)
#RUN npm install -g @ionic/cli

# Poort vrijgeven (Ionic gebruikt standaard 8100)
EXPOSE 8100

# Commando om de app te starten
CMD ["ionic", "serve", "--external", "--host=0.0.0.0", "--port=8100", "--no-open"]