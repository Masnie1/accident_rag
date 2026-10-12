---
id: INC-04
titre: Certificat TLS expiré ou renouvellement en échec
service: web
gravite: haute
alertes:
  - "SSL certificate expires in less than 7 days"
  - "NET::ERR_CERT_DATE_INVALID"
  - "certificate has expired"
revision: 2026-09-21
---

## Symptômes
Le navigateur affiche une alerte de sécurité sur le domaine. Les clients d'API échouent avec « certificate has expired ». La supervision signale une expiration proche.

## Impact
Les utilisateurs ne peuvent plus accéder au service sans contourner l'alerte. Les appels entre applications échouent.

## Diagnostic
1. Lire la date d'expiration et l'émetteur du certificat servi : `echo | openssl s_client -connect <domaine>:443 -servername <domaine> 2>/dev/null | openssl x509 -noout -dates -issuer`
2. Chercher l'erreur de renouvellement dans les logs du proxy : `docker logs <proxy> 2>&1 | grep -iE "acme|certificate|challenge" | tail -30`
3. Vérifier que le domaine pointe sur la bonne adresse : comparer `dig +short <domaine>` avec l'IP publique (`curl -s https://ifconfig.me`).
4. Causes fréquentes : port 80 injoignable depuis Internet (le challenge HTTP échoue), enregistrement DNS obsolète, limite de demandes Let's Encrypt atteinte (« rateLimited » dans les logs).

## Actions
1. Port 80 injoignable : rétablir l'accès en suivant INC-07, puis relancer le renouvellement depuis l'interface du proxy. Avec Caddy ou Traefik, un redémarrage relance la tentative : `docker restart <proxy>`
2. DNS obsolète : corriger l'enregistrement A chez le registrar, attendre la propagation, puis relancer le renouvellement.
3. Limite Let's Encrypt atteinte : ne pas relancer en boucle, chaque tentative prolonge le blocage. Attendre la fin de la fenêtre et escalader.

## Escalade
N2 réseau si le DNS ou le port 80 sont en cause. N2 système si la limite de demandes est atteinte.

## Vérification
La commande du diagnostic 1 affiche une date `notAfter` repoussée de plusieurs semaines, et le navigateur n'affiche plus d'alerte.
