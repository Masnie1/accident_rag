---
id: INC-14
titre: La base de données refuse les connexions (Too many connections)
service: base de données
gravite: haute
alertes:
  - "ERROR 1040 (HY000): Too many connections"
  - "Database connection pool exhausted"
  - "Unable to connect to the database"
revision: 2026-09-30
---

## Symptômes
L'application ne parvient plus à se connecter à la base. Les logs affichent « Too many connections ».

## Impact
Toutes les applications qui utilisent cette base sont en erreur.

## Diagnostic
1. Se connecter avec un compte administrateur : MariaDB garde une connexion en réserve pour les comptes qui ont le privilège `CONNECTION ADMIN` ou `SUPER`.
2. Comparer le nombre de connexions à la limite : `SHOW GLOBAL STATUS LIKE 'Threads_connected';` et `SHOW VARIABLES LIKE 'max_connections';`
3. Voir qui occupe les connexions : `SELECT user, host, command, COUNT(*) AS nb FROM information_schema.PROCESSLIST GROUP BY user, host, command ORDER BY nb DESC;`. Beaucoup de connexions en `Sleep` venant d'une même application signalent une fuite de connexions.

## Actions
1. Redémarrer l'application qui accumule les connexions : ses connexions sont libérées.
2. Mesure temporaire si besoin : `SET GLOBAL max_connections = 300;`. Ce réglage est perdu au redémarrage de MariaDB. Il ne doit être reporté dans la configuration qu'après validation du N2.
3. Ouvrir un ticket pour corriger la fuite côté application.

## Escalade
Escalader au N2 base de données, et au N2 applicatif pour la fuite de connexions.

## Vérification
`Threads_connected` reste stable et sous 70 % de `max_connections`, et l'application se connecte normalement.
