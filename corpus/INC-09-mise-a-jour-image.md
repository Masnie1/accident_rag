---
id: INC-09
titre: Une mise à jour d'image casse le service, retour arrière
service: docker
gravite: haute
alertes:
  - "Service unhealthy after image update"
  - "Database migration failed"
  - "Container is unhealthy"
revision: 2026-10-01
---

## Symptômes
Juste après une mise à jour (`docker compose pull` puis `up -d`, ou mise à jour automatique), le service ne démarre plus, affiche des erreurs, ou son interface ne fonctionne plus.

## Impact
Le service est indisponible. Si la nouvelle version a migré la base de données, un simple retour à l'ancienne image peut ne pas suffire.

## Diagnostic
1. Lire les erreurs apparues depuis le redémarrage : `docker compose logs --tail 100 <service>`
2. Identifier la version en cours : `docker inspect --format '{{.Config.Image}}' <conteneur>`
3. Retrouver la version précédente encore présente sur la machine : `docker image ls <image>`
4. Lire les notes de version de l'image : changements incompatibles, migration manuelle à faire.

## Actions
1. Avant tout retour arrière, sauvegarder le volume de données, car une migration a pu modifier son contenu : `docker run --rm -v <volume>:/data -v $(pwd):/backup alpine tar czf /backup/<volume>-$(date +%F).tar.gz -C /data .`
2. Fixer la version précédente dans le compose (`image: <image>:<ancienne_version>`), jamais `latest`.
3. Relancer : `docker compose up -d <service>`
4. Si l'ancienne version refuse de démarrer parce que la base a déjà été migrée : restaurer la sauvegarde d'avant la mise à jour (voir INC-10) et escalader.

## Escalade
Escalader au N2 système dès qu'une restauration de données est nécessaire.

## Vérification
Le service fonctionne sur l'ancienne version, et la version fixée est enregistrée dans le dépôt du compose.
