---
id: INC-13
titre: Requête SQL lente qui bloque l'application
service: base de données
gravite: haute
alertes:
  - "Lock wait timeout exceeded; try restarting transaction"
  - "Slow query detected"
  - "Application response time above 10s"
revision: 2026-09-30
---

## Symptômes
Les pages de l'application mettent très longtemps à répondre ou finissent en erreur 504. Des écritures échouent avec « Lock wait timeout exceeded ».

## Impact
L'application est ralentie ou bloquée pour tous les utilisateurs.

## Diagnostic
1. Lister les requêtes en cours et leur durée : `SHOW FULL PROCESSLIST;`. Repérer les colonnes `Time` élevées et les états « Sending data », « Waiting for table metadata lock », « Locked ».
2. Repérer les transactions ouvertes depuis longtemps : `SELECT * FROM information_schema.INNODB_TRX\G`
3. Vérifier que le journal des requêtes lentes est actif : `SHOW VARIABLES LIKE 'slow_query_log%';`
4. Analyser le plan d'exécution de la requête en cause : `EXPLAIN <requete>;`. Un `type` à `ALL` sur une grosse table signale un parcours complet, souvent un index manquant.

## Actions
1. Une requête de lecture (rapport, export) bloque tout : l'arrêter avec `KILL QUERY <id>;`, qui stoppe la requête sans couper la connexion.
2. Ne jamais tuer une transaction d'écriture sans l'accord du N2 : son annulation peut durer longtemps et aggraver le blocage.
3. Noter dans le ticket la requête complète, sa durée et son plan d'exécution.
4. Le correctif durable (index, réécriture, pré-calcul dans une table dédiée) relève du N2.

## Escalade
Escalader au N2 base de données.

## Vérification
`SHOW FULL PROCESSLIST;` ne montre plus de requête de plus de 30 secondes, et l'application répond normalement.
