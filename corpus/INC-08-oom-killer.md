---
id: INC-08
titre: Conteneur tué par manque de mémoire (OOM killer)
service: système
gravite: haute
alertes:
  - "Out of memory: Killed process"
  - "OOMKilled: true"
  - "Container exited with code 137"
revision: 2026-09-28
---

## Symptômes
Un conteneur s'arrête ou redémarre sans erreur applicative dans ses logs. Son code de sortie est 137. Les journaux du noyau signalent un processus tué par manque de mémoire.

## Impact
Le service concerné s'interrompt, parfois au milieu d'une écriture. Si la mémoire de l'hôte est saturée, d'autres services peuvent être tués à leur tour.

## Diagnostic
1. Confirmer la cause : `docker inspect --format '{{.State.OOMKilled}} {{.State.ExitCode}}' <conteneur>`
2. Lire les journaux du noyau : `sudo dmesg -T | grep -iE "out of memory|killed process" | tail`
3. Mémoire de l'hôte et swap : `free -h` et `swapon --show`
4. Consommation par conteneur : `docker stats --no-stream --format "table {{.Name}}\t{{.MemUsage}}\t{{.MemPerc}}"`
5. Distinguer les deux cas : le conteneur a atteint sa propre limite mémoire (`mem_limit` dans le compose), ou c'est l'hôte entier qui est saturé.

## Actions
1. Limite du conteneur atteinte avec un usage normal : augmenter `mem_limit` dans le compose, puis `docker compose up -d <service>`.
2. Hôte saturé : identifier le plus gros consommateur avec `docker stats` et arrêter les services non critiques.
3. Mémoire qui grimpe sans jamais redescendre (fuite) : redémarrer le conteneur pour rétablir le service (`docker restart <conteneur>`) et ouvrir un ticket pour l'équipe applicative.

## Escalade
Escalader au N2 système si l'hôte sature de façon récurrente. Ne jamais désactiver l'OOM killer.

## Vérification
Le conteneur est `Up`, et 15 minutes après, `docker stats` le montre sous 80 % de sa limite.
