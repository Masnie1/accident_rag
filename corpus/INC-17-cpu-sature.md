---
id: INC-17
titre: Processeur saturé sur un hôte
service: système
gravite: moyenne
alertes:
  - "High CPU utilization (over 90% for 5m)"
  - "Load average is too high"
  - "CPU iowait above 30%"
revision: 2026-09-28
---

## Symptômes
Tous les services de la machine ralentissent. Les temps de réponse augmentent et des erreurs 504 peuvent apparaître.

## Impact
Dégradation de tous les services hébergés sur l'hôte.

## Diagnostic
1. Processus les plus gourmands : `top -o %CPU` (ou `htop`)
2. Conteneur responsable : `docker stats --no-stream`
3. Comparer la charge au nombre de cœurs : `uptime` et `nproc`. Une charge élevée avec un CPU peu occupé signale une attente disque : regarder le champ `wa` de `top`, ou `iostat -x 2 3` (paquet `sysstat`).
4. Si le processus en tête est `mariadbd`, appliquer INC-13.

## Actions
1. Processus identifié et légitime (traitement ponctuel, indexation) : le laisser finir si l'impact est acceptable, sinon l'arrêter.
2. Conteneur emballé : `docker restart <conteneur>`, puis poser une limite dans le compose (`cpus: "2.0"`) pour protéger les autres services.
3. Processus inconnu, au nom aléatoire, avec des connexions sortantes inhabituelles : suspicion de compromission, par exemple un mineur de cryptomonnaie. Ne pas le tuer, ce qui effacerait des traces. Couper l'accès réseau de la machine et escalader immédiatement.

## Escalade
Escalader au N2 système. En cas de suspicion de compromission, escalader immédiatement au N2 sécurité.

## Vérification
L'utilisation du processeur reste sous 70 % pendant 15 minutes.
