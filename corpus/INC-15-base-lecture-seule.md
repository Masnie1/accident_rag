---
id: INC-15
titre: La base de données est passée en lecture seule
service: base de données
gravite: critique
alertes:
  - "The MariaDB server is running with the --read-only option so it cannot execute this statement"
  - "Read-only file system"
  - "Database write failed"
revision: 2026-09-30
---

## Symptômes
Les lectures fonctionnent mais toutes les écritures échouent. L'application affiche des erreurs à chaque enregistrement.

## Impact
Plus aucune donnée ne peut être enregistrée. Si la cause est matérielle, les données sont en danger.

## Diagnostic
1. Lire les options de lecture seule : `SHOW GLOBAL VARIABLES LIKE '%read_only%';`
2. Vérifier si le système de fichiers a été remonté en lecture seule après des erreurs disque : `findmnt -no OPTIONS <point_de_montage_des_donnees>` (présence de `ro`), puis `sudo dmesg -T | grep -iE "i/o error|remount|read-only"`
3. Un disque plein bloque aussi les écritures avec des erreurs proches : vérifier `df -h` et, si besoin, appliquer INC-01.

## Actions
1. `read_only` à `ON` posé volontairement (maintenance, serveur réplica) : vérifier auprès du N2 avant toute modification. S'il a été posé par erreur : `SET GLOBAL read_only = OFF;`
2. Système de fichiers en lecture seule après des erreurs disque : ne rien forcer, pas de remontage en écriture, risque de corruption. Arrêter l'application et escalader immédiatement.
3. `innodb_read_only` à `ON` : c'est une option de démarrage, non modifiable à chaud. Escalader.

## Escalade
Escalader immédiatement au N2 système si le disque est en cause, au N2 base de données dans les autres cas.

## Vérification
Une écriture de test réussit sur une table prévue à cet effet, et l'application enregistre de nouveau.
