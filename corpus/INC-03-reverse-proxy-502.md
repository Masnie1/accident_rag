---
id: INC-03
titre: Le reverse proxy renvoie une erreur 502 ou 504
service: web
gravite: haute
alertes:
  - "502 Bad Gateway"
  - "504 Gateway Timeout"
  - "Web scenario failed: HTTP 502"
revision: 2026-10-02
---

## Symptômes
Le navigateur affiche une page « 502 Bad Gateway » ou « 504 Gateway Timeout » servie par le reverse proxy. Les autres services derrière le même proxy fonctionnent. Si tous les services sont touchés depuis l'extérieur, voir plutôt INC-07.

## Impact
Le service concerné est inaccessible pour les utilisateurs, même s'il tourne peut-être correctement.

## Diagnostic
1. Le service cible tourne-t-il ? `docker ps --filter "name=<service>"`. S'il est en état `Restarting`, appliquer INC-02.
2. Le proxy joint-il le service depuis son propre réseau ? `docker exec <proxy> curl -sS -m 5 http://<service>:<port_interne>` (ou `wget -qO- -T 5` si curl est absent de l'image).
3. Les deux conteneurs sont-ils sur le même réseau Docker ? `docker network inspect <reseau_proxy>`
4. Lire les logs du proxy : `docker logs --tail 50 <proxy>`. « connection refused » signifie service arrêté ou mauvais port. « timeout » signifie service trop lent.

## Actions
1. Service arrêté : `docker compose up -d <service>`
2. Conteneurs sur des réseaux différents : `docker network connect <reseau_proxy> <service>` pour rétablir tout de suite, puis ajouter le réseau dans le compose pour que la correction survive au prochain redémarrage.
3. Mauvais port : corriger la cible dans la configuration du proxy. C'est le port interne du conteneur, pas le port publié sur l'hôte.
4. 504 avec un service lent : voir INC-17 (CPU saturé) ou INC-13 (requête SQL lente).

## Escalade
Si le proxy renvoie des erreurs pour tous les services, escalader au N2 réseau.

## Vérification
`curl -sS -o /dev/null -w "%{http_code}\n" https://<domaine>` renvoie 200 ou une redirection 30x.
