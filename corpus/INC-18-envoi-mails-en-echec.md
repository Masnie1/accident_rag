---
id: INC-18
titre: Les mails automatiques ne partent plus
service: traitements
gravite: moyenne
alertes:
  - "SMTP Error: Could not authenticate."
  - "535 5.7.8 Authentication failed"
  - "SMTP connect() failed: connection timed out"
revision: 2026-09-15
---

## Symptômes
Les rapports et notifications envoyés automatiquement par l'application ne sont plus reçus. Les logs de l'application montrent une erreur SMTP au moment de l'envoi.

## Impact
Les destinataires ne reçoivent plus leurs rapports. Le traitement lui-même a pu s'exécuter normalement.

## Diagnostic
1. Lire les logs de l'application à l'heure de l'envoi pour obtenir l'erreur SMTP exacte.
2. Vérifier que le serveur SMTP est joignable : `nc -vz <serveur_smtp> 587`
3. Tester la connexion chiffrée à la main : `openssl s_client -starttls smtp -connect <serveur_smtp>:587 -crlf`, puis taper `EHLO test`.
4. Erreur d'authentification : mot de passe changé ou expiré, mot de passe d'application révoqué, compte bloqué.
5. Mails envoyés mais jamais reçus : vérifier les dossiers de spam et les enregistrements SPF du domaine expéditeur, `dig +short TXT <domaine>`.

## Actions
1. Identifiant invalide : mettre à jour le mot de passe dans la configuration de l'application (variable d'environnement, jamais en dur dans le code), puis redémarrer l'application.
2. Port injoignable : vérifier le pare-feu et l'hébergeur, certains bloquent les ports SMTP sortants. Utiliser le port 587 plutôt que le 25.
3. Une fois corrigé, renvoyer à la main les rapports manqués.

## Escalade
Escalader au N2 applicatif.

## Vérification
Un mail de test envoyé par l'application est reçu par le destinataire.
