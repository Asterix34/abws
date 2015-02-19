# AuthBoxWS REST

## Définition de l'API

Method | URL | Action
-------|-----|---------
GET | /api/v1/getCredentials | Crée et renvoi un couple identifiants, mot de passe valide pendant une durée prédéfinie.
GET | /api/v1/getCredentials/{seconds} | Comme ci-dessus, sauf qu'on définie la durée en paramètre.

## Librairies

# Sylex

Sylex est un microframework, il embarque les composants permettant de charger une application php interagissant avec les requetes et réponses HTTP, et de gérer des routes avec paramètres.