# Pikit

Système de gestion et de réservation de matériel basé sur WordPress.

## Stack

- WordPress
- Secure Custom Fields (SCF)
- Docker
- MySQL

## Installation

### 1. Cloner le repo

git clone https://github.com/tonrepo/pikit.git  
cd pikit

### 2. Lancer l'environnement

docker compose up -d

### 3. Accéder au site

http://localhost:8000

### 4. Connexion admin

URL: /wp-admin

## Structure

- WordPress
- JSON SCF versionné

## Objectif

Créer un système de réservation de matériel audiovisuel permettant :

- gestion du matériel
- gestion des réservations
- vérification de disponibilité
