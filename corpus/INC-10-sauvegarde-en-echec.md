---
id: INC-10
titre: Sauvegarde nocturne en échec
service: sauvegarde
gravite: moyenne
alertes:
  - "Backup job failed"
  - "rsync error: some files/attrs were not transferred (code 23)"
  - "Backup destination is not mounted"
revision: 2025-06-12
---

## Symptômes
Le rapport de sauvegarde du matin est absent ou en erreur. Le journal du script de sauvegarde contient une erreur rsync ou signale une destination introuvable.

## Impact
Aucun impact immédiat sur les services, mais plus de point de restauration récent en cas de panne.

## Diagnostic
1. Lire la fin du journal du script : `tail -50 /var/log/sauvegarde.log`
2. Vérifier que la destination est montée : `mountpoint <point_de_montage>`, puis son espace libre : `df -h <point_de_montage>`
3. Interpréter le code rsync : 23 = fichiers partiellement transférés, souvent un problème de droits ; 24 = fichiers disparus pendant la copie, généralement bénin ; 11 = erreur d'écriture, souvent un disque plein ; 30 ou 35 = délai dépassé, problème réseau.
4. Pour une base de données, la sauvegarde doit passer par un export (`mariadb-dump`), jamais par la copie des fichiers de la base pendant qu'elle tourne.

## Actions
1. Destination non montée : la remonter (`sudo mount <point_de_montage>`), puis relancer le script à la main.
2. Code 23 : corriger les droits sur les fichiers listés dans le journal, puis relancer.
3. Destination pleine : escalader. Ne jamais supprimer d'anciennes sauvegardes pour faire de la place sans validation.
4. Après chaque correction, relancer le script à la main et suivre le journal jusqu'au bout.

## Escalade
Escalader au N2 système après deux nuits consécutives en échec.

## Vérification
La dernière sauvegarde est datée du jour, et la restauration d'un fichier de test fonctionne.
