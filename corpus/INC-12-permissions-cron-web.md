---
id: INC-12
titre: Conflit de droits entre une tâche planifiée et le serveur web
service: traitements
gravite: moyenne
alertes:
  - "Failed to open stream: Permission denied"
  - "HTTP 500 after scheduled job"
  - "Unable to write to the cache directory"
revision: 2026-09-15
---

## Symptômes
Après le passage d'une tâche planifiée, l'application web renvoie des erreurs 500. Ses logs indiquent qu'elle ne peut plus écrire dans son dossier de cache, de sessions ou de logs.

## Cause habituelle
La tâche tourne dans la crontab de root. Les fichiers qu'elle crée (cache, logs) appartiennent alors à root, et le serveur web, qui tourne sous `www-data`, ne peut plus les modifier.

## Impact
L'application est en erreur pour tous les utilisateurs jusqu'à la correction des droits.

## Diagnostic
1. Repérer les fichiers appartenant à root dans le dossier inscriptible de l'application : `ls -l <dossier_inscriptible>`
2. Retrouver la tâche en cause : `sudo crontab -l` (root) et `sudo crontab -u www-data -l`

## Actions
1. Rétablir les droits : `sudo chown -R www-data:www-data <dossier_inscriptible>`
2. Déplacer la tâche dans la crontab de `www-data` (`sudo crontab -u www-data -e`) et la retirer de celle de root, sinon le problème reviendra à la prochaine exécution.
3. Si l'application tourne dans un conteneur : lancer la tâche avec le bon utilisateur, `docker exec -u www-data <conteneur> <commande>`.

## Escalade
Escalader au N2 applicatif si les erreurs persistent avec des droits corrects.

## Vérification
Après la prochaine exécution de la tâche, `ls -l` montre des fichiers appartenant à `www-data` et l'application ne renvoie plus d'erreur.
