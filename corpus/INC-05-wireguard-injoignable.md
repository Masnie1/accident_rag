---
id: INC-05
titre: Le VPN WireGuard ne monte pas, plus d'accès d'administration à distance
service: réseau
gravite: moyenne
alertes:
  - "Handshake did not complete after 5 seconds, retrying"
  - "WireGuard peer latest handshake older than 3 minutes"
  - "VPN tunnel down"
revision: 2026-10-05
---

## Contexte
L'accès d'administration à distance passe par le serveur WireGuard intégré à la passerelle UniFi. La box opérateur est en mode DMZ et redirige tout le trafic entrant vers le port WAN de la passerelle.

## Symptômes
Le client WireGuard se dit connecté mais aucun trafic ne passe. Les interfaces d'administration restent injoignables depuis l'extérieur.

## Impact
Aucune intervention à distance n'est possible. Les services publics ne sont pas forcément touchés.

## Diagnostic
1. Côté client Linux, regarder la dernière poignée de main : `sudo wg show`. Une ligne `latest handshake` absente ou de plus de 2 minutes indique un tunnel non établi.
2. Tester depuis un autre réseau (partage 4G) pour écarter un réseau client qui bloque l'UDP (Wi-Fi d'hôtel, réseau d'entreprise).
3. Vérifier que l'adresse `Endpoint` du client correspond à l'IP publique actuelle.
4. Sur la box, vérifier que la DMZ est active et pointe vers l'adresse WAN actuelle de la passerelle. Si la passerelle reçoit son adresse en DHCP, elle a pu changer.
5. Dans l'interface UniFi Network, vérifier que le serveur VPN est actif et noter son port UDP.

## Actions
1. Réseau client qui bloque l'UDP : passer en partage 4G.
2. Endpoint obsolète : mettre à jour l'adresse dans la configuration du client.
3. DMZ qui pointe vers une ancienne adresse : corriger la cible de la DMZ, puis créer un bail DHCP statique pour la passerelle sur la box afin que le problème ne revienne pas.
4. En dernier recours seulement, régénérer la configuration client depuis UniFi : l'ancienne clé devient alors invalide sur tous les appareils.

## Escalade
Escalader au N2 réseau. Ne jamais ouvrir un port d'administration sur Internet pour contourner la panne du VPN.

## Vérification
`sudo wg show` affiche une poignée de main de moins de 2 minutes, et `ping <ip_lan_passerelle>` répond depuis le client.
