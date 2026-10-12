---
id: INC-06
titre: Le serveur de calcul ne se réveille pas (Wake-on-LAN)
service: système
gravite: moyenne
alertes:
  - "Host is unreachable: ICMP ping timeout"
  - "Wake-on-LAN packet sent, host did not respond"
  - "Waiting for upstream server to wake up"
revision: 2026-10-05
---

## Contexte
Le nœud frontal reste allumé en permanence. Le serveur de calcul est éteint par défaut et le nœud frontal le démarre par Wake-on-LAN quand une requête vise un service qui y est hébergé.

## Symptômes
Les services hébergés sur le serveur de calcul restent injoignables. La page d'attente ne se termine jamais. Le serveur reste éteint.

## Impact
Les services du serveur de calcul sont indisponibles. Les services du nœud frontal fonctionnent.

## Diagnostic
1. Depuis le nœud frontal : `ping -c 3 <ip_serveur>`
2. Envoyer un paquet de réveil à la main : `wakeonlan <adresse_mac>` (paquet `wakeonlan`), ou `sudo etherwake -i <interface> <adresse_mac>`.
3. Si le réveil manuel fonctionne, c'est le mécanisme de déclenchement du nœud frontal qui est en cause : lire ses logs (`journalctl -u <service_reveil> -n 50` ou `docker logs <conteneur_reveil>`).
4. Si le réveil manuel échoue, démarrer le serveur physiquement puis vérifier la carte réseau : `sudo ethtool <interface> | grep Wake-on`. `Wake-on: g` est attendu, `Wake-on: d` signifie désactivé.
5. Après une coupure de courant, certains BIOS désactivent le Wake-on-LAN : vérifier l'option « Wake on LAN » ou « Power On By PCI-E ».

## Actions
1. Mécanisme de déclenchement en panne : redémarrer le service de réveil du nœud frontal et vérifier qu'il démarre sans erreur.
2. Carte réseau en `Wake-on: d` : l'activer avec `sudo ethtool -s <interface> wol g`, puis rendre le réglage permanent dans netplan en ajoutant `wakeonlan: true` sous l'interface, et appliquer avec `sudo netplan apply`.
3. Option BIOS désactivée : la réactiver.

## Escalade
Escalader au N2 système si le serveur ne démarre pas, même physiquement.

## Vérification
Éteindre proprement le serveur (`sudo shutdown -h now`), envoyer un paquet de réveil : le serveur répond au ping en moins de 90 secondes et le service redevient accessible.
