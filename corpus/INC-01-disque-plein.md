---
id: INC-01
titre: Disque plein sur le serveur
service: système
gravite: haute
alertes:
  - "Free disk space is less than 10% on volume /"
  - "No space left on device"
  - "Disk space is critically low"
revision: 2026-09-28
---

## Symptômes
Les conteneurs plantent au démarrage ou n'écrivent plus leurs logs. Les mises à jour et les sauvegardes échouent avec « No space left on device ». Une base de données peut refuser les écritures.

## Impact
Tous les services hébergés sur la machine peuvent tomber en même temps.

## Diagnostic
1. Vérifier l'occupation des volumes : `df -h`
2. Trouver le dossier qui prend la place : `sudo du -xh / --max-depth=1 2>/dev/null | sort -h`
3. Mesurer la part de Docker (images, conteneurs, volumes, cache) : `docker system df`
4. Repérer un fichier de log de conteneur anormalement gros : `sudo sh -c 'du -h /var/lib/docker/containers/*/*-json.log' | sort -h | tail -5`

## Actions
1. Supprimer les images qui ne sont utilisées par aucun conteneur : `docker image prune -a`
2. Supprimer le cache de build : `docker builder prune`
3. Réduire les journaux système : `sudo journalctl --vacuum-size=200M`
4. Si un log de conteneur est énorme, le vider sans arrêter le conteneur : `sudo truncate -s 0 $(docker inspect --format='{{.LogPath}}' <conteneur>)`
5. Puis limiter la taille des logs dans le compose du service (`logging.options.max-size: "10m"` et `max-file: "3"`) et recréer le conteneur : `docker compose up -d <service>`

## Escalade
Ne jamais lancer `docker volume prune` ni supprimer de fichiers dans les volumes de données : ce sont les données des services. Si l'espace libéré ne suffit pas, arrêter les services non critiques et escalader au N2 système.

## Vérification
`df -h` affiche moins de 80 % d'occupation et tous les conteneurs sont `Up` dans `docker ps`.
