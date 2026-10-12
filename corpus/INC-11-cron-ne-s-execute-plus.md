---
id: INC-11
titre: Une tâche planifiée CRON ne s'exécute plus
service: traitements
gravite: moyenne
alertes:
  - "Scheduled job did not run"
  - "No report received since 24h"
  - "cron job last execution older than expected"
revision: 2026-09-15
---

## Symptômes
Un rapport automatique n'est plus reçu, ou un traitement planifié ne laisse aucune trace à l'heure prévue.

## Impact
Les utilisateurs ne reçoivent plus leurs rapports, ou des données ne sont plus mises à jour.

## Diagnostic
1. Le service cron tourne-t-il ? `systemctl status cron`
2. La tâche a-t-elle été lancée ? `grep CRON /var/log/syslog | tail -20`, ou `journalctl -u cron --since today`
3. Est-elle dans la bonne crontab, celle du bon utilisateur ? `sudo crontab -u <utilisateur> -l`
4. Lancer la commande à la main avec l'environnement minimal de cron, qui n'a pas le même PATH qu'une session : `sudo -u <utilisateur> env -i PATH=/usr/bin:/bin sh -c '<commande>'`
5. Pièges fréquents : `%` non échappé dans la crontab (il faut écrire `\%`), chemin relatif, binaire absent du PATH (écrire `/usr/bin/php` plutôt que `php`).

## Actions
1. Service arrêté : `sudo systemctl restart cron`
2. Commande qui échoue avec l'environnement de cron : utiliser des chemins absolus partout, échapper les `%`.
3. Ajouter une redirection pour garder la trace des prochaines exécutions : `>> /var/log/<tache>.log 2>&1` en fin de ligne.
4. Si l'erreur est « Permission denied » : appliquer INC-12.
5. Relancer le traitement manqué à la main pour rattraper la période.

## Escalade
Escalader au N2 applicatif si la commande échoue aussi en lancement manuel.

## Vérification
Une ligne CRON apparaît dans le syslog à l'heure prévue, et le rapport est reçu.
