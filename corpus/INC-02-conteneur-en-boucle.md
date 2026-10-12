---
id: INC-02
titre: Conteneur qui redémarre en boucle
service: docker
gravite: haute
alertes:
  - "Container is restarting"
  - "Restarting (1) 10 seconds ago"
  - "Container exited with code 1"
revision: 2026-10-02
---

## Symptômes
`docker ps` affiche le conteneur en état `Restarting`. Le service est disponible par intermittence, ou pas du tout. Derrière le reverse proxy, les utilisateurs voient une erreur 502.

## Impact
Le service concerné est indisponible. Les redémarrages répétés remplissent les logs et peuvent saturer le disque.

## Diagnostic
1. Voir l'état et le code de sortie : `docker ps -a --filter "name=<conteneur>"`
2. Lire la dernière erreur avant l'arrêt : `docker logs --tail 100 <conteneur>`
3. Vérifier si le noyau l'a tué faute de mémoire : `docker inspect --format '{{.State.ExitCode}} {{.State.OOMKilled}}' <conteneur>`. Un code 137 avec `true` renvoie à INC-08.
4. Causes fréquentes : variable d'environnement manquante, volume non monté ou droits incorrects, base de données pas encore prête, port déjà utilisé.

## Actions
1. Code 137 et `OOMKilled` à `true` : appliquer INC-08.
2. L'erreur cite la connexion à la base : vérifier que le conteneur de la base est `Up` (`docker compose ps`), le relancer en premier, puis relancer le service.
3. L'erreur cite un fichier ou un droit : vérifier les montages avec `docker inspect --format '{{json .Mounts}}' <conteneur>`.
4. L'incident suit une mise à jour de l'image : appliquer INC-09.
5. Après correction, relancer avec `docker compose up -d <service>` et suivre les logs pendant deux minutes : `docker logs -f <conteneur>`

## Escalade
Si la cause n'est pas trouvée en 15 minutes, arrêter le conteneur (`docker compose stop <service>`) pour stopper la boucle, puis escalader au N2 système en joignant les 100 dernières lignes de logs.

## Vérification
Le conteneur est `Up` depuis plus de 5 minutes et son compteur de redémarrages ne bouge plus : `docker inspect --format '{{.RestartCount}}' <conteneur>`.
