---
id: INC-07
titre: Service accessible en local mais pas depuis Internet
service: réseau
gravite: haute
alertes:
  - "External web check failed: connection timed out"
  - "ERR_CONNECTION_TIMED_OUT"
  - "ERR_CONNECTION_REFUSED"
revision: 2026-10-05
---

## Contexte
Le trafic entrant suit la chaîne : Internet, box opérateur en DMZ, passerelle UniFi (redirection des ports 80 et 443), reverse proxy, service.

## Symptômes
Le service répond sur le réseau local par son adresse IP, mais pas depuis Internet ni depuis un téléphone en 4G. Le navigateur affiche un délai dépassé ou une connexion refusée.

## Impact
Tous les services publiés peuvent être inaccessibles de l'extérieur. Le renouvellement des certificats échoue aussi (voir INC-04).

## Diagnostic
Tester toujours depuis l'extérieur (4G ou outil de test de port en ligne) : depuis le réseau local, l'accès par le nom de domaine peut échouer même quand tout fonctionne. Remonter la chaîne dans l'ordre :
1. DNS : comparer `dig +short <domaine>` avec l'IP publique (`curl -s https://ifconfig.me`).
2. Box : la DMZ est active et pointe vers l'adresse WAN actuelle de la passerelle.
3. Passerelle : les redirections des ports 80 et 443 vers la machine du reverse proxy existent dans UniFi Network et sont actives.
4. Machine du reverse proxy : le proxy écoute bien, `sudo ss -tlnp | grep -E ':80 |:443 '`
5. Pare-feu de la machine : `sudo ufw status`

## Actions
Corriger le premier maillon défaillant trouvé, puis retester depuis l'extérieur avant de passer au suivant :
1. DNS obsolète : corriger l'enregistrement A chez le registrar.
2. DMZ incorrecte : corriger la cible et créer un bail DHCP statique pour la passerelle.
3. Redirection absente : la recréer vers l'IP fixe de la machine du reverse proxy.
4. Proxy qui n'écoute pas : `docker compose up -d <proxy>` et lire ses logs.
5. Pare-feu : autoriser 80 et 443 (`sudo ufw allow 80,443/tcp`).

## Escalade
Escalader au N2 réseau si la chaîne paraît correcte mais que le test extérieur échoue toujours.

## Vérification
Depuis un téléphone en 4G, `https://<domaine>` s'affiche correctement.
